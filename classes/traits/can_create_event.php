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

namespace report_modulecompletion\traits;

use context_system;
use coding_exception;
use report_modulecompletion\persistents\filter;

/**
 * Report module completion can_create_event trait.
 *
 * @package    report_modulecompletion
 * @copyright  2025 L’Institut Agro Enseignement à distance
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
trait can_create_event {
    /**
     * Creates an instance from a report object
     *
     * @param filter $report
     * @return self
     */
    public static function create_from_object(filter $report): self {
        $eventparams = [
            'context'  => context_system::instance(),
            'objectid' => $report->get('id'),
            'other' => [
                'name'     => $report->get('name'),
                'filters'   => [
                    'userids' => $report->get('users'),
                    'cohortids' => $report->get('cohorts'),
                    'courseids' => $report->get('courses'),
                    'only_cohorts_courses' => !!$report->get('only_cohorts_courses'),
                    'starting_date' => userdate($report->get('starting_date'), get_string('strftimedatemonthabbr',
                    'core_langconfig')),
                    'ending_date' => userdate($report->get('ending_date'), get_string('strftimedatemonthabbr',
                    'core_langconfig')),
                    'order_by_column' => get_string('form_order_by_' . $report->get('order_by_column'),
                    'report_modulecompletion'),
                    'order_by_type' => get_string('form_order_by_' . $report->get('order_by_type'),
                    'report_modulecompletion'),
                ],
            ],
        ];
        $event = self::create($eventparams);
        $event->add_record_snapshot($event->objecttable, $report->to_record());
        return $event;
    }

    /**
     * Custom validations.
     *
     * @throws coding_exception
     */
    protected function validate_data(): void {
        parent::validate_data();
        if (!isset($this->objectid)) {
            throw new coding_exception('The \'objectid\' must be set.');
        }
    }
}
