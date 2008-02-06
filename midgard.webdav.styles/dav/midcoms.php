<?php
/**
 * @package midgard.webdav.styles
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * @package midgard.webdav.styles
 */
class midgard_webdav_styles_handler_midcoms_webdav extends midgard_webdav_styles_dav {

    function PROPFIND (&$param, &$files) {
        $this->log( __CLASS__ . '::PROPFIND' );
        foreach ($_MIDCOM->componentloader->manifests as $manifest) {
            if ($manifest->purecode)
            {
                continue;
            }
            $files['files'][] = $this->get_virtual_directory_info($manifest->name);
        }
        return true;
    }


    function PUT(&$param) {
        return "501 not implemented";
    }
    function MKCOL($options) {
        return "501 not implemented";
    }

    function LOCK( &$options ) {
        return parent::LOCK( $options );
    }


    function PROPPATCH() {
        return "501 not implemented";
    }
    /**
     * I'm not sure if this is needed.
     */
    function GET(&$options) {
        $this->log(__CLASS__ . " GET: " . $options['path'] . "\n");
        //$this->log($options);
        //$elements = $this->get_style_elements($style->id) ;
        $options['mimetype'] = $this->mkprop("getcontenttype", "httpd/unix-directory");
        $options['size'] = 0;
        $options['mtime'] = $style->metadata->revised;
        return true;
    }

}

/**
 * @package midgard.webdav.styles
 */
class midgard_webdav_styles_handler_midcoms_element extends
midgard_webdav_styles_handler_midcoms_files
{
    var $element;
    function set_element( $element ) {
         $this->log(__CLASS__ . ": set_element: " . $element);
        $this->element = $element;
    }

    function GET ( &$options ) {
        $fspath = MIDCOM_ROOT .  str_replace( '.' , '/', $this->midcom ) . "/style/" . $this->element;
        if (!file_exists($fspath))
        {
            return false;
        }
        //$options['mimetype'] = $this->mkprop("getcontenttype", "text/plain");
        $options['mimetype'] = 'text/php';
        //$this->get_element( &$options, $element );
        $options['mtime'] = filemtime($fspath);
        // detect resource size
        $options['size'] = filesize($fspath);
        // no need to check result here, it is handled by the base class
        $options['stream'] = fopen($fspath, "r");
        return true;

    }

}

/**
 * @package midgard.webdav.styles
 */
class midgard_webdav_styles_handler_midcoms_files extends midgard_webdav_styles_dav {
    var $midcom ;
    var $style;

    function setMidcom( $file  ) {
        $this->midcom = $file;
    }
    function setStyle( $style ) {
        $this->style = $style;
    }


    function GET (&$options ) {
        $this->log( __CLASS__  ."\nGET: " . $options['path'] . "\n");

        // todo: return a unix dir here!
        return true;
    }

    function PROPFIND ( &$param, &$files) {
        $this->log( "midcoms/files/propfind: "  . $this->midcom);
        $midcoms = $this->get_styleelements( $this->midcom );
        $this->log("midcoms: " .$midcoms);
        $info = array();
        $info['path'] = $this->get_prefix( ) . '/midcoms/' . $this->midcom ;
        $info['props'] = array();
        $info["props"][] = $this->mkprop("displayname", $this->midcom);
        // creation and modification time
        $info["props"][] = $this->mkprop("creationdate",  $this->style->metadata->created);
        $info["props"][] = $this->mkprop("getlastmodified", $this->style->metadata->modified);
        $info["props"][] = $this->mkprop("resourcetype", "");
        //  if (is_readable($fspath)) {
        $info["props"][] = $this->mkprop("getcontenttype", 'text/plain');
        $info["props"][] = $this->mkprop("resourcetype", "collection");
        $info["props"][] = $this->mkprop("getcontenttype", "httpd/unix-directory");
        //$files['files'][] = $info;

        foreach ( $midcoms as $key => $element ) {
            $files['files'][] = $this->get_midcom_styleelement_info( $element );
        }
        return true;
    }

    function get_midcom_styleelement_info( $element ) {
        $path = MIDCOM_ROOT .  str_replace( '.' , '/', $this->midcom ) . "/style/$element";
        $info = array(  );
        $info['path'] = $this->get_prefix( )  . '/midcoms/' .
            $this->midcom . "/$element";
        $this->log("info path: ". $info['path'] );
        $info['props'] = array();
        $info["props"][] = $this->mkprop("displayname", $element);
        // creation and modification time
        $info["props"][] = $this->mkprop("creationdate",filectime($path));
        $info["props"][] = $this->mkprop("getlastmodified", filemtime($path));
        $info["props"][] = $this->mkprop("resourcetype", "");
        $info["props"][] = $this->mkprop("getcontenttype", "text/html");
        $info["props"][] = $this->mkprop("getcontentlength", filesize($path));
        return $info;

    }
    function get_styleelements( $midcom ) {
        $path = MIDCOM_ROOT .  str_replace( '.' , '/', $midcom ) . "/style";
        $this->log("path:" . $path);
        $ret = array(  );
        if ( !file_exists( $path ) )
        {
            $this->log("Path $path does not exist");
            return false;
            return array( );
        }
        $files = dir ( $path);

        while ( false !== ( $file = $files->read( )  ) ) {
            if (substr($file,0,1) == '.')
            {
                continue;
            }
            $ret[] = $file;
        }
$this->log("ret: " . $ret);
        return $ret;

    }


    function PUT ( &$options ) {
        return "403 Forbidden";
    }
}


?>