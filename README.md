# ML-Arena challenge activity for Moodle (mod_mlarena)

Bring [ML-Arena](https://ml-arena.com) machine-learning challenges and academic
courses straight into your Moodle course. Once installed, teachers get a new
**ML-Arena challenge** entry in *Add an activity or resource*, alongside Quiz,
Assignment and the rest.

## What it does

* Adds an **activity module** so a teacher can attach an ML-Arena challenge or
  academic course to any course section.
* Students open the challenge from inside Moodle — either as a **landing page**
  with a launch button, or **embedded** in an iframe.
* For course links, the ML-Arena **join code** is shown so students can
  self-enrol.
* Optionally renders the challenge's **live leaderboard** on the activity page
  (one row per participant or team, top 100), read from the public ML-Arena API
  (with an optional filter to a specific ML-Arena course's students).

It only reads **public**, unauthenticated ML-Arena endpoints
(`GET /api/challenges/{id}` and `GET /api/leaderboard/challenge/{id}`), so no
API token or Moodle personal data is sent to ML-Arena by the server. Version
v1.0.0-beta3 or later is required: ML-Arena removed the older
`/api/competitions/…` routes that v1.0.0-beta called, and the leaderboard keys
(`mean_reward`, `elo_score`, `is_elo_score`, `metric`, `frontend_precision`)
that v1.0.0-beta2 read.

## Requirements

* Moodle 4.5 (LTS) or later.
* Outbound HTTPS access from the Moodle server to your ML-Arena base URL
  (default `https://ml-arena.com`) for the optional metadata/leaderboard
  features. The launch button and embed work without server-side access.

## Installation

1. Copy this repository into `mod/mlarena` of your Moodle site
   (`public/mod/mlarena` on Moodle 5.0+, which moved the web root under
   `public/`):

   ```bash
   git clone https://github.com/ml-arena/moodle-mod_mlarena.git mod/mlarena
   ```

   …or download the ZIP and install it via
   *Site administration → Plugins → Install plugins*.
2. Visit *Site administration → Notifications* to run the database upgrade.
3. (Optional) Set the ML-Arena base URL and defaults under
   *Site administration → Plugins → Activity modules → ML-Arena challenge*.

## Usage

1. In a course, turn editing on and choose **Add an activity or resource →
   ML-Arena challenge**.
2. Pick **Challenge** and enter the numeric challenge id (the `42` in
   `https://ml-arena.com/viewchallenge/42`), **or** pick **Course** and enter
   the course join code.
3. Choose whether to show it as a landing page or embedded, and whether to show
   the leaderboard.
4. Save. Students now see the activity in the course.

## Known limitations

* **Embedded display is read-only.** ML-Arena's sign-in cookie is not sent inside
  an iframe on another site, so learners browse the embedded page signed out.
  Use the landing page whenever learners must sign in to submit or join a team.
* **No grade or identity link.** Learners use their own ML-Arena account. Moodle
  completion tracks views only, and no score is sent to the gradebook. To show
  only your cohort on the leaderboard, enrol the learners in an ML-Arena course
  and enter its id in *ML-Arena course id*.
* **A course link opens the ML-Arena enrolment page.** The lessons stay on
  ML-Arena.
* **Only public challenges show details and a leaderboard.** A private challenge
  shows the "could not be loaded" notice.
* The plugin ships English strings only.

## Privacy

The plugin stores only the teacher's configuration in Moodle. It does not store
personal data and does not automatically transmit Moodle personal data to
ML-Arena. See `classes/privacy/provider.php` for the declared external-service
metadata.

## Contributing / issues

* Source: <https://github.com/ml-arena/moodle-mod_mlarena>
* Issues: <https://github.com/ml-arena/moodle-mod_mlarena/issues>

Please report bugs and feature requests via the issue tracker above.

## License

2026 ML-Arena.

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version. See [LICENSE](LICENSE) for details.
