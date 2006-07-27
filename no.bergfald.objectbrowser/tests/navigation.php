<?php
/**
 * @package no.bergfald.objectbrowser
 */
require_once '../../../../midcom/admin/aegir/tests/navigation.php';
require_once '../aegir_navigation.php';
require_once '../schema.php';
class ObjectBrowserNavigationTest extends NavigationHandlerTest {


    var $nodes = array ('midgard_style', '3c0e7dd12c7642936891d25a200683ea');
    var $verbose = false; 

    /*   */

    function setUp () {
        $this->nav = new no_bergfald_objectbrowser_aegir_navigation();
    }

} 
