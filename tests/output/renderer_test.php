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

namespace mod_mlarena\output;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/mlarena/locallib.php');
require_once($CFG->libdir . '/filelib.php');

/**
 * Unit tests for the activity renderer against the ML-Arena API payloads.
 *
 * @package    mod_mlarena
 * @category   test
 * @copyright  2026 ML-Arena <contact@ml-arena.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_mlarena\output\renderer
 */
final class renderer_test extends \advanced_testcase {
    /**
     * The activity page shows the challenge card and the leaderboard read from the envelope.
     */
    public function test_render_activity_with_leaderboard(): void {
        global $CFG, $PAGE;
        $this->resetAfterTest();
        set_config('baseurl', 'https://ml-arena.example', 'mod_mlarena');

        $course = $this->getDataGenerator()->create_course();
        $mlarena = $this->getDataGenerator()->create_module('mlarena', [
            'course' => $course->id,
            'reftype' => MLARENA_REF_COMPETITION,
            'competitionid' => 42,
            'showleaderboard' => 1,
        ]);
        $cm = get_coursemodule_from_instance('mlarena', $mlarena->id);

        $json = file_get_contents($CFG->dirroot . '/mod/mlarena/tests/fixtures/leaderboard_envelope.json');
        $envelope = json_decode($json, true);
        $envelope['total'] = 250;
        // Responses are served last-in first-out: the challenge is fetched first.
        \curl::mock_response(json_encode($envelope));
        \curl::mock_response(json_encode([
            'id' => 42,
            'name' => 'Lunar Lander',
            'description' => 'Land softly.',
            'miniature' => '/api/challenge_asset/42/image/miniature',
        ]));

        $PAGE->set_url('/mod/mlarena/view.php', ['id' => $cm->id]);
        $renderer = $PAGE->get_renderer('mod_mlarena');
        $html = $renderer->render_activity($mlarena, $cm, mlarena_get_target_url($mlarena));

        $this->assertStringContainsString('Lunar Lander', $html);
        $this->assertStringContainsString('https://ml-arena.example/api/challenge_asset/42/image/miniature', $html);
        $this->assertStringContainsString('https://ml-arena.example/viewchallenge/42', $html);
        $this->assertStringContainsString('Accuracy', $html);
        $this->assertStringContainsString('alice', $html);
        $this->assertStringContainsString('baseline-v2', $html);
        $this->assertStringContainsString(format_float(0.91234, 3), $html);
        $truncated = get_string('leaderboardtruncated', 'mlarena', (object)['shown' => 1, 'total' => 250]);
        $this->assertStringContainsString($truncated, $html);
    }
}
