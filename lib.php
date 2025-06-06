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
 * Report module completion lib file.
 *
 * @package    report_modulecompletion
 * @copyright  2025 L’Institut Agro Enseignement à distance
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/report/modulecompletion/locallib.php');
use core_user\output\myprofile\tree;
use core_user\output\myprofile\node;

/**
 * Triggered as soon as practical on every moodle bootstrap after config has
 * been loaded.
 *
 * @return void
 */
function report_modulecompletion_after_config() {
    // Check if some activity module was selected in the settings.and has been removed from Moodle.
    $moduleslist = report_modulecompletion_get_module_types(false); // Modules list from DB.
    $trackedmodules = explode(',', get_config('report_modulecompletion', 'modules_list')); // Tracked modules list.
    if ($trackedmodules && count($trackedmodules) === 1 && $trackedmodules[0] === '') {
        // This is only supposed to happen during the plugin installation.
        return;
    }
    // We then filter out the modules that are not existant anymore.
    $purgedmodules = array_filter($trackedmodules, fn ($moduleid) => array_key_exists($moduleid, $moduleslist));
    // Finally, we set the config with the updated list of modules.
    set_config('modules_list', implode(',', $purgedmodules), 'report_modulecompletion');
}

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
    global $USER;
    $context = context_user::instance($user->id);
    // We allow users to see their own achievement reports without checking the for capability.
    if (has_capability('report/modulecompletion:view', $context) || $user->id == $USER->id) {
        $url = new moodle_url('/report/modulecompletion/user.php', ['id' => $user->id]);
        $node = new node('reports', 'modulecompletion', get_string('pluginname', 'report_modulecompletion'),
            null, $url);
        $tree->add_node($node);
    }
}
