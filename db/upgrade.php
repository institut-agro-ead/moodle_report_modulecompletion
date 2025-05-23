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
 * DB upgrading definitions.
 *
 * @package    report_modulecompletion
 * @copyright  2023 L’Institut Agro Enseignement à distance
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Determines if the plugin’s table needs to be updated during the upgrading process of the plugin.
 *
 * @param int $oldversion the old version number of the plugin
 */
function xmldb_report_modulecompletion_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    // Adding order_by.
    if ($oldversion < 2018032800) {
        // Define field order_by_column to be added to report_modulecompletion.
        $table = new xmldb_table('report_modulecompletion');

        $field = new xmldb_field('order_by_column', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, 'student', 'ending_date');
        // Conditionally launch add field order_by_column.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('order_by_type', XMLDB_TYPE_CHAR, '5', null, XMLDB_NOTNULL, null, 'asc', 'order_by_column');
        // Conditionally launch add field order_by_type.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Modulecompletion savepoint reached.
        upgrade_plugin_savepoint(true, 2018032800, 'report', 'modulecompletion');
    }

    // Adding cohorts field.
    if ($oldversion < 2018120500) {
        // Define field cohorts to be added to report_modulecompletion.
        $table = new xmldb_table('report_modulecompletion');

        $field = new xmldb_field('cohorts', XMLDB_TYPE_TEXT, null, null, null, null, null, null);
        // Conditionally launch add field cohorts.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Modulecompletion savepoint reached.
        upgrade_plugin_savepoint(true, 2018120500, 'report', 'modulecompletion');
    }

    // Adding cohorts field.
    if ($oldversion < 2020120800) {
        // Define field only_cohorts_courses to be added to report_modulecompletion.
        $table = new xmldb_table('report_modulecompletion');

        $field = new xmldb_field('only_cohorts_courses', XMLDB_TYPE_INTEGER, 1, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, null);
        // Conditionally launch add field cohorts.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Modulecompletion savepoint reached.
        upgrade_plugin_savepoint(true, 2020120800, 'report', 'modulecompletion');
    }

    if ($oldversion < 2025051300) {
        // Convert the old modules_list setting to the new one (moduleslist).
        $moduleslist = get_config('report_modulecompletion', 'modules_list');
        set_config(
            'moduleslist',
            $moduleslist,
            'report_modulecompletion'
        );

        upgrade_plugin_savepoint(true, 2025051300, 'report', 'modulecompletion');
    }

    return true;
}
