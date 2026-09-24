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

namespace mod_mlarena\output;

use mod_mlarena\local\client;
use html_writer;
use moodle_url;

/**
 * Renderer for the mod_mlarena plugin.
 *
 * @package    mod_mlarena
 * @copyright  2026 ML-Arena <contact@ml-arena.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends \plugin_renderer_base {
    /**
     * Render the whole activity page body.
     *
     * @param \stdClass  $mlarena   The instance record.
     * @param \stdClass  $cm        The course module record.
     * @param moodle_url $targeturl The public ML-Arena URL for this instance.
     * @return string HTML.
     */
    public function render_activity($mlarena, $cm, moodle_url $targeturl): string {
        global $CFG;
        require_once($CFG->dirroot . '/mod/mlarena/locallib.php');

        $out = '';

        $intro = mlarena_get_intro($mlarena, $cm);
        if ($intro !== '') {
            $out .= html_writer::div($intro, 'mod-mlarena-intro');
        }

        // Optional challenge metadata (name, description, thumbnail).
        if ($mlarena->reftype === MLARENA_REF_COMPETITION && !empty($mlarena->competitionid)) {
            $out .= $this->render_challenge_card((int)$mlarena->competitionid);
        }

        // Launch button or embedded iframe.
        if ((int)$mlarena->display === MLARENA_DISPLAY_EMBED) {
            $out .= $this->render_embed($mlarena, $targeturl);
        } else {
            $out .= $this->render_launch($mlarena, $targeturl);
        }

        // Optional leaderboard.
        if (!empty($mlarena->showleaderboard) && !empty($mlarena->competitionid)) {
            $out .= $this->render_leaderboard(
                (int)$mlarena->competitionid,
                $mlarena->leaderboardcourseid ? (int)$mlarena->leaderboardcourseid : null
            );
        }

        return html_writer::div($out, 'mod-mlarena');
    }

    /**
     * Render the launch call to action.
     *
     * @param \stdClass  $mlarena
     * @param moodle_url $targeturl
     * @return string HTML.
     */
    protected function render_launch($mlarena, moodle_url $targeturl): string {
        $label = $mlarena->reftype === MLARENA_REF_COURSE
            ? get_string('opencourse', 'mlarena')
            : get_string('openchallenge', 'mlarena');

        $button = html_writer::link($targeturl, $label, [
            'class' => 'btn btn-primary btn-lg',
            'target' => '_blank',
            'rel' => 'noopener noreferrer',
        ]);

        $body = html_writer::div($button, 'mod-mlarena-launch mt-2 mb-2');

        // For a course reference, surface the join code so learners can self-enrol.
        if ($mlarena->reftype === MLARENA_REF_COURSE && !empty($mlarena->joincode)) {
            $code = html_writer::tag('code', s($mlarena->joincode), ['class' => 'mod-mlarena-joincode']);
            $body .= html_writer::div(
                get_string('joincodehint', 'mlarena', $code),
                'mod-mlarena-joincode-hint text-muted'
            );
        }

        return $body;
    }

    /**
     * Render an embedded iframe with a launch fallback.
     *
     * @param \stdClass  $mlarena
     * @param moodle_url $targeturl
     * @return string HTML.
     */
    protected function render_embed($mlarena, moodle_url $targeturl): string {
        $height = (int)$mlarena->iframeheight > 0 ? (int)$mlarena->iframeheight : 720;

        $iframe = html_writer::tag('iframe', '', [
            'src' => $targeturl->out(false),
            'width' => '100%',
            'height' => $height,
            'class' => 'mod-mlarena-iframe',
            'title' => format_string($mlarena->name),
            'allowfullscreen' => 'allowfullscreen',
            'loading' => 'lazy',
        ]);

        $fallback = html_writer::div(
            get_string('embedfallback', 'mlarena', html_writer::link($targeturl, $targeturl->out(false), [
                'target' => '_blank',
                'rel' => 'noopener noreferrer',
            ])),
            'mod-mlarena-embed-fallback text-muted mt-1'
        );

        return html_writer::div($iframe . $fallback, 'mod-mlarena-embed');
    }

    /**
     * Render a small metadata card for a challenge, if the API responds.
     *
     * @param int $challengeid
     * @return string HTML (empty when metadata is unavailable).
     */
    protected function render_challenge_card(int $challengeid): string {
        $client = new client();
        $challenge = $client->get_challenge($challengeid);
        if (empty($challenge) || $challenge['name'] === '') {
            return '';
        }

        $title = html_writer::tag('h3', s($challenge['name']), ['class' => 'mod-mlarena-comp-title']);

        $body = '';
        if (!empty($challenge['miniature'])) {
            // The API serves `miniature` as a path on the ML-Arena site, not on this Moodle site.
            $img = html_writer::empty_tag('img', [
                'src' => (new moodle_url(mlarena_get_base_url() . $challenge['miniature']))->out(false),
                'alt' => s($challenge['name']),
                'class' => 'mod-mlarena-comp-thumb',
            ]);
            $body .= html_writer::div($img, 'mod-mlarena-comp-thumb-wrap');
        }
        if (!empty($challenge['description'])) {
            // Description is plain text from the API; escape and preserve line breaks.
            $body .= html_writer::div(nl2br(s($challenge['description'])), 'mod-mlarena-comp-desc');
        }

        return html_writer::div($title . $body, 'mod-mlarena-comp-card card p-3 mb-3');
    }

    /**
     * Render the challenge leaderboard as a table.
     *
     * Reads the backend's leaderboard envelope: the ranking settings from its
     * `challenge` block, the rows from `leaders`.
     *
     * @param int      $challengeid
     * @param int|null $mlarenacourseid Optional ML-Arena course filter.
     * @return string HTML.
     */
    protected function render_leaderboard(int $challengeid, ?int $mlarenacourseid): string {
        $client = new client();
        $envelope = $client->get_leaderboard($challengeid, $mlarenacourseid);

        $heading = html_writer::tag('h3', get_string('leaderboard', 'mlarena'), ['class' => 'mod-mlarena-lb-title mt-3']);

        if ($envelope === null) {
            return $heading . $this->output->notification(get_string('leaderboardunavailable', 'mlarena'), 'warning');
        }
        $rows = $envelope['leaders'];
        if (count($rows) === 0) {
            return $heading . html_writer::div(get_string('leaderboardempty', 'mlarena'), 'text-muted');
        }

        // Metric label and precision are the challenge's, served once on the envelope.
        $challenge = $envelope['challenge'];
        $iselo = !empty($challenge['is_elo_score']);
        $metriclabel = $iselo
            ? get_string('elo', 'mlarena')
            : (!empty($challenge['metric']) ? s($challenge['metric']) : get_string('score', 'mlarena'));
        $precision = (int)$challenge['frontend_precision'];

        $table = new \html_table();
        $table->head = [
            get_string('rank', 'mlarena'),
            get_string('participant', 'mlarena'),
            get_string('submission', 'mlarena'),
            $metriclabel,
            get_string('episodes', 'mlarena'),
        ];
        $table->attributes['class'] = 'generaltable mod-mlarena-leaderboard';

        foreach ($rows as $row) {
            $score = $iselo ? ($row['elo_score'] ?? null) : ($row['mean_reward'] ?? null);
            $scorecell = is_numeric($score) ? format_float((float)$score, $precision) : '-';

            $table->data[] = [
                (int)$row['rank'],
                !empty($row['team_name']) ? s($row['team_name']) : s($row['username']),
                s($row['submission_name']),
                $scorecell,
                (int)$row['n_episodes_total'],
            ];
        }

        $out = $heading . html_writer::table($table);
        if ($envelope['total'] > count($rows)) {
            $out .= html_writer::div(
                get_string('leaderboardtruncated', 'mlarena', (object)['shown' => count($rows), 'total' => $envelope['total']]),
                'mod-mlarena-lb-truncated text-muted'
            );
        }
        return $out;
    }
}
