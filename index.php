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

/**
 * Default file where all the requests of the plugin are processed
 *
 * @package    report_modulecompletion
 * @copyright  2023 L’Institut Agro Enseignement à distance
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/report/modulecompletion/locallib.php');

use report_modulecompletion\persistents\filter;
use report_modulecompletion\output\reports;
use core\output\mustache_template_finder;

require_login();

$context = context_system::instance();
require_capability('report/modulecompletion:view', $context);

admin_externalpage_setup('reportmodulecompletion', '', null, '', ['pagelayout' => 'report']);
$PAGE->set_context($context);
$PAGE->set_title($SITE->shortname . ': ' . get_string('configmodulecompletion', 'report_modulecompletion'));
$output = $PAGE->get_renderer('report_modulecompletion');

$out = $output->header();

// Checks if a template from core_form exists.
// If it does not exist, it means that the current theme is not inherited from Boost and might not use templates.
// Is there an easier way to check this ?
try {
    mustache_template_finder::get_template_filepath('core_form/element-template-inline');
} catch (moodle_exception $e) {
    $out .= html_writer::tag(
        'div',
        get_string('no_template', 'report_modulecompletion'),
        ['class' => 'bg-danger text-white p-2 mb-2']
    );
    echo $out . $output->footer();
    exit;
}

$action  = optional_param('action', null, PARAM_ALPHANUM);
$checkid = function () {
    $id = optional_param('id', null, PARAM_INT);
    if (!$id) {
        $out .= $output->render_error(get_string('filter_id_required', 'report_modulecompletion'));
        echo $out . $output->footer();
        exit;
    }
    return $id;
};
global $SESSION;

// If we don't need the session quick filter, we remove it.
if (isset($SESSION->quick_filter) && $action !== REPORT_MODULECOMPLETION_ACTION_EXPORT &&
    $action !== REPORT_MODULECOMPLETION_ACTION_SAVE_QUICK_FILTER) {
    unset($SESSION->quick_filter);
}

switch ($action) {
    case REPORT_MODULECOMPLETION_ACTION_ADD_FILTER:
        $form = report_modulecompletion_filter_form_action();
        $out .= $output->render_form($form);
        break;
    case REPORT_MODULECOMPLETION_ACTION_EDIT_FILTER:
        $id = $checkid();
        try {
            $form = report_modulecompletion_filter_form_action($id);
        } catch (moodle_exception $e) {
            $out .= $output->render_error($e->getMessage());
        }
        $out .= $output->render_form($form);
        break;
    case REPORT_MODULECOMPLETION_ACTION_COPY_FILTER:
        $id = $checkid();
        try {
            report_modulecompletion_duplicate_filter($id);
        } catch (moodle_exception $e) {
            $out .= $output->render_error($e->getMessage());
        }
        break;
    case REPORT_MODULECOMPLETION_ACTION_DELETE_FILTER:
        $id = $checkid();
        try {
            require_sesskey();
            report_modulecompletion_remove_filter($id);
        } catch (moodle_exception $e) {
            $out .= $output->render_error($e->getMessage());
        }
        break;
    case REPORT_MODULECOMPLETION_ACTION_LOAD_FILTER:
        $id = $checkid();
        try {
            $filter  = report_modulecompletion_get_filter($id);
            $reports = report_modulecompletion_get_reports(
                $filter->get('users'),
                $filter->get('cohorts'),
                $filter->get('only_cohorts_courses'),
                $filter->get('courses'),
                $filter->get('starting_date'),
                $filter->get('ending_date')
            );
            $out .= $output->render_reports($filter, $reports);
        } catch (moodle_exception $e) {
            $out .= $output->render_error($e->getMessage());
        }
        break;
    case REPORT_MODULECOMPLETION_ACTION_QUICK_FILTER:
        $form = report_modulecompletion_filter_form_action(null, [], true);
        // The form is valid, we fetch the reports and show them.
        if ($validateddata = $form->get_data()) {
            // We simulate a new filter to save.
            $filter = new filter(0, $validateddata);
            // We save it in session so we can save it later or export results with it.
            $SESSION->quick_filter = $validateddata;
            $reports               = report_modulecompletion_get_reports(
                $filter->get('users'),
                $filter->get('cohorts'),
                $filter->get('only_cohorts_courses'),
                $filter->get('courses'),
                $filter->get('starting_date'),
                $filter->get('ending_date')
            );
            $out .= $output->render_reports($filter, $reports);
        } else {
            $filters = report_modulecompletion_get_user_filters();
            $out .= $output->render_filters_list($filters, $form);
        }
        break;
    case REPORT_MODULECOMPLETION_ACTION_SAVE_QUICK_FILTER:
        if (isset($SESSION->quick_filter)) {
            // We simulate a new filter to save.
            $persistent = new filter(0, $SESSION->quick_filter);
            $form       = report_modulecompletion_filter_form_action(null, ['persistent' => $persistent]);
            $out .= $output->render_form($form);
        } else {
            redirect(new moodle_url($CFG->wwwroot . '/report/modulecompletion/index.php'));
        }
        break;
    case REPORT_MODULECOMPLETION_ACTION_EXPORT:
        $type = optional_param('type', null, PARAM_ALPHANUM);
        if (!$type) {
            $type = 'csv';
        }
        if (!in_array(strtolower($type), ['csv', 'xlsx'])) {
            $out .= $output->render_error(get_string('export_type_required', 'report_modulecompletion'));
            echo $out . $output->footer();
            exit;
        }
        if (isset($SESSION->quick_filter)) {
            // We simulate a new filter to save.
            $filter = new filter(0, $SESSION->quick_filter);
            $data   = $SESSION->quick_filter;
        } else {
            $id = $checkid();
            try {
                $filter = report_modulecompletion_get_filter($id);
                $data    = $filter->to_record();
            } catch (moodle_exception $e) {
                $out .= $output->render_error($e->getMessage());
                echo $out . $output->footer();
            }
        }
        $reports = report_modulecompletion_get_reports(
            $data->users,
            $data->cohorts,
            $data->only_cohorts_courses,
            $data->courses,
            $data->starting_date,
            $data->ending_date
        );
        $reportsrenderable = new reports($filter, $reports);
        $formattedreports  = $reportsrenderable->export_for_template($output)->reports;
        ('report_modulecompletion_export_' . $type)($formattedreports);
        break;
    default:
        $filters = report_modulecompletion_get_user_filters();
        $form    = report_modulecompletion_filter_form_action(null, [], true);
        $out .= $output->render_filters_list($filters, $form);
        break;
}

echo $out . $output->footer();
