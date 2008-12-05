<?php

/**
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

require_once "phing/Task.php";

/**
 * task to generate components style handle for Phing
 */
class resolveComponentsStyleHandle extends Task
{
    protected $returnProperty; // name of property to set to return value
    
    private $component = null; // name of the component to mangle with
    
    function __construct() {}
    
    /**
     * The setter for the attribute "component"
     */
    public function setComponent($str)
    {
        $this->component = $str;
    }
    
    /**
     * Sets property name to set with return value of function or expression.
     */
    public function setReturnProperty($r)
    {
        $this->returnProperty = $r;
    }
    
    /**
     * The init method: Do init steps.
     */
    public function init()
    {
        // nothing to do here
    }
    
    /**
     * The main entry point method.
     */
    public function main()
    {        
        $name_parts = explode("_", $this->component);
        
        $style_handle = "{$name_parts[0][0]}{$name_parts[1][0]}{$name_parts[2][0]}";
        
        $this->project->setProperty($this->returnProperty, $style_handle);
    }
}
    
?>