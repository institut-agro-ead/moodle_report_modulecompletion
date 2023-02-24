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


namespace report_modulecompletion;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use context_system;
use core_user\external\user_summary_exporter;
use core_cohort\external\cohort_summary_exporter;
use core_course\external\course_summary_exporter;

/**
 * External API for this report, for Ajax calls.
 *
 * @package    report_modulecompletion
 * @copyright  2023 L’Institut Agro Enseignement à distance
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class external extends external_api {

    /**
     * Allows to search users via Ajax request.
     *
     * @param string $userstring The search query
     * @return stdClass|array The results
     */
    public static function search_user($userstring) {
        global $PAGE;
        $PAGE->set_context(context_system::instance());

        $params = self::validate_parameters(
            self::search_user_parameters(),
            [
                'userName' => $userstring,
            ]
        );

        $renderable = new output\ajax\user_search($params['userName']);
        $renderer = $PAGE->get_renderer('report_modulecompletion');

        $data = $renderable->export_for_template($renderer);
        return $data;
    }

    /**
     * Returns description of search_user() parameters.
     *
     * @return \external_function_parameters
     */
    public static function search_user_parameters() {
        $username = new external_value(
            PARAM_TEXT,
            'The user search string',
            VALUE_REQUIRED
        );
        $params = [
            'userName' => $username,
        ];
        return new external_function_parameters($params);
    }

    /**
     * Returns description of search_user() result value.
     *
     * @return \external_description
     */
    public static function search_user_returns() {
        return new external_single_structure([
            'users' => new external_multiple_structure(
                user_summary_exporter::get_read_structure()
            )
        ]);
    }

    /**
     * Allows to search cohorts via Ajax request.
     *
     * @param string $cohortstring The search query
     * @return stdClass|array The results
     */
    public static function search_cohort($cohortstring) {
        global $PAGE;
        $PAGE->set_context(context_system::instance());

        $params = self::validate_parameters(
            self::search_cohort_parameters(),
            [
                'cohortName' => $cohortstring,
            ]
        );

        $renderable = new output\ajax\cohort_search($params['cohortName']);
        $renderer = $PAGE->get_renderer('report_modulecompletion');

        $data = $renderable->export_for_template($renderer);
        return $data;
    }

    /**
     * Returns description of search_cohort() parameters.
     *
     * @return \external_function_parameters
     */
    public static function search_cohort_parameters() {
        $cohortname = new external_value(
            PARAM_TEXT,
            'The cohort search string',
            VALUE_REQUIRED
        );
        $params = [
            'cohortName' => $cohortname
        ];
        return new external_function_parameters($params);
    }

    /**
     * Returns description of search_cohort() result value.
     *
     * @return \external_description
     */
    public static function search_cohort_returns() {
        return new external_single_structure([
            'cohorts' => new external_multiple_structure(
                cohort_summary_exporter::get_read_structure()
            )
        ]);
    }

    /**
     * Allows to search courses via Ajax request.
     *
     * @param string $coursestring The search query
     * @return stdClass|array The results
     */
    public static function search_course($coursestring) {
        global $PAGE;
        $PAGE->set_context(context_system::instance());

        $params = self::validate_parameters(
            self::search_course_parameters(),
            [
                'courseName' => $coursestring,
            ]
        );

        $renderable = new output\ajax\course_search($params['courseName']);
        $renderer = $PAGE->get_renderer('report_modulecompletion');

        $data = $renderable->export_for_template($renderer);
        return $data;
    }

    /**
     * Returns description of search_course() parameters.
     *
     * @return \external_function_parameters
     */
    public static function search_course_parameters() {
        $coursename = new external_value(
            PARAM_TEXT,
            'The course search string',
            VALUE_REQUIRED
        );
        $params = [
            'courseName' => $coursename,
        ];
        return new external_function_parameters($params);
    }

    /**
     * Returns description of search_course() result value.
     *
     * @return \external_description
     */
    public static function search_course_returns() {
        return new external_single_structure([
            'courses' => new external_multiple_structure(
                course_summary_exporter::get_read_structure()
            )
        ]);
    }
}
