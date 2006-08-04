<?php
/**
 * Function newline etc encoding issues in serialized data
 * 
 * @package org.openpsa.helpers
 * @author Eero af Heurlin, http://www.iki.fi/rambo
 * @version $Id: fix_serialization.php,v 1.1 2006/02/13 13:33:12 rambo Exp $
 * @copyright Nemein Oy, http://www.nemein.com
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Fixes newline etc encoding issues in serialized data
 *
 * @param string $data The data to fix.
 * @return string $data with serializations fixed.
 */
function org_openpsa_helpers_fix_serialization($data = null)
{
    //Skip on empty data
    if (empty($data))
    {
        return $data;
    }
    
    $preg='/s:([0-9]+):"(.*?)";/ms';
    //echo "DEBUG: preg=$preg<br>\n";
    preg_match_all($preg, $data, $matches);
    $cache = array();
    
    foreach ($matches[0] as $k => $origFullStr)
    {
          $origLen = $matches[1][$k];
          $origStr = $matches[2][$k];
          $newLen = strlen($origStr);
          //echo "DEBUG: origFullStr=$origFullStr, origLen=$origLen, newLen=$newLen <br>\n";
          if ($newLen != $origLen)
          {
             $newFullStr="s:$newLen:\"$origStr\";";
            //For performance we cache information on which strings have already been replaced
             if (!array_key_exists($origFullStr, $cache))
             { 
                 $data = str_replace($origFullStr, $newFullStr, $data);
                 $cache[$origFullStr] = true;
             }
          }
    }
    
    return $data;    
}
?>