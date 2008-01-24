<?php
/**
 * @package net.nemein.attention
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM wrapper class for user clickstream storage
 *
 * @package net.nemein.attention
 */
class net_nemein_attention_click_dba extends __net_nemein_attention_click_dba
{
    var $_use_rcs = false;
    function __construct($id = null)
    {
        return parent::__construct($id);
    }
}
?>