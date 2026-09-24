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
 * Unit tests for the ML-Arena API client: the routes it calls and the payloads it accepts.
 *
 * @package    mod_mlarena
 * @category   test
 * @copyright  2026 ML-Arena <contact@ml-arena.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_mlarena\local\client
 */
final class client_test extends \advanced_testcase {
    /**
     * A client whose HTTP layer records the requested paths and returns a canned response.
     *
     * @param array|null $response What the API "returns" for every request.
     * @return client
     */
    private function recording_client(?array $response): client {
        return new class ($response) extends client {
            /** @var string[] Paths requested, in order. */
            public $paths = [];

            /** @var array|null Canned response. */
            private $response;

            /**
             * Constructor.
             *
             * @param array|null $response Canned response.
             */
            public function __construct(?array $response) {
                parent::__construct('https://ml-arena.example');
                $this->response = $response;
            }

            /**
             * Record the path instead of making a request.
             *
             * @param string $path Path beginning with a slash.
             * @return array|null
             */
            protected function get_json(string $path): ?array {
                $this->paths[] = $path;
                return $this->response;
            }
        };
    }

    /**
     * A leaderboard envelope as the backend serves it (`LeaderboardEnvelope`).
     *
     * @return array
     */
    private function envelope(): array {
        global $CFG;
        $json = file_get_contents($CFG->dirroot . '/mod/mlarena/tests/fixtures/leaderboard_envelope.json');
        return json_decode($json, true);
    }

    /**
     * Challenge details come from GET /api/challenges/{id}.
     */
    public function test_get_challenge_route(): void {
        $this->resetAfterTest();

        $client = $this->recording_client(['id' => 42, 'name' => 'Lunar Lander', 'miniature' => '/api/x']);
        $this->assertSame('Lunar Lander', $client->get_challenge(42)['name']);
        $this->assertSame(['/api/challenges/42'], $client->paths);

        // A payload without the challenge name is refused.
        $client = $this->recording_client(['error' => 'nope']);
        $this->assertNull($client->get_challenge(43));
        $this->assertDebuggingCalled();
    }

    /**
     * The leaderboard comes from GET /api/leaderboard/challenge/{id}, one row per participant.
     */
    public function test_get_leaderboard_route(): void {
        $this->resetAfterTest();

        $client = $this->recording_client($this->envelope());
        $envelope = $client->get_leaderboard(42);
        $this->assertSame('baseline-v2', $envelope['leaders'][0]['submission_name']);

        $client->get_leaderboard(42, 15);
        $this->assertSame([
            '/api/leaderboard/challenge/42?aggregate=user&limit=' . client::LEADERBOARD_LIMIT,
            '/api/leaderboard/challenge/42?aggregate=user&limit=' . client::LEADERBOARD_LIMIT . '&course_id=15',
        ], $client->paths);
    }

    /**
     * A bare array of rows (the pre-rename shape) is not a leaderboard envelope.
     */
    public function test_get_leaderboard_rejects_other_shapes(): void {
        $this->resetAfterTest();

        $client = $this->recording_client([['Rank' => 1, 'Username' => 'alice']]);
        $this->assertNull($client->get_leaderboard(42));
        $this->assertDebuggingCalled();
    }
}
