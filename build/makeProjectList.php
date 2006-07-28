<?php
/**
 * Created on 27/07/2006
 * @author tarjei huse
 * @package midcom.admin.aegir
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * 
 */
 
require_once "phing/Task.php";

class makeProjectList extends Task {

    /**
     * The message passed in the buildfile.
     */
    private $path = null;

    protected $returnProperty; // name of property to set to return value

    /**
     * The setter for the attribute "message"
     */
    public function setPath($str) {
        $this->path = $str;
    }
    
    /** Sets property name to set with return value of function or expression.*/
    public function setReturnProperty($r) {
       $this->returnProperty = $r;
    }
    

    /**
     * The init method: Do init steps.
     */
    public function init() {
      // nothing to do here
    }

    /**
     * The main entry point method.
     */
    public function main() 
    {
        
        // dirs to skipp
        $skipp = array ( '.' , '..', 'scaffold' );
        $skipp = array_flip($skipp);
        $modules = array();    
    
        $files = dir($this->path);
        if (!$files) 
        {
            
            echo "midcom.devel.makeProjectList: No files found in {$this->path}";
            return;
        }
        while (($file = $files->read()) !== false) 
        {
            if (is_dir ($file) ) 
            { 
                if (array_key_exists($file, $skipp)) 
                {
                    continue;
                }
                if (file_exists($file . "/midcom/interfaces.php")) 
                {
                    $modules[] = $file;
                }
            }
            
            
        }
        
        //$this->returnProperty = $modules;
        $this->project->setProperty($this->returnProperty, implode(",", $modules));
        
    }
}

?>