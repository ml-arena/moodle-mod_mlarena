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
 * English strings for the mod_mlarena plugin.
 *
 * @package    mod_mlarena
 * @copyright  2026 ML-Arena <contact@ml-arena.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Plugin identity.
$string['pluginname'] = 'ML-Arena competition';
$string['modulename'] = 'ML-Arena competition';
$string['modulenameplural'] = 'ML-Arena competitions';
$string['modulename_help'] = 'The ML-Arena activity lets you embed a machine learning competition or academic course hosted on ML-Arena directly in your course.

Add the activity, choose whether you are linking a competition or a course, and enter the competition id or course join code from ML-Arena. Students then open (or view embedded) the competition from within Moodle, and you can optionally show the live leaderboard on the activity page.';
$string['modulename_link'] = 'mod/mlarena/view';
$string['pluginadministration'] = 'ML-Arena activity administration';

// Capabilities.
$string['mlarena:addinstance'] = 'Add a new ML-Arena activity';
$string['mlarena:view'] = 'View an ML-Arena activity';

// Form: reference.
$string['reftype'] = 'Link type';
$string['reftype_help'] = 'Choose what this activity points to:

* **Competition** - a single ML-Arena competition, identified by its numeric id.
* **Course** - an ML-Arena academic course that students self-enrol into with a join code.';
$string['reftype_competition'] = 'Competition';
$string['reftype_course'] = 'Course (join code)';
$string['competitionid'] = 'Competition id';
$string['competitionid_help'] = 'The numeric id of the ML-Arena competition. You can find it in the competition URL, for example in https://ml-arena.com/viewcompetition/42 the id is 42.';
$string['joincode'] = 'Course join code';
$string['joincode_help'] = 'The join code (or enrolment link) of the ML-Arena academic course. Students visiting the activity are sent to the ML-Arena enrolment page for this code.';

// Form: appearance.
$string['displayselect'] = 'Display';
$string['displayselect_help'] = 'How the competition or course is shown:

* **Landing page** - a page in Moodle with a button that opens ML-Arena in a new tab.
* **Embedded** - the ML-Arena page is shown inside an iframe on the activity page. This only works if ML-Arena allows itself to be framed; otherwise use the landing page.';
$string['display_page'] = 'Landing page with launch button';
$string['display_embed'] = 'Embedded in the page';
$string['iframeheight'] = 'Embed height (pixels)';
$string['iframeheight_help'] = 'Height in pixels of the embedded iframe when the display type is set to embedded.';

// Form: leaderboard.
$string['leaderboardheader'] = 'Leaderboard';
$string['showleaderboard'] = 'Show leaderboard on the activity page';
$string['showleaderboard_help'] = 'When enabled, the public ML-Arena leaderboard for the competition is fetched and displayed on the activity page. Only available for competition links.';
$string['leaderboardcourseid'] = 'ML-Arena course id (optional)';
$string['leaderboardcourseid_help'] = 'If set, the leaderboard is filtered to the students enrolled in this ML-Arena course. Leave empty to show the full public leaderboard.';

// Form: validation errors.
$string['error_competitionid_required'] = 'Enter a valid numeric competition id.';
$string['error_joincode_required'] = 'Enter the course join code.';
$string['error_iframeheight'] = 'Enter an embed height of at least 200 pixels.';

// View page.
$string['opencompetition'] = 'Open competition on ML-Arena';
$string['opencourse'] = 'Open course on ML-Arena';
$string['joincodehint'] = 'Use the join code {$a} to enrol on ML-Arena.';
$string['embedfallback'] = 'Not seeing the competition? Open it directly: {$a}';
$string['notconfigured'] = 'This ML-Arena activity has not been fully configured yet. A teacher needs to edit it and set the competition id or course join code.';

// Leaderboard rendering.
$string['leaderboard'] = 'Leaderboard';
$string['leaderboardempty'] = 'No entries on the leaderboard yet.';
$string['leaderboardunavailable'] = 'The leaderboard could not be loaded right now. Please try again later.';
$string['rank'] = 'Rank';
$string['participant'] = 'Participant';
$string['agent'] = 'Agent';
$string['episodes'] = 'Episodes';
$string['score'] = 'Score';
$string['elo'] = 'Elo';

// Index page.
$string['noinstances'] = 'There are no ML-Arena activities in this course.';

// Admin settings.
$string['setting_baseurl'] = 'ML-Arena base URL';
$string['setting_baseurl_desc'] = 'Base URL of the ML-Arena platform used to build links and to call the public API. The default is https://ml-arena.com.';
$string['setting_requesttimeout'] = 'API request timeout (seconds)';
$string['setting_requesttimeout_desc'] = 'Maximum time in seconds to wait for the ML-Arena API when fetching competition details or the leaderboard.';

// Privacy.
$string['privacy:metadata:mlarena_platform'] = 'This activity links to the external ML-Arena platform. Moodle does not automatically send any personal data to ML-Arena; when a learner chooses to open a competition or course, they interact with ML-Arena directly and any account they use there is governed by ML-Arena\'s own privacy policy. The plugin only reads public competition and leaderboard information from the ML-Arena API.';
$string['privacy:metadata:mlarena_platform:querystring'] = 'When a course-scoped leaderboard is shown, the ML-Arena course id (not any Moodle user data) is sent to the public leaderboard API.';
