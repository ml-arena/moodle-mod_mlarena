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
 * Version metadata for the mod_mlarena plugin.
 *
 * @package    mod_mlarena
 * @copyright  2026 ML-Arena <contact@ml-arena.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'mod_mlarena';       // Full frankenstyle name of the plugin.
$plugin->version   = 2026070100;          // The current plugin version (Date: YYYYMMDDXX).
$plugin->requires  = 2024100700;          // Requires Moodle 4.5 (LTS) or later.
$plugin->maturity  = MATURITY_BETA;       // This is a first public release.
$plugin->release   = 'v1.0.0-beta';       // Human-friendly version name.
$plugin->cron      = 0;
