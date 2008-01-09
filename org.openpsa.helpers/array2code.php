<?php
/**
 * returns array as code to generate it
 *
 * @package org.openpsa.helpers
 * @author Eero af Heurlin, http://www.iki.fi/rambo
 * @copyright Nemein Oy, http://www.nemein.com
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * returns array as code to generate it
 */
if (!function_exists('array2code')) {
    function array2code($arr, $level=0, $code = '')
    {
        $pad1 = '';
        $d = $level * 4;
        while ($d--)
        {
            $pad1 .= ' ';
        }
        $pad2 = '';
        $d = ($level+1) * 4;
        while ($d--)
        {
            $pad2 .= ' ';
        }
        $code .= "Array\n{$pad1}(\n";
        foreach ($arr as $k => $v)
        {
            $code .= $pad2;
            switch (true)
            {
                case is_numeric($k):
                    $code .= "{$k} => ";
                    break;
                default:
                    $code .= "'{$k}' => ";
                    break;
            }
            switch (true)
            {
                case is_array($v):
                    $code = array2code($v, $level+2, $code);
                    break;
                case is_numeric($v):
                    $code .= "{$v},\n";
                    break;
                default:
                    $code .= "'" . str_replace("'", "\'", $v) . "',\n";
                    break;
            }
        }
        $code .= "{$pad1})";
        if ($level > 0)
        {
            $code .= ",\n";
        }
        return $code;
    }
}

?>