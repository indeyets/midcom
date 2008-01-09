<?php
/**
 * returns output of print_r as string
 *
 * @package org.openpsa.helpers
 * @author Eero af Heurlin, http://www.iki.fi/rambo
 * @copyright Nemein Oy, http://www.nemein.com
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * returns output of print_r as string
 */
if (!function_exists('sprint_r')) {
    function sprint_r($var) {
             ob_start();
             print_r($var);
             $ret = ob_get_contents();
             ob_end_clean();
      return $ret;
    }
}

?>