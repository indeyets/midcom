<?php


/**
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * 
 */
require_once "phing/Task.php";

require 'class.JavaScriptPacker.php';

/**
 * Javascript packer task for Phing
 */
class packJavascripts extends Task
{
	function __construct()
	{

	}

	protected $returnProperty; // name of property to set to return value

	/**
	 * The root path to where the files are stored.
	 */
	private $path = null;
	
	/**
	 * List of files to be packed
	 */
	private $js_files = array();
	/**
	 * List of packed files
	 */
	private $js_packed_files = array();
	
	private $statistics = null;
	
	/**
	 * The target directory where the packed files should be saved.
	 */
	protected $target_dir = null;
	
	/**
	 * The setter for the attribute "target_dir"
	 */
	public function setTarget_dir($str)
	{
		$this->target_dir = $str;
	}
	
	public function setPath($str)
	{
		$this->path = $str;
	}
	
	/**
	 * Sets property name to set with return value of function or expression.
	 */
	public function setReturnProperty($r)
	{
		$this->returnProperty = $r;
	}

	protected $copyfiles = array ();

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
	    $this->directory_list_js_files($this->path);
	    
	    $this->pack_files();
	    
	    $this->project->setProperty($this->returnProperty, $this->statistics);
    }
	
	/**
	 * Generate the filelist
	 * @param array $config File listing configuration
	 * @return string File XML list
	 */
	function directory_list_js_files($path)
	{
		$directory = dir($path);

		// List contents
		while (false !== ($entry = $directory->read()))
		{
			if (substr($entry, 0, 1) == '.')
			{
				// Ignore dotfiles
				continue;
			}
			if ($entry == 'CVS' || $entry == '.svn')
			{
				// Ignore CVS directories
				continue;
			}

			// Check for js files
			$path_parts = pathinfo($entry);
			switch ($path_parts['extension'])
			{
				case 'js' :
					$this->js_files[] = $path . "/" . $entry;
					//$this->js_packed_files[] = $this->target_dir . "/" . $path_parts['filename'] . ".pack.js";
					$this->js_packed_files[] = $path . "/" . $path_parts['filename'] . ".pack.js";
					break;
				default:
					break;
			}

			if (is_dir("{$path}/{$entry}"))
			{
				// List the subdirectory
				$subpath = "{$path}/{$entry}";
				$this->directory_list_js_files($subpath);
			}
		}
	}
	
	function pack_files()
	{
	    $t1 = microtime(true);
	    
	    $file_count = count($this->js_files);
	    foreach ($this->js_files as $key => $src_file)
	    {
	        $src_file_contents = file_get_contents($src_file);
	        $trgt_file = $this->js_packed_files[$key];
	        $this->statistics .= "Packing file: {$src_file} to {$trgt_file}... ";

	        $packer = new JavaScriptPacker($src_file_contents, 'Normal', true, false);
            $packed = $packer->pack();
            
            $this->statistics .= "writing packed file... ";
            file_put_contents($trgt_file, $packed);
            $this->statistics .= "DONE\n";
	    }
	    
        $t2 = microtime(true);
        $time = sprintf('%.4f', ($t2 - $t1) );
        $this->statistics .= "{$file_count} files packed in {$time} seconds. \n";
	}
    
}

?>