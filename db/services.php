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
 * Linking Ajax calls to external API methods.
 *
 * @package    report_modulecompletion
 * @copyright  2023 L’Institut Agro Enseignement à distance
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'report_modulecompletion_get_users' => [
        'classname' => 'report_modulecompletion\external',
        'methodname' => 'search_user',
        'classpath' => '',
        'description' => 'Search for user(s) with the given string',
        'type' => 'read',
        'capabilities' => 'moodle/user:viewalldetails',
        'ajax' => true,
    ],
    'report_modulecompletion_get_cohorts' => [
        'classname' => 'report_modulecompletion\external',
        'methodname' => 'search_cohort',
        'classpath' => '',
        'description' => 'Search for cohort(s) with the given string',
        'type' => 'read',
        'capabilities' => 'moodle/cohort:view',
        'ajax' => true,
    ],
    'report_modulecompletion_get_courses' => [
        'classname' => 'report_modulecompletion\external',
        'methodname' => 'search_course',
        'classpath' => '',
        'description' => 'Search for course(s) with the given string',
        'type' => 'read',
        'capabilities' => 'moodle/course:view',
        'ajax' => true,
    ]
];
