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

namespace report_modulecompletion\output;

defined('MOODLE_INTERNAL') || die;

use renderable;
use templatable;
use renderer_base;
use stdClass;
use core_date;
use DateTime;
use report_modulecompletion\traits\has_courses;
use report_modulecompletion\traits\has_metadata;
use report_modulecompletion\traits\has_rows;

require_once($CFG->dirroot . '/report/modulecompletion/locallib.php');
require_once($CFG->dirroot . '/report/modulecompletion/utils.php');
require_once($CFG->dirroot . '/lib/evalmath/evalmath.class.php');

/**
 * Report module completion renderable class (reports).
 *
 * @package    report_modulecompletion
 * @copyright  2023 L’Institut Agro Enseignement à distance
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class reports implements renderable, templatable {
    use has_metadata, has_courses, has_rows;

    /**
     * @var \report_modulecompletion\persistents\filter The filter from which to get the reports
     */
    private $filter;

    /**
     * @var array The reports fetched from the DB
     */
    private $reports;

    /**
     * Class’ constructor.
     *
     * @param \report_modulecompletion\persistents\filter $filter The filter from which we got the reports
     * @param array $reports The reports
     */
    public function __construct($filter, $reports) {
        $this->filter = $filter;
        $this->reports = $reports;
        if (get_config('report_modulecompletion', 'use_metadata') &&
            $selectedmetas = get_config('report_modulecompletion', 'metadata_list')) {
            $this->metas = report_modulecompletion_get_modules_metadata(true);
            $this->selectedmetas = $selectedmetas !== '' ? explode(',', $selectedmetas) : [];
            $selectednumericmetas = get_config('report_modulecompletion', 'numeric_metadata_list');
            $this->numericmetas = $selectednumericmetas !== '' ?
                explode(',', get_config('report_modulecompletion', 'numeric_metadata_list')) : [];
        }
    }

    /**
     * Builds student’s properties from the given report.
     *
     * @param stdClass $report The report
     * @return array
     */
    private function build_user_infos($report) {
        $userinfos = [
            'user_id' => $report->user_id,
            'last_name' => $report->last_name,
            'first_name' => $report->first_name,
            'email' => $report->email,
            'completed_modules' => 0,
            'most_recent_completed_module_date' => $report->completed_on,
            'total_modules' => $report->total_modules,
            'courses' => [],
            'meta_totals' => [],
        ];
        if (count($this->numericmetas)) {
            foreach ($this->numericmetas as $metaid) {
                $userinfos['meta_totals'][$metaid] = [
                    'name' => ucwords($this->metas[$metaid]->name),
                    'counter' => '0',
                ];
            }
        }
        return $userinfos;
    }

    /**
     * Sets the progression in % for the student or the course.
     *
     * @param array $reports The reports
     */
    private function set_progress(&$reports) {
        array_walk($reports, function (&$report, $index) {
            $report['progress_bar'] = [
            'progress' => round(($report['completed_modules'] * 100) / $report['total_modules']),
            'hasprogress' => true,
            ];
            if (isset($report['courses'])) {
                $this->set_progress($report['courses']);
            }
        });
    }

    /**
     * Sorts the reports at the top level (students) from the filters form ordering fields.
     *
     * @param array $reports The reports
     */
    private function sort_reports(&$reports) {
        $direction = $this->filter->get('order_by_type');
        $column = 'last_name';
        switch ($this->filter->get('order_by_column')) {
            case 'completion':
                $column = 'progress_bar';
                break;
            case 'last_completed':
                $column = 'most_recent_completed_module_date';
                break;
        }
        uasort($reports, function ($a, $b) use ($column, $direction) {
            $v1 = $a[$column];
            $v2 = $b[$column];
            if ($v1 === $v2) {
                return 0;
            }
            return ($v1 < $v2 && $direction === 'asc') || ($v1 > $v2 && $direction === 'desc') ? -1 : 1;
        });
    }

    /**
     * Sorts the reports at the second level (courses) from the filters form ordering fields.
     *
     * @param array $courses The courses
     */
    private function sort_courses(&$courses) {
        $direction = $this->filter->get('order_by_type');
        $column = 'course_name';
        switch ($this->filter->get('order_by_column')) {
            case 'student':
            case 'last_completed':
                $direction = 'asc';
                break;
            case 'completion':
                $column = 'progress_bar';
                break;
        }
        uasort($courses, function ($a, $b) use ($column, $direction) {
            if ($a[$column] === $b[$column]) {
                return 0;
            }
            return ($a[$column] < $b[$column] && $direction === 'asc') ||
                ($a[$column] > $b[$column] && $direction === 'desc') ? -1 : 1;
        });
    }

    /**
     * Builds cohorts list from filters form (if any selected).
     *
     * @param string $list The list of selected cohorts
     */
    private function set_cohorts($list = '') {
        $cohorts = report_modulecompletion_get_cohorts($list);
        $ret = [];
        foreach ($cohorts as $c) {
            if (!isset($ret[$c->cohort_id])) {
                $ret[$c->cohort_id] = [
                    'name' => $c->cohort_name,
                    'users' => [],
                ];
            }
            $ret[$c->cohort_id]['users'][] = [
                'firstname' => $c->user_fn,
                'lastname' => $c->user_ln,
            ];
        }
        return array_values($ret);
    }

    /**
     * function to export the renderer data in a format that is suitable for a mustache template.
     *
     * @param renderer_base $output Used to do a final render of any components that need to be rendered for export.
     * @return stdClass|array
     */
    public function export_for_template(renderer_base $output) {
        global $SESSION;
        $data = new stdClass();
        // Filter.
        $startingdate = new DateTime('@' . $this->filter->get('starting_date'));
        $startingdate->setTimezone(core_date::get_server_timezone_object());
        $endingdate = new DateTime('@' . $this->filter->get('ending_date'));
        $endingdate->setTimezone(core_date::get_server_timezone_object());
        $data->filter = [
            'id' => $this->filter->get('id'),
            'name' => $this->filter->get('name'),
            'starting_date' => $startingdate->format(get_string('full_date_format', 'report_modulecompletion')),
            'ending_date' => $endingdate->format(get_string('full_date_format', 'report_modulecompletion')),
            'order_by_column' => get_string('form_order_by_' . $this->filter->get('order_by_column'), 'report_modulecompletion'),
            'order_by_type' => get_string('form_order_by_' . $this->filter->get('order_by_type'), 'report_modulecompletion'),
        ];
        if ($this->filter->get('cohorts')) {
            $data->filter['cohorts'] = $this->set_cohorts($this->filter->get('cohorts'));
            $data->filter['has_cohorts'] = true;
            $data->filter['only_cohorts_courses'] = (bool)$this->filter->get('only_cohorts_courses');
        }
        if (isset($SESSION->quick_filter)) {
            $data->filter['is_quick_filter'] = true;
        }
        // Reports.
        $data->reports = [];
        foreach ($this->reports as $report) {
            // If user not yet added to the returned reports.
            if (!isset($data->reports[$report->user_id])) {
                $data->reports[$report->user_id] = $this->build_user_infos($report);
            }
            // If the completion date is more recent for this module we replace the old one for the user.
            if ($report->completed_on > $data->reports[$report->user_id]['most_recent_completed_module_date']) {
                $data->reports[$report->user_id]['most_recent_completed_module_date'] = $report->completed_on;
            }

            // Checks if the current browsed course doesn't exist in the returned reports.
            if (!isset($data->reports[$report->user_id]['courses'][$report->course_id])) {
                $data->reports[$report->user_id]['courses'][$report->course_id] = $this->build_course_infos($report);
            }

            // We only create a new row when there is a new module completion, otherwise that's the same module with new metadata.
            if (!isset($data->reports[$report->user_id]['courses'][$report->course_id]['completions']['rows'][$report->id])) {
                $data->reports[$report->user_id]['courses'][$report->course_id]['completions']['rows'][$report->id] =
                    $this->build_row($report);
                $data->reports[$report->user_id]['completed_modules']++;
                $data->reports[$report->user_id]['courses'][$report->course_id]['completed_modules']++;
                // Metadata is numeric, we increment the total counters (user and course).
                if (count($this->selectedmetas) && count($this->numericmetas) &&
                    isset($data->reports[$report->user_id]['meta_totals'][$report->meta_id]) && $report->meta_data !== null) {
                    $data->reports[$report->user_id]['meta_totals'][$report->meta_id]['counter'] += (float) $report->meta_data;
                    $data->reports[$report->user_id]['courses'][$report->course_id]['meta_totals'][$report->meta_id]['counter'] +=
                     (float) $report->meta_data;
                }
            }
            // Appends metadata if there is any.
            if (count($this->selectedmetas)) {
                $data->reports[$report->user_id]['courses'][$report->course_id]['completions']['rows'][$report->id][] =
                    $report->meta_id ?
                        $this->convert_metadata($report->meta_id, $report->meta_data) : '';
            }
        }
        $this->set_progress($data->reports);
        $this->sort_reports($data->reports);
        foreach ($data->reports as &$report) {
            $this->sort_courses($report['courses']);
            $report['meta_totals'] = $this->get_converted_numeric_metadata(array_values($report['meta_totals']));
            $report['courses'] = array_values($report['courses']);
            $report['most_recent_completed_module_date'] = gmdate(get_string('full_date_format', 'report_modulecompletion'),
                $report['most_recent_completed_module_date']);
            foreach ($report['courses'] as &$course) {
                $course['meta_totals'] = $this->get_converted_numeric_metadata(array_values($course['meta_totals']));
                $course['completions']['rows'] = array_values($course['completions']['rows']);
                // If metadata is null we fill the rest of the columns of the row.
                if (count($this->selectedmetas)) {
                    foreach ($course['completions']['rows'] as &$row) {
                        if (count($row) !== (FIXED_NUM_COLS + count($this->selectedmetas))) {
                            $row = array_merge(
                                $row,
                                array_fill(FIXED_NUM_COLS, (FIXED_NUM_COLS + count($this->selectedmetas) - count($row)), ''));
                        }
                    }
                }
            }
        }
        $data->reports = array_values($data->reports);
        $data->count = count($data->reports);
        $data->max_progress = $data->count <= 0 ? 0 : max(array_column(array_column($data->reports, 'progress_bar'), 'progress'));
        return $data;
    }
}
