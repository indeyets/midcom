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
class midgard_webdav_styles_dav_element extends midgard_webdav_styles_dav {
    var $element ;
    var $style;
    function set_element_name( $element ) {
        $this->element = $element;
        $this->log( __CLASS__  .": set_element_name: " . $element);
    }


    function GET (&$options ) {
        $this->log( __CLASS__  .": GET: " . $options['path']);

        $this->log( __CLASS__ . ": GET: this->element = " .  $this->element );

        $element = $this->get_element_by_name( $this->element , $this->style);
        //$options['mimetype'] = $this->mkprop("getcontenttype", "text/plain");
        $options['mimetype'] = 'text/php';
        $options['data'] = $element->value;
        $options['mtime'] = $element->metadata->revised;
        $options['size']  = strlen( $element->value ); // todo: strlen implies ascii!
        //$this->get_element( &$options, $element );
        return true;
    }

    function PROPFIND ( &$param, &$files) {
        $this->log(__CLASS__ . ": PROPFIND element: " . $this->element . " style: " . $this->style->name);
        parent::PROPFIND( &$param, &$files) ;
        $element = $this->get_element_by_name( $this->element, $this->style );
        $files['files'][] = $this->get_element_info( $element );
        return true;
    }

    function PUT ( &$options ) {
        $this->log( "FILES::PUT\n" );
        //    $this->log( $options );
        $element =  $this->get_element_by_name( $this->element, $this->style );
        $this->log( "this->style->name: " . $this->style->name );
        $this->log( "element" . $element);
        // new element
        if ( !$element )
        {
            $this->log( "Creating new element " . $this->element );
            $element = new midcom_db_element;
            $element->name = str_replace( '.php', '', $this->element );
            $element->style = $this->style->id;
            if ( !$element->create())
            {
                $this->log( "403 Forbidden\n" );
                return "403 Forbidden";
            }
        }
        //element exists, but we don't want to duplicate the content
        else
        {
            $element->value = "";
        }

        while (!feof($options["stream"])) {
          $element->value .= fread($options["stream"], 4096);
        }
        if ( !$element->update() )
        {
            $this->log( "403 Forbidden - \n" );
            return "403 Forbidden";
        }
        $this->log( __CLASS__ . "New element value: {$element->value}
                " );
        return true;

    }
}


?>