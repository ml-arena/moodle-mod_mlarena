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
 * Site-wide settings and modedit defaults for the mod_mlarena plugin.
 *
 * @package    mod_mlarena
 * @copyright  2026 ML-Arena <contact@ml-arena.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/mlarena/locallib.php');

if ($ADMIN->fulltree) {

    // Connection settings.
    $settings->add(new admin_setting_configtext(
        'mod_mlarena/baseurl',
        get_string('setting_baseurl', 'mlarena'),
        get_string('setting_baseurl_desc', 'mlarena'),
        'https://ml-arena.com',
        PARAM_URL
    ));

    $settings->add(new admin_setting_configtext(
        'mod_mlarena/requesttimeout',
        get_string('setting_requesttimeout', 'mlarena'),
        get_string('setting_requesttimeout_desc', 'mlarena'),
        10,
        PARAM_INT,
        4
    ));

    // Modedit defaults.
    $settings->add(new admin_setting_heading(
        'mod_mlarena/defaultshdr',
        get_string('modeditdefaults', 'admin'),
        get_string('condifmodeditdefaults', 'admin')
    ));

    $displayoptions = [
        MLARENA_DISPLAY_PAGE => get_string('display_page', 'mlarena'),
        MLARENA_DISPLAY_EMBED => get_string('display_embed', 'mlarena'),
    ];
    $settings->add(new admin_setting_configselect(
        'mod_mlarena/display',
        get_string('displayselect', 'mlarena'),
        get_string('displayselect_help', 'mlarena'),
        MLARENA_DISPLAY_PAGE,
        $displayoptions
    ));

    $settings->add(new admin_setting_configtext(
        'mod_mlarena/iframeheight',
        get_string('iframeheight', 'mlarena'),
        get_string('iframeheight_help', 'mlarena'),
        720,
        PARAM_INT,
        6
    ));

    $settings->add(new admin_setting_configcheckbox(
        'mod_mlarena/showleaderboard',
        get_string('showleaderboard', 'mlarena'),
        get_string('showleaderboard_help', 'mlarena'),
        0
    ));
}
