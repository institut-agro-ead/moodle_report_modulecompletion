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

use context_course;
use html_writer;
use moodle_url;

/**
 * Report module completion has_courses trait.
 *
 * @package    report_modulecompletion
 * @copyright  2025 L’Institut Agro Enseignement à distance
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
trait has_courses {
    /**
     * Builds course’s properties from the given report.
     *
     * @param stdClass $report The report
     * @return array
     */
    private function build_course_infos($report) {
        global $USER;
        $coursecontext = context_course::instance($report->course_id);
        $userenrolled = is_enrolled($coursecontext, $USER->id, '', true);
        $courseinfos = [
            'course_id' => $report->course_id,
            'course_name' => $report->course_name,
            'course_name_as_title' => $userenrolled ? html_writer::link(
                new moodle_url('/course/view.php', ['id' => $report->course_id]),
                $report->course_name,
                ['target' => '_blank']
            ) : $report->course_name,
            'completed_modules' => 0,
            'total_modules' => $report->total_modules_per_course,
            'has_restrictions' => $report->has_restrictions > 0,
            'meta_totals' => [],
            'completions' => [
                'headers' => $this->get_headers(),
                'rows' => [],
            ],
        ];
        if (count($this->selectedmetas)) {
            foreach ($this->selectedmetas as $metaid) {
                $courseinfos['completions']['headers'][] = ucwords($this->metas[$metaid]->name);
            }
        }
        if (count($this->numericmetas)) {
            foreach ($this->numericmetas as $metaid) {
                $courseinfos['meta_totals'][$metaid] = [
                    'name' => ucwords($this->metas[$metaid]->name),
                    'counter' => '0',
                ];
            }
        }
        return $courseinfos;
    }
}
