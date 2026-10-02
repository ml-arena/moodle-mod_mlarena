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
 * Unit tests for the metric descriptor formatter.
 *
 * @package    mod_mlarena
 * @category   test
 * @copyright  2026 ML-Arena <contact@ml-arena.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_mlarena\local\metric_format
 */
final class metric_format_test extends \basic_testcase {
    /**
     * A metric descriptor with the given presentation fields.
     *
     * @param string $format The descriptor format.
     * @param int $precision Decimals.
     * @param string|null $unit Unit.
     * @return array
     */
    private function spec(string $format, int $precision, ?string $unit = null): array {
        return [
            'key' => 'k',
            'label' => 'Label',
            'format' => $format,
            'unit' => $unit,
            'precision' => $precision,
            'is_ranking' => true,
            'visible' => true,
        ];
    }

    /**
     * Each format renders as the ML-Arena console renders it.
     */
    public function test_format(): void {
        $nbsp = "\u{202F}";
        $this->assertSame(format_float(0.91234, 3), metric_format::format(0.91234, $this->spec('number', 3)));
        $this->assertSame(format_float(1235, 0), metric_format::format(1234.6, $this->spec('integer', 2)));
        $this->assertSame(format_float(91.2, 1) . '%', metric_format::format(0.912, $this->spec('percent', 1)));
        $this->assertSame(format_float(1.5, 2) . $nbsp . 's', metric_format::format(1.5, $this->spec('seconds', 2)));
        $this->assertSame(format_float(1.5, 1) . $nbsp . 'MB', metric_format::format(1572864, $this->spec('bytes', 1)));
        $this->assertSame('0' . $nbsp . 'B', metric_format::format(0, $this->spec('bytes', 1)));
        $this->assertSame(format_float(12, 0) . $nbsp . '€', metric_format::format(12.0, $this->spec('currency', 2, '€')));
        $this->assertSame(format_float(12.5, 2) . $nbsp . '€', metric_format::format(12.5, $this->spec('currency', 2, '€')));
    }

    /**
     * A row without a value yet renders as a dash.
     */
    public function test_format_missing_value(): void {
        $this->assertSame('-', metric_format::format(null, $this->spec('number', 3)));
    }

    /**
     * The unit goes in the header, except for currency where the value carries it.
     */
    public function test_header(): void {
        $this->assertSame('Label (mm)', metric_format::header($this->spec('number', 4, 'mm')));
        $this->assertSame('Label', metric_format::header($this->spec('number', 4)));
        $this->assertSame('Label', metric_format::header($this->spec('currency', 2, '€')));
    }
}
