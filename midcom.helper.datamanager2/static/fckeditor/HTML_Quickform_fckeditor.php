<?php
/**
 * Custom HTML_Quickform elementtype for FCKeditor textarea
 *
 * This elementtype builds an FCKeditor instance for PEAR::HTML_Quickform
 * class. It extends HTML_Quickform
 *
 * 1. Place this file in the FCK directory (where fckeditor.php is)
 *
 * 2. Register the element type in Quickform.
 *    ! Make sure the path to this class file is relative to the location of the
 *      script that is calling this command.
 *        HTML_Quickform::registerElementType('fckeditor'
 *                                           ,'path/to/HTML_Quickform_fckeditor.php'
 *                                           ,'HTML_Quickform_fckeditor');
 *
 * 3. Create an instance in the Quickform object, here with some config options. See
 *    $_aFckConfigProps for all the possible options.
 *    ! The basepath (here in $sFCKBasePath) should be absolute to de documentroot
 *      of the webserver.
 *    ! It seems that StylesXmlPath needs the same absolute path as basepath.
 *
 *        $oQFElement = HTML_Quickform::createElement ('fckeditor'      // QF type
 *                                                    ,'myfckinstance'  // element name
 *                                                    ,'The Label');    // label
 *        $sFCKBasePath = '/path/from/documentroot/to/fckdir/';
 *        $oQFElement->setFCKProps($sFCKBasePath     // BasePath
 *                                ,'Basic'           // Toolbarset
 *                                ,'800'             // Width
 *                                ,'300'             // Height
 *                                ,array('SkinPath'                 => 'editor/skins/office2003/'
 *                                      ,'DefaultLanguage'          => 'nl'
 *                                      ,'StylesXmlPath'            => 'path/to/fckstyles.xml'
 *                                      ,'UseBROnCarriageReturn'    => 'true'
 *                                      ,'StartupFocus'             => 'false'
 *                                      ,'CustomConfigurationsPath' => 'config.js'
 *                                      ,'EditorAreaCSS'            => 'fck_editorarea.css'));
 *
 * @author Jordi Backx <jbackx@westsitemedia.nl>
 * @version 1.1
 */
class HTML_Quickform_fckeditor extends HTML_Quickform_element
{
    /**
     * Path to FCK class
     *
     * @var string Path to PHP FCK class
     * @access private
     */
    var $_sFckBasePath = NULL;
    /**
     * Toolbar
     *
     * @var string Requested toolbarset
     * @access private
     */
    var $_sToolbarSet = NULL;
    /**
     * Height of editor
     *
     * @var string Height
     * @access private
     */
    var $_sHeight = NULL;
    /**
     * Width of editor
     *
     * @var string Width
     * @access private
     */
    var $_sWidth = NULL;
    /**
     * FCK properties
     *
     * @var array Set of FCK only properties
     * @access private
     */
    var $_aFckConfigProps = array('CustomConfigurationsPath' => NULL
                                 ,'EditorAreaCSS'            => NULL
                                 ,'Debug'                    => NULL
                                 ,'SkinPath'                 => NULL
                                 ,'PluginsPath'              => NULL
                                 ,'AutoDetectLanguage'       => NULL
                                 ,'DefaultLanguage'          => NULL
                                 ,'EnableXHTML'              => NULL
                                 ,'EnableSourceXHTML'        => NULL
                                 ,'GeckoUseSPAN'             => NULL
                                 ,'StartupFocus'             => NULL
                                 ,'ForcePasteAsPlainText'    => NULL
                                 ,'ForceSimpleAmpersand'     => NULL
                                 ,'TabSpaces'                => NULL
                                 ,'UseBROnCarriageReturn'    => NULL
                                 ,'LinkShowTargets'          => NULL
                                 ,'LinkTargets'              => NULL
                                 ,'LinkDefaultTarget'        => NULL
                                 ,'ToolbarStartExpanded'     => NULL
                                 ,'ToolbarCanCollapse'       => NULL
                                 ,'StylesXmlPath'            => NULL
                                  );
    /**
     * Class constructor
     *
     * @param string $sElementName  Name attribute of element
     * @param mixed  $mElementLabel Label attribute of element
     * @param mixed  $mAttributes   Other non-FCK optional attributes
     *
     * @access public
     * @return void
     */
    function HTML_Quickform_fckeditor($sElementName  = NULL
                                      ,$mElementLabel = NULL
                                      ,$mAttributes   = NULL) {
        HTML_Quickform_element::HTML_Quickform_element($sElementName, $mElementLabel, $mAttributes);
        $this->_persistantFreeze = TRUE;
        $this->_type             = 'fckeditor';
    }// End constructor
    /**
     * Set properties for FCKeditor instance
     *
     * @param string $sFckBasePath       Basepath
     * @param string $sFckStylesXMLPath  Path to XML styles
     * @param string $sToolbarSet        Toolbar
     * @param string $sWidth             Width of the editor
     * @param string $sHeight            Height of the editor
     * @param mixed  $mFckRequestedAttrs Set of FCK only attributes
     * @access public
     * @return void
     */
    function setFCKProps ($sFckBasePath       = NULL
                         ,$sToolbarSet        = NULL
                         ,$sWidth             = NULL
                         ,$sHeight            = NULL
                         ,$mFckRequestedAttrs = NULL) {
        /*
         * Set the paths
         */
        $this->_sFckBasePath      = $sFckBasePath;
        /*
         * Set public FCK attributes
         */
        $this->_sWidth      = $sWidth;
        $this->_sHeight     = $sHeight;
        $this->_sToolbarSet = $sToolbarSet;
        /*
         * Set configuration array if not NULL
         */
        if ($mFckRequestedAttrs !== NULL) {
            // Collect keys of requested attributes
            $aFckRequestedAttrKeys = array_keys($mFckRequestedAttrs);
            // Search in supported attribute array for the keys
            foreach ($this->_aFckConfigProps as $sFckProp => $sFckValue) {
                $mArraySearchResult = array_search($sFckProp, $aFckRequestedAttrKeys);
                if ($mArraySearchResult === FALSE) {
                    unset($this->_aFckConfigProps[$sFckProp]);
                } else {
                    $this->_aFckConfigProps[$sFckProp] = $mFckRequestedAttrs[$sFckProp];
                }
            }
        } else {
            // No properties requested
            $this->_aFckConfigProps = NULL;
        }
    }
    /**
     * Register name atribute
     *
     * @param string $sName Name attribute of element
     * @access public
     * @return void
     */
    function setName($sName) {
        $this->updateAttributes(array('name' => $sName));
    }// End function setName
    /**
     * Naam teruggeven (name attribute)
     *
     * @access public
     * @return string Name attribute element
     */
    function getName() {
        return $this->getAttribute('name');
    }// End function getName
    /**
     * Waarde/inhoud registreren (value attribute)
     *
     * @param string $sWaarde Value attribute of element
     * @access public
     * @return void
     */
    function setValue($sValue) {
        $this->updateAttributes(array('value' => $sValue));
    }// End function setValue
    /**
     * Return Value (value attribute)
     *
     * @access public
     * @return string Value attribute element
     */
    function getValue() {
        return $this->getAttribute('value');
    }// End function getValue
    /**
     * Generate and return HTML code for editor
     *
     * @access public
     * @return string HTML code element
     */
    function toHtml() {
        if ($this->_flagFrozen) {
            return $this->getFrozenHtml();
        } else {
            /*
             * Create FCK editor
             */
            // Load FCKeditor class
            require_once('fckeditor.php');
            // Create instance
            $oFCKeditor = new FCKeditor($this->getAttribute('name'));
            // Set parameters
            if ($this->_sFckBasePath !== NULL) {
                $oFCKeditor->BasePath                    = $this->_sFckBasePath;
            }
            if ($this->_sToolbarSet !== NULL) {
                $oFCKeditor->ToolbarSet                  = $this->_sToolbarSet;
            }
            if ($this->_sWidth !== NULL) {
                $oFCKeditor->Width                       = $this->_sWidth;
            }
            if ($this->_sHeight !== NULL) {
                $oFCKeditor->Height                      = $this->_sHeight;
            }
            if ($this->_aFckConfigProps !== NULL) {
                $oFCKeditor->Config                      = $this->_aFckConfigProps;
                // If a relative path is given, then precede it with the editor's basepath (like in fckconfig.js)'
                if (isset($oFCKeditor->Config['SkinPath']) && substr($oFCKeditor->Config['SkinPath'], 0, 1) != '/') {
                    $oFCKeditor->Config['SkinPath']      = $this->_sFckBasePath.$oFCKeditor->Config['SkinPath'];
                }
                if (isset($oFCKeditor->Config['EditorAreaCSS']) && substr($oFCKeditor->Config['EditorAreaCSS'], 0, 1) != '/') {
                    $oFCKeditor->Config['EditorAreaCSS'] = $this->_sFckBasePath.$oFCKeditor->Config['EditorAreaCSS'];
                }
            }
            $oFCKeditor->Value                           = $this->getValue();
            // Generate the HTML code for the editor
            $sFCKCode = $oFCKeditor->CreateHTML();
            // Destroy FCKeditor object
            unset($oFCKeditor);
            /*
             * return code
             */
            return $this->_gettabs().$sFCKCode;
        }
    }// End function toHtml
    /**
     * Return contents without HTML tags
     *
     * @access public
     * @return string Text contents element
     */
    function getFrozenHtml() {
        $sValue = htmlspecialchars($this->getValue());
        if ($this->getAttribute('wrap') == 'off') {
            $sHtml = $this->_getTabs(). '<pre>' .$sValue. '</pre>' . "\n";
        } else {
            $sHtml = nl2br($sValue). "\n";
        }
        return $sHtml.$this->_getPersistantData();
    }// End function getFrozenHtml
}
?>