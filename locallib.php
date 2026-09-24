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
 * Internal helpers for the mod_mlarena plugin.
 *
 * @package    mod_mlarena
 * @copyright  2026 ML-Arena <contact@ml-arena.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/** Display the activity as a Moodle landing page with a launch button. */
define('MLARENA_DISPLAY_PAGE', 0);

/** Display the ML-Arena page embedded in an iframe. */
define('MLARENA_DISPLAY_EMBED', 1);

/**
 * Reference type: an ML-Arena challenge.
 *
 * The stored value (and the `competitionid` field that goes with it) keeps the
 * name ML-Arena used before it renamed competitions to challenges, so existing
 * activities and backups stay valid without an upgrade step.
 */
define('MLARENA_REF_COMPETITION', 'competition');

/** Reference type: an ML-Arena academic course (join code). */
define('MLARENA_REF_COURSE', 'course');

/**
 * Return the configured ML-Arena base URL (no trailing slash).
 *
 * @return string
 */
function mlarena_get_base_url() {
    $baseurl = get_config('mod_mlarena', 'baseurl');
    if (empty($baseurl)) {
        // Sensible default so the plugin works out of the box against production.
        $baseurl = 'https://ml-arena.com';
    }
    return rtrim($baseurl, '/');
}

/**
 * Build the public ML-Arena URL a learner should be sent to for this instance.
 *
 * @param stdClass $mlarena The mlarena instance record.
 * @return moodle_url|null   The target URL, or null if the instance is not fully configured.
 */
function mlarena_get_target_url($mlarena) {
    $base = mlarena_get_base_url();

    if ($mlarena->reftype === MLARENA_REF_COURSE) {
        if (empty($mlarena->joincode)) {
            return null;
        }
        return new moodle_url($base . '/enroll/' . rawurlencode($mlarena->joincode));
    }

    if (empty($mlarena->competitionid)) {
        return null;
    }
    return new moodle_url($base . '/viewchallenge/' . (int)$mlarena->competitionid);
}

/**
 * Return the activity intro rendered as HTML, or the empty string.
 *
 * @param stdClass $mlarena The mlarena instance record.
 * @param stdClass $cm      The course module record.
 * @return string
 */
function mlarena_get_intro($mlarena, $cm) {
    if (empty($mlarena->intro)) {
        return '';
    }
    return format_module_intro('mlarena', $mlarena, $cm->id);
}
