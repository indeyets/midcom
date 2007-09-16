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
class packageMidCOM extends Task
{

	protected $package = null; // package name
	/**
	 * The PEAR name of the package.
	 */
	protected $package_name;
	protected $channel = "pear.midcom-project.org";

	function __construct()
	{
        ini_set('memory_limit', '-1'); 
	}

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
		$this->package_name = str_replace('.', '_', $this->package);
		$this->package_name = str_replace('/', '', $this->package_name);
		echo "Using package name : {$this->package_name} \n";
		// todo add more validation
		if ($this->target_dir === null  
			|| !is_dir($this->target_dir)  
		) {
			throw new Exception("You must set the target attribute to a writable directory (current: {$this->target_dir})!\n");
		}
		// read the manifest
		$this->readManifest();
		$packageInfo = $this->getComponentInfo();
		// build the filelist
		$filelist = $this->getFileList($packageInfo);
		// create the xml for package.xml
		$xml = $this->createXml($packageInfo, $filelist);
		// save package.xml.
		file_put_contents($this->path . "/" . $this->package . "/package.xml", $xml);
		
		$this->execPearPackage();
		// I planned to use the tar task to create the package, then this would be needed
		$name = $this->package_name . "-" . $packageInfo['version'];
		$this->project->setProperty($this->returnProperty, $name);
		// should I delete package.xml? Hmm, todo I think.

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
		
		$ret = exec("$pear package-validate {$this->path}/{$this->package}/package.xml", $out, $status);
		$out = null;
		if ($status == 0)
		{
			$ret = exec("$pear package {$this->path}/{$this->package}/package.xml", $out, $status);
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

	protected function getFileList($packageInfo)
	{

		$filelist_config = array (
			'filelist' => '',
			'package' => $packageInfo,
			'component' => $this->package_name,
			'path' => $this->path . "/" . $this->package,
			'prefix' => '    ',
			'baseinstalldir' => 'midcom/lib/' . str_replace('.',
			'/',
			$this->package
		), 'static' => false,);
		$filelist = $this->directory_list_contents($filelist_config);
		return $filelist;
	}

	/**
	 * Generate the filelist
	 *�@param array $config File listing configuration
	 * @return string File XML list
	 */
	function directory_list_contents($config, $directory_name_override = null)
	{
		$directory = dir($config['path']);

		if ($directory_name_override)
		{
			$dir_name = $directory_name_override;
		} else
		{
			$dir_name = basename($config['path']);
		}

		// Add more to the prefix
		$config['prefix'] .= '    ';

		// Start the directory output
		if ($dir_name == 'static' && $config['prefix'] != '        ')
		{
			// All static files are handled through Role_Web
			$config['static'] = true;
			$config['filelist'] .= "{$config['prefix']}<dir name=\"{$dir_name}\">\n";
			$config['baseinstalldir'] = str_replace('_', '.', "/{$config['component']}");
			$config['install-as-prefix'] = '';
		} else
		{
			$config['filelist'] .= "{$config['prefix']}<dir name=\"{$dir_name}\">\n";
		}

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
			if ($entry == 'package.xml')
			{
				// Ignore the package file itself
				continue;
			}

			// Handle packaging file roles 
			elseif ($dir_name == 'config' && $entry == 'mgdschema.xml')
			{
				// MgdSchemas shipped by components are placed in config/mgdschema.xml
				$role = 'mgdschema';
			}
			elseif ($dir_name == 'config' && $entry == 'mgdschema.sql')
			{
				// SQL files shipped by components are placed in config/mgdschema.xml
				// These will be installed via the Datagard database update command
				$role = 'midgardsql';
			} else
			{
				// All files are by default PHP
				$role = 'php';

				// Check for potential other file extensions
				$path_parts = pathinfo($entry);
				switch ($path_parts['extension'])
				{
					// binary formats
					case 'jpg' :
					case 'gif' :
					case 'png' :
					case 'zip' :
					case 'tgz' :
						$role = 'data';
						break;
						// Web formats *not* in static directory
					case 'html' :
					case 'js' :
					case 'htc' :
					case 'css' :
						$role = 'data';
						break;
				}
			}

			if (is_dir("{$config['path']}/{$entry}"))
			{
				// List the subdirectory
				$subconfig = $config;
				$subconfig['path'] = "{$config['path']}/{$entry}";
				$subconfig['install-as-prefix'] .= "{$entry}/";
				$config['filelist'] = $this->directory_list_contents($subconfig);
			} else
			{
				// List the files
				if ($config['static'])
				{
					$role = 'web';
					$config['filelist'] .= "{$config['prefix']}    <file baseinstalldir=\"{$config['baseinstalldir']}\" install-as=\"{$config['install-as-prefix']}{$entry}\" name=\"{$entry}\" role=\"{$role}\" />\n";
				} else
				{
					$config['filelist'] .= "{$config['prefix']}    <file baseinstalldir=\"{$config['baseinstalldir']}\" name=\"{$entry}\" role=\"{$role}\" />\n";
				}
			}
			$this->copyfiles[] = $config['path'] . "/" . $entry;
		}

		$directory->close();
		$config['filelist'] .= "{$config['prefix']}</dir>\n";
		return $config['filelist'];
	}

	public function getComponentInfo()
	{
		$component = array ();
		$component['baseinstalldir'] = "midcom/lib";
		$component_path_array = explode('.', $this->package);
		foreach ($component_path_array as $directory)
		{
			$component['baseinstalldir'] = "{$component['baseinstalldir']}/{$directory}";
		}
		// PEAR packages can't have dots in their names
		$package['name'] = $this->package_name;

		// Default license to LGPL if missing
		if (array_key_exists('license', $this->manifest['package.xml']))
		{
			$package['license'] = $this->manifest['package.xml']['license'];
		} else
		{
			$package['license'] = 'LGPL';
		}

		// Version string is a string
		$package['version'] = $this->manifest['version'];

		// Release date is today
		// TODO: Get latest modification date from CHANGES
		$package['date'] = date('Y-m-d');
		$package['time'] = date('H:i:s');

		// Package state. Default to stable
		if (array_key_exists('state', $this->manifest))
		{
			$package['state'] = $this->manifest['state'];
		} else
		{
			echo "Note: You have not set packagestate. Defaulting to alpha.\n";
			$package['state'] = 'alpha';
		}

		// Load the summary
		if (array_key_exists('summary', $this->manifest['package.xml']))
		{
			$package['summary'] = $this->manifest['package.xml']['summary'];
		} else
		{
			$package['summary'] = "MidCOM component {$this->package}";
		}

		// Load the description
		if (array_key_exists('description', $this->manifest['package.xml']))
		{
			$package['description'] = $this->manifest['package.xml']['description'];
		} else
		{
			$package['description'] = "MidCOM component {$this->package}";
		}

		// 	Generate the maintainer list
		$package['maintainers'] = '';
		if (array_key_exists('maintainers', $this->manifest['package.xml']) && is_array($this->manifest['package.xml']['maintainers']))
		{
			foreach ($this->manifest['package.xml']['maintainers'] as $username => $person)
			{
				if (!is_array($person))
				{
					$person = Array ();
				}

				if (!array_key_exists('name', $person))
				{
					// Maintainer must have a name
					continue;
				}

				if (!array_key_exists('role', $person))
				{
					$person['role'] = 'developer';
				}

				if (!array_key_exists('active', $person))
				{
					$person['active'] = 'yes';
				}

				$package['maintainers'] .= "
											    <{$person['role']}>
											        <name>{$person['name']}</name>    
											        <user>{$username}</user>
											        <email>{$person['email']}</email>
											        <active>{$person['active']}</active>
											    </{$person['role']}>
								        		";
			}
		}

		// Generate dependencies, if any
		$package['dependencies'] = '';
		if (array_key_exists('dependencies', $this->manifest['package.xml']) && is_array($this->manifest['package.xml']['dependencies']))
		{
			foreach ($this->manifest['package.xml']['dependencies'] as $requirement => $dependency)
			{
				if (!is_array($dependency))
				{
					$dependency = Array ();
				}

				$dependency['min'] = '';
				if (array_key_exists('version', $dependency))
				{
					// No version specified, the dependency just needs to exist
					$dependency['min'] = "<min>{$dependency['version']}</min>";
				}

				if (!array_key_exists('type', $dependency))
				{
					// Default to depending on PEAR packages
					$dependency['type'] = 'package';
				}

				if (!array_key_exists('channel', $dependency))
				{
					// Default to depending on packages from MidCOM repository
					$dependency['channel'] = $this->channel;
				}

				if (strstr($requirement, '.'))
				{
					// Convert dots in component names to underscores used in PEAR packages
					$requirement = str_replace('.', '_', $requirement);
				}

				$package['dependencies'] .= "
										            <{$dependency['type']}>
										                <name>{$requirement}</name>
										                <channel>{$dependency['channel']}</channel>
										                {$dependency['min']}
										            </{$dependency['type']}>            
										        ";
			}
		}
		return $package;
	}
	/**
	 * Reads the manifest file and redies it for parsing.
	 */
	protected $manifest = null;
	protected function readManifest()
	{
		$file = sprintf("%s/%s/config/manifest.inc", $this->path, $this->package);
		$manifest = array ();
		if (!file_exists($file))
		{
			die("Missing componentfile $file. Cannot package {$this->package}\n");
		}
		eval ('$manifest = Array(' . file_get_contents($file) . ');');

		$this->manifest = $manifest;
		// Require PEAR information
		if (!array_key_exists('package.xml', $this->manifest) || !is_array($this->manifest['package.xml']))
		{
			die("PEAR packaging information missing from component manifest {$component['manifest_file']}.\n");
		}

	}

	protected function createXml($package, $filelist)
	{

		// Create package XML
		return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
				<package packagerversion=\"1.4.5\" version=\"2.0\" xmlns=\"http://pear.php.net/dtd/package-2.0\" xmlns:tasks=\"http://pear.php.net/dtd/tasks-1.0\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://pear.php.net/dtd/tasks-1.0
				http://pear.php.net/dtd/tasks-1.0.xsd
				http://pear.php.net/dtd/package-2.0
				http://pear.php.net/dtd/package-2.0.xsd\">
				    <name>{$package['name']}</name>
				    <channel>{$this->channel}</channel>
				    <summary>
				        {$package['summary']}
				    </summary>
				    <description>
				        {$package['description']}
				    </description>
				    {$package['maintainers']}
				    <date>{$package['date']}</date>
				    <time>{$package['time']}</time>
				    <version>
				        <release>{$package['version']}</release>
				        <api>{$package['version']}</api>
				    </version>
				    <stability>
				        <release>{$package['state']}</release>
				        <api>{$package['state']}</api>
				    </stability>
				    <license>{$package['license']}</license>
				    <notes>{$package['version']} {$package['state']}</notes>        
				    <contents>\n{$filelist}    </contents>        
				    <dependencies>
				        <required>
				            <php>
				                <min>4.3.0</min>
				            </php>
				            <pearinstaller>
				                <min>1.4.0</min>
				            </pearinstaller>        
				            {$package['dependencies']}  
                            <extension>
                                <name>midgard</name>
                                <min>1.8.1</min>
                            </extension>
				        </required>
				    </dependencies>
				    <phprelease /> 
				</package>
				    ";

	}

}
?>