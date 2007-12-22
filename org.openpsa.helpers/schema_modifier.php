<?php
/**
 * Helper function for modifying Datamanager schemas
 * @package org.openpsa.helpers
 * @author Henri Bergius, http://bergie.iki.fi
 * @version $Id: schema_modifier.php,v 1.2 2005/10/14 06:59:52 bergius Exp $
 * @copyright Nemein Oy, http://www.nemein.com
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

function org_openpsa_helpers_schema_modifier(&$datamanager, $field, $key, $value, $schema = 'default', $create_field = true)
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
?>