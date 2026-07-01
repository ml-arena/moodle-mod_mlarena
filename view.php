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
 * Learner-facing page for a mod_mlarena instance.
 *
 * @package    mod_mlarena
 * @copyright  2026 ML-Arena <contact@ml-arena.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot . '/mod/mlarena/lib.php');
require_once($CFG->dirroot . '/mod/mlarena/locallib.php');
require_once($CFG->libdir . '/completionlib.php');

$id = optional_param('id', 0, PARAM_INT);       // Course module id.
$m  = optional_param('m', 0, PARAM_INT);         // The mlarena instance id.

if ($m) {
    $mlarena = $DB->get_record('mlarena', ['id' => $m], '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('mlarena', $mlarena->id, $mlarena->course, false, MUST_EXIST);
} else {
    $cm = get_coursemodule_from_id('mlarena', $id, 0, false, MUST_EXIST);
    $mlarena = $DB->get_record('mlarena', ['id' => $cm->instance], '*', MUST_EXIST);
}

$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/mlarena:view', $context);

// Completion and events.
mlarena_view($mlarena, $course, $cm, $context);

$PAGE->set_url('/mod/mlarena/view.php', ['id' => $cm->id]);
$PAGE->set_title($course->shortname . ': ' . format_string($mlarena->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_activity_record($mlarena);

$renderer = $PAGE->get_renderer('mod_mlarena');
$targeturl = mlarena_get_target_url($mlarena);

echo $OUTPUT->header();

if ($targeturl === null) {
    // The activity was saved without a usable reference (should not normally happen).
    echo $OUTPUT->notification(get_string('notconfigured', 'mlarena'), 'error');
    echo $OUTPUT->footer();
    die;
}

echo $renderer->render_activity($mlarena, $cm, $targeturl);

echo $OUTPUT->footer();
