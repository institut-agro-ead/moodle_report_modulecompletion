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
use report_modulecompletion\traits\has_courses;
use stdClass;
use report_modulecompletion\traits\has_metadata;
use report_modulecompletion\traits\has_rows;

require_once($CFG->dirroot . '/report/modulecompletion/locallib.php');

/**
 * Report module completion renderable class for one specific user.
 *
 * @package    report_modulecompletion
 * @copyright  2025 L’Institut Agro Enseignement à distance
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_reports implements renderable, templatable {

    use has_metadata, has_courses, has_rows;
    /**
     * @var array The user fetched from the DB
     */
    private $user;

    /**
     * @var array The reports fetched from the DB
     */
    private $reports;

    /**
     * Class’ constructor.
     *
     * @param array $user The user for whom we want to display the reports
     * @param array $reports The reports for the user
     */
    public function __construct($user, $reports) {
        $this->user = $user;
        $this->reports = $reports;
        if (get_config('report_modulecompletion', 'use_metadata') &&
            $selectedmetas = get_config('report_modulecompletion', 'metadata_list') &&
            get_config('report_modulecompletion', 'show_metadata_in_profile')) {
            $this->metas = report_modulecompletion_get_modules_metadata(true);
            $this->selectedmetas = $selectedmetas !== '' ? explode(',', $selectedmetas) : [];
            $selectednumericmetas = get_config('report_modulecompletion', 'numeric_metadata_list');
            $this->numericmetas = $selectednumericmetas !== '' ?
                explode(',', get_config('report_modulecompletion', 'numeric_metadata_list')) : [];
        }
    }

    /**
     * Builds user’s properties from the given report.
     *
     * @param stdClass $report The report
     * @return stdClass
     */
    private function get_data_structure_from_report($report) {
        $data = new stdClass();
        $data->user_id  = $report->user_id;
        $data->last_name = $report->last_name;
        $data->first_name = $report->first_name;
        $data->email = $report->email;
        $data->completed_modules = 0;
        $data->starting_date = gmdate(get_string('full_date_format', 'report_modulecompletion'), $this->user->firstaccess);
        $data->ending_date = gmdate(get_string('full_date_format', 'report_modulecompletion'), time());
        $data->most_recent_completed_module_date = $report->completed_on;
        $data->total_modules = $report->total_modules;
        $data->courses = [];
        $data->meta_totals = [];
        if (count($this->numericmetas)) {
            foreach ($this->numericmetas as $metaid) {
                $data->meta_totals[$metaid] = [
                    'name' => ucwords($this->metas[$metaid]->name),
                    'counter' => '0',
                ];
            }
        }
        return $data;
    }

    /**
     * function to export the renderer data in a format that is suitable for a mustache template.
     *
     * @param renderer_base $output Used to do a final render of any components that need to be rendered for export.
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        if (!$this->reports->valid()) {
            $data = new stdClass();
            $data->no_data = true;
            return $data;
        }
        $data = $this->get_data_structure_from_report($this->reports->current());
        foreach ($this->reports as $report) {
            // If the completion date is more recent for this module we replace the old one for the user.
            if ($report->completed_on > $data->most_recent_completed_module_date) {
                $data->most_recent_completed_module_date = $report->completed_on;
            }

            // Checks if the current browsed course doesn't exist in the returned reports.
            if (!isset($data->courses[$report->course_id])) {
                $data->courses[$report->course_id] = $this->build_course_infos($report);
            }

            // We only create a new row when there is a new module completion, otherwise that's the same module with new metadata.
            if (!isset($data->courses[$report->course_id]['completions']['rows'][$report->id])) {
                $data->courses[$report->course_id]['completions']['rows'][$report->id] =
                $this->build_row($report);
                $data->completed_modules++;
                $data->courses[$report->course_id]['completed_modules']++;
                // Metadata is numeric, we increment the total counters (user and course).
                if (count($this->selectedmetas) && count($this->numericmetas) &&
                isset($data->meta_totals[$report->meta_id]) && $report->meta_data !== null) {
                    $data->meta_totals[$report->meta_id]['counter'] += (float) $report->meta_data;
                    $data->courses[$report->course_id]['meta_totals'][$report->meta_id]['counter'] +=
                     (float) $report->meta_data;
                }
            }
            // Appends metadata if there is any.
            if (count($this->selectedmetas)) {
                $data->reportscourses[$report->course_id]['completions']['rows'][$report->id][] =
                $report->meta_id ?
                    $this->convert_metadata($report->meta_id, $report->meta_data) : '';
            }
        }
        // User progress bar.
        $data->progress_bar = [
            'progress' => round(($data->completed_modules * 100) / $data->total_modules),
            'hasprogress' => true,
        ];
        // Courses progress bar.
        array_walk($data->courses, function (&$course, $index) {
            $course['progress_bar'] = [
                'progress' => round(($course['completed_modules'] * 100) / $course['total_modules']),
                'hasprogress' => true,
            ];
        });
        // Courses sorting.
        uasort($data->courses, function ($a, $b) {
            if ($a['course_name'] === $b['course_name']) {
                return 0;
            }
            return ($a['course_name'] < $b['course_name']) ? -1 : 1;
        });
        $data->meta_totals = $this->get_converted_numeric_metadata(array_values($data->meta_totals));
        $data->courses = array_values($data->courses);
        $data->most_recent_completed_module_date = gmdate(get_string('full_date_format', 'report_modulecompletion'),
        $data->most_recent_completed_module_date);
        foreach ($data->courses as &$course) {
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
        global $CFG;
        $vmatches = [];
        preg_match('/^(\d+\.\d+).*$/', $CFG->release, $vmatches);
        $data->isbeforemoodle42 = ($vmatches[1] ?? 0.0) < 4.2;
        return $data;
    }
}
