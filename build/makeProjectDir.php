<?php
/**
 * Created on 27/07/2006
 * @author tarjei huse
 * @package midcom.admin.aegir
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * 
 * Creates the module directory based on the path and module name
 * 
 */
 
require_once "phing/Task.php";

class makeProjectDir extends Task {

    /**
     * The name of the module
     */
    private $module = null;
    /**
     * The path to install the module, should be set
     * in build properties
     */
    protected $install_dir = "/tmp";
    
    public function setInstall_dir($str) {
        $this->install_dir = $str;
    }
    /**
     * The setter for the attribute "project_dir"
     */
    protected $project_dir = null;
    
    public function setProject_dir($str) {
        $this->project_dir = $str;
    }
    /**
     * Umask for the dirs created.
     */
    protected $umask = 0777;
    public function setUmask($str) {
        $this->umask = $str;
    }
    
    public function setModule($str) {
        $this->module = $str;
    }

    /**
     * The init method: Do init steps.
     */
    public function init() {
      // nothing to do here
    }

    /**
     * Create the projectdir and then make a symlink into the structure. 
     */
    public function main() {
        if ($this->install_dir === null) {
            throw new Exception("Path must be set for this task to work!");
        }
        $dirs = explode('.', $this->module);
        $module_dir = array_pop($dirs);
        $module_path = $this->install_dir . "/" . implode('/',$dirs);
        
        if (!file_exists($module_path) && !mkdir ($module_path,0777, true)) {
            echo "Failed to create directory {$module_path}\n";
        }
        
        $command = sprintf("ln -s %s/%s %s/%s", $this->project_dir,$this->module, $module_path, $module_dir); 
        $ret = "";
        exec ($command, &$output, $ret);
        
        
        echo "$command returned $ret\n";
        
    }
}

?>