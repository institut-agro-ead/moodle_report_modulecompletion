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

function report_modulecompletion_after_config() {
    // Check if some activity module was selected in the settings.and has been removed from Moodle.
    $moduleslist    = report_modulecompletion_get_module_types(false); // Modules list from DB.
    $trackedmodules = explode(',', get_config('report_modulecompletion', 'modules_list')); // Tracked modules list.
    // We then filter out the modules that are not existant anymore.
    $purgedmodules = array_filter($trackedmodules, fn ($moduleid) => array_key_exists($moduleid, $moduleslist));
    // Finally, we set the config with the updated list of modules.
    set_config('modules_list', implode(',', $purgedmodules), 'report_modulecompletion');
}
