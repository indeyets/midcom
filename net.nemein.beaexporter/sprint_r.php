<?php
/**
 * @package net.nemein.beaexporter
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * returns output of print_r as string
 * @package net.nemein.beaexporter
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