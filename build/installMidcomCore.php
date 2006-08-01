<?php

/**
 * Created on 27/07/2006
 * @author tarjei huse
 * @package midcom.admin.aegir
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * 
 * Creates the midcom directory and it's subdirectories and 
 * makes symlinks to the files in question.
 * 
 * The major
 * 
 */

require_once "phing/Task.php";

class installMidcomCore extends Task
{

    /**
     * The name of the module
     */
    private $module = 'midcom.core';
    /**
     * The name of the module when installed
     */
    private $module_to = 'midcom';
    
    /**
     * The path to install the module, should be set
     * in build properties
     */
    protected $install_dir = null;

    public function setInstall_dir($str)
    {
        $this->install_dir = $str;
    }
    /**
     * The setter for the attribute "project_dir"
     */
    protected $project_dir = null;

    public function setProject_dir($str)
    {
        $this->project_dir = $str;
    }
    /**
     * @var string path to where static files should be installed
     */
    protected $static_dir = null;
    public function setStatic_dir($dir) {
        $this->static_dir = $dir;
    }
    /**
     * Directories that we make so that packages may be installed 
     * bellow them.
     */
    protected $subdirs = array (
        'admin',
        'helper',
        'services'
    );
    /**
     * files that should not be symlinked for different 
     * reasons.
     */
    protected $skip_dirs = array (
        '.svn',
        '.',
        '..',
        'static'
    );
    
    
    
    /**
     * The installdir/midcom path
     */
    protected $to = false;
    /**
     * The project_dir/midcom.core path
     */
    protected $from = false;
    /**
     * The init method: Do init steps.
     */
    public function init()
    {
        // invert the skipdirs and symlink arrays so we can use 
        // array_key_exists
        $this->subdirs = array_flip($this->subdirs);
        $this->skip_dirs = array_flip($this->skip_dirs);
    }

    /**
     * Create the projectdir and then make a symlink into the structure. 
     */
    public function main()
    {
        $this->check();
        
        $root_files = $this->get_module_dirs($this->project_dir . "/" . $this->module );
        foreach ($root_files as $file => $value) 
        {
            $to = sprintf("%s/%s", $this->install_dir, $file);
            $from = sprintf("%s/%s/%s", $this->project_dir,$this->module, $file);
            if (!is_dir($from)) 
            {
                $this->make_symlink("$from", "$to");
            }
        }
        
        $dirs = $this->get_module_dirs($this->from);

        foreach ($dirs as $dir => $value)
        {
            if (array_key_exists($dir, $this->subdirs))
            {
                $this->make_sub_dir($dir);
            }
            else
            {
                $this->make_symlink("{$this->from}/$dir","{$this->to}/$dir");
            }
        }
        
        /**
         * Make symlinks for the static files
         */
        $dirs = $this->get_module_dirs($this->from . "/static");
        
        foreach ($dirs as $dir => $value) {
            $from =  "{$this->from}/static/$dir";
            $to   =  "{$this->static_dir}/$dir";   
            $this->make_symlink($from, $to);
        }
        
    }
    /**
     * Creates the subdir and symlinks the files in it
     */
    function make_sub_dir($dir)
    {

        // midcom/$dir    
        if (!is_dir($this->to . "/" . $dir )) 
        {
            mkdir($this->to . "/" . $dir, 0777, true);
        }
        $files = dir($this->from . "/" . $dir);
        // quick fix.
        
        // symlink the files below
        if ($files)
            while (($file = $files->read()) !== false)
            {
                if (array_key_exists($file, $this->skip_dirs))
                {
                    continue;
                }
                $link = sprintf("%s/%s/%s",$this->to, $dir, $file );
                if (is_link($link)) 
                {
                    continue;
                }
                 
                $command = sprintf("ln -s %s/%s/%s %s",
                            $this->from,$dir, $file,
                            $link
                            );
                $this->exec_command($command);
            }

    }
    
    
    /**
     * Creates a symlink to the file or directory
     * @param string  paramname
     */
    private function make_symlink($from , $link, $debug = false)
    {
        if (is_link($link)) 
        {
            return;
        }   
        $command = sprintf("ln -s %s %s", $from, $link);
        $this->exec_command($command, $debug);
    }
    /**
     * returns a list of subdirectories and files as a assosiative 
     * array
     * dirname => dirname 
     */
    private function get_module_dirs($from)
    {
        $dirs = dir($from);
        $ret = array ();
        while (($dir = $dirs->read()) !== false)
        {
            if (array_key_exists($dir, $this->skip_dirs))
            {
                continue;
            }
            $ret[$dir] = $dir;
        }
        
        return $ret;
    }
    
    /**
     * Executes a given command.
     * @return none
     * @throws exception
     * @param string $command the command to be executed
     * @param boolean $debug set to true if you want to just se the 
     * command to be executed.
     */
    private function exec_command($command, $debug = false) {
        if ($debug) {
            echo $command . "\n";
            return;
        }
        $ret = "";
        exec($command, & $output, $ret);
        if ($ret !== 0)
        {
            throw new Exception("Exec of $command returned non zero code $ret");
        }
        
    }
    /**
     * Checks that the correct dirs exists, defines this->from, this->to,
     * Also responsible for converting the path midcom.core to midcom.
     */
    private function check() {
        if ($this->install_dir === null)
        {
            throw new Exception("Path must be set for this task to work!");
        }
        // this is the midcom.core => midcom directory conversin.
        $this->to = $this->install_dir . "/" . $this->module_to;
        $this->from = $this->project_dir . "/" . $this->module . "/" . $this->module_to;
        
        if (!is_dir($this->to))
        {
            throw new Exception("The directory {$this->to} does not exist!");
        }
        
        if (!is_dir($this->from))
        {
            throw new Exception("The directory {$this->from} does not exist!");
        }
        
        if (!file_exists($this->install_dir) && !mkdir($this->install_dir, 0777, true))
        {
            echo "Failed to create the needed directory {$this->install_dir}\n";
            return;
        }

        
    }
}
?>