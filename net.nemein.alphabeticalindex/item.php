<?php
/**
 * @package net.nemein.alphabeticalindex
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: mail.php 11482 2007-08-06 09:59:38Z w_i $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Alphabetical index
 *
 * @package net.nemein.alphabeticalindex
 */
class net_nemein_alphabeticalindex_item extends __net_nemein_alphabeticalindex_item
{
    var $internal = false;
    
    function net_nemein_alphabeticalindex_item($id = null)
    {
        parent::__net_nemein_alphabeticalindex_item($id);
    }
    
    /**
     * Human-readable label for cases like Asgard navigation
     */
    function get_label()
    {
        if ($this->title)
        {
            return $this->title;
        }
        return "item #{$this->id}";
    }
    
    function _on_loaded()
    {
        if ($this->objectGuid != '')
        {
            $this->internal = true;
        }

        return true;
    }

}

?>