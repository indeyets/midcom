<?php
/**
 * This is a simple helper class that you can extend. It provides some
 * helper methods to check if you have defined your toolbar correctly.
 * 
 * Created on Nov 30, 2005
 * @author tarjei huse
 * @package midcom.tests
 *  
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * 
 */

 class midcom_tests_lib_toolbar extends UnitTestCase {
 
 
    /**
     * this function takes a toolbar object and inspects that each and every array has 
     * the needed functionality.
     * @param midcom_helper_toolbar the toolbar object
     * @param boolean recursive. Do not use this param.
     */
    function assert_that_toolbar_is_correctly_defined($toolbar) {
        
        
        $this->assertTrue(is_a( $toolbar,'midcom_helper_toolbar'), "The toolbar object passed inn is not a ");
        
        foreach ($toolbar->items as $item) {
            $this->assert_toolbar_item($item);
        } 
        
    }
    /**
     * inspect a single item array
     */
    function assert_toolbar_item($item) {

        
        $this->assertTrue (array_key_exists(MIDCOM_TOOLBAR_LABEL, $item), 
            "The toolbar is missing the MIDCOM_TOOLBAR_LABEL key");
        $this->assertTrue (array_key_exists(MIDCOM_TOOLBAR_HELPTEXT, $item),
            "The toolbar is missing the MIDCOM_TOOLBAR_HELPTEXT key");
            
        if ($this->assertTrue (array_key_exists(MIDCOM_TOOLBAR_ENABLED , $item),
            "The toolbar is missing the MIDCOM_TOOLBAR_ENABLED key") ) {
            if ( $this->assertTrue (is_bool($item[MIDCOM_TOOLBAR_ENABLED]),
            "The MIDCOM_TOOLBAR_ENABLED key must be a boolean") ) {
            
                $this->assertTrue (array_key_exists(MIDCOM_TOOLBAR_URL, $item), 
                "The toolbaritem is missing the MIDCOM_TOOLBAR_URL. ");
            }
        }
        
        $this->assertTrue (array_key_exists(MIDCOM_TOOLBAR_ICON, $item) , 
                    "One toolbar item is missing the MIDCOM_TOOLBAR_ICON key.");
        $this->assertTrue ($item[MIDCOM_TOOLBAR_ICON] !== "" , 
                    "The MIDCOM_TOOLBAR_ICON key should not be defined as an empty string ('').");
        if ($this->assertTrue (array_key_exists(MIDCOM_TOOLBAR_HIDDEN , $item),
            "The toolbar is missing the MIDCOM_TOOLBAR_HIDDEN key")) {
                
            $this->assertTrue (is_bool($item[MIDCOM_TOOLBAR_HIDDEN]),
            "The MIDCOM_TOOLBAR_HIDDEN key must be a boolean");
        }
        if (array_key_exists(MIDCOM_TOOLBAR_OPTIONS, $item)) {
            $this->assertTrue (is_array($item[MIDCOM_TOOLBAR_OPTIONS]),
            "MIDCOM_TOOLBAR_OPTIONS must be an array is:" . gettype($item[MIDCOM_TOOLBAR_OPTIONS]));
        }
        if (array_key_exists(MIDCOM_TOOLBAR_SUBMENU, $item) ) {
            $this->assertTrue( $item[MIDCOM_TOOLBAR_SUBMENU] === null || 
                               ( is_object($item[MIDCOM_TOOLBAR_SUBMENU]) && 
                                 is_a($item[MIDCOM_TOOLBAR_SUBMENU],'midcom_helper_toolbar')
                               ) , 
                             "The MIDCOM_TOOLBAR_SUBMENU key may either be null or a midcom_helper_toolbar. It is: " . gettype($item[MIDCOM_TOOLBAR_SUBMENU]));
            if (array_key_exists(MIDCOM_TOOLBAR_SUBMENU, $item)&& $item[MIDCOM_TOOLBAR_SUBMENU]  !== null) 
            {
                $this->assert_that_toolbar_is_correctly_defined($item[MIDCOM_TOOLBAR_SUBMENU]);
            }    
            
        }    
        return;
    }
 
 }