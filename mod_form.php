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
 * Activity settings form for the mod_mlarena plugin.
 *
 * @package    mod_mlarena
 * @copyright  2026 ML-Arena <contact@ml-arena.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/mlarena/locallib.php');

/**
 * The activity settings form.
 *
 * @package    mod_mlarena
 * @copyright  2026 ML-Arena <contact@ml-arena.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_mlarena_mod_form extends moodleform_mod {
    /**
     * Define the form fields.
     */
    public function definition() {
        $mform = $this->_form;

        // General settings.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name'), ['size' => '48']);
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        // What are we linking to?
        $reftypes = [
            MLARENA_REF_COMPETITION => get_string('reftype_challenge', 'mlarena'),
            MLARENA_REF_COURSE => get_string('reftype_course', 'mlarena'),
        ];
        $mform->addElement('select', 'reftype', get_string('reftype', 'mlarena'), $reftypes);
        $mform->setDefault('reftype', MLARENA_REF_COMPETITION);
        $mform->addHelpButton('reftype', 'reftype', 'mlarena');

        // Challenge id (shown for a challenge reference; stored in the competitionid field).
        $mform->addElement('text', 'competitionid', get_string('challengeid', 'mlarena'), ['size' => '10']);
        $mform->setType('competitionid', PARAM_INT);
        $mform->addHelpButton('competitionid', 'challengeid', 'mlarena');
        $mform->hideIf('competitionid', 'reftype', 'neq', MLARENA_REF_COMPETITION);

        // Join code (shown for course reference).
        $mform->addElement('text', 'joincode', get_string('joincode', 'mlarena'), ['size' => '24']);
        $mform->setType('joincode', PARAM_ALPHANUMEXT);
        $mform->addHelpButton('joincode', 'joincode', 'mlarena');
        $mform->hideIf('joincode', 'reftype', 'neq', MLARENA_REF_COURSE);

        $this->standard_intro_elements();

        // Appearance.
        $mform->addElement('header', 'appearancehdr', get_string('appearance'));

        $displayoptions = [
            MLARENA_DISPLAY_PAGE => get_string('display_page', 'mlarena'),
            MLARENA_DISPLAY_EMBED => get_string('display_embed', 'mlarena'),
        ];
        $mform->addElement('select', 'display', get_string('displayselect', 'mlarena'), $displayoptions);
        $mform->setDefault('display', get_config('mod_mlarena', 'display'));
        $mform->addHelpButton('display', 'displayselect', 'mlarena');

        $mform->addElement('text', 'iframeheight', get_string('iframeheight', 'mlarena'), ['size' => '6']);
        $mform->setType('iframeheight', PARAM_INT);
        $mform->setDefault('iframeheight', get_config('mod_mlarena', 'iframeheight'));
        $mform->hideIf('iframeheight', 'display', 'neq', MLARENA_DISPLAY_EMBED);

        // Leaderboard.
        $mform->addElement('header', 'leaderboardhdr', get_string('leaderboardheader', 'mlarena'));

        $mform->addElement('advcheckbox', 'showleaderboard', get_string('showleaderboard', 'mlarena'));
        $mform->setDefault('showleaderboard', get_config('mod_mlarena', 'showleaderboard'));
        $mform->addHelpButton('showleaderboard', 'showleaderboard', 'mlarena');
        $mform->hideIf('showleaderboard', 'reftype', 'neq', MLARENA_REF_COMPETITION);

        $mform->addElement('text', 'leaderboardcourseid', get_string('leaderboardcourseid', 'mlarena'), ['size' => '10']);
        $mform->setType('leaderboardcourseid', PARAM_INT);
        $mform->addHelpButton('leaderboardcourseid', 'leaderboardcourseid', 'mlarena');
        $mform->hideIf('leaderboardcourseid', 'reftype', 'neq', MLARENA_REF_COMPETITION);
        $mform->hideIf('leaderboardcourseid', 'showleaderboard', 'notchecked');

        // Standard course module elements.
        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    /**
     * Server-side validation.
     *
     * @param array $data  Submitted values.
     * @param array $files Submitted files.
     * @return array Errors keyed by element name.
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if ($data['reftype'] === MLARENA_REF_COURSE) {
            if (trim($data['joincode'] ?? '') === '') {
                $errors['joincode'] = get_string('error_joincode_required', 'mlarena');
            }
        } else {
            if (empty($data['competitionid']) || (int)$data['competitionid'] <= 0) {
                $errors['competitionid'] = get_string('error_challengeid_required', 'mlarena');
            }
        }

        if ((int)$data['display'] === MLARENA_DISPLAY_EMBED && (int)$data['iframeheight'] < 200) {
            $errors['iframeheight'] = get_string('error_iframeheight', 'mlarena');
        }

        return $errors;
    }
}
