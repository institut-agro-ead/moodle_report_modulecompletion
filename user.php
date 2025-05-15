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
 * Modules completion report for one specific user
 *
 * @package    report_modulecompletion
 * @copyright  2025 L’Institut Agro Enseignement à distance
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/completionlib.php');
require_once($CFG->dirroot . '/report/modulecompletion/locallib.php');

use report_modulecompletion\output\user_reports;

require_login();

$userid = required_param('id', PARAM_INT);
$user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);
// We allow users to see their own achievement reports without checking the for capability.
if ((int)$USER->id !== $userid) {
    $context = context_user::instance($userid);
    require_capability('report/modulecompletion:view', $context);
}

$PAGE->set_context($context);
$url = new moodle_url('/report/modulecompletion/index.php', ['user' => $user->id]);
$PAGE->set_url($url);
$PAGE->set_title($SITE->shortname . ': ' . get_string('configmodulecompletion', 'report_modulecompletion'));
$output = $PAGE->get_renderer('report_modulecompletion');

$out = $output->header();

$action = optional_param('action', null, PARAM_ALPHANUM);
switch ($action) {
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
        $reports = report_modulecompletion_get_reports($user->id, null, 0, null, $user->firstaccess, time());
        $reportsrenderable = new user_reports($user, $reports);
        $formattedreports = $reportsrenderable->export_for_template($output);
        // We need to convert the stdClass to an array for the export function.
        ('report_modulecompletion_export_' . $type)([json_decode((string) json_encode($formattedreports), true)]);
        break;
    default:
        $reports = report_modulecompletion_get_reports($user->id, null, 0, null, $user->firstaccess, time());
        $out .= $output->render_user_reports($user, $reports);
        break;
}

$reports->close();
echo $out . $output->footer();
