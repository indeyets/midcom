<?php
/**
 * Collection of small helper functions for OpenPSA
 *
 * @package org.openpsa.helpers
 * @author Eero af Heurlin, http://www.iki.fi/rambo
 * @version $Id: main.php,v 1.8 2006/06/13 10:50:52 rambo Exp $
 * @copyright Nemein Oy, http://www.nemein.com
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * @package org.openpsa.helpers
 */
class org_openpsa_helpers
{
    /**
     * returns output of print_r as string
     */
    static function sprint_r($var)
    {
        ob_start();
        print_r($var);
        $ret = ob_get_contents();
        ob_end_clean();
        return $ret;
    }

    /**
     * returns array as code to generate it
     */
    static function array2code($arr, $level = 0, $code = '')
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
                    $code = self::array2code($v, $level+2, $code);
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

    /**
     * Fixes newline etc encoding issues in serialized data
     *
     * @param string $data The data to fix.
     * @return string $data with serializations fixed.
     */
    static function fix_serialization($data = null)
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

    /**
     * Function for adding JavaScript buttons for saving/cancelling DataManager form via toolbar
     */
    static function dm_savecancel(&$toolbar, &$handler)
    {
        if (   !is_object($toolbar)
            || !method_exists($toolbar, 'add_item'))
        {
            return;
        }
        $toolbar->add_item(
            Array(
                MIDCOM_TOOLBAR_URL => 'javascript:document.forms["midcom_helper_datamanager__form"]["midcom_helper_datamanager_submit"].click();',
                MIDCOM_TOOLBAR_LABEL => $handler->_request_data['l10n_midcom']->get("save"),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/save.png',
                MIDCOM_TOOLBAR_ENABLED => true,
                MIDCOM_TOOLBAR_OPTIONS  => Array(
                    'rel' => 'directlink',
                ),
            )
        );
        $toolbar->add_item(
            Array(
                MIDCOM_TOOLBAR_URL => 'javascript:document.forms["midcom_helper_datamanager__form"]["midcom_helper_datamanager_cancel"].click();',
                MIDCOM_TOOLBAR_LABEL => $handler->_request_data['l10n_midcom']->get("cancel"),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/cancel.png',
                MIDCOM_TOOLBAR_ENABLED => true,
                MIDCOM_TOOLBAR_OPTIONS  => Array(
                    'rel' => 'directlink',
                ),
            )
        );
    }
    
    /**
     * @return boolean Indicating success.
     */
    static function schema_modifier(&$datamanager, $field, $key, $value, $schema = 'default', $create_field = true)
    {
        if (array_key_exists($schema, $datamanager->_layoutdb))
        {
            if (   !array_key_exists($field, $datamanager->_layoutdb[$schema]['fields'])
                && $create_field)
            {
                $datamanager->_layoutdb[$schema]['fields'][$field] = array();
            }
    
            if (array_key_exists($field, $datamanager->_layoutdb[$schema]['fields']))
            {
                $datamanager->_layoutdb[$schema]['fields'][$field][$key] = $value;
                return true;
            }
        }
        return false;
    }

}

?>