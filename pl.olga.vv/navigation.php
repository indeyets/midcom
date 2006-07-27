<?php

/* NAP code, called by the "nap" interface class */

class pl_olga_vv_navigation {

  var $_object;


  function pl_olga_vv_navigation() {
    $this->_object = null;
  }


  function is_internal() {

    // return true if the current topic should not be displayed by NAP
    return false;
  }


  function get_leaves() {

    // list leaves under the current topic. for example:

    $topic = &$this->_object;
    $leaves = array ();
/*    if ($articles = mgd_list_topic_articles($topic->id, "reverse created")) {
      while ($articles->fetch ()) {
        $leaves[$articles->id] = array (
          MIDCOM_NAV_SITE => Array (
            MIDCOM_NAV_URL => $articles->name.".html",
            MIDCOM_NAV_NAME => $articles->title),
          MIDCOM_NAV_ADMIN => Array (
            MIDCOM_NAV_URL => "view/" . $articles->id,
            MIDCOM_NAV_NAME => $articles->title),
            MIDCOM_NAV_VISIBLE => ($topic->parameter("pl.olga.vv", "visible")=="false") ? false : true,
          MIDCOM_META_CREATOR => $articles->creator,
          MIDCOM_META_EDITOR => $articles->revisor,
          MIDCOM_META_CREATED => $articles->created,
          MIDCOM_META_EDITED => $articles->revised
        );
      }
    }*/
    return $leaves;
  }


  function get_node() {

    // information about the current node (topic)
    $topic = &$this->_object;
    return array (
      MIDCOM_NAV_URL => "",
      MIDCOM_NAV_NAME => $topic->extra,
      MIDCOM_NAV_VISIBLE => ($topic->parameter("pl.olga.vv", "visible") == "false") ? false : true,
      MIDCOM_META_CREATOR => $topic->creator,
      MIDCOM_META_EDITOR => $topic->revisor,
      MIDCOM_META_CREATED => $topic->created,
      MIDCOM_META_EDITED => $topic->revised
    );
  }


  function set_object($object) {

    debug_add ("Component: setting NAP Element to " . $object->name ." [" . $object->id . "]");
    $this->_object = $object;
    return true;
  }


  function get_current_leaf() {
    return $GLOBALS["pl_olga_vv_nap_activeid"];
  }

} // navigation

?>