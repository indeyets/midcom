<?php

function wfile($obj, $f) {

    ob_start();
    var_dump($obj);
    file_put_contents($f, ob_get_contents());
    ob_end_clean();

}

/**
 * Created on 10/09/2006
 * @author tarjei huse
 * @package midcom.admin.aegir
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * 
 */
require_once "phing/Task.php";
require_once 'PEAR/PackageFileManager2.php';
require_once 'PEAR/PackageFileManager/File.php';
/**
 * This class 
 */
class packageMidCOMcore extends Task
{

    protected $package = null; // package name
    /**
     * The PEAR name of the package.
     */
    protected $package_name;
    /* pear channel */
    protected $channel = "pear.midcom-project.org";
    // package stability
    protected $stability = 'beta';
    // minimal phpversion
    protected $phpversion = '4.3.0'; // for 2.8

    protected $returnProperty; // name of property to set to return value
    protected $version;

    /**
     * The root path to where the module is stored.
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
        echo "Setting path to $str\n";
        $this->path = $str;
    }
    public function setPackage($str)
    {
        $this->package = $str;
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
    /**
     * the template pacakge.xml file to use. 
     */
    public function setTemplate($t) {
        $this->template = $t;
    }
    public function setStability($s) {
        $this->stability = $s;
    }
    public function setVersion($s) {
        $this->version = $s;
    }


    // todo: add a setVersion from the commandline
    protected $copyfiles = array ();
    /**
     * array of files in the static dir. 
     */
    protected $staticFiles ;

    /**
     * The init method: Do init steps.
     */
    public function init()
    {
        //
        return;
    }

    /**
     * The main entry point method.
     */
    public function main()
    {
        $baseOptions = array('filelistgenerator' => 'File',
            'packagedirectory' => $this->path,
            'baseinstalldir' => 'midcom/lib',
            'installexceptions' => array( 'support' => '/'),
            'dir_roles' => array(), 
            'simpleoutput' => true,
                        'ignore' => array('package-template.xml'),
                        'include' => array('*.php', 'midcom*', 'support*'),
            );
          
        $package = $this->makeBase($baseOptions);
        $package->addRole('jpg','web');
        $package->addRole('gif','web');
        $package->addRole('png','web');
        $package->addRole('zip','web');
        $package->addRole('tgz','web');
        $package->addRole('html','web');
        $package->addRole('js','web');
        $package->addRole('htc','web');
        $package->addRole('css','web');
        $package->addRole('inc','php');
        $package->addRole('txt','php');
        $package->addRole('sql','midgardsql');
        $package->addRole('xml', 'mgdschema');

        var_dump($this->version);

        $package->generateContents();

        $this->addStatic($package);

        if ($package->debugPackageFile()) {
            echo "Writing package.....\n";
            $package->writePackageFile();
        }
        $this->project->setProperty('pear_name', 'midcom_core');

    }
    
    protected function getNotes($package) {
        // todo add release notes...
    }
    /**
     * This method builds the list of files in the static dir and adds them to the 
     */
    protected function addStatic($package) 
    {

        $this->staticFiles = array();
        $this->getDirFilesRecursive($this->path . "/static");
        foreach ($this->staticFiles as $path => $files ) 
        {
            $fpath = str_replace($this->path . '/static/', "", $path);
            foreach ($files as $filename) 
            {
                $dir = '/static';
                $filen =  $fpath .'/' . $filename ;
                $package->addFile($dir, $filen , array(
                            'role' => 'web', 
                            'baseinstalldir' => '/', 
                            'install-as' => $fpath ."/". $filename ) );
            }
        }

    }

    protected function getDirFilesRecursive($path) {
        $list = dir($path);
        while (($file = $list->read()) !== FALSE) {
            if ($file{0} == '.') continue; // skipp .svn , . and ..
            if (is_dir($path .'/'.$file)) {
                $this->getDirFilesRecursive($path .'/' . $file);
            } else {
                if (!isset($this->staticFiles[$path])) $this->staticFiles[$path] = array();
                $this->staticFiles[$path][] = $file;
            }
        }
        $list->close();
    }

    /**
     * sets up the basic packageobject
     * @return the package object
     */
    protected function makeBase($options) {
        PEAR::setErrorHandling(PEAR_ERROR_DIE);
        //$package = new PEAR_PackageFileManager2();
        $package = PEAR_PackageFileManager2::importOptions($this->template, $options);
        $package->setPackageType('php');
        $package->setAPIStability($this->stability);
        $package->setPhpDep($this->phpversion);
        $package->setReleaseVersion($this->version);
        echo "Done with makeBase\n\n";
        return $package;
    }
}
?>