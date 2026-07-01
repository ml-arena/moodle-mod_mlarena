# ML-Arena competition activity for Moodle (mod_mlarena)

Bring [ML-Arena](https://ml-arena.com) machine-learning competitions and academic
courses straight into your Moodle course. Once installed, teachers get a new
**ML-Arena competition** entry in *Add an activity or resource*, alongside Quiz,
Assignment and the rest.

## What it does

* Adds an **activity module** so a teacher can attach an ML-Arena competition or
  academic course to any course section.
* Students open the competition from inside Moodle — either as a **landing page**
  with a launch button, or **embedded** in an iframe.
* For course links, the ML-Arena **join code** is shown so students can
  self-enrol.
* Optionally renders the competition's **live leaderboard** on the activity page,
  read from the public ML-Arena API (with an optional filter to a specific
  ML-Arena course's students).

It only reads **public** ML-Arena endpoints (`/api/competitions/{id}` and
`/api/leaderboard/competition/{id}`), so no API token or Moodle personal data is
sent to ML-Arena by the server.

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
   *Site administration → Plugins → Activity modules → ML-Arena competition*.

## Usage

1. In a course, turn editing on and choose **Add an activity or resource →
   ML-Arena competition**.
2. Pick **Competition** and enter the numeric competition id (the `42` in
   `https://ml-arena.com/viewcompetition/42`), **or** pick **Course** and enter
   the course join code.
3. Choose whether to show it as a landing page or embedded, and whether to show
   the leaderboard.
4. Save. Students now see the activity in the course.

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
