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
 * Backup steps for the mod_mlarena plugin.
 *
 * @package    mod_mlarena
 * @copyright  2026 ML-Arena <contact@ml-arena.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define the complete mlarena structure for backup, with file and id annotations.
 */
class backup_mlarena_activity_structure_step extends backup_activity_structure_step {
    /**
     * Define the backup structure.
     *
     * @return backup_nested_element
     */
    protected function define_structure() {
        // The mlarena module stores no per-user data.
        $mlarena = new backup_nested_element('mlarena', ['id'], [
            'name', 'intro', 'introformat', 'reftype', 'competitionid', 'joincode',
            'display', 'iframeheight', 'showleaderboard', 'leaderboardcourseid', 'timemodified',
        ]);

        // Define sources.
        $mlarena->set_source_table('mlarena', ['id' => backup::VAR_ACTIVITYID]);

        // Define file annotations (intro editor files).
        $mlarena->annotate_files('mod_mlarena', 'intro', null);

        // Return the root element (mlarena), wrapped into the standard activity structure.
        return $this->prepare_activity_structure($mlarena);
    }
}
