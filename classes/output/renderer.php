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

use stdClass;
use plugin_renderer_base;

/**
 * Report module completion renderer.
 *
 * @package    report_modulecompletion
 * @copyright  2023 L’Institut Agro Enseignement à distance
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {

    /**
     * Renders the filtering form.
     *
     * @param \report_modulecompletion\forms\filters $form The form to render
     * @return string|bool
     */
    public function render_form($form) {
        $filterrenderable = new filter_form($form);
        $data = $filterrenderable->export_for_template($this);
        return $this->render_from_template('report_modulecompletion/filter_form', $data);
    }

    /**
     * Renders the the list of filters and a quick form.
     *
     * @param array $filters The filters to render
     * @param \report_modulecompletion\forms\filters $form The form to render
     * @return string|bool
     */
    public function render_filters_list($filters, $form) {
        $listrenderable = new filters_list($filters);
        $data = $listrenderable->export_for_template($this);
        $filterrenderable = new filter_form($form);
        $data->form = (array) $filterrenderable->export_for_template($this);
        return $this->render_from_template('report_modulecompletion/filters_list', $data);
    }

    /**
     * Renders reports for the given filter.
     *
     * @param \report_modulecompletion\persistents\filter $filter The filter from which to get the reports
     * @param array $reports The reports
     * @return string|bool
     */
    public function render_reports($filter, $reports) {
        $reportsrenderable = new reports($filter, $reports);
        $data = $reportsrenderable->export_for_template($this);
        return $this->render_from_template('report_modulecompletion/reports', $data);
    }

    /**
     * Renders reports for a specific user.
     *
     * @param stdClass $user The user
     * @param array $reports The reports
     * @return string|bool
     */
    public function render_user_reports($user, $reports) {
        $reportsrenderable = new user_reports($user, $reports);
        $data = $reportsrenderable->export_for_template($this);
        return $this->render_from_template('report_modulecompletion/user_reports', $data);
    }

    /**
     * Renders a specific error.
     *
     * @param string $error The error to display
     * @return string|bool
     */
    public function render_error(string $error) {
        $data = new stdClass();
        $data->error = $error;
        return $this->render_from_template('report_modulecompletion/error', $data);
    }
}
