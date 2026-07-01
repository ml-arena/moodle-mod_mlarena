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

    /**
     * Fetch competition metadata: GET /api/competitions/{id}.
     *
     * @param int $competitionid The ML-Arena competition id.
     * @return array|null Decoded response, or null on failure.
     */
    public function get_competition(int $competitionid): ?array {
        if ($competitionid <= 0) {
            return null;
        }

        $cache = \cache::make('mod_mlarena', 'competition');
        $cached = $cache->get($competitionid);
        if ($cached !== false) {
            return $cached ?: null;
        }

        $data = $this->get_json('/api/competitions/' . $competitionid);
        // Store even a null result (as an empty array) to avoid repeated failing calls.
        $cache->set($competitionid, $data ?? []);
        return $data;
    }

    /**
     * Fetch a competition leaderboard: GET /api/leaderboard/competition/{id}.
     *
     * @param int      $competitionid The ML-Arena competition id.
     * @param int|null $mlarenacourseid Optional ML-Arena course id to filter the board to that course's students.
     * @return array|null List of leaderboard rows, or null on failure.
     */
    public function get_leaderboard(int $competitionid, ?int $mlarenacourseid = null): ?array {
        if ($competitionid <= 0) {
            return null;
        }

        $path = '/api/leaderboard/competition/' . $competitionid;
        if (!empty($mlarenacourseid)) {
            $path .= '?course_id=' . $mlarenacourseid;
        }

        $cachekey = $competitionid . '_' . (int)$mlarenacourseid;
        $cache = \cache::make('mod_mlarena', 'leaderboard');
        $cached = $cache->get($cachekey);
        if ($cached !== false) {
            return $cached ?: null;
        }

        $data = $this->get_json($path);
        $cache->set($cachekey, $data ?? []);
        return $data;
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
