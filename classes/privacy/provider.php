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

namespace mod_mlarena\privacy;

use core_privacy\local\metadata\collection;

/**
 * Privacy Subsystem implementation for mod_mlarena.
 *
 * The plugin stores no personal data in Moodle: the mlarena table only holds
 * the teacher's configuration (competition id, join code, display options).
 * It does, however, integrate with the external ML-Arena platform, so the
 * external location link is declared here as required for external services.
 *
 * @package    mod_mlarena
 * @copyright  2026 ML-Arena <contact@ml-arena.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements \core_privacy\local\metadata\provider {
    /**
     * Describe the data this plugin handles.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection The updated collection.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_external_location_link(
            'mlarena_platform',
            [
                'querystring' => 'privacy:metadata:mlarena_platform:querystring',
            ],
            'privacy:metadata:mlarena_platform'
        );

        return $collection;
    }
}
