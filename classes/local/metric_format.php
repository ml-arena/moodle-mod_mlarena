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
 * Display formatting of a leaderboard value through its ML-Arena metric descriptor.
 *
 * A descriptor is one entry of the leaderboard envelope's `challenge.metrics`
 * (ML-Arena's `MetricSpec`): `key`, `label`, `format`, `unit`, `precision`,
 * `is_ranking`, `visible`, ... Its `format`, `unit` and `precision` are the only
 * presentation inputs, as on the ML-Arena console:
 *
 * - `number`: the value at `precision` decimals;
 * - `integer`: the value rounded, no decimals;
 * - `percent`: a fraction shown x100 with a `%` sign;
 * - `seconds`: the value with an `s` suffix;
 * - `bytes`: the value scaled to B / KB / MB / GB / TB (base 1024);
 * - `currency`: the value with its `unit`; a whole amount drops its decimals.
 *
 * A non-currency `unit` belongs to the column header, not to the value.
 *
 * @package    mod_mlarena
 * @copyright  2026 ML-Arena <contact@ml-arena.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class metric_format {
    /** @var string[] The descriptor formats this plugin can display (ML-Arena's `MetricSpec.format`). */
    public const FORMATS = ['number', 'integer', 'percent', 'seconds', 'bytes', 'currency'];

    /** @var string Narrow no-break space between a value and its unit. */
    protected const NARROW_NBSP = "\u{202F}";

    /** @var string[] Byte scale units. */
    protected const BYTE_UNITS = ['B', 'KB', 'MB', 'GB', 'TB'];

    /**
     * Format a value as its descriptor declares it.
     *
     * @param mixed $value The row value (a number, or null when there is no value yet).
     * @param array $spec The metric descriptor.
     * @return string The display text ('-' when there is no value).
     */
    public static function format($value, array $spec): string {
        if (!is_int($value) && !is_float($value)) {
            return '-';
        }
        $value = (float)$value;
        $precision = (int)$spec['precision'];

        switch ($spec['format']) {
            case 'number':
                return format_float($value, $precision);
            case 'integer':
                return format_float(round($value), 0);
            case 'percent':
                return format_float($value * 100, $precision) . '%';
            case 'seconds':
                return format_float($value, $precision) . self::NARROW_NBSP . 's';
            case 'bytes':
                return self::format_bytes($value, $precision);
            case 'currency':
                $digits = floor($value) == $value ? format_float($value, 0) : format_float($value, $precision);
                return $digits . self::NARROW_NBSP . $spec['unit'];
        }
        throw new \coding_exception('mod_mlarena: unknown metric format ' . $spec['format']);
    }

    /**
     * The column header of a descriptor: its label, plus the unit when the value does not carry it.
     *
     * @param array $spec The metric descriptor.
     * @return string Plain text (escape before output).
     */
    public static function header(array $spec): string {
        if (!empty($spec['unit']) && $spec['format'] !== 'currency') {
            return $spec['label'] . ' (' . $spec['unit'] . ')';
        }
        return $spec['label'];
    }

    /**
     * A byte count scaled to B / KB / MB / GB / TB (base 1024).
     *
     * @param float $bytes The byte count.
     * @param int $precision Decimals above the byte unit.
     * @return string
     */
    protected static function format_bytes(float $bytes, int $precision): string {
        if ($bytes == 0) {
            return '0' . self::NARROW_NBSP . 'B';
        }
        $i = (int)min(count(self::BYTE_UNITS) - 1, max(0, floor(log(abs($bytes)) / log(1024))));
        return format_float($bytes / (1024 ** $i), $i === 0 ? 0 : $precision) . self::NARROW_NBSP . self::BYTE_UNITS[$i];
    }
}
