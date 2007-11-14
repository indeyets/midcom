<?php
/**
 * This File is part of the SmartLoader
 *
* 
* @author Maarten Manders (mandersm@student.ethz.ch)
* Adapted to MidCOM by Tarjei Huse
* @copyright Copyright 2005, Maarten Manders
* @link http://www.maartenmanders.org
* @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
* @package midcom 
*/

	/**
	* PHP Autoload Hook
	* 
	* Gets called, when an undefined class is being instanciated
	*
	* @package midcom 
	* @param_string string class name
	*/
	function __autoload($class_name) {
        $ldr = SmartLoader::instance();
        trigger_error("Autoloading");
		/* initializing loader */
		if(!$ldr->classes) {
			$ldr->setCacheFilename($GLOBALS['midcom_config_default']['cache_base_directory'] . "smartloader.cache.php");
			$ldr->addDir(MIDCOM_ROOT);
            //$ldr->addDir(MIDCOM_ROOT);
			$ldr->setClassFileEndings(array('.php'));
			$ldr->setIgnoreHiddenFiles(true);
			$ldr->setIgnoreSVN(true);
		}
		
		/* load the class or trigger some fatal error on failure */
		if(!$ldr->loadClass($class_name)) {
			trigger_error("SmartLoader: Cannot load class '".$class_name."'", E_USER_ERROR);
		}
	}
if (function_exists('spl_autoload_register'))
{
      ini_set('unserialize_callback_func', 'spl_autoload_call');

      spl_autoload_register('__autoload');

}
else if (!function_exists('__autoload'))
{
      ini_set('unserialize_callback_func', '__autoload');
}
        $ldr = SmartLoader::instance();
		/* initializing loader */
		if(!$ldr->classes) {
			$ldr->setCacheFilename($GLOBALS['midcom_config_default']['cache_base_directory'] . "smartloader.cache.php");
			$ldr->addDir(MIDCOM_ROOT);
            //$ldr->addDir(MIDCOM_ROOT);
			$ldr->setClassFileEndings(array('.php'));
			$ldr->setIgnoreHiddenFiles(true);
			$ldr->setIgnoreSVN(true);
            $ldr->createCache();
		}
	

	/**
	* SmartLoader Class
	*
	* Singleton. Manages class/interface retrieval, caching and inclusion.
	*
	* @package SmartLoader
	* @author Maarten Manders (mandersm@student.ethz.ch)
	* @example index.php
	* @see SmartLoader::instance()
	* @see SmartLoader::addDir()
	* @see SmartLoader::loadClass()
	*/
	class SmartLoader
	{
		/**
		* Class Directories
		* 
		* Holds the directories SmartLoader scans for class/interface definitions
		* 
		* @var array
		* @access private
		*/
		private $classDirectories = array();
		
		/**
		* Cache File Path
		* 
		* Holds the filename of the generated cache file.
		* 
		* @var string
		* @access private
		*/
		private $cacheFilename = 'smartloader_cache.php';
		
		/**
		* Class Index
		* 
		* Holds an associative array (class name => class file) when scanning.
		* 
		* @var array
		* @access private
		*/
		private $classIndex = array();
		
		/**
		* Class File Endings
		* 
		* Files with these endings will be parsed by the class/interface scanner.
		* 
		* @var array
		* @access private
		*/
		private $classFileEndings = array();
		
		/**
		* Follow SymLinks
		* 
		* Should SmartLoader follow SymLinks when searching class dirs?
		* 
		* @var boolean
		* @access private
		*/
		private $followSymlinks = true;
		
		/**
		* Ignore hidden files
		* 
		* Should SmartLoader ignore hidden files?
		* 
		* @access private
		*/
		private $ignoreHiddenFiles = false;
        /**
         * If directories starting with .svn should be ignored
         */
        private $ignoreSvnDirectories = false;
        /**
         * The regular expression to use when parsing for classes
         */
        public $classRegularExpression =  "%(interface|class)\s+(\w+)\s+(extends\s+(\w+)\s+)?(implements\s+\w+\s*(,\s*\w+\s*)*)?{%";

        /**
         * The static singleton instance
         */
        private static $soleInstance = FALSE;
        /**
         * The cache of classnames
         */
        public $classes = FALSE;
        
		/**
		* Constructor
		* 
		* Initialize SmartLoader
		*
		* @access public
		*/
		private function __construct() {
			/* do something smart here */
		}
        /**
         * Singleton factory
         * Remember: Singletons are hidden GLOBALS! 
         * @todo: register autoloader instead
         */
        public static function instance() 
        {
            if ( ! self::$soleInstance ) 
            {
                $class = __CLASS__;
                self::$soleInstance = new $class;
            }
            return self::$soleInstance;
        }
        
		
		/**
		* Set Cache File Path
		* 
		* Define a path to store the cache file. Make sure PHP has permission read/write on it.
		*
		* @access public
		* @param string string Cache File Path
		*/
		public function setCacheFilename($cacheFilename) {
			$this->cacheFilename = $cacheFilename;
		}
		
		/**
		* Set Class File Endings
		* 
		* Define which file endings will be considered by the class/interface scanner
		* An empty array will let the scanner parse any file type.
		*
		* @access public
		* @param array Endings
		*/
		public function setClassFileEndings($classFileEndings) {
			$this->classFileEndings = $classFileEndings;
		}
		
		/**
		* Set Follow Symlinks Flag
		* 
		* Define whether SmartLoader should follow symlinks in when searching the class directory
		*
		* @access public
		* @param boolean follow symlinks
		*/
		public function setfollowSymlinks($value) {
			$this->followSymlinks = $value;
		}
		
		/**
		* Set ignore hidden files
		* 
		* Define whether SmartLoader should ignore hidden files
		*
		* @access public
		* @param boolean value, true to ignore
		*/
		public function setIgnoreHiddenFiles($value) {
			$this->ignoreHiddenFiles = $value;
		}
        /**
         * Sets if SVN directories should be ignored
         * @param boolean true is SVN dirs should be ignored
         */
        public function setIgnoreSVN($value) {
            $this->ignoreSvnDirectories = $value; 
        }
		/**
		* Add a directory to retrieve classes/interfaces from
		* 
		* This function adds a directory to retrieve class/interface definitions from.
		*
		* @access public
		* @param string $directory_path
		*/
		public function addDir($directory_path) {
			// add trailing slash
			if(substr($directory_path, -1) != '/') {
				$directory_path .= '/';
			}
			if(!in_array($directory_path, $this->classDirectories)) {
				$this->classDirectories[] = $directory_path;
			}
		}
		
		/**
		* Load a Class
		* 
		* Loads a class by its name
		* - If the matching class definition file can't be found in the cache,
		* 	it will try once again with $retry = true.
		* - When retrying, the cached index is invalidated, regenerated and re-included.
		*
		* @access public
		* @param string $class_name
		* @param boolean $retry used for recursion
		* @return boolean Success
		*/
		public function loadClass($class_name, $retry = false) {
			/* Is the class already defined? (can be omitted in combination with __autoload) */
			if(class_exists($class_name)) {
				return true;
			}
			
			/* Is our cache outdated or not available? Recreate it! */
			if($retry || !is_readable($this->cacheFilename)) {
                var_dump($this->cacheFilename);
				$this->createCache();
			}
			
			/* Include the cache file or raise error if something's wrong with the cache */
            $this->classes = include($this->cacheFilename);
            var_dump($this->classes);
			if(!$this->classes) {
				trigger_error("SmartLoader: Cannot include cache file from '".$this->cacheFilename."'", E_USER_ERROR);
			}
			
			/* Include requested file. Return on success */
			if(isset($this->classes[$class_name]) && is_readable($this->classes[$class_name])) {
				if(include($this->classes[$class_name])) {
					return true;
				}
			} 
			
			/* On failure retry recursively, but only once. */
			if($retry) {
				return false;
			} else {
				return $this->loadClass($class_name, true);
			}
		}
        /**
         * Checks if a class exists in the class cache
         */
        public function classExists($class_name) {
            
            return isset($this->classes[$class_name]);
        }
		
		/**
		* Create Cache
		* 
		* - Scans the class dirs for class/interface definitions and 
		* 	creates an associative array (class name => class file) 
		* - Generates the array in PHP code and saves it as cache file
		*
		* @access private
		* @param param_type $param_name
		*/
		public function createCache() {
			/* Create class list */
			foreach($this->classDirectories as $dir) {
				$this->parseDir($dir);
			}
			
			/* Generate php cache file */
			$cache_content = "<?php\n\t// this is an automatically generated cache file.\n"
				."\t// it serves as 'class name' / 'class file' association index for the SmartLoader\n";
			foreach($this->classIndex as $class_name => $class_file) {
				$cache_content .= "\t\$classes['".$class_name."'] = '".$class_file."';\n";
			}
			$cache_content .= " return \$classes; ?>";
			if($cacheFilename_handle = fopen($this->cacheFilename, "w+")) {
				fwrite($cacheFilename_handle, $cache_content);
				/* Allow ftp users to access/modify/delete cache file, suppress chmod errors here */
				@chmod($this->cacheFilename, 0664);
			}
		}
		
		/**
		* Parse Directory
		* 
		* Parses a directory for class/interface definitions. Saves found definitions
		* in $classIndex. Needless to say: Mind recursion cycles when using symlinks.
		* TODO: Clean up this method; use SPL, if suitable.
		*
		* @access private
		* @param string $directory_path
		* @return boolean Success
		*/
		private function parseDir($directory_path) {
			if(is_dir($directory_path)) {
				if($dh = opendir($directory_path)) {
					while(($file = readdir($dh)) !== false) {
						$file_path = $directory_path.$file;
						if(!$this->ignoreHiddenFiles || $file{0} != '.') {
							switch(filetype($file_path))
							{
								case 'dir':
                                    if($file != "." && $file != ".." ) {
                                        if  ($this->ignoreSvnDirectories && $file == '.svn') 
                                        {
                                            break;
                                        }
										/* parse on recursively */
										$this->parseDir($file_path.'/');
									}
									break;
								case 'link':
									if($this->followSymlinks) {
										/* follow link, parse on recursively */
										$this->parseDir($file_path.'/');
									}
									break;
								case 'file':
									/* a non-empty endings array implies an ending check
									 * TODO: Write a more sophisticated suffix check. */
									if(!sizeof($this->classFileEndings) || in_array(substr($file, strrpos($file, '.')), $this->classFileEndings)) {
										if($php_file = fopen($file_path, "r")) {
                                            $size = filesize($file_path);
											if($size > 0 && $buf = fread($php_file, $size)) {
                                                $result = array();
                                                if(($res = preg_match_all( $this->classRegularExpression, $buf, $result )) != FALSE ) 
                                                    {
                                                    if (!isset ($result[2]) || !is_array($result[2])) {
                                                        echo $file_path . " didn't contain any classes?!'<br/>";
                                                        var_dump($res);

                                                        var_dump($result);
                                                    } else foreach($result[2] as $class_name) {
														$this->classIndex[$class_name] = $file_path;
													}
												}
											}
										}
									}
									break;
							}
						}
					}
					return true;
				}
			}
			return false;
		}
	}
?>
