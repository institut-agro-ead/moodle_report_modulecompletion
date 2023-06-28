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
 * EN lang strings.
 *
 * @package    report_modulecompletion
 * @copyright  2023 L’Institut Agro Enseignement à distance
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Module completion tracker';
$string['meta_settings'] = 'Metadata settings';
$string['modulecompletion:view'] = 'Access module completion tracker';
$string['categoryname'] = 'Module reports';
$string['configmodulecompletion'] = 'Tracking module completion';
$string['modules_list_label'] = 'Modules list';
$string['modules_list_description'] = 'Select which modules need to be used for tracking students modules completion';
$string['use_metadata_label'] = 'Use metadata plugin';
$string['use_metadata_description'] = 'If the metadata plugin is installed on your Moodle, you can use it with this plugin and display modules metadata';
$string['metadata_list_label'] = 'Pick metadata';
$string['metadata_list_description'] = 'Select metadata that need to be shown in reports';
$string['numeric_metadata_list_label'] = 'Pick numeric metadata';
$string['numeric_metadata_list_description'] = 'Select metadata which is supposed to be considered as numerical information. A total will be calculated for each course and a total for each student';
$string['numeric_metadata_conversion'] = 'Metadata conversion';
$string['numeric_metadata_conversion_description'] = '<p>Pick how to convert numeric metadata. Enter a formula applied to the value.<br><strong>Example:</strong> to convert <strong>minutes</strong> metadata to hours, you just have to divide by 60. Simply enter <strong>/60</strong><br><em>NB : Accepted operators : <strong>+</strong>, <strong>-</strong>, <strong>*</strong>, <strong>/</strong>, <strong>%</strong>. Use of parentheses is highly experimental and could work in an unexpected way</em>.</p><p>Then pick a label for the converted value, example <strong>hour(s)</strong>.</p>';
$string['numeric_metadata_formula'] = '(Formula)';
$string['numeric_metadata_formula_description'] = 'If the formula is incorrect, it will be ignored.';
$string['numeric_metadata_label'] = '(Label)';

// Sql formats.
$string['month_date_format'] = 'Y-m';
$string['full_date_format'] = 'Y-m-d';

// Form.
$string['user_label'] = 'Enter a student name';
$string['user_placeholder'] = 'Name';
$string['cohort_label'] = 'Enter a cohort name';
$string['cohort_placeholder'] = 'Name';
$string['cohorts'] = 'Cohorts';
$string['course_label'] = 'Enter a course name';
$string['course_placeholder'] = 'Name';

$string['form_filter_name'] = 'Filter name';
$string['form_filter_name_placeholder'] = 'Name';
$string['form_save_filter'] = 'Save filter';
$string['form_only_cohorts_courses'] = 'Only cohorts\' courses';
$string['form_only_cohorts_courses_help'] = 'Only fetch courses and modules for which selected cohorts are enrolled';
$string['form_starting_date'] = 'Starting date';
$string['form_ending_date'] = 'Ending date';
$string['form_order_by_column'] = 'Ordered By';
$string['form_order_by_type'] = 'Direction';
$string['form_order_by_student'] = 'Student';
$string['form_order_by_completion'] = 'Completion';
$string['form_order_by_last_completed'] = 'Date of last completion';
$string['form_order_by_asc'] = 'Ascending';
$string['form_order_by_desc'] = 'Descending';
$string['form_quickfilter_submit'] = 'Filter';
$string['form_quickfilter_name'] = 'Quick filter';

// Form errors.
$string['form_name_required'] = 'You must give a name to your filter';
$string['form_missing_starting_date'] = 'Starting date must be given and correctly formatted';
$string['form_missing_ending_date'] = 'Ending date must be given and correctly formatted';
$string['form_starting_date_must_be_anterior'] = 'Starting date must be anterior to ending date';
$string['form_user_not_found'] = 'Requested user does not exist';
$string['form_cohort_not_found'] = 'Requested cohort does not exist';
$string['form_course_not_found'] = 'Requested course does not exist';

// Templates.
$string['max_achievement_percentage'] = 'Maximum percentage achieved by a student';
$string['reports_count'] = 'Number of results';
$string['completed_modules'] = 'achieved modules';
$string['last_completion_date'] = 'Last module completion date';
$string['has_restrictions'] = 'This course contains section(s) and/or module(s) with restriction(s). These modules will be included in the total number of course modules even if the student does not have access to them';
$string['backtofilters'] = 'Back to filters';
$string['no_reports'] = 'Not result found';
$string['expand'] = 'Expand';
$string['collapse'] = 'Collapse';
$string['show_all'] = 'Show All';
$string['hide_all'] = 'Hide All';

$string['your_filters'] = 'Your filters';
$string['quick_filter'] = 'Quick filter';
$string['add_filter'] = 'Add a new filter';
$string['load_filter_title'] = 'Load this filter';
$string['edit_filter_title'] = 'Edit this filter';
$string['copy_filter_title'] = 'Duplicate this filter';
$string['delete_filter_title'] = 'Delete this filter';

// Modal.
$string['confirm_filter_deletion'] = 'Are you sure you want to delete this filter ?';

// Error.
$string['no_template'] = 'This plugin uses templates defined in the Boost Theme, your theme should inherit Boost.';
$string['filter_id_required'] = 'Filter id parameter required';
$string['filter_not_found'] = 'This is not the filter you are looking for...';
$string['export_type_required'] = 'Export type parameter required (csv or xlsx)';

// Table/export headers.
$string['month_header'] = 'Month';
$string['user_header'] = 'Student name';
$string['user_email_header'] = 'Student email';
$string['course_header'] = 'Course name';
$string['section_header'] = 'Section name';
$string['module_type_header'] = 'Module type';
$string['module_header'] = 'Module name';
$string['completed_header'] = 'Completed on';
$string['course_completed_header'] = 'Completed course modules';
$string['course_completed_percent_header'] = 'Modules in percentage';
$string['total_completed_header'] = 'Total modules completed';
$string['total_completed_percent_header'] = 'Total in percentage';

// Privacy.
$string['privacy:metadata'] = 'The ModuleCompletion plugin does not store any personal data.';
