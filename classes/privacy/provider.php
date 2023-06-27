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

namespace report_modulecompletion\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\writer;
use core_privacy\local\request\userlist;
use core_privacy\local\request\approved_userlist;
use report_modulecompletion\persistents\filter;

/**
 * Privacy Subsystem implementation for report_modulecompletion.
 *
 * @package    report_modulecompletion
 * @copyright  2023 L’Institut Agro Enseignement à distance
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {

    /**
     * Returns metadata about the component
     *
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(filter::TABLE, [
            'userid' => 'privacy:metadata:filter:userid',
            'name' => 'privacy:metadata:filter:name',
            'users' => 'privacy:metadata:filter:users',
            'cohorts' => 'privacy:metadata:filter:cohorts',
            'only_cohorts_courses' => 'privacy:metadata:filter:only_cohorts_courses',
            'courses' => 'privacy:metadata:filter:courses',
            'starting_date' => 'privacy:metadata:filter:starting_date',
            'ending_date' => 'privacy:metadata:filter:ending_date',
            'order_by_column' => 'privacy:metadata:filter:order_by_column',
            'order_by_type' => 'privacy:metadata:filter:order_by_type',
        ], 'privacy:metadata:filter');

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     * @return contextlist $contextlist The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {
        $contextlist = new contextlist();
        $contextlist->add_from_sql(
            'SELECT c.id
                FROM {context} c
                JOIN {report_modulecompletion} f ON f.userid = :userid
                WHERE contextlevel = :contextlevel',
            ['userid' => $userid, 'contextlevel' => CONTEXT_SYSTEM]
        );
        return $contextlist;
    }

    /**
     * Get the list of users within a specific context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if (!$context instanceof \context_system) {
            return;
        }

        $sql = 'SELECT userid
                FROM {report_modulecompletion}';

        $userlist->add_from_sql('userid', $sql, []);
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;
        $context = \context_system::instance();
        if (!$contextlist->count() || !in_array($context->id, $contextlist->get_contextids())) {
            return;
        }

        $user = $contextlist->get_user();
        $sql = 'SELECT * FROM {report_modulecompletion} WHERE userid = ?';
        $rs = $DB->get_recordset_sql($sql, [$user->id]);
        foreach ($rs as $record) {
            $subcontext = [get_string('pluginname', 'report_modulecompletion'), $record->name];
            $users = 'N/A';
            $cohorts = 'N/A';
            $courses = 'N/A';
            if ($record->users) {
                $users = '';
                list($usersql, $userparams) = $DB->get_in_or_equal(explode(',', $record->users));
                $rs = $DB->get_recordset_sql('SELECT firstname, lastname, email FROM {user} WHERE id ' . $usersql, $userparams);
                foreach ($rs as $rec) {
                    $users .= $rec->firstname . ' ' . $rec->lastname . ' (' . $rec->email . '), ';
                }
                $users = substr($users, 0, -2);
            }
            if ($record->cohorts) {
                $cohorts = '';
                list($cohortsql, $cohortparams) = $DB->get_in_or_equal(explode(',', $record->cohorts));
                $rs = $DB->get_recordset_sql('SELECT name FROM {cohort} WHERE id ' . $cohortsql, $cohortparams);
                foreach ($rs as $rec) {
                    $cohorts .= $rec->name . ', ';
                }
                $cohorts = substr($cohorts, 0, -2);
            }

            if ($record->courses) {
                $courses = '';
                list($coursesql, $courseparams) = $DB->get_in_or_equal(explode(',', $record->courses));
                $rs = $DB->get_recordset_sql('SELECT fullname FROM {course} WHERE id ' . $coursesql, $courseparams);
                foreach ($rs as $rec) {
                    $courses .= $rec->fullname . ', ';
                }
                $courses = substr($courses, 0, -2);
            }

            $filter = (object)[
                'id' => $record->id,
                'userid' => transform::user($record->userid),
                'name' => $record->name,
                'users' => $users,
                'cohorts' => $cohorts,
                'only_cohorts_courses' => transform::yesno($record->only_cohorts_courses),
                'courses' => $courses,
                'starting_date' => transform::datetime($record->starting_date),
                'ending_date' => transform::datetime($record->ending_date),
                'order_by_column' => get_string('form_order_by_' . $record->order_by_column, 'report_modulecompletion'),
                'order_by_type' => get_string('form_order_by_' . $record->order_by_type, 'report_modulecompletion'),
            ];
            writer::with_context($context)->export_data($subcontext, $filter);
        }
        $rs->close();
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * We delete filters in case of system context.
     *
     * @param context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;
        // Filters can only be defined in system context.
        if ($context->id == \context_system::instance()->id) {
            $DB->delete_records('report_modulecompletion', []);
        }
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();

        if ($context instanceof \context_system) {
            list($usersql, $userparams) = $DB->get_in_or_equal($userlist->get_userids(), SQL_PARAMS_NAMED);
            $DB->delete_records_select('report_modulecompletion', "userid {$usersql}", $userparams);
        }
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;
        $context = \context_system::instance();
        if (!$contextlist->count() || !in_array($context->id, $contextlist->get_contextids())) {
            return;
        }

        $DB->delete_records('report_modulecompletion', ['userid' => $contextlist->get_user()->id]);
    }
}
