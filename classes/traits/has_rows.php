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
 * Report module completion has_rows trait.
 *
 * @package    report_modulecompletion
 * @copyright  2025 L’Institut Agro Enseignement à distance
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
trait has_rows {
    /**
     * Get strings for the user’ selected language.
     *
     * @return array
     */
    private function get_headers() {
        return [
            get_string('month_header', 'report_modulecompletion'),
            get_string('course_header', 'report_modulecompletion'),
            get_string('section_header', 'report_modulecompletion'),
            get_string('module_type_header', 'report_modulecompletion'),
            get_string('module_header', 'report_modulecompletion'),
            get_string('completed_header', 'report_modulecompletion'),
        ];
    }

    /**
     * Builds a report row (single completion).
     *
     * @param stdClass $report The report
     * @return array
     */
    private function build_row($report) {
        global $USER;
        $coursecontext = context_course::instance($report->course_id);
        $userenrolled = is_enrolled($coursecontext, $USER->id, '', true);
        return [
            gmdate(get_string('month_date_format', 'report_modulecompletion'), $report->month),
            $userenrolled ? html_writer::link(
                new moodle_url('/course/view.php', ['id' => $report->course_id]),
                $report->course_name,
                ['target' => '_blank']
            ) : $report->course_name,
            $report->section_name ?: 'N/A',
            get_string('modulename', 'mod_' . $report->module_type),
            $report->module,
            gmdate(get_string('full_date_format', 'report_modulecompletion'), $report->completed_on),
        ];
    }
}
