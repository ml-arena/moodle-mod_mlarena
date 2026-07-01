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
 * Mandatory public API of the mod_mlarena plugin.
 *
 * @package    mod_mlarena
 * @copyright  2026 ML-Arena <contact@ml-arena.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Return the list of features this module supports.
 *
 * @param string $feature FEATURE_xx constant for the requested feature.
 * @return mixed True if module supports the feature, false if not, null if unknown,
 *               or a string (module purpose) for FEATURE_MOD_PURPOSE.
 */
function mlarena_supports($feature) {
    return match ($feature) {
        FEATURE_MOD_ARCHETYPE => MOD_ARCHETYPE_OTHER,
        FEATURE_GROUPS => false,
        FEATURE_GROUPINGS => false,
        FEATURE_MOD_INTRO => true,
        FEATURE_COMPLETION_TRACKS_VIEWS => true,
        FEATURE_GRADE_HAS_GRADE => false,
        FEATURE_GRADE_OUTCOMES => false,
        FEATURE_BACKUP_MOODLE2 => true,
        FEATURE_SHOW_DESCRIPTION => true,
        FEATURE_MOD_PURPOSE => MOD_PURPOSE_INTERACTIVECONTENT,
        default => null,
    };
}

/**
 * Add a new mlarena instance.
 *
 * @param stdClass $data Submitted form data.
 * @param mod_mlarena_mod_form|null $mform The form instance (unused).
 * @return int The id of the newly created instance.
 */
function mlarena_add_instance($data, $mform = null) {
    global $DB;

    $data = mlarena_prepare_record($data);
    $data->timemodified = time();
    $data->id = $DB->insert_record('mlarena', $data);

    $completiontimeexpected = !empty($data->completionexpected) ? $data->completionexpected : null;
    \core_completion\api::update_completion_date_event($data->coursemodule, 'mlarena', $data->id, $completiontimeexpected);

    return $data->id;
}

/**
 * Update an existing mlarena instance.
 *
 * @param stdClass $data Submitted form data.
 * @param mod_mlarena_mod_form|null $mform The form instance (unused).
 * @return bool
 */
function mlarena_update_instance($data, $mform = null) {
    global $DB;

    $data = mlarena_prepare_record($data);
    $data->timemodified = time();
    $data->id = $data->instance;

    $DB->update_record('mlarena', $data);

    $completiontimeexpected = !empty($data->completionexpected) ? $data->completionexpected : null;
    \core_completion\api::update_completion_date_event($data->coursemodule, 'mlarena', $data->id, $completiontimeexpected);

    return true;
}

/**
 * Delete a mlarena instance.
 *
 * @param int $id The instance id to delete.
 * @return bool
 */
function mlarena_delete_instance($id) {
    global $DB;

    if (!$mlarena = $DB->get_record('mlarena', ['id' => $id])) {
        return false;
    }

    $cm = get_coursemodule_from_instance('mlarena', $id);
    if ($cm) {
        \core_completion\api::update_completion_date_event($cm->id, 'mlarena', $id, null);
    }

    $DB->delete_records('mlarena', ['id' => $mlarena->id]);

    return true;
}

/**
 * Normalise submitted form data into a storable record.
 *
 * Fields that do not apply to the chosen reference type are cleared so the
 * stored row is unambiguous.
 *
 * @param stdClass $data Submitted form data.
 * @return stdClass
 */
function mlarena_prepare_record($data) {
    global $CFG;
    require_once($CFG->dirroot . '/mod/mlarena/locallib.php');

    if ($data->reftype === 'course') {
        $data->competitionid = null;
        $data->showleaderboard = 0;
        $data->leaderboardcourseid = null;
    } else {
        $data->reftype = 'competition';
        $data->joincode = null;
    }

    if (empty($data->showleaderboard)) {
        $data->showleaderboard = 0;
        $data->leaderboardcourseid = null;
    }

    if (empty($data->leaderboardcourseid)) {
        $data->leaderboardcourseid = null;
    }

    if ((int)$data->display !== MLARENA_DISPLAY_EMBED) {
        $data->display = MLARENA_DISPLAY_PAGE;
    }

    return $data;
}

/**
 * Return extra information used when this activity is shown in a course listing.
 *
 * @param cm_info|stdClass $coursemodule
 * @return cached_cm_info|null
 */
function mlarena_get_coursemodule_info($coursemodule) {
    global $DB;

    $fields = 'id, name, intro, introformat';
    if (!$mlarena = $DB->get_record('mlarena', ['id' => $coursemodule->instance], $fields)) {
        return null;
    }

    $info = new cached_cm_info();
    $info->name = $mlarena->name;

    if ($coursemodule->showdescription) {
        // Convert intro to html. Do not filter the cached version, filters run at display time.
        $info->content = format_module_intro('mlarena', $mlarena, $coursemodule->id, false);
    }

    return $info;
}

/**
 * Mark the activity as viewed (if completion tracks views) and trigger the
 * course_module_viewed event.
 *
 * @param stdClass $mlarena  The mlarena instance record.
 * @param stdClass $course   The course record.
 * @param stdClass $cm       The course module record.
 * @param context  $context  The module context.
 */
function mlarena_view($mlarena, $course, $cm, $context) {
    $params = [
        'context' => $context,
        'objectid' => $mlarena->id,
    ];

    $event = \mod_mlarena\event\course_module_viewed::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('mlarena', $mlarena);
    $event->trigger();

    $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
}

/**
 * This module stores no per-user data, so course reset has nothing to do.
 *
 * @param stdClass $data The data submitted from the reset course form.
 * @return array
 */
function mlarena_reset_userdata($data) {
    return [];
}
