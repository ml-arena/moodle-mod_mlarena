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

namespace mod_mlarena\local;

/**
 * Thin read-only client for the public ML-Arena REST API.
 *
 * Only unauthenticated GET endpoints are used, so no token or personal data
 * ever leaves the Moodle site through this client. Responses are cached in a
 * short-lived application cache to avoid hammering the API when many learners
 * open the same activity at once.
 *
 * @package    mod_mlarena
 * @copyright  2026 ML-Arena <contact@ml-arena.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class client {
    /** @var string Base URL of the ML-Arena site, without a trailing slash. */
    protected $baseurl;

    /** @var int Request timeout in seconds. */
    protected $timeout;

    /**
     * Constructor.
     *
     * @param string|null $baseurl Base URL override. Falls back to the plugin setting.
     */
    public function __construct(?string $baseurl = null) {
        global $CFG;
        require_once($CFG->dirroot . '/mod/mlarena/locallib.php');

        $this->baseurl = $baseurl !== null ? rtrim($baseurl, '/') : mlarena_get_base_url();
        $timeout = (int)get_config('mod_mlarena', 'requesttimeout');
        $this->timeout = $timeout > 0 ? $timeout : 10;
    }

    /** @var int Leaderboard rows requested from the API (the envelope's `total` still counts every row). */
    public const LEADERBOARD_LIMIT = 100;

    /**
     * Fetch challenge metadata: GET /api/challenges/{id}.
     *
     * The response is the backend's `ChallengeDetailOut`; this plugin reads
     * `name`, `description` and `miniature` (a path on the ML-Arena site).
     *
     * @param int $challengeid The ML-Arena challenge id.
     * @return array|null Decoded response, or null on failure.
     */
    public function get_challenge(int $challengeid): ?array {
        if ($challengeid <= 0) {
            return null;
        }

        $cache = \cache::make('mod_mlarena', 'challenge');
        $cached = $cache->get($challengeid);
        if ($cached !== false) {
            return $cached ?: null;
        }

        $data = $this->get_json('/api/challenges/' . $challengeid);
        if ($data !== null && (!isset($data['name']) || !is_string($data['name']))) {
            debugging('mod_mlarena: unexpected payload from /api/challenges/' . $challengeid . '.', DEBUG_DEVELOPER);
            $data = null;
        }
        // Store even a null result (as an empty array) to avoid repeated failing calls.
        $cache->set($challengeid, $data ?? []);
        return $data;
    }

    /**
     * Fetch a challenge leaderboard: GET /api/leaderboard/challenge/{id}.
     *
     * The response is the backend's `LeaderboardEnvelope`, one JSON object:
     * `challenge` (the ranking settings every row shares: `is_elo_score`,
     * `metric`, `ranked_order`, `frontend_precision`, ...), `total` (every
     * ranked row), `leaders` (the first rows, snake_case), `me`, `matches`
     * and, with a course filter, `course_context`.
     *
     * The board is requested with `aggregate=user` (one row per participant
     * or team, as the ML-Arena console shows it) and `limit`.
     *
     * @param int      $challengeid The ML-Arena challenge id.
     * @param int|null $mlarenacourseid Optional ML-Arena course id to filter the board to that course's students.
     * @return array|null The envelope, or null on failure.
     */
    public function get_leaderboard(int $challengeid, ?int $mlarenacourseid = null): ?array {
        if ($challengeid <= 0) {
            return null;
        }

        $params = [
            'aggregate' => 'user',
            'limit' => self::LEADERBOARD_LIMIT,
        ];
        if (!empty($mlarenacourseid)) {
            $params['course_id'] = $mlarenacourseid;
        }
        $path = '/api/leaderboard/challenge/' . $challengeid . '?' . http_build_query($params, '', '&');

        $cachekey = $challengeid . '_' . (int)$mlarenacourseid;
        $cache = \cache::make('mod_mlarena', 'leaderboard');
        $cached = $cache->get($cachekey);
        if ($cached !== false) {
            return $cached ?: null;
        }

        $data = $this->get_json($path);
        if ($data !== null && !self::is_leaderboard_envelope($data)) {
            debugging('mod_mlarena: unexpected payload from ' . $path . '.', DEBUG_DEVELOPER);
            $data = null;
        }
        $cache->set($cachekey, $data ?? []);
        return $data;
    }

    /**
     * Whether a decoded response has the leaderboard envelope's shape.
     *
     * @param array $data Decoded response.
     * @return bool
     */
    protected static function is_leaderboard_envelope(array $data): bool {
        return isset($data['challenge']) && is_array($data['challenge'])
            && array_key_exists('is_elo_score', $data['challenge'])
            && isset($data['leaders']) && is_array($data['leaders'])
            && isset($data['total']) && is_int($data['total']);
    }

    /**
     * Perform a GET request and decode the JSON body.
     *
     * Network and decoding errors are swallowed and reported as null so the
     * activity page degrades gracefully rather than crashing; a debugging
     * message is emitted for developers.
     *
     * @param string $path Path beginning with a slash, appended to the base URL.
     * @return array|null Decoded array on success, null otherwise.
     */
    protected function get_json(string $path): ?array {
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');

        $curl = new \curl();
        $response = $curl->get($this->baseurl . $path, [], [
            'CURLOPT_TIMEOUT' => $this->timeout,
            'CURLOPT_CONNECTTIMEOUT' => $this->timeout,
            'CURLOPT_FOLLOWLOCATION' => 0,
        ]);

        $info = $curl->get_info();
        $httpcode = isset($info['http_code']) ? (int)$info['http_code'] : 0;

        if ($curl->get_errno() || $httpcode < 200 || $httpcode >= 300) {
            debugging('mod_mlarena: request to ' . $path . ' failed (http ' . $httpcode . ').', DEBUG_DEVELOPER);
            return null;
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            debugging('mod_mlarena: could not decode response from ' . $path . '.', DEBUG_DEVELOPER);
            return null;
        }

        return $decoded;
    }
}
