<?php
/**
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: php.php 11210 2007-07-13 01:05:56Z solt $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanager 2 Simple php datatype. The php value encaspulated by this type is
 * passed as-is to the storage layers, no specialieties done, just a string.
 *
 * @package midcom.helper.datamanager2
 */
class midcom_helper_datamanager2_type_php extends midcom_helper_datamanager2_type
{
    /**
     * The current string encaspulated by this type.
     *
     * @var string
     * @access public
     */
    var $value = '';
    
    var $code_valid = true;
    var $code_valid_errors = array();

    function convert_from_storage ($source)
    {
        $this->value = $source;
    }

    function convert_to_storage()
    {
        // Normalize line breaks to the UNIX format
        $this->value = preg_replace("/\n\r|\r\n|\r/", "\n", $this->value);
        
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
     * The validateion callback ensures that we dont't have an array or an object
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
        
        /*
        if (function_exists('parsekit_compile_string'))
        {
            // Use parsekit for evaluation if available
            $errors = array();
            parsekit_compile_string("?>{$this->value}", $errors, PARSEKIT_QUIET);
            
            if (!empty($errors))
            {
                $parse_errors = array();
                foreach ($errors as $error)
                {
                    if ($error['errno'] == E_PARSE)
                    {
                        $parse_errors[$error['lineno']] = $error['errstr'];
                    }
                }
    
                if (count($parse_errors) > 0)
                {
                    $error_message = '';
                    foreach ($parse_errors as $line => $error)
                    {
                        $error_message .= "<br />\nline {$line}: {$error}";
                    }
                    $this->validation_error = sprintf($this->_l10n->get('type php: parse error %s'), $error_message);
                    return false;
                }
            }
            
            return true;
        }
        */

        // TODO: Figure out safer way to do this
        // Try to just run the code and see what happens
        ob_start();
        eval("?>{$this->value}");
        $parse_results = ob_get_contents();
        ob_end_clean();
        
        if (strstr($parse_results, 'Parse error'))
        {
            $this->validation_error = $this->_l10n->get('type php: parse error');
            return false;
        }
        
        return true;
    }

    function convert_to_html()
    {
        return highlight_string($this->value, true);
    }
}

?>