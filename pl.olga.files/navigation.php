<?php

class pl_olga_files_navigation {

    var $_object;
    var $_path;

    function pl_olga_files_navigation() {
        global $argv;

        $this->_object = null;
        array_shift($argv);
        $this->_path=$argv;

    }


    function is_internal() {
        return false;
    }


    function get_leaves() {
         
        global $argv;
        $leaves = array ();
        $topic = $this->_object;
        $tmppath = "";
        
        foreach($this->_path as $item){
           $tmppath.="$item/";
           $leaves[$item] = array (
                     MIDCOM_NAV_SITE => Array (
                            MIDCOM_NAV_URL => "/".$topic->name."/".$tmppath,
                            MIDCOM_NAV_NAME => $item),
                        MIDCOM_NAV_ADMIN => Array (
                            MIDCOM_NAV_URL => "",
                            MIDCOM_NAV_NAME => ""),
                        MIDCOM_NAV_VISIBLE => true,
                        MIDCOM_META_CREATOR => $topic->creator,
                        MIDCOM_META_EDITOR => $topic->revisor,
                        MIDCOM_META_CREATED => $topic->created,
                        MIDCOM_META_EDITED => $topic->revised
           );
        }
        
        return $leaves;
    }


    function get_node() {
        
        $topic = $this->_object;
        return array (
            MIDCOM_NAV_URL => "/".$topic->name,
            MIDCOM_NAV_NAME => $topic->extra,
            MIDCOM_NAV_VISIBLE =>  true,
            MIDCOM_META_CREATOR => $topic->creator,
            MIDCOM_META_EDITOR => $topic->revisor,
            MIDCOM_META_CREATED => $topic->created,
            MIDCOM_META_EDITED => $topic->revised
        );
    }


    function set_object($object) {
        
        debug_add ("Component: setting NAP Element to " . $object->name .
          " [" . $object->id . "]");
        $this->_object = $object;
        return true;
    }


    function get_current_leaf() {

        return $GLOBALS["pl_olga_files_nap_activeid"];
    }

}

?>
