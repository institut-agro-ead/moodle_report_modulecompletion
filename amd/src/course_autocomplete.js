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
 *
 * This module is compatible with core/form-autocomplete.
 *
 * @copyright  2023 L’Institut Agro Enseignement à distance
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(["jquery", "core/ajax", "core/notification"], function (
  $,
  Ajax,
  Notification
) {
  return /** @alias module:report_modulecompletion/course_autocomplete */ {
    /**
     * Process the results for auto complete elements.
     *
     * @param {String} selector The selector of the auto complete element.
     * @param {Array} results An array or results.
     * @return {Array} New array of results.
     */
    processResults: function (selector, results) {
      var options = [];
      if (results.courses) {
        $.each(results.courses, function (index, data) {
          options.push({
            value: data.id,
            label: data.fullname,
          });
        });
      }
      return options;
    },

    /**
     * Source of data for Ajax element.
     *
     * @param {String} selector The selector of the auto complete element.
     * @param {String} query The query string.
     * @param {Function} callback A callback function receiving an array of results.
     */
    transport: function (selector, query, callback) {
      var promise = Ajax.call([
        {
          methodname: "report_modulecompletion_get_courses",
          args: {
            course_name: query,
          },
        },
      ])[0];
      promise.fail(Notification.exception).then(callback);
    },
  };
});
