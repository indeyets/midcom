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
 * This class 
 */
class generateSchemaFile extends Task
{

	

	function __construct()
	{

	}

	protected $returnProperty; // name of property to set to return value

	/**
	 * The root path to where the module is stored.
	 */
	private $root = null;
	/**
	 * The target directory where the packagefile should be saved.
	 */
	protected $target_dir = null;

	public function setRoot($str)
	{
		$this->root = $str;
	}

	/** Sets property name to set with return value of function or expression.*/
	public function setReturnProperty($r)
	{
		$this->returnProperty = $r;
	}


	/**
	 * The init method: Do init steps.
	 */
	public function init()
	{
		
	}

	/**
	 * The main entry point method.
	 */
	public function main()
	{
		$command = sprintf("find %s -name '*.xml'", $this->root);
		exec($command, $result, $status);
			
		if ($status != 0 ) {
			throw new Exception("Find returned nonzero error!\n");
		}
		$schemafiles = array();
		foreach ($result as $key =>  $file ) {
			$filename = basename($file);
			if (is_dir($file)) continue;
			
			switch ($filename) {
				case 'mgdschema.xml':
					$schemafiles[] = $file;
					break;
				case 'package.xml':
				case 'build.xml':
					break;
				default:
					break;
			}
		}
		//var_dump($schemafiles);
		
		$xml = $this->generateXML($schemafiles);
		echo $xml;
		$location = $this->root . "/includes.xml";
		file_put_contents($location , $xml);
		$this->project->setProperty($this->returnProperty, $location);
	}
	
	protected function generate_include ($file) {
		return sprintf('    <include name="%s" />"', $file);
		//var_dump($var);
	}
	
	protected function generateXML ($files) {
		$includes = implode("\n", array_map(array($this,'generate_include'), $files));
		
		$xml = sprintf(
'<?xml version="1.0" encoding="UTF-8"?> <Schema xmlns="http://www.midgard-project.org/repligard/1.4"> 
%s
</Schema>' , $includes ) ;		
		return $xml;	
	}

	
}
?>
