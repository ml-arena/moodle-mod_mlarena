# Changelog

All notable changes to mod_mlarena are documented here.

## v1.0.0-beta3 (2026-10-02)

ML-Arena's score model (2026-10-02) removed the leaderboard keys v1.0.0-beta2
reads, so v1.0.0-beta2 shows "The leaderboard could not be loaded". Upgrade
required.

* The leaderboard columns come from the envelope's `challenge.metrics`
  descriptors: the ranking descriptor (`is_ranking`) first, then every other
  `visible` one, in declaration order. Each column is headed by the
  descriptor's `label` (plus its `unit`, except for currency).
* A row's ranking value is its `score`; any other column reads
  `metrics[key]`. Values are formatted by the descriptor's `format`
  (`number`, `integer`, `percent`, `seconds`, `bytes`, `currency`), `unit`
  and `precision`, as on ML-Arena.
* Removed reads: `challenge.is_elo_score`, `challenge.metric`,
  `challenge.frontend_precision`, row `mean_reward` and `elo_score`. A rated
  (Elo) challenge declares a `rating` descriptor, so it needs no special case.
* An envelope without exactly one ranking descriptor, or with a format the
  plugin cannot display, is refused (the activity shows the "could not be
  loaded" notice).
* Removed the now unused `elo` and `score` language strings.

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
