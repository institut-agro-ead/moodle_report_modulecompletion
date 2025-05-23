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
 * Links and settings
 *
 * @package    report_modulecompletion
 * @copyright  2023 L’Institut Agro Enseignement à distance
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/report/modulecompletion/locallib.php');
require_once($CFG->dirroot . '/report/modulecompletion/utils.php');

$ADMIN->add('reports', new admin_category('report_modulecompletion', get_string('categoryname', 'report_modulecompletion')));

$ADMIN->add('report_modulecompletion', new admin_externalpage(
    'reportmodulecompletion',
    get_string('configmodulecompletion', 'report_modulecompletion'),
    new moodle_url($CFG->wwwroot . '/report/modulecompletion/index.php'),
    'report/modulecompletion:view'
));

if ($hassiteconfig) {
    $settingspage = new admin_settingpage('report_modulecompletion_settings', get_string('settings'));

    if ($ADMIN->fulltree) {
        $types = report_modulecompletion_get_module_types();

        $settingspage->add(new admin_setting_configmulticheckbox(
            'report_modulecompletion/modules_list',
            get_string('modules_list_label', 'report_modulecompletion'),
            get_string('modules_list_description', 'report_modulecompletion'),
            $types,
            $types
        ));
    }
    $ADMIN->add('report_modulecompletion', $settingspage);

    $modulesmetadata = report_modulecompletion_get_modules_metadata();

    // Metadata plugin is installed and one or more module metadata exist.
    if (is_array($modulesmetadata) && count($modulesmetadata) > 0) {
        $metasettings = new admin_settingpage(
            'report_modulecompletion_meta_settings',
            get_string('meta_settings', 'report_modulecompletion')
        );

        if ($ADMIN->fulltree) {
            $settingspage->add(new admin_setting_configcheckbox(
                'report_modulecompletion/use_metadata',
                get_string('use_metadata_label', 'report_modulecompletion'),
                get_string('use_metadata_description', 'report_modulecompletion'),
                1
            ));

            $metasettings->add(new admin_setting_configmulticheckbox(
                'report_modulecompletion/metadata_list',
                get_string('metadata_list_label', 'report_modulecompletion'),
                get_string('metadata_list_description', 'report_modulecompletion'),
                array_combine(
                    array_keys($modulesmetadata),
                    array_fill(0, count($modulesmetadata), 0)
                ),
                $modulesmetadata
            ));
            $metasettings->add(new admin_setting_configmulticheckbox(
                'report_modulecompletion/numeric_metadata_list',
                get_string('numeric_metadata_list_label', 'report_modulecompletion'),
                get_string('numeric_metadata_list_description', 'report_modulecompletion'),
                array_combine(
                    array_keys($modulesmetadata),
                    array_fill(0, count($modulesmetadata), 0)
                ),
                $modulesmetadata
            ));

            $numericmetadata = explode(',', get_config('report_modulecompletion', 'numeric_metadata_list'));
            if (count($numericmetadata) > 0 && $numericmetadata[0] !== '') {
                $metasettings->add(new admin_setting_heading(
                    'report_modulecompletion/metadata_conversion',
                    get_string('numeric_metadata_conversion', 'report_modulecompletion'),
                    get_string('numeric_metadata_conversion_description', 'report_modulecompletion')
                ));
                foreach ($numericmetadata as $nmetaid) {
                    $nmeta = $modulesmetadata[$nmetaid];
                    $slugmeta = report_modulecompletion_slug($nmeta, '_');
                    // Numeric metadata heading.
                    $metasettings->add(
                        new admin_setting_heading('report_modulecompletion/metadata_conversion_' . $slugmeta, $nmeta, '')
                    );

                    // Numeric metadata formula.
                    $metasettings->add(new admin_setting_configtext(
                        'report_modulecompletion/metadata_conversion_' . $slugmeta . '_formula',
                        $nmeta . ' ' . get_string('numeric_metadata_formula', 'report_modulecompletion'),
                        'ex: /60. ' . get_string('numeric_metadata_formula_description', 'report_modulecompletion'),
                        ''
                    ));

                    // Numeric metadata label.
                    $metasettings->add(new admin_setting_configtext(
                        'report_modulecompletion/metadata_conversion_' . $slugmeta . '_label',
                        $nmeta . ' ' . get_string('numeric_metadata_label', 'report_modulecompletion'),
                        'ex: heure(s)',
                        ''
                    ));
                }
            }
        }
        $ADMIN->add('report_modulecompletion', $metasettings);
    }
}

$settings = null;
