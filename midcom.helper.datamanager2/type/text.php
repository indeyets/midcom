<?php
/**
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanager 2 Simple text datatype. The text value encapsulated by this type is
 * passed as-is to the storage layers, no specialties done, just a string.
 *
 * <b>Available configuration options:</b>
 *
 * - <i>int maxlength:</i> The maximum length of the string allowed for this field.
 *   This includes any newlines, which account as at most two characters, depending
 *   on the OS. Set this to 0 (the default) to have unlimited input.
 * - <i>string output_mode:</i> This option controls how convert_to_html operates. The
 *   default 'specialchars' will just pass the data entered in the field through
 *   htmlspecialchars(). See below for a full option listing.
 *
 * Available output modes:
 *
 * - 'html': No conversion is done.
 * - 'specialchars': The value is run through htmlspecialchars() (the default).
 * - 'midgard_f': Uses the Midgard :f formatter.
 * - 'midgard_F': Uses the Midgard :F formatter.
 * - 'markdown': Uses net.nehmer.markdown.
 *
 * @package midcom.helper.datamanager2
 */
class midcom_helper_datamanager2_type_text extends midcom_helper_datamanager2_type
{
    /**
     * The current string encapsulated by this type.
     *
     * @var string
     * @access public
     */
    var $value = '';

    /**
     * Maximum length of the string encapsulated by this type. 0 means no limit.
     * This is checked during validation.
     *
     * @param int
     * @access public
     */
    var $maxlength = 0;

    /**
     * Sets output formatting. See class introduction.
     *
     * @var string
     * @access private
     */
    var $output_mode = 'specialchars';
    
    /**
     * Runs HTML contents through the HTML Purifier library to ensure safe XHTML compatibility.
     *
     * Note: Applies only when output_mode is 'html'
     */
    var $purify = false;
    
    /**
     * Configuration values for HTML Purifier
     */
    var $purify_config = array();

    /**
     * Compatibility handler for the deprecated is_html configuration option.
     */
    function _on_configuring($config)
    {
        if (array_key_exists('is_html', $config))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('The configuration option "is_html" is deprecated, use "output_mode" instead.', MIDCOM_LOG_ERROR);
            if ($config['is_html'])
            {
                debug_add('Output mode config option set to "html" by the "is_html" compatibility mode.');
                $config['output_mode'] = 'html';
            }
            debug_pop();
        }
        
        $this->purify = $this->_config->get('html_purify');
        $this->purify_config = $this->_config->get('html_purify_config');
    }


    function convert_from_storage ($source)
    {
        $this->value = $source;
    }

    function purify_content()
    {
        if (isset($this->purify_config['Cache']['SerializerPath']) 
            && !file_exists($this->purify_config['Cache']['SerializerPath']))
        {
            mkdir($this->purify_config['Cache']['SerializerPath']);
        }
    
        require_once('HTMLPurifier.php');
        // For some reason we lose this along the way!
        error_reporting(E_ALL);

        $purifier = new HTMLPurifier($this->purify_config);
        
        // Set local IDPrefix to field name...
        $purifier->config->set('Attr', 'IDPrefixLocal', "{$this->name}_");

        // Load custom element/attribute definitions
        $config_defs = $this->_config->get('html_purify_HTMLDefinition');
        if (   is_array($config_defs)
            && !empty($config_defs))
        {
            $def =& $purifier->config->getHTMLDefinition(true);
            if (   isset($config_defs['addAttribute'])
                && is_array($config_defs['addAttribute'])
                && !empty($config_defs['addAttribute']))
            {
                foreach ($config_defs['addAttribute'] as $attrdef)
                {
                    if (!is_array($attrdef))
                    {
                        continue;
                    }
                    call_user_func_array(array($def, 'addAttribute'), $attrdef);
                }
            }
            if (   isset($config_defs['addElement'])
                && is_array($config_defs['addElement'])
                && !empty($config_defs['addElement']))
            {
                foreach ($config_defs['addElement'] as $elemdef)
                {
                    if (!is_array($elemdef))
                    {
                        continue;
                    }
                    call_user_func_array(array($def, 'addElement'), $elemdef);
                }
            }
        }

        /*
        echo "DEBUG: value before\n<pre>\n";
        echo htmlentities($this->value);
        echo "\n</pre>\n";
        */

        $this->value = $purifier->purify($this->value);

        /*
        echo "DEBUG: value after\n<pre>\n";
        echo htmlentities($this->value);
        echo "\n</pre>\n";
        die('Testing');
        */
    }

    function convert_to_storage()
    {
        // Normalize line breaks to the UNIX format
        $this->value = preg_replace("/\n\r|\r\n|\r/", "\n", $this->value);
                
        if ($this->purify)
        {
            switch ($this->output_mode)
            {
                case 'html':
                case 'markdown':
                    $this->purify_content();
                    break;
            }
        }
        
        return $this->value;
    }

    function convert_from_csv ($source)
    {
        $this->value = $source;
    }

    function convert_to_csv()
    {
        return $this->value;
    }

    /**
     * The validation callback ensures that we dont't have an array or an object
     * as a value, which would be wrong.
     *
     * @return bool Indicating validity.
     */
    function _on_validate()
    {
        if (   is_array($this->value)
            || is_object($this->value))
        {
            $this->validation_error = $this->_l10n->get('type text: value may not be array or object');
            return false;
        }

        if (   $this->maxlength > 0
            && strlen($this->value) > $this->maxlength)
        {
            $this->validation_error = sprintf($this->_l10n->get('type text: value is longer then %d characters'),
                $this->maxlength);
            return false;
        }

        return true;
    }

    function convert_to_html()
    {
        switch ($this->output_mode)
        {
            case 'html':
                return $this->value;

            case 'code':
                return '<pre style="overflow:auto">'.htmlspecialchars($this->value).'</pre>';

            case 'specialchars':
                return htmlspecialchars($this->value);

            case 'midgard_f':
                return mgd_format($this->value, 'f');

            case 'midgard_F':
                return mgd_format($this->value, 'F');

            case 'markdown':
                static $markdown = null;
                if (! $markdown)
                {
                    if (! class_exists('net_nehmer_markdown_markdown'))
                    {
                        $_MIDCOM->componentloader->load('net.nehmer.markdown');
                    }
                    $markdown = new net_nehmer_markdown_markdown();
                }
                return $markdown->render($this->value);

        }
    }
}

?>