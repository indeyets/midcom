<?php
/**
 * @package midgard.webdav.styles
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */


/** @ignore */
require 'HTTP/WebDAV/Server.php';

/**
 * Base davclass. Provides logging for all functions so I can see what is needed.
 *
 * @package midgard.webdav.styles
 */
class midgard_webdav_styles_dav extends HTTP_WebDAV_Server {

    var $style;
    function log($obj) {
        ob_start();
        echo "\n" . date("H:i:s" ) ." - ";
        print_r($obj);
        $end = ob_get_contents();
        ob_end_clean();
        error_log($end, 3, '/tmp/davlog.txt');
    }

    function get_prefix( ) {
        return str_replace( 'http:/', '',  $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                );
    }

    function set_style( $style ) {
        $this->log(__CLASS__ . ": setting style " . $style->id);
        $this->style = $style;
    }

    function PROPFIND (&$param, &$files) {
        $this->log ( __CLASS__ . "::PROPFIND (files, param)" );
        $this->log($files);
        $this->log($param);
        return true;
    }


    function PUT(&$param) {
        $this->log(__CLASS__ . "::PUT\n");
        $this->log( $param );
        return true;
    }
    function MKCOL($options) {
        $this->log( __CLASS__ . "MKCOL\n");
        $this->log( $options );
        return true;
    }

    function PROPPATCH() {
         $this->log(__FUNCTION__);
         return true;
    }
    function GET(&$options) {
        $this->log( __CLASS__ . "::GET" . $options['path'] . "\n");
        //$this->log($options);
        return true;
    }

    function LOCK( &$options ) {
        $this->log( __CLASS__ .  "::LOCK" . $options['path'] );
        return "200 OK";

    }

    function get_style_info($style) {
        $this->log( __CLASS__ .  ": get info for style " . $style->id );
        $info = array();
        $info['path'] = $this->get_prefix(  )  . urlencode( $style->name ) . "/";
        $info['props'] = array();
        $info["props"][] = $this->mkprop("displayname",urlencode( $style->name ));
        // creation and modification time
        if ($style->metadata->created)
        {
            $info["props"][] = $this->mkprop("creationdate", strtotime($style->metadata->created) );
        }
        if ($style->metadata->revised)
        {
            $info["props"][] = $this->mkprop("getlastmodified", strtotime($style->metadata->revised) );
        }
        $info["props"][] = $this->mkprop("resourcetype", "collection");
        $info["props"][] = $this->mkprop("getcontenttype", "httpd/unix-directory");
        return $info;
    }

    function get_virtual_directory_info($dirname) {
        $info = array();
        $info['path'] = $this->get_prefix(  ). '/' . $dirname . "/";
        $info['props'] = array();
        $info["props"][] = $this->mkprop("displayname", $dirname);
        // creation and modification time
           if ($style->metadata->created)
        {
            $info["props"][] = $this->mkprop("creationdate", strtotime($this->style->metadata->created) );
        }
        if ($style->metadata->revised)
        {
            $info["props"][] = $this->mkprop("getlastmodified", strtotime($this->style->metadata->revised) );
        }
        $info["props"][] = $this->mkprop("resourcetype", "collection");
        $info["props"][] = $this->mkprop("getcontenttype", "httpd/unix-directory");
        return $info;

    }
    /**
     * Gets an element by name.
     */
    function get_element_by_name($name, $style ) {

        $this->log(__CLASS__ . ": get element " . $name . " from style " .$style->id);
        $qb = midcom_db_element::new_query_builder( ) ;
        $qb->add_constraint( 'name' , '=', str_replace( '.php', '' ,$name ));
        $qb->add_constraint( 'style', '=' , $style->id);
        $res = $qb->execute( );
        //$this->log($res);
        $this->log(__CLASS__ . ": element name from DB: " . $res[0]->name);
        if ( !$res )
        {
            return false;
        }
        return $res[0];

    }

    /**
     * Get an elements info - for directory listings
     */
    function get_element_info($element) {
        $this->log(__CLASS__ . ": get element info for " .$element->id);
        $info = array();
        $info['path'] = $this->get_prefix() .  $element->name . ".php";
        $info['props'] = array();
        $info["props"][] = $this->mkprop("displayname", $element->name);
        // creation and modification time
           if ($element->metadata->created)
           {
            $info["props"][] = $this->mkprop("creationdate", strtotime($element->metadata->created) );
           }
           if ($element->metadata->revised)
        {
            $info["props"][] = $this->mkprop("getlastmodified", strtotime($element->metadata->revised) );
        }
        // plain file (WebDAV resource)
        $info["props"][] = $this->mkprop("resourcetype", "");
        //  if (is_readable($fspath)) {
        $info["props"][] = $this->mkprop("getcontenttype", 'text/plain');

       //         } else {
       //             $info["props"][] = $this->mkprop("getcontenttype", "application/x-non-readable");
       //         }
        $info["props"][] = $this->mkprop("getcontentlength", $element->metadata->size);
        return $info;
    }


}
?>