<?php


/**
 * Created on 10/09/2006
 * @author tarjei huse
 * @package midcom.admin.aegir
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * 
 */
require_once "phing/Task.php";
/**
 * This class runs pear package on a finished package.xml file. 
 */
class pearPackagePackage extends Task
{

    protected $channel = "pear.midcom-project.org";

    function __construct()
    {
        ini_set('memory_limit', '-1'); 
    }

    protected $returnProperty; // name of property to set to return value

    /**
     * The path to where the module is stored.
     */
    private $path = null;
    /**
     * The target directory where the packagefile should be saved.
     */
    protected $target_dir = null;
    /**
     * The setter for the attribute "message"
     */
    public function setTarget_dir($str)
    {
        $this->target_dir = $str;
    }
    public function setPath($str)
    {
        $this->path = $str;
    }
    public function setChannel($str)
    {
        $this->channel = $str;
    }
    /** Sets property name to set with return value of function or expression.*/
    public function setReturnProperty($r)
    {
        $this->returnProperty = $r;
    }

    protected $copyfiles = array();

    /**
     * The init method: Do init steps.
     */
    public function init()    {}

    /**
     * The main entry point method.
     */
    public function main()
    {
        if ($this->target_dir === null  
            || !is_dir($this->target_dir)  
        ) {
            throw new Exception("You must set the target attribute to a writable directory (current: {$this->target_dir})!\n");
        }
        $this->execPearPackage();
    }

    protected function execPearPackage()
    {
        $curr_dir = getcwd();
        chdir($this->target_dir);
        $pear = exec('which pear');
        if (!is_executable($pear))
        {
            die("Pear executable $pear is not executable!");
        }
        
        $ret = exec("$pear package-validate {$this->path}/package.xml", $out, $status);
        $out = null;
        if ($status == 0)
        {
            $ret = exec("$pear package {$this->path}/package.xml", $out, $status);
            foreach ($out as $line) {
                if (stripos($line, 'error')) {
                    echo $line . "\n";
                }
                if (stripos($line, 'warning')) {
                    echo $line . "\n";
                }
                
            }
            
        } 
        else 
        {
            chdir($curr_dir);
            if (!is_null($out))
            {
                echo implode($out);
            }
            die ("Packagefile did not validate! Exiting.");
        }
        chdir($curr_dir);
        
    }
}
?>