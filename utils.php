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
 * Utilitarian functions
 *
 * @package    report_modulecompletion
 * @copyright  2023 L’Institut Agro Enseignement à distance
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Slugifies a given string.
 *
 * @param string $string The string to slugify
 * @param string $separator The separator
 * @return string The slugified string
 */
function report_modulecompletion_slug($string = '', $separator = '-') {
    return strtolower(trim(preg_replace('~[^0-9a-z]+~i',
        $separator, html_entity_decode(preg_replace(
            '~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i',
            '$1', htmlentities(trim($string), ENT_QUOTES, 'UTF-8')
        ), ENT_QUOTES, 'UTF-8')), $separator));
}

/**
 * Determine if the given formula has a valid syntax
 *
 * @param string $formula The formula to check
 * @return bool Whether the formula is valid or not
 */
function report_modulecompletion_validate_formula($formula) {
    if (preg_match('/^(?:[-+\/*%][(]*\d+(\.\d+)?[)]*)+/', $formula)) {
        // The regex is valid, we now check the parentheses.
        $oppos = strpos($formula, '(');
        $cppos = strpos($formula, ')');
        if ($oppos !== false || $cppos !== false) {
            // If there is one (or more) of one type of parenthese and not the other, that's wrong.
            if ($oppos === false || $cppos === false) {
                return false;
            }
            // First we check if the first parenthese found is ( and not ).
            if ($oppos < $cppos) {
                // The first ( is before the first ) => OK.
                // Now we check if parenthses are opened and closed in the right order.
                // This is a very naive check though, it does not mean the syntax of the formula is correct.
                $openedparentheses = 0;
                foreach (str_split($formula) as $letter) {
                    $openedparentheses = $letter === '(' ?
                        $openedparentheses + 1 :
                        ($letter === ')' ? $openedparentheses - 1 : $openedparentheses);
                }
                // If $opened_parentheses is < 0 , there are more ) than (, if > 0 then there are more ( than ),
                // otherwise same number of ( and ).
                return $openedparentheses === 0;
            }
            return false;
        }
        // No parenthese, so we're good here.
        return true;
    }
    return false;
}
