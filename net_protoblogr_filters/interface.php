<?php
/**
 * @package net_protoblogr_filters
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM output filtering library
 *
 * @package net_protoblogr_filters
 */
class net_protoblogr_filters extends midcom_core_component_baseclass
{
    /**
     * HTML Tidy output filters
     */
    public function tidy($source)
    {
        if (!class_exists('tidy', false)) 
        {
            $_MIDCOM->log('net_protoblogr_filters::tidy', 'No Tidy installed', 'error');
            return $source;
        }
        
        $tidy = new tidy();
        
        if (!method_exists($tidy, 'parseString')) 
        {
            $_MIDCOM->log('net_protoblogr_filters::tidy', 'Tidy has no parseString capability', 'error');
            return $source;
        }
        
        // TODO: Read from configuration
        $opts = array
        (
            'indent' => true,
            'indent-spaces' => 4,
            'wrap' => 0,
            'output-xhtml' => true,
            'force-output' => true
        );
        
        $tidy->parseString($source, $opts, 'utf8');
        $tidy->cleanRepair();

        $source = (string) $tidy;
        
        // Some additional cleanups
        $source = preg_replace("@([\s]+)?(<(h[1-6])([^>]+)?>)([\s])+@", "\n\\1\\2\n", $source);
        $source = preg_replace("@(<(h[1-6]|li|title)([^>]+)?>)[\s]+@", '\\1', $source);
        $source = preg_replace("@[\s]+(</(h[1-6])>)@", "\\1\n", $source);
        $source = preg_replace("@[\s]+(</(li|title)>)@", '\\1', $source);
        $source = preg_replace("@(</head>)([\s]+)@", "\\2\\1\\2", $source);
        $source = preg_replace("@\n\n@", "\n", $source);

        return $source;
    }
}
?>