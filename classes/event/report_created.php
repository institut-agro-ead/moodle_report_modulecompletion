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

declare(strict_types=1);

namespace report_modulecompletion\event;

use context_system;
use coding_exception;
use core\event\base;
use report_modulecompletion\persistents\filter;
use moodle_url;

/**
 * Report builder custom report created event class.
 *
 * @package     report_modulecompletion
 * @copyright   2023 L’Institut Agro Enseignement à distance
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @property-read array $other {
 *      Extra information about the event.
 *
 *      - string    name:     The name of the report/filter
 *      - string    userids:               The user ids used to filter the report
 *      - string    cohortids:             The cohort ids used to filter the report
 *      - string    courseids:             The course ids used to filter the report
 *      - boolean   only_cohorts_courses:  Wether or not report should only display courses for which selected cohorts are affected
 *      - string    starting_date:         The starting date to filter the report
 *      - string    ending_date:           The ending date to filter the report
 *      - string    order_by_column:       The selected property to order the report’s results
 *      - string    order_by_type:         The selected sequence (asc or desc) to order the report’s results
 * }
 */
class report_created extends base {

    /**
     * Initialise the event data.
     */
    protected function init() {
        $this->data['objecttable'] = filter::TABLE;
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

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
     * Returns localised general event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('reportcreated', 'report_modulecompletion');
    }

    /**
     * Returns non-localised description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' created the module completion report with id '$this->objectid'.";
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

    /**
     * Returns relevant URL.
     *
     * @return moodle_url
     */
    public function get_url(): moodle_url {
        return new moodle_url('/report/modulecompletion/index.php', ['action' => 'editfilter', 'id' => $this->objectid]);
    }
}
