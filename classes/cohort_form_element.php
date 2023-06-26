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
 * Course selector field.
 *
 * Allows auto-complete ajax searching for cohort.
 *
 * @package   report_modulecompletion
 * @copyright 2023 L’Institut Agro Enseignement à distance
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/form/cohort.php');

/**
 * Cohort autocomplete element.
 *
 * @package   report_modulecompletion
 * @copyright 2023 L’Institut Agro Enseignement à distance
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_modulecompletion_cohort_form_element extends MoodleQuickForm_cohort {

    /**
     * Set the value of this element. If values can be added or are unknown, we will
     * make sure they exist in the options array.
     * We need to ignore PHPCS here as the the method overrides its parent method which is in Moodle core, incoherent much ?
     * @param string|array $value The value to set.
     * @return boolean
     */
    public function setValue($value) { // phpcs:ignore
        global $DB;
        $values = (array) $value;
        $cohortstofetch = [];

        foreach ($values as $onevalue) {
            if ($onevalue && !$this->optionExists($onevalue) &&
                ($onevalue !== '_qf__force_multiselect_submission')) {
                array_push($cohortstofetch, $onevalue);
            }
        }

        if (empty($cohortstofetch)) {
            $this->setSelected($values);
            return true;
        }

        list($whereclause, $params) = $DB->get_in_or_equal($cohortstofetch, SQL_PARAMS_NAMED, 'id');

        $list = $DB->get_records_select('cohort', 'id ' . $whereclause, $params, 'name');

        $currentcontext = context_helper::instance_by_id($this->contextid);
        foreach ($list as $cohort) {
            // Make sure we can see the cohort.
            // Replicate logic in cohort_can_view_cohort() because we can't use it directly as we don't have a course context.
            $cohortcontext = context::instance_by_id($cohort->contextid);
            if (!$cohort->visible && !has_capability('moodle/cohort:view', $cohortcontext)) {
                continue;
            }
            $label = format_string($cohort->name, true, $currentcontext);
            $this->addOption($label, $cohort->id);
        }

        $this->setSelected($values);
        return true;
    }
}
