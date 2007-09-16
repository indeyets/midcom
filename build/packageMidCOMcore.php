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
    /*
     * the template pacakge.xml file to use. 
     * */
    public function setTemplate($t) {
        $this->template = $t;
    }
    public function setStability($s) {
        $this->stability = $s;
    }

    // todo: add a setVersion from the commandline
	protected $copyfiles = array ();

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
            'simpleoutput' => true,
			            'include' => array('*.php', 'midcom/*', 'support/*'),
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
        $package->addRole('sql','midgardsql');
        $package->addRole('xml', 'mgdschema');

        $package->generateContents();

        if ($package->debugPackageFile()) {
            echo "Writing package.....\n";
            $package->writePackageFile();
        }
        $this->project->setProperty('pear_name', 'midcom_core');

    }
    
    protected function getNotes($package) {
        // todo add release notes...
    }

    /*
     * sets up the basic packageobject
     * @returns the package object
     * */
    protected function makeBase($options) {
        PEAR::setErrorHandling(PEAR_ERROR_DIE);
        //$package = new PEAR_PackageFileManager2();
        $package = PEAR_PackageFileManager2::importOptions($this->template, $options);
        $package->setPackageType('php');
        $package->setAPIStability($this->stability);
        $package->setPhpDep($this->phpversion);
        echo "Done with makeBase\n\n";
        return $package;
    }
}
?>
