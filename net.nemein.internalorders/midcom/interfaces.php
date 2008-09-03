<?php

/**
 * @package net.nemein.internalorders
 * @author Oskari Kokko, http://www.nemein.com/
 * @version $Id: interfaces.php,v 1.3.2.5 2005/11/07 18:57:51 bergius Exp $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Internal orders MidCOM interface class.
 * 
 * @package net.nemein.internalorders
 */

class net_nemein_internalorders_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     * 
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function net_nemein_internalorders_interface()
    {
        parent::__construct();
        
//        $this->_on_initialize();
        
        $this->_component = 'net.nemein.internalorders';
        $this->_autoload_files = Array(
            'viewer.php', 
            'navigation.php',
        );
        $this->_autoload_libraries = Array('midcom.helper.datamanager');
        
        if (!defined('NET_NEMEIN_INTERNALORDERS_NEW'))
        {
            define('NET_NEMEIN_INTERNALORDERS_NEW', 0);
            define('NET_NEMEIN_INTERNALORDERS_SENT', 1);
            define('NET_NEMEIN_INTERNALORDERS_SENT_LOCKED', 2);
            define('NET_NEMEIN_INTERNALORDERS_RECEIVED', 3);
            define('NET_NEMEIN_INTERNALORDERS_HIDDEN', 4);
            define('NET_NEMEIN_INTERNALORDERS_REMOVED', 9);
        }
        if (!defined('N_N_INTERNALORDERS_PRODUCT_ACTIVE'))
        {
                define('N_N_INTERNALORDERS_PRODUCT_ACTIVE', 1);
                define('N_N_INTERNALORDERS_PRODUCT_TOBEREMOVED', 2);
                define('N_N_INTERNALORDERS_PRODUCT_REMOVED', 3);
                define('N_N_INTERNALORDERS_GROUP_TR', 10);
                define('N_N_INTERNALORDERS_GROUP_AR', 11);
        }
    }
    
      /**
     * Initialize n.n.internalorders library.
     *
     * @return boolean inidicating success.
     */
/*
    function _on_initialize()
    {
        parent::_on_initialize();

        return true;
    }
*/
}

?>