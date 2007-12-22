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
class midgard_webdav_styles_dav_style_index extends midgard_webdav_styles_dav_style {
    function get_virtual_directories() {
        return array(  $this->get_virtual_directory_info( 'midcoms' ),
                       $this->get_virtual_directory_info( 'allstyles' ),
                );

    }


}

/**
 * @package midgard.webdav.styles
 */
class midgard_webdav_styles_dav_style extends midgard_webdav_styles_dav {

    function PROPFIND (&$param, &$files) {
        $this->log( __CLASS__ . '::PROPFIND');
        $this->log( __CLASS__ . ": Path: " .  $param['path'] . " depth: " . $param['depth']);
		if (!array_key_exists('files',$files))
		{
			$files['files'] = array();
		}
        if ( $param['depth'] == 0 )
        {
           $files['files'][0] = $this->get_style_info( $this->style );
           $files['files'][0]['path'] = $param['path'];
           $this->log( __CLASS__ . ": Returning simple collection" );
           return true;
        }

        $files["files"] = array_merge( $files["files"], $this->get_virtual_directories( ));
        $files["files"] = array_merge( $files["files"], $this->get_substyles( ) );
        $files["files"] = array_merge( $files["files"], $this->get_style_elements(  )  );
        //$this->log( $this->style );
        //$this->log( $param   );

        // todo: check if $param['options'] ! empty
        return true;
    }

    /**
     * Gets all the substyles to a style
     */
    function get_substyles( ) {
		$this->log(__CLASS__ . "::get_substyles for id " . $this->style->id);
        $qb = midcom_db_style::new_query_builder(  );
        $qb->add_constraint( 'up', '=', $this->style->id );
        $styles = $qb->execute( ) ;
        $ret = array(  );
        foreach (  $styles as $style ) {
            $ret[] = $this->get_style_info( $style );
        }
        //$this->log($ret);
        return $ret;

    }
    /**
     * Get a style styleelements
     */
    function get_style_elements(  ) {
		$this->log(__CLASS__ . "::get_style_elements for id " . $this->style->id);
        if ( $this->style->id == 0 )
        {
            return array(  );
        }
        $ret = array( );
        $qb = midcom_db_element::new_query_builder();
        $qb->add_constraint('style' , '=' ,$this->style->id);
        $elements = $qb->execute();
        foreach($elements as $element) {
            $ret[] = $this->get_element_info($element);
        }
        return $ret;
    }
    /**
     * The slight variation between a normal style-listing and the one for root
     */
    function get_virtual_directories() {
        return array( );
    }


    function PUT(&$param) { $this->log("\nINDEX::PUT\n");  }
    function MKCOL($options) { $this->log("MKCOL\n");}

    function LOCK( &$options ) {
        return parent::LOCK( $options );
    }


    function PROPPATCH() {
         $this->log("styles: " . __FUNCTION__);
    }
    function GET(&$options) {
        $this->log("GET: " . $options['path'] . "\n");
        //$this->log($options);
        $style = new midcom_db_style($_MIDGARD['style']);
        if (!$style->id)
        {
        	return false;
        }
        //$elements = $this->get_style_elements($style->id) ;
        $options['mimetype'] = $this->mkprop("getcontenttype", "httpd/unix-directory");
        $options['size'] = 0;
        $options['mtime'] = $style->metadata->revised;
        return true;
    }


}

?>
