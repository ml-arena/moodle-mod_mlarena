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

/**
 * Defines the restore task for the mod_mlarena plugin.
 *
 * @package    mod_mlarena
 * @copyright  2026 ML-Arena <contact@ml-arena.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/mlarena/backup/moodle2/restore_mlarena_stepslib.php');

/**
 * Restore task that provides all the settings and steps to perform one complete
 * restore of the activity.
 */
class restore_mlarena_activity_task extends restore_activity_task {
    /**
     * No particular settings for this activity.
     */
    protected function define_my_settings() {
    }

    /**
     * Define the single restore structure step.
     */
    protected function define_my_steps() {
        $this->add_step(new restore_mlarena_activity_structure_step('mlarena_structure', 'mlarena.xml'));
    }

    /**
     * Define the contents in the activity that must be processed by the link decoder.
     *
     * @return array
     */
    public static function define_decode_contents() {
        $contents = [];
        $contents[] = new restore_decode_content('mlarena', ['intro'], 'mlarena');
        return $contents;
    }

    /**
     * Define the decoding rules for links belonging to the activity.
     *
     * @return array
     */
    public static function define_decode_rules() {
        $rules = [];
        $rules[] = new restore_decode_rule('MLARENAINDEX', '/mod/mlarena/index.php?id=$1', 'course');
        $rules[] = new restore_decode_rule('MLARENAVIEWBYID', '/mod/mlarena/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('MLARENAVIEWBYM', '/mod/mlarena/view.php?m=$1', 'mlarena');
        return $rules;
    }

    /**
     * Define the restore log rules for this activity.
     *
     * @return array
     */
    public static function define_restore_log_rules() {
        $rules = [];
        $rules[] = new restore_log_rule('mlarena', 'add', 'view.php?id={course_module}', '{mlarena}');
        $rules[] = new restore_log_rule('mlarena', 'update', 'view.php?id={course_module}', '{mlarena}');
        $rules[] = new restore_log_rule('mlarena', 'view', 'view.php?id={course_module}', '{mlarena}');
        return $rules;
    }

    /**
     * Define the restore log rules applied when restoring course logs.
     *
     * @return array
     */
    public static function define_restore_log_rules_for_course() {
        $rules = [];
        $rules[] = new restore_log_rule('mlarena', 'view all', 'index.php?id={course}', null);
        return $rules;
    }
}
