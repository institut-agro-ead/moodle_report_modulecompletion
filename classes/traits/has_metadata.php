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

namespace report_modulecompletion\traits;

defined('MOODLE_INTERNAL') || die;

use core_date;
use DateTime;
use EvalMath;

require_once($CFG->dirroot . '/report/modulecompletion/utils.php');
require_once($CFG->dirroot . '/lib/evalmath/evalmath.class.php');

/**
 * Report module completion has_metadata trait.
 *
 * @package    report_modulecompletion
 * @copyright  2025 L’Institut Agro Enseignement à distance
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
trait has_metadata {
    /**
     * @var array List of module metadata
     */
    private $metas;

    /**
     * @var array List of selected metadata from settings
     */
    private $selectedmetas;

    /**
     * @var array List of numerical metadata
     */
    private $numericmetas;

    /**
     * Converts metadata from its defined type.
     *
     * @param int $id The metadata id
     * @param mixed $data The data to convert
     * @return array
     */
    private function convert_metadata($id, $data) {
        $meta = $this->metas[$id];
        $value = $data;
        switch ($meta->datatype) {
            case 'datetime':
                $date = new DateTime('@' . $value);
                $date->setTimezone(core_date::get_server_timezone_object());
                $value = $date->format(get_string('full_date_format', 'report_modulecompletion'));
                break;
            case 'checkbox':
                $value = get_string($value ? 'yes' : 'no');
                break;
        }
        return $value;
    }

    /**
     * Calculates totals for numerical metadata from formulas set in plugin’ settings.
     *
     * @param array $metatotalarr numerical metadata
     */
    private function get_converted_numeric_metadata($metatotalarr = []) {
        for ($i = 0; $i < count($metatotalarr); $i++) {
            $metatotal = $metatotalarr[$i];
            if (isset($metatotal['name'])) {
                $slug = report_modulecompletion_slug($metatotal['name'], '_');
                $formula = trim(get_config('report_modulecompletion', 'metadata_conversion_' . $slug . '_formula'));
                $label = trim(get_config('report_modulecompletion', 'metadata_conversion_' . $slug . '_label'));
                if ($formula !== '' && report_modulecompletion_validate_formula($formula) === true) {
                    $counter = $metatotal['counter'];
                    $em = new EvalMath();
                    $converted = $em->evaluate($counter . $formula);
                    $metatotalarr[$i]['counter'] .= ' (' . round($converted, 2) . ' ' . $label . ')';
                }
            }
        }
        return $metatotalarr;
    }
}
