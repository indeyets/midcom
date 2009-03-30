<?php
/**
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

require_once "phing/Task.php";
require_once 'phing/types/FileSet.php';

/**
 * task to link all static folders
 */
class symlinkStatics extends Task
{    
	private $static_dir;
	private $filesets = array();
    
    function __construct() {}

    /**
     * The setter for the attribute "static_dir"
     */
    public function setStatic_dir($str)
    {
        $this->static_dir = $str;
    }

	/**
	 * Add a new fileset containing the .php files to process
	 *
	 * @param FileSet the new fileset containing static files
	 */
	public function addFileSet(FileSet $fileset)
	{
		$this->filesets[] = $fileset;
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
        $folders = $this->get_folders();
        
        foreach ($folders as $component => $folder)
        {
            $folder = realpath($folder);            
            $link = "{$this->static_dir}/{$component}";
            
            if (is_dir($folder))
            {
                $this->make_symlink($folder, $link);
            }
        }
    }
    
	/**
	 * Iterate over all filesets and return the details of all separate component and their static folder.
	 *
	 * @return array an array of (component, static folder) pairs
	 */
	private function get_folders()
	{
		$folders = array();

		foreach ($this->filesets as $fileset)
		{
			$ds = $fileset->getDirectoryScanner($this->project);
			$ds->scan();

            $includedFolders = $ds->getIncludedDirectories();
            
			foreach ($includedFolders as $folder)
			{
				$folderparts = explode('/', $folder);
				
				if (! array_key_exists($folderparts[0], $folders))
				{
    				$folders[$folderparts[0]] = $folder;
				}
			}
		}

		return $folders;
	}
	
    /**
     * Creates a symlink to the file or directory
     * @param string  from the folder to link
     * @param string  link name of the link to be created
     * @param string  debug enable output
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
     * Executes a given command.
     * @return none
     * @throws exception
     * @param string $command the command to be executed
     * @param boolean $debug set to true if you want to just see the
     * command to be executed.
     */
    private function exec_command($command, $debug = false) {
        if ($debug)
        {
            echo $command . "\n";
            return;
        }
        
        $ret = "";
        exec($command, $output, $ret);
        if ($ret !== 0)
        {
            throw new Exception("Exec of $command returned non zero code $ret");
        }

    }
}
    
?>