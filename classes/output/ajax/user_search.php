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

namespace report_modulecompletion\output\ajax;

defined('MOODLE_INTERNAL') || die;

use renderable;
use templatable;
use renderer_base;
use stdClass;
use core_user\external\user_summary_exporter;

require_once($CFG->dirroot.'/report/modulecompletion/locallib.php');

/**
 * Report module completion renderable class (users list).
 *
 * @package    report_modulecompletion
 * @copyright  2023 L’Institut Agro Enseignement à distance
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_search implements renderable, templatable {

    /**
     * @var string $usersearch
     */
    protected $usersearch;

    /**
     * Class’ constructor.
     *
     * @param string $search The user search query
     */
    public function __construct($search) {
        $this->user_search = $search;
    }

    /**
     * function to export the renderer data in a format that is suitable for a mustache template.
     *
     * @param renderer_base $output Used to do a final render of any components that need to be rendered for export.
     * @return stdClass|array
     */
    public function export_for_template(renderer_base $output) {
        $users = \report_modulecompletion_search_users($this->user_search);
        $result = [];
        foreach ($users as $userdata) {
            $exporter = new user_summary_exporter($userdata);
            $result[] = $exporter->export($output);
        }
        $data = new stdClass();
        $data->users = $result;
        return $data;
    }

}
