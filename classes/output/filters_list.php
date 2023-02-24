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

namespace report_modulecompletion\output;

use renderable;
use templatable;
use renderer_base;
use stdClass;

/**
 * Report module completion renderable class (filters list).
 *
 * @package    report_modulecompletion
 * @copyright  2023 L’Institut Agro Enseignement à distance
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class filters_list implements renderable, templatable {

    /**
     * @var array The filters to render
     */
    private $filters;

    /**
     * Class’ constructor.
     *
     * @param array $filters The list of filters
     */
    public function __construct(array $filters) {
        $this->filters = $filters;
    }

    /**
     * function to export the renderer data in a format that is suitable for a mustache template.
     *
     * @param renderer_base $output Used to do a final render of any components that need to be rendered for export.
     * @return stdClass|array
     */
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();
        $data->filters = [];
        foreach ($this->filters as $filter) {
            $data->filters[] = [
            'id' => $filter->get('id'),
            'name' => $filter->get('name')
            ];
        }
        $data->sesskey  = sesskey(); // For filter removal.
        return $data;
    }

}
