<?php

/**
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

require_once "phing/Task.php";

/**
 * task to find out asked component name part for Phing
 */
class resolveComponentNamePart extends Task
{
    protected $returnProperty; // name of property to set to return value
    
    private $component = null; // name of the component to mangle with
    private $part = 'name'; // name of the part to return
    
    function __construct() {}
    
    /**
     * The setter for the attribute "component"
     */
    public function setModule($str)
    {
        $this->component = $str;
    }
    
    /**
     * The setter for the attribute "part"
     */
    public function setPart($str)
    {
        $this->part = $str;
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
                
        switch ($this->part)
        {
            case 'domain':
                $part = $name_parts[0];
            break;
            case 'host':
                $part = $name_parts[1];
            break;
            case 'name':
            default:
                $part = $name_parts[2];
        }
        
        $this->project->setProperty($this->returnProperty, $part);
    }
}
    
?>