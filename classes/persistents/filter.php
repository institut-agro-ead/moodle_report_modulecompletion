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


namespace report_modulecompletion\persistents;

use core\persistent;
use core_user;
use lang_string;

/**
 * DB storage managment for filters
 *
 * @package    report_modulecompletion
 * @copyright  2023 L’Institut Agro Enseignement à distance
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class filter extends persistent {

    /**
     * Name of the table.
     */
    const TABLE = 'report_modulecompletion';

    /**
     * Return the definition of the properties of this model.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
            'userid' => [
                'type' => PARAM_INT,
            ],
            'name' => [
                'type' => PARAM_RAW,
            ],
            'users' => [
                'type' => PARAM_RAW,
                'null' => NULL_ALLOWED
            ],
            'cohorts' => [
                'type' => PARAM_RAW,
                'null' => NULL_ALLOWED
            ],
            'only_cohorts_courses' => [
                'type' => PARAM_INT,
                'default' => 0,
                'choices' => [0, 1]
            ],
            'courses' => [
                'type' => PARAM_RAW,
                'null' => NULL_ALLOWED
            ],
            'starting_date' => [
                'type' => PARAM_INT,
                'default' => function () {
                    return \time();
                }
            ],
            'ending_date' => [
                'type' => PARAM_INT,
                'default' => function () {
                    return \time();
                }
            ],
            'order_by_column' => [
                'type' => PARAM_RAW,
                'choices' => ['student', 'completion', 'last_completed']
            ],
            'order_by_type' => [
                'type' => PARAM_RAW,
                'choices' => ['asc', 'desc']
            ]
        ];
    }

    /**
     * Convenience method to set the user ID.
     *
     * @param object|int $idorobject The user ID, or a user object.
     */
    protected function set_userid($idorobject) {
        $userid = $idorobject;
        if (is_object($idorobject)) {
            $userid = $idorobject->id;
        }
        $this->raw_set('userid', $userid);
    }

    /**
     * Validate the user ID.
     *
     * @param int $value The value.
     * @return true|lang_string
     */
    protected function validate_userid($value) {
        if (!core_user::is_real_user($value, true)) {
            return new lang_string('invaliduserid', 'error');
        }
        return true;
    }
}
