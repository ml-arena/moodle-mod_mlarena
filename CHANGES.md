# Changelog

All notable changes to mod_mlarena are documented here.

## v1.0.0-beta2 (2026-09-24)

ML-Arena renamed *competitions* to *challenges* and removed the old API routes,
so v1.0.0-beta can no longer load challenge details or leaderboards. Upgrade
required.

* Challenge details are read from `GET /api/challenges/{id}` (was
  `/api/competitions/{id}`); the thumbnail is now loaded from the ML-Arena site
  instead of the Moodle site.
* The leaderboard is read from `GET /api/leaderboard/challenge/{id}` (was
  `/api/leaderboard/competition/{id}`). The response is one envelope object:
  the metric, Elo flag and precision come from its `challenge` block, the rows
  from `leaders` (snake_case keys).
* The leaderboard shows one row per participant or team (`aggregate=user`, as on
  ML-Arena) and at most 100 rows, with a note when the board is longer.
* The *Agent* column is now *Submission*.
* Launch links point to `/viewchallenge/{id}` (was `/viewcompetition/{id}`).
* UI and help texts say *challenge*; the activity is named *ML-Arena challenge*.
  The database fields and stored values (`competitionid`, `reftype = competition`)
  are unchanged, so existing activities and backups keep working without an
  upgrade step.
* Added the missing cache definition strings; the metadata cache is renamed
  `challenge`.
* PHPUnit coverage of the API client routes and the leaderboard rendering.

## v1.0.0-beta (2026-07-01)

Initial release.

* Activity module that appears in the *Add an activity or resource* chooser.
* Link an ML-Arena competition (by id) or academic course (by join code).
* Landing-page or embedded (iframe) display.
* Optional public leaderboard rendering, with optional ML-Arena course filter.
* Site-level settings for base URL, request timeout and modedit defaults.
* Backup/restore and privacy (external-service metadata) support.
* PHPUnit coverage of the library functions.
