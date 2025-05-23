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
 * Usefull set of functions used throughout the plugin
 *
 * @package    report_modulecompletion
 * @copyright  2023 L’Institut Agro Enseignement à distance
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('REPORT_MODULECOMPLETION_ACTION_ADD_FILTER', 'addfilter');
define('REPORT_MODULECOMPLETION_ACTION_EDIT_FILTER', 'editfilter');
define('REPORT_MODULECOMPLETION_ACTION_COPY_FILTER', 'copyfilter');
define('REPORT_MODULECOMPLETION_ACTION_DELETE_FILTER', 'deletefilter');
define('REPORT_MODULECOMPLETION_ACTION_LOAD_FILTER', 'loadfilter');
define('REPORT_MODULECOMPLETION_ACTION_QUICK_FILTER', 'quickfilter');
define('REPORT_MODULECOMPLETION_ACTION_SAVE_QUICK_FILTER', 'savequickfilter');
define('REPORT_MODULECOMPLETION_ACTION_EXPORT', 'export');
define('FIXED_NUM_COLS', 6);

use core\notification;
use report_modulecompletion\persistents\filter as persistent_filter;
use report_modulecompletion\forms\filters as form;

/**
 * Gets the loggedin user’s filters.
 *
 * @return array The filters
 */
function report_modulecompletion_get_user_filters() {
    global $USER;
    return persistent_filter::get_records(['userid' => (int)$USER->id], 'name');
}

/**
 * Gets a filter from its id.
 *
 * @param int $filterid The filter id
 * @return persistent_filter The filter
 */
function report_modulecompletion_get_filter($filterid) {
    global $USER;
    try {
        $persistentfilter = new persistent_filter($filterid);
    } catch (moodle_exception $e) {
        throw new moodle_exception('filter_not_found', 'report_modulecompletion');
    }
    if ($persistentfilter->get('userid') !== (int)$USER->id) { // Trying to access a filter that does not belong to the user.
        throw new moodle_exception('filter_not_found', 'report_modulecompletion');
    }
    return $persistentfilter;
}

/**
 * Duplicates a filter from the given id.
 *
 * @param int $filterid The filter id
 */
function report_modulecompletion_duplicate_filter($filterid) {
    global $CFG;
    $persistentfilter = report_modulecompletion_get_filter($filterid);
    $persistentfilter->set('id', 0);
    $persistentfilter->set('name', $persistentfilter->get('name') . ' (' . get_string('copy', 'core') . ')');
    $persistentfilter->create();
    // We are done, so let's redirect somewhere.
    redirect(new moodle_url($CFG->wwwroot . '/report/modulecompletion/index.php'));
}

/**
 * Removes a filter with the given id.
 *
 * @param int $filterid The filter id
 */
function report_modulecompletion_remove_filter($filterid) {
    global $CFG;
    $persistentfilter = report_modulecompletion_get_filter($filterid);
    $persistentfilter->delete();
    // We are done, so let's redirect somewhere.
    redirect(new moodle_url($CFG->wwwroot . '/report/modulecompletion/index.php'));
}

/**
 * Loads the filters form.
 *
 * @param int|null $filterid The filter id
 * @param array $data Custom data for the form
 * @param bool $quickfilter Is it a quickfilter or a saved filter
 * @return form The form
 */
function report_modulecompletion_filter_form_action($filterid = null, $data = [], $quickfilter = false) {
    global $CFG, $PAGE, $USER;
    $persistent = null;
    if ($filterid) {
        $persistent = report_modulecompletion_get_filter($filterid);
    }
    $customdata = [
        'persistent'  => $persistent,
        'quickfilter' => $quickfilter,
        'userid'      => (int)$USER->id, // For the hidden userid field.
    ];
    $customdata = array_merge($customdata, $data);
    $action     = $quickfilter ? (isset($data['persistent']) ?
        REPORT_MODULECOMPLETION_ACTION_SAVE_QUICK_FILTER : REPORT_MODULECOMPLETION_ACTION_QUICK_FILTER) :
        ($persistent ? (REPORT_MODULECOMPLETION_ACTION_EDIT_FILTER . '&id=' . $filterid) :
        REPORT_MODULECOMPLETION_ACTION_ADD_FILTER);
    $filterform = new form($PAGE->url->out(false) . '?action=' . $action, $customdata);
    if ($quickfilter) { // We don't need to create/update a new filter in the DB.
        // We simply return the form.
        return $filterform;
    }
    if ($validateddata = $filterform->get_data()) {
        // Either it's a new filter or we're editing an existing one.
        try {
            if (empty($validateddata->id)) {
                $persistent = new persistent_filter(0, $validateddata);
                $persistent->create();
            } else {
                $persistent->from_record($validateddata);
                $persistent->update();
            }
            notification::success(get_string('changessaved'));
        } catch (Exception $e) {
            notification::error($e->getMessage());
        }

        // We are done, so let's redirect somewhere.
        redirect(new moodle_url($CFG->wwwroot . '/report/modulecompletion/index.php'));
    }
    return $filterform;
}

/**
 * Gets users from the query.
 *
 * @param string $search user query
 * @return array The results of the query
 */
function report_modulecompletion_search_users($search = '') {
    global $DB;
    return $DB->get_records_sql('SELECT * FROM {user}  WHERE (' .
        $DB->sql_like('firstname', ':firstname', false) .
        ' OR ' . $DB->sql_like('lastname', ':lastname', false) .
        ') AND deleted = 0 AND suspended = 0 ORDER BY lastname ASC, firstname ASC', [
            'firstname' => '%' . $search . '%',
            'lastname'  => '%' . $search . '%',
        ]);
}

/**
 * Gets cohorts from the query.
 *
 * @param string $search cohort query
 * @return array The results of the query
 */
function report_modulecompletion_search_cohorts($search = '') {
    global $DB;
    return $DB->get_records_sql('SELECT * FROM {cohort}  WHERE ' .
        $DB->sql_like('name', ':name', false) .
        ' ORDER BY name ASC', [
            'name' => '%' . $search . '%',
        ]);
}

/**
 * Gets courses from the query.
 *
 * @param string $search course query
 * @return array The results of the query
 */
function report_modulecompletion_search_courses($search = '') {
    global $DB;
    return $DB->get_records_sql('SELECT * FROM {course}  WHERE ' .
        $DB->sql_like('fullname', ':fullname', false) .
        ' ORDER BY fullname ASC', [
            'fullname' => '%' . $search . '%',
        ]);
}

/**
 * Gets cohorts from the list of ids.
 *
 * @param string $list list of cohorts ids
 * @return array The cohorts
 */
function report_modulecompletion_get_cohorts($list = '') {
    global $DB;
    list($insql, $inparams) = $DB->get_in_or_equal(explode(',', $list));
    return $DB->get_recordset_sql('SELECT u.firstname as user_fn, u.lastname as user_ln, h.id as cohort_id, h.name as cohort_name
        FROM {cohort} h
        JOIN {cohort_members} hm ON h.id = hm.cohortid
        JOIN {user} u ON hm.userid = u.id
        WHERE h.id ' . $insql . ' ORDER BY h.name', $inparams);
}

/**
 * Gets the reports from the given parameters.
 *
 * @param string $users The list of users id (if any)
 * @param string $cohorts The list of cohorts id (if any)
 * @param int $onlycohortscourses Whether or not to get only courses associated with selected cohorts
 * @param string $courses The list of courses id (if any)
 * @param int $startingdate The period starting date
 * @param int $endingdate The period ending date
 * @return array The reports
 */
function report_modulecompletion_get_reports(
    $users = null,
    $cohorts = null,
    $onlycohortscourses = 0,
    $courses = null,
    $startingdate = null,
    $endingdate = null
) {
    global $DB;
    $params = [];
    if ($users != null) {
        $users = explode(',', $users);
    }
    if ($cohorts != null) {
        $cohorts = explode(',', $cohorts);
        if (is_array($cohorts) && count($cohorts) > 0) {
            list($insqlcohorts, $inparamscohorts) = $DB->get_in_or_equal($cohorts);
        }
    }
    if ($courses != null) {
        $courses = explode(',', $courses);
        if (is_array($courses) && count($courses) > 0) {
            list($insqlcourses, $inparamscourses) = $DB->get_in_or_equal($courses);
        }
    }
    // Metadata.
    $metaselect  = '';
    $metafrom    = '';
    $metawhere   = '';
    $metagroupby = '';
    $metaparams  = [];
    if (get_config('report_modulecompletion', 'use_metadata') && $metas = get_config('report_modulecompletion', 'metadata_list')) {
        $metaselect = ', lmdt.fieldid AS meta_id,
      lmdt.data AS meta_data';
        $metafrom = 'LEFT OUTER JOIN {local_metadata} lmdt ON cm.id = lmdt.instanceid
      LEFT OUTER JOIN {local_metadata_field} lmdtf ON lmdt.fieldid = lmdtf.id';
        $metas       = explode(',', $metas);
        $metawhere   = ' AND (lmdt.fieldid IS NULL' . str_repeat(' OR lmdt.fieldid = ?', count($metas)) . ')';
        $metagroupby = ', lmdt.fieldid';
        $metaparams  = $metas;
    }
    $sql = "SELECT
    cmc.id,
    cmc.timemodified AS month,
    u.id AS user_id,
    c.id AS course_id,
    c.fullname AS course_name,
    cs.name AS section_name,
    m.name AS module_type,
    UPPER(u.lastname) AS last_name,
    u.firstname AS first_name,
    u.email AS email,
    cm.instance,
    (SELECT COUNT(DISTINCT cmod.id)
      FROM {modules} mods
      JOIN {course_modules} cmod ON mods.id = cmod.module
      JOIN {course} crse ON cmod.course = crse.id
      JOIN {enrol} en ON crse.id = en.courseid
      JOIN {user_enrolments} ue ON en.id = ue.enrolid
      JOIN {user} us ON ue.userid = us.id
      WHERE us.id = u.id
      AND cmod.visible = 1
      AND cmod.completion > 0";
    if (is_array($courses) && count($courses) > 0) {
        $sql .= ' AND crse.id ' . $insqlcourses;
        $params = array_merge($params, $inparamscourses);
    }
    if (is_array($cohorts) && count($cohorts) > 0 && $onlycohortscourses) {
        $sql .= ' AND en.customint1 ' . $insqlcohorts;
        $params = array_merge($params, $inparamscohorts);
    }
    $sql .= ") AS total_modules,
    (SELECT COUNT(DISTINCT cmod_course.id)
      FROM {modules} mod_course
      JOIN {course_modules} cmod_course ON mod_course.id = cmod_course.module
      JOIN {course} crse_course ON cmod_course.course = crse_course.id
      JOIN {enrol} en_course ON crse_course.id = en_course.courseid
      JOIN {user_enrolments} ue_course ON en_course.id = ue_course.enrolid
      JOIN {user} us_course ON ue_course.userid = us_course.id
      WHERE us_course.id = u.id
      AND cmod_course.visible = 1
      AND cmod_course.completion > 0
      AND crse_course.id = c.id) AS total_modules_per_course,
    (SELECT COUNT(*)
      FROM {course_modules} crse_modules,{course_sections} crse_sections
      WHERE
      ((crse_sections.availability IS NOT NULL
      AND crse_sections.availability != '{\"op\":\"&\",\"c\":[],\"showc\":[]}')
      OR (crse_modules.availability IS NOT NULL
      AND crse_modules.availability != '{\"op\":\"&\",\"c\":[],\"showc\":[]}'))
      AND crse_modules.visible = 1
      AND crse_modules.completion > 0
      AND crse_modules.course = c.id AND crse_sections.course = c.id) AS has_restrictions,
    c.fullname AS course_name,";
    if ($modules = get_config('report_modulecompletion', 'modules_list')) {
        $list     = explode(',', $modules);
        $fulllist = report_modulecompletion_get_module_types(false);
        $sql .= ' CASE';
        foreach ($list as $id) {
            $alias = 'a' . $id;
            $sql .= ' WHEN cm.module = ' . $id . ' THEN (SELECT ' .
            $alias . '.name FROM {' . $fulllist[$id] . '} ' .
            $alias . ' WHERE ' . $alias . '.id = cm.instance)';
        }
        $sql .= ' END AS module,';
    }
    $sql .= 'cmc.timemodified AS completed_on';
    $sql .= $metaselect;
    $sql .= ' FROM {course_modules_completion} cmc
    JOIN {user} u ON cmc.userid = u.id
    LEFT JOIN {cohort_members} hm ON u.id = hm.userid
    LEFT JOIN {cohort} h ON hm.cohortid = h.id
    JOIN {course_modules} cm ON cmc.coursemoduleid = cm.id
    JOIN {course} c ON cm.course = c.id
    LEFT JOIN {enrol} en ON c.id = en.courseid
    JOIN {course_sections} cs ON cm.section = cs.id
    JOIN {context} cc ON cc.instanceid = c.id AND cc.contextlevel = 50
    JOIN {modules} m ON cm.module = m.id
    JOIN {role_assignments} asg ON u.id = asg.userid AND asg.contextid = cc.id
    JOIN {role_capabilities} rc ON asg.roleid = rc.roleid
    JOIN {capabilities} cap ON rc.capability = cap.name ';
    $sql .= $metafrom;
    $sql .= ' WHERE ';
    if (isset($modules)) {
        list($inmodsql, $inmodparams) = $DB->get_in_or_equal(explode(',', $modules));
        $sql .= 'm.id ' . $inmodsql . ' AND ';
        $params = array_merge($params, $inmodparams);
    }
    $sql .= 'rc.capability = ? ';
    $params[] = 'moodle/course:isincompletionreports';
    $sql .= 'AND rc.permission = 1
    AND u.deleted = 0
    AND u.suspended = 0
    AND cm.visible = 1
    AND (cmc.completionstate = ' . COMPLETION_COMPLETE . ' OR cmc.completionstate = ' . COMPLETION_COMPLETE_PASS . ')';
    $sql .= $metawhere;
    $params = array_merge($params, $metaparams);
    if ($startingdate) {
        $sql .= ' AND cmc.timemodified >= ?';
        $params[] = $startingdate;
    }
    if ($endingdate) {
        $sql .= ' AND cmc.timemodified <= ?';
        $params[] = $endingdate;
    }
    if (is_array($users) && count($users) > 0) {
        list($insql, $inparams) = $DB->get_in_or_equal($users);
        $sql .= ' AND ' . (is_array($cohorts) && count($cohorts) > 0 ? '(' : '') . ' u.id ' . $insql;
        $params = array_merge($params, $inparams);
    }
    if (is_array($cohorts) && count($cohorts) > 0) {
        $sql .= is_array($users) && count($users) > 0 ? (' OR h.id ' . $insqlcohorts . ')') : (' AND h.id ' . $insqlcohorts);
        $params = array_merge($params, $inparamscohorts);
        if ($onlycohortscourses) {
            $sql .= ' AND en.customint1 ' . $insqlcohorts;
            $params = array_merge($params, $inparamscohorts);
        }
    }
    if (is_array($courses) && count($courses) > 0) {
        $sql .= ' AND c.id ' . $insqlcourses;
        $params = array_merge($params, $inparamscourses);
    }
    $sql .= ' GROUP BY cmc.id, cmc.timemodified, u.id, c.id, cs.name, m.name,
        cm.module, cm.instance, u.lastname, u.firstname, c.fullname';
    $sql .= $metagroupby;
    $sql .= " ORDER BY cmc.timemodified DESC, u.lastname ASC";
    return $DB->get_recordset_sql($sql, $params);
}

/**
 * Exports given reports to CSV file.
 *
 * @param array $reports The reports to export
 */
function report_modulecompletion_export_csv($reports = []) {
    global $CFG;
    require_once($CFG->dirroot . '/lib/csvlib.class.php');
    $writer = new csv_export_writer();
    $writer->set_filename('export_');
    $writer->add_data(report_modulecompletion_get_export_headers($reports[0]));
    foreach ($reports as $report) {
        // We browse every user, then every course and every module.
        foreach ($report['courses'] as $course) {
            foreach ($course['completions']['rows'] as $module) {
                $data = [
                    $module[0],
                    strtoupper($report['last_name']) . ' ' . $report['first_name'],
                    $report['email'],
                    str_replace(['"', PHP_EOL], ['\'', ' '], strip_tags($module[1])),
                    $module[2],
                    $module[3],
                    str_replace(['"', PHP_EOL], ['\'', ' '], $module[4]),
                    $module[5],
                ];
                $data = array_merge(
                    $data,
                    array_slice($module, FIXED_NUM_COLS),
                    array_column($course['meta_totals'], 'counter'),
                    array_column($report['meta_totals'], 'counter')
                );
                $data[] = $course['completed_modules'] . ' ' . get_string('outof', 'mod_lesson', $course['total_modules']);
                $data[] = $course['progress_bar']['progress'] . '%';
                $data[] = $report['completed_modules'] . ' ' . get_string('outof', 'mod_lesson', $report['total_modules']);
                $data[] = $report['progress bar']['progress'] . '%';
                $data[] = $report['most_recent_completed_module_date'];
                $writer->add_data($data);
            }
        }
    }
    $writer->download_file();
}

/**
 * Exports given reports to Excel file.
 *
 * @param array $reports The reports to export
 */
function report_modulecompletion_export_xlsx($reports = []) {
    global $CFG;
    require_once($CFG->dirroot . '/lib/excellib.class.php');
    // Creating a workbook.
    $workbook = new MoodleExcelWorkbook('-');
    // Sending HTTP headers.
    $workbook->send('export_' . time());
    $formatdate = $workbook->add_format();
    $formatdate->set_num_format(15);
    // Adding the worksheet.
    $myxls = $workbook->add_worksheet('Export ' .
        date(get_string('full_date_format', 'report_modulecompletion')));
    $headers = report_modulecompletion_get_export_headers($reports[0]);
    // Format and styling.
    $headersformat = $workbook->add_format([
        'align'    => 'center',
        'color'    => 'white',
        'bg_color' => 'teal',
    ]);
    $myxls->set_column(0, count($headers) - 1, 25);
    $myxls->set_row(0, null, $headersformat);
    $colpos = 0;
    // Write headers.
    foreach ($headers as $header) {
        $myxls->write_string(0, $colpos++, $header);
    }

    // Write data.
    $rowpos = 1;
    foreach ($reports as $report) {
        // We browse every user, then every course and every module.
        foreach ($report['courses'] as $course) {
            foreach ($course['completions']['rows'] as $module) {
                $colpos = 0;
                $myxls->write_string($rowpos, $colpos++, $module[0]);
                $myxls->write_string($rowpos, $colpos++, strtoupper($report['last_name']) . ' ' . $report['first_name']);
                $myxls->write_string($rowpos, $colpos++, $report['email']);
                $myxls->write_string($rowpos, $colpos++, strip_tags($module[1]));
                $myxls->write_string($rowpos, $colpos++, $module[2]);
                $myxls->write_string($rowpos, $colpos++, $module[3]);
                $myxls->write_string($rowpos, $colpos++, $module[4]);
                $myxls->write_string($rowpos, $colpos++, $module[5]);
                // Metadata.
                foreach (array_slice($module, FIXED_NUM_COLS) as $meta) {
                    if (is_numeric($meta)) {
                        $myxls->write_number($rowpos, $colpos++, $meta);
                    } else {
                        $myxls->write_string($rowpos, $colpos++, $meta);
                    }
                }
                // Course totals.
                foreach ($course['meta_totals'] as $ctot) {
                    if (is_numeric($ctot['counter'])) {
                        $myxls->write_number($rowpos, $colpos++, $ctot['counter']);
                    } else {
                        $myxls->write_string($rowpos, $colpos++, $ctot['counter']);
                    }
                }
                // User totals.
                foreach ($report['meta_totals'] as $utot) {
                    if (is_numeric($utot['counter'])) {
                        $myxls->write_number($rowpos, $colpos++, $utot['counter']);
                    } else {
                        $myxls->write_string($rowpos, $colpos++, $utot['counter']);
                    }
                }
                $myxls->write_string($rowpos, $colpos++, $course['completed_modules'] . ' / ' . $course['total_modules']);
                $myxls->write_string($rowpos, $colpos++, $course['progress_bar']['progress'] . '%');
                $myxls->write_string($rowpos, $colpos++, $report['completed_modules'] . ' / ' . $report['total_modules']);
                $myxls->write_string($rowpos, $colpos++, $report['progress_bar']['progress'] . '%');
                $myxls->write_date(
                    $rowpos,
                    $colpos++,
                    (new DateTime($report['most_recent_completed_module_date']))->getTimestamp(),
                    $formatdate
                );
                $rowpos++;
            }
        }
    }
    $workbook->close();
    exit;
}

/**
 * Gets headers string for CSV and Excel file export.
 *
 * @param array $report A single report
 * @return array An array of headers
 */
function report_modulecompletion_get_export_headers($report) {
    $headers = [get_string('month_header', 'report_modulecompletion'),
        get_string('user_header', 'report_modulecompletion'),
        get_string('user_email_header', 'report_modulecompletion'),
        get_string('course_header', 'report_modulecompletion'),
        get_string('section_header', 'report_modulecompletion'),
        get_string('module_type_header', 'report_modulecompletion'),
        get_string('module_header', 'report_modulecompletion'),
        get_string('completed_header', 'report_modulecompletion'),
    ];
    /**
     * Prefixes 'Total' in headers.
     *
     * @param string $item The string to prefix
     * @param int $key Unused array index
     * @param string $prefix The prefix
     */
    function prefix_totals(&$item, $key, $prefix) {
        $item = $prefix . $item;
    }
    $course            = $report['courses'][0];
    $coursemetaheaders = array_column($course['meta_totals'], 'name');
    array_walk($coursemetaheaders, 'prefix_totals', 'Total (' . get_string('course') . ') ');
    $usermetaheaders = array_column($report['meta_totals'], 'name');
    array_walk($usermetaheaders, 'prefix_totals', 'Total ');
    $headers = array_merge(
        $headers,
        array_slice($course['completions']['headers'], FIXED_NUM_COLS),
        $coursemetaheaders,
        $usermetaheaders
    );
    $headers[] = get_string('course_completed_header', 'report_modulecompletion');
    $headers[] = get_string('course_completed_percent_header', 'report_modulecompletion');
    $headers[] = get_string('total_completed_header', 'report_modulecompletion');
    $headers[] = get_string('total_completed_percent_header', 'report_modulecompletion');
    $headers[] = get_string('last_completion_date', 'report_modulecompletion');
    return $headers;
}

/**
 * Gets list of available modules on the platform.
 *
 * @param bool $withlabel Whether to fetch modules with label
 * @return array List of available modules
 */
function report_modulecompletion_get_module_types($withlabel = true) {
    global $DB;
    $types = $DB->get_records_sql('SELECT * FROM {modules}');
    $ret   = [];
    foreach ($types as $type) {
        $ret[$type->id] = $withlabel ? get_string('modulename', 'mod_' . $type->name) : $type->name;
    }
    return $ret;
}

/**
 * Gets modules metadata.
 *
 * @param bool $asobject Whether to return the list as object or array
 * @return stdClass|array The modules metadata
 */
function report_modulecompletion_get_modules_metadata($asobject = false) {
    global $DB;
    if ($DB->get_manager()->table_exists('local_metadata')) {
        $metas = $DB->get_records_sql('SELECT * FROM {local_metadata_field} WHERE contextlevel = 70');
        if ($asobject) {
            return $metas;
        }
        $ret = [];
        foreach ($metas as $meta) {
            $ret[$meta->id] = ucwords($meta->name);
        }
        return $ret;
    }
    return false;
}
