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
 * Defines the backup task for the mod_mlarena plugin.
 *
 * @package    mod_mlarena
 * @copyright  2026 ML-Arena <contact@ml-arena.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/mlarena/backup/moodle2/backup_mlarena_stepslib.php');

/**
 * Provides all the settings and steps to perform one complete backup of the activity.
 */
class backup_mlarena_activity_task extends backup_activity_task {
    /**
     * No specific settings for this activity.
     */
    protected function define_my_settings() {
    }

    /**
     * Define the backup step that stores the instance data in the mlarena.xml file.
     */
    protected function define_my_steps() {
        $this->add_step(new backup_mlarena_activity_structure_step('mlarena_structure', 'mlarena.xml'));
    }

    /**
     * Encode links to the index.php and view.php scripts.
     *
     * @param string $content HTML that may contain links to this activity's scripts.
     * @return string The content with the links encoded.
     */
    public static function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot . '/mod/mlarena', '#');

        // Link to the course index of instances.
        $content = preg_replace(
            '#(' . $base . '/index\.php\?id=)([0-9]+)#',
            '$@MLARENAINDEX*$2@$',
            $content
        );

        // Link to a view by course module id.
        $content = preg_replace(
            '#(' . $base . '/view\.php\?id=)([0-9]+)#',
            '$@MLARENAVIEWBYID*$2@$',
            $content
        );

        // Link to a view by instance id.
        $content = preg_replace(
            '#(' . $base . '/view\.php\?m=)([0-9]+)#',
            '$@MLARENAVIEWBYM*$2@$',
            $content
        );

        return $content;
    }
}
