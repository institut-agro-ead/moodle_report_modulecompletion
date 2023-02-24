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

namespace report_modulecompletion\output\ajax;

defined('MOODLE_INTERNAL') || die;

use renderable;
use templatable;
use renderer_base;
use stdClass;
use core_cohort\external\cohort_summary_exporter;

require_once($CFG->dirroot . '/report/modulecompletion/locallib.php');

/**
 * Report module completion renderable class (cohorts list).
 *
 * @package    report_modulecompletion
 * @copyright  2023 L’Institut Agro Enseignement à distance
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cohort_search implements renderable, templatable {

    /**
     * @var string $cohortsearch
     */
    protected $cohortsearch;

    /**
     * Class’ constructor.
     *
     * @param string $search The cohort search query
     */
    public function __construct($search) {
        $this->cohort_search = $search;
    }

    /**
     * function to export the renderer data in a format that is suitable for a mustache template.
     *
     * @param renderer_base $output Used to do a final render of any components that need to be rendered for export.
     * @return stdClass|array
     */
    public function export_for_template(renderer_base $output) {
        $cohorts = \report_modulecompletion_search_cohorts($this->cohort_search);
        $result = [];
        foreach ($cohorts as $cohortdata) {
            $cohortcontext = \context::instance_by_id($cohortdata->contextid);
            $exporter = new cohort_summary_exporter($cohortdata, ['context' => $cohortcontext]);
            $result[] = $exporter->export($output);
        }
        $data = new stdClass();
        $data->cohorts = $result;
        return $data;
    }
}
