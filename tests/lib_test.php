<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace mod_mlarena;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/mlarena/lib.php');
require_once($CFG->dirroot . '/mod/mlarena/locallib.php');

/**
 * Unit tests for the mod_mlarena library functions.
 *
 * @package    mod_mlarena
 * @category   test
 * @copyright  2026 ML-Arena <contact@ml-arena.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mlarena_supports
 */
final class lib_test extends \advanced_testcase {
    /**
     * The module should declare the features an interactive-content activity needs.
     */
    public function test_supports(): void {
        $this->assertTrue(mlarena_supports(FEATURE_MOD_INTRO));
        $this->assertTrue(mlarena_supports(FEATURE_BACKUP_MOODLE2));
        $this->assertTrue(mlarena_supports(FEATURE_COMPLETION_TRACKS_VIEWS));
        $this->assertFalse(mlarena_supports(FEATURE_GRADE_HAS_GRADE));
        $this->assertSame(MOD_PURPOSE_INTERACTIVECONTENT, mlarena_supports(FEATURE_MOD_PURPOSE));
        $this->assertNull(mlarena_supports('a feature that does not exist'));
    }

    /**
     * Creating, updating and deleting an instance should round-trip through the DB.
     */
    public function test_add_update_delete_instance(): void {
        global $DB;
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $module = $this->getDataGenerator()->create_module('mlarena', [
            'course' => $course->id,
            'name' => 'Test challenge',
            'reftype' => 'competition',
            'competitionid' => 42,
        ]);

        $record = $DB->get_record('mlarena', ['id' => $module->id], '*', MUST_EXIST);
        $this->assertSame('competition', $record->reftype);
        $this->assertEquals(42, $record->competitionid);
        $this->assertNull($record->joincode);

        $record->instance = $record->id;
        $record->coursemodule = get_coursemodule_from_instance('mlarena', $record->id)->id;
        $record->reftype = 'course';
        $record->joincode = 'ABC123XY';
        $this->assertTrue(mlarena_update_instance($record));

        $updated = $DB->get_record('mlarena', ['id' => $module->id], '*', MUST_EXIST);
        $this->assertSame('course', $updated->reftype);
        $this->assertSame('ABC123XY', $updated->joincode);
        // Challenge-only fields are cleared when switching to a course link.
        $this->assertNull($updated->competitionid);

        $this->assertTrue(mlarena_delete_instance($module->id));
        $this->assertFalse($DB->record_exists('mlarena', ['id' => $module->id]));
    }

    /**
     * The public target URL should be derived from the reference type.
     */
    public function test_get_target_url(): void {
        set_config('baseurl', 'https://ml-arena.com', 'mod_mlarena');

        $challenge = (object)[
            'reftype' => 'competition',
            'competitionid' => 42,
            'joincode' => null,
        ];
        $this->assertSame(
            'https://ml-arena.com/viewchallenge/42',
            mlarena_get_target_url($challenge)->out(false)
        );

        $courselink = (object)[
            'reftype' => 'course',
            'competitionid' => null,
            'joincode' => 'ABC123XY',
        ];
        $this->assertSame(
            'https://ml-arena.com/enroll/ABC123XY',
            mlarena_get_target_url($courselink)->out(false)
        );

        $unconfigured = (object)[
            'reftype' => 'competition',
            'competitionid' => null,
            'joincode' => null,
        ];
        $this->assertNull(mlarena_get_target_url($unconfigured));
    }
}
