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
 * Lib file for report modulecompletion.
 *
 * @package    report_modulecompletion
 * @copyright  2025 L’Institut Agro Enseignement à distance
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_user\output\myprofile\tree;
use core_user\output\myprofile\node;

/**
 * Add nodes to myprofile page.
 *
 * @param tree $tree Tree object
 * @param stdClass $user user object
 * @param bool $iscurrentuser
 * @param stdClass $course Course object
 *
 * @return bool
 */
function report_modulecompletion_myprofile_navigation(tree $tree, $user, $iscurrentuser, $course) {
    $context = context_user::instance($user->id);
    if (has_capability('report/modulecompletion:view', $context)) {
        $url = new moodle_url('/report/modulecompletion/user.php', ['id' => $user->id]);
        $node = new node('reports', 'modulecompletion', get_string('pluginname', 'report_modulecompletion'),
            null, $url);
        $tree->add_node($node);
    }
}
