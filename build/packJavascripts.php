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
    
    private $js_src_files = array();
    
    private $js_file_names = array();
    
    private $no_source = true;
    
    private $action = 'pack';
    
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
    
    public function setAction($str)
    {
        $this->action = $str;
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
        
        $this->prepare_files();
        
        if ($this->action == 'pack')
        {
            $this->pack_files();            
        }
        else
        {
            $this->unpack_files();            
        }
                
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
            if ($entry == '.svn')
            {
                // Ignore SVN directories
                continue;
            }

            // Check for js files
            $path_parts = pathinfo($entry);
            switch ($path_parts['extension'])
            {
                case 'js':
                    $clean_filename = str_replace('.src', '', $path_parts['filename']);
                    $clean_filename = str_replace('.pack', '', $clean_filename);
                    
                    if (! in_array($path_parts['filename'], $this->js_file_names))
                    {
                        if (   strpos($path_parts['filename'], '.src') === false
                            && strpos($path_parts['filename'], '.pack') === false)
                        {
                            $this->js_file_names[$clean_filename] = $path_parts['filename'];
                            $this->js_files[$clean_filename] = $path . "/" . $entry;
                        }
                    }
                    if (strpos($path_parts['filename'], '.pack') !== false)
                    {
                        $this->js_packed_files[$clean_filename] = $path . "/" . $entry;
                    }
                    if (strpos($path_parts['filename'], '.src') !== false)
                    {
                        $this->js_src_files[$clean_filename] = $path . "/" . $entry;
                    }
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
    
    function prepare_files()
    {
        foreach ($this->js_file_names as $clean_name => $file)
        {
            $old_packed = $this->js_packed_files[$clean_name];
            $src_file = $this->js_src_files[$clean_name];
            $sel_source = '';
            //echo "\nClean name: {$clean_name}, file: {$file}\n";
            //echo "\nPacked: {$old_packed}\n";
            //echo "\nSource: {$src_file}\n";
            
            $orig_file_parts = pathinfo($this->js_files[$clean_name]);
            $new_packed_file = $orig_file_parts['dirname'] . "/" . $orig_file_parts['filename'] . ".pack.js";
            $new_source_file = $orig_file_parts['dirname'] . "/" . $orig_file_parts['filename'] . ".src.js";
                        
            if (empty($src_file))
            {
                //echo "\nWe have no separate source file, assume the original is the source.\n";
                $this->no_source = true;
                $sel_source = $this->js_files[$clean_name];
            }
            else
            {
                //echo "\nWe have source file. Then we also must have either packed file, and/or the original is packed.\n";
                $this->no_source = false;
                $sel_source = $src_file;

                if (! empty($old_packed))
                {
                    //echo "\nWe have old packed file. Delete this.\n";
                    unlink($old_packed);
                }
            }            
            
            //echo "\nSelected source file {$sel_source}\n";
            
            if (! $this->no_source)
            {
                //Separate source file
                //echo "\nRename current packed file {$this->js_files[$clean_name]} to .pack\n";
                rename($this->js_files[$clean_name], $new_packed_file);
                //echo "\nRename current source file {$sel_source} to {$this->js_files[$clean_name]}\n";
                rename($sel_source, $this->js_files[$clean_name]);
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
            
            $src_file_parts = pathinfo($src_file);
            $src_new_file = $src_file_parts['dirname'] . "/" . $src_file_parts['filename'] . ".src.js";
            $trgt_file = $src_file;
            
            if (file_exists($src_file))
            {
                rename($src_file, $src_new_file);
                //echo "\nRename source file {$src_file} to {$src_new_file}\n";
                $this->statistics .= "\nrename {$src_file} to {$src_new_file}\n";
            }
            else
            {
                echo "\nFATAL ERROR: Could not find source file {$src_file}\n";
                continue;
            }
            
            $this->js_src_files[$key] = $src_new_file;
            $this->js_packed_files[$key] = $trgt_file;
            
            $this->statistics .= "Packing file: {$src_new_file} to {$trgt_file}... ";

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
    
    function unpack_files()
    {
        $t1 = microtime(true);
        
        $file_count = count($this->js_files);
        foreach ($this->js_files as $key => $file)
        {
            $file_parts = pathinfo($file);
            $src_file = $this->js_src_files[$key];
            $packed_file = $this->js_packed_files[$key];
            $new_src_file = $file;
            $new_packed_file = $file_parts['dirname'] . "/" . $file_parts['filename'] . ".pack.js";
            
            if (file_exists($packed_file))
            {
                if (   file_exists($new_packed_file)
                    && $packed_file != $new_packed_file)
                {
                    //echo "\nWe have old packed file. Delete this.\n";
                    unlink($new_packed_file);
                    $this->statistics .= "\nunlink {$new_packed_file}\n";
                }
                
                //echo "\nrename {$packed_file} to {$new_packed_file}\n";
                rename($packed_file, $new_packed_file);
                $this->statistics .= "\nrename {$packed_file} to {$new_packed_file}\n";
            }
            
            if (file_exists($src_file))
            {
                //echo "\nrename {$src_file} to {$new_src_file}\n";
                rename($src_file, $new_src_file);
                $this->statistics .= "\nrename {$src_file} to {$new_src_file}\n";
            }
        }
        
        $t2 = microtime(true);
        $time = sprintf('%.4f', ($t2 - $t1) );
        $this->statistics .= "\nunpacked {$file_count} files in {$time} seconds. \n";
    }
    
}

?>