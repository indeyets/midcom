<?php

/* Interface classes between midcom.application and the component */

class midcom_admin_controls_l10n_midcom {

    function initialize() {

        require(MIDCOM_ROOT . '/midcom/admin/controls/l10n/main.php');
        return true;
    }


    function properties() {

        return array(
            MIDCOM_PROP_NAME => "Localization Control",
            MIDCOM_PROP_VERSION => "0.1"
        );
    }

} // midcom



class midcom_admin_controls_l10n_component {

    var $configs;
    var $components;


    function configure($configuration, $contextid) {

        // initalize configuration instance for this context
        $tmp = new midcom_helper_configuration($configuration);
        if ($tmp === false)
            return false;
        else {
            $this->configs[$contextid] = $tmp;
            return true;
        }
    }


    function can_handle($topic, $argc, $argv, $contextid) {

        // initialize component instance for this context
        $this->components[$contextid] = new midcom_admin_controls_l10n_main
            ($topic, $this->configs[$contextid]);
        return $this->components[$contextid]->can_handle($argc, $argv);
    }


    function handle($topic, $argc, $argv, $contextid) {

        return $this->components[$contextid]->handle($argc, $argv);
    }

 
    function errcode($contextid) {

        return $this->components[$contextid]->errcode;
    }
 

    function errstr($contextid) {

        return $this->components[$contextid]->errstr;
    }


    function get_metadata ($contextid) {

        return $this->components[$contextid]->get_metadata ();
    }


    function show_content ($contextid) {

        $this->components[$contextid]->show();
    }

} // component



class midcom_admin_controls_l10n_nap {

    var $_current;


    function net_siriux_example_nap() {

        $this->_current = null;
    }


    function is_internal() {

        return true;
    }


    function set_object($object) {

        $this->_current = $object;
        return true;
    }


    function get_node() {
        return null;
    }


    function get_current_leaf() {

        return false;
    }


    function get_leaves() {

        return array();
    }

} // nap

?>