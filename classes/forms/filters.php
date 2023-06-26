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

namespace report_modulecompletion\forms;

use core\form\persistent;
use core_date;
use stdClass;

/**
 * Class that creates the filtering form for the plugin
 *
 * @package    report_modulecompletion
 * @copyright  2023 L’Institut Agro Enseignement à distance
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class filters extends persistent {

    /** @var string Persistent class name. */
    protected static $persistentclass = 'report_modulecompletion\\persistents\\filter';

    /** @var array Fields to remove when getting the final data. */
    protected static $fieldstoremove = ['save_filter_form'];

    /**
     * Form definition.
     *
     * @return void
     */
    public function definition() {
        global $CFG;
        $mform = $this->_form;
        // User ID.
        $mform->addElement('hidden', 'userid');
        $mform->setConstant('userid', $this->_customdata['userid']);
        if (isset($this->_customdata['quickfilter']) && $this->_customdata['quickfilter'] === false) {
            $mform->addElement(
                'text',
                'name',
                \get_string('form_filter_name', 'report_modulecompletion'),
                ['id' => 'report_modulecompletion_name',
                'placeholder' => \get_string('form_filter_name_placeholder', 'report_modulecompletion')]
            );
        }

        $mform->addElement(
            'autocomplete',
            'users',
            \get_string('user_label', 'report_modulecompletion'),
            [],
            $this->get_user_autocomplete_options()
        );

        \MoodleQuickForm::registerElementType(
            'report_modulecompletion_cohort',
            "$CFG->dirroot/report/modulecompletion/classes/cohort_form_element.php",
            'report_modulecompletion_cohort_form_element'
        );
        $mform->addElement(
            'report_modulecompletion_cohort',
            'cohorts',
            \get_string('cohort_label', 'report_modulecompletion'),
            ['multiple' => true]
        );

        $radioarray = [];
        $radioarray[] = $mform->createElement('radio', 'only_cohorts_courses', '', \get_string('yes'), 1);
        $radioarray[] = $mform->createElement('radio', 'only_cohorts_courses', '', \get_string('no'), 0);
        $mform->addGroup(
            $radioarray,
            'only_cohorts_courses_group',
            \get_string('form_only_cohorts_courses', 'report_modulecompletion'),
            [' '],
            false
        );
        $mform->addHelpButton('only_cohorts_courses_group', 'form_only_cohorts_courses', 'report_modulecompletion');

        $mform->addElement(
            'course',
            'courses',
            \get_string('course_label', 'report_modulecompletion'),
            ['multiple' => true]
        );
        $mform->addElement('date_selector', 'starting_date', \get_string('form_starting_date', 'report_modulecompletion'), [
            'startyear' => 2016,
            'stopyear' => \date('Y') + 2,
            'timezone' => core_date::get_server_timezone_object()
        ]);

        $mform->addElement('date_selector', 'ending_date', \get_string('form_ending_date', 'report_modulecompletion'), [
            'startyear' => 2016,
            'stopyear' => \date('Y') + 2,
            'timezone' => core_date::get_server_timezone_object()
        ]);
        $orderby = $mform->addElement('select', 'order_by_column', \get_string('form_order_by_column', 'report_modulecompletion'), [
            'student' => \get_string('form_order_by_student', 'report_modulecompletion'),
            'completion' => \get_string('form_order_by_completion', 'report_modulecompletion'),
            'last_completed' => \get_string('form_order_by_last_completed', 'report_modulecompletion'),
        ]);
        $orderby->setSelected('student');

        $orderbytype = $mform->addElement('select', 'order_by_type', \get_string('form_order_by_type', 'report_modulecompletion'), [
            'asc' => \get_string('form_order_by_asc', 'report_modulecompletion'),
            'desc' => \get_string('form_order_by_desc', 'report_modulecompletion'),
        ]);
        $orderbytype->setSelected('asc');
        $mform->addElement(
            'submit',
            'save_filter_form',
            \get_string(isset($this->_customdata['quickfilter']) && $this->_customdata['quickfilter'] === false ?
                'form_save_filter' : 'form_quickfilter_submit', 'report_modulecompletion')
        );
    }

    /**
     * Convert fields.
     *
     * @param stdClass $data The data.
     * @return stdClass
     */
    protected static function convert_fields(stdClass $data) {
        $data = parent::convert_fields($data);

        $data->users = $data->users ? implode(',', $data->users) : '';
        $data->courses = $data->courses ? implode(',', $data->courses) : '';
        $data->cohorts = $data->cohorts ? implode(',', $data->cohorts) : '';
        $data->ending_date += 86399; // Adds 23 hours, 59 minutes and 59 seconds.

        return $data;
    }

    /**
     * Get the default data.
     *
     * @return stdClass
     */
    protected function get_default_data() {
        $data = parent::get_default_data();
        $data->users = $data->users ? explode(',', $data->users) : [];
        $data->cohorts = $data->cohorts ? explode(',', $data->cohorts) : [];
        $data->courses = $data->courses ? explode(',', $data->courses) : [];
        return $data;
    }

    /**
     * Defines extra validations for fileds which need more complicated controls.
     *
     * @param array $data Form data
     * @param array $files Potentials files
     * @param array $errors Form errors
     * @return array Extra validations
     */
    public function extra_validation($data, $files, array &$errors) {
        if (isset($this->_customdata['quickfilter']) && $this->_customdata['quickfilter'] === false && $data->name == null) {
            $errors['name'] = \get_string('form_name_required', 'report_modulecompletion');
        } else if (isset($errors['name'])) {
            unset($errors['name']);
        }
        if (empty($data->starting_date)) {
            $errors['starting_date'] = \get_string('form_missing_starting_date', 'report_modulecompletion');
        }
        if (empty($data->ending_date)) {
            $errors['ending_date'] = \get_string('form_missing_ending_date', 'report_modulecompletion');
        }
        $startingdate = $data->starting_date;
        $endingdate = $data->ending_date;
        if (!$this->is_timestamp($startingdate)) {
            $errors['starting_date'] = \get_string('form_missing_starting_date', 'report_modulecompletion');
        }
        if (!$this->is_timestamp($endingdate)) {
            $errors['ending_date'] = \get_string('form_missing_ending_date', 'report_modulecompletion');
        }
        if ($startingdate > $endingdate) {
            $errors['starting_date'] = \get_string('form_starting_date_must_be_anterior', 'report_modulecompletion');
        }
    }

    /**
     * Checks if a string is a valid timestamp.
     *
     * @param  string $timestamp Timestamp to validate.
     * @return bool Whether or not the timestamp is valid
     */
    private function is_timestamp($timestamp) {
        $check = (is_int($timestamp) || is_float($timestamp))
        ? $timestamp
        : (string) (int) $timestamp;
        return  ($check === $timestamp)
            && ((int) $timestamp <= PHP_INT_MAX)
            && ((int) $timestamp >= ~PHP_INT_MAX);
    }

    /**
     * Gets autocomplete’s options for 'users'.
     *
     * @return array The options
     */
    private function get_user_autocomplete_options() {
        return [
            'ajax' => 'core_search/form-search-user-selector',
            'multiple' => true,
            'valuehtmlcallback' => function ($value) {
                global $DB, $OUTPUT;
                $user = $DB->get_record('user', ['id' => (int)$value], '*', IGNORE_MISSING);
                if (!$user || !user_can_view_profile($user)) {
                    return false;
                }
                $details = user_get_user_details($user);
                return $OUTPUT->render_from_template(
                    'core_search/form-user-selector-suggestion',
                    $details
                );
            }
        ];
    }
}
