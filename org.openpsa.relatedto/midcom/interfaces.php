<?php
/**
 * OpenPSA relatedto library, handled saving and retvieving "related to" information
 * 
 * Startup loads main class, which is used for all operations.
 * 
 * @package org.openpsa.relatedto
 */
class org_openpsa_relatedto_interface extends midcom_baseclasses_components_interface
{
    
    function org_openpsa_relatedto_interface()
    {
        parent::midcom_baseclasses_components_interface();
        
        $this->_component = 'org.openpsa.relatedto';
        $this->_purecode = true;
        $this->_autoload_files = Array(
            'relatedto.php',
            'main.php',
            'handler_prototype.php',
            'suspects.php',
        );
    }
    
    function _on_initialize()
    {
        define('ORG_OPENPSA_RELATEDTO_STATUS_SUSPECTED', 100);
        define('ORG_OPENPSA_RELATEDTO_STATUS_CONFIRMED', 120);
        define('ORG_OPENPSA_RELATEDTO_STATUS_NOTRELATED', 130);

        // This component uses AHAH, include the handler javascripts
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL."/Pearified/JavaScript/Prototype/prototype.js");
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL."/Pearified/JavaScript/Scriptaculous/scriptaculous.js?effects");
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL."/org.openpsa.helpers/ajaxutils.js");
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL."/org.openpsa.relatedto/related_to.js");
        
        $_MIDCOM->add_link_head(array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL."/org.openpsa.relatedto/related_to.css",
            )
        );
        return true;
    }
}


?>