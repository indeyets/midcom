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
 * This class importes a style 
 */
class importStyle extends Task
{

	protected $style = null; // package name

	function __construct()
	{

	}

	protected $returnProperty; // name of property to set to return value

	/**
	 * The root path to where the module is stored.
	 */
	private $path = null;
	/**
	 */
	protected $template = null;

	public function setPath($str) 
	{
		$this->path = $str;
	}
	public function setPackage($str)	
	{		
		$this->package = $str;	
	}
	public function setTemplate($str) 
	{ 
		$this->template = $str; 
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
		// nothing to do here
	}

	/**
	 * The main entry point method.
	 */
	public function main()
	{
		
        $name = $this->template;
        $style_name = "template_{$name}";
        $qb = new midgardQueryBuilder('midgard_style');
        $qb->add_constraint('name', '=', $style_name);
        $qb->add_constraint('up', '=', 0);

        $styles = $qb->execute();

        if (count($styles) == 0)
        {
            // Create missing style template
            $new_style = new midgard_style();
            $new_style->up = 0;
            $new_style->name = $style_name;
            $stat = $new_style->create();
            if (!$stat)
            {
                PEAR::raiseError("Failed to create Midgard style \"{$style_name}\", check config directives in the Midgard conf.d file \"{$init_file}\". Error was " . mgd_errstr());
            }
            $style = new midgard_style();
            $style->get_by_id($new_style->id);
        }
        else
        {
            echo "installing into existing style...\n";
            $style = $styles[0];
        }

        echo "Installing template: " . $style->name ;
        
        $files = dir($this->path . "/" . $style_name);
        $elements = array();
        while (($file = $files->read()) !== false ) {
            if (substr($file,0, 1) == '.' || !is_file("$dir/$file") ) continue;
            $path = pathinfo($file);
            if ($path['extension'] == 'php') {
                
                $elements[] = str_replace('.php', '', $path['basename']);
            } else {
                echo "@todo: install static $file\n";
            }
        }
        //print_r($elements); exit;
        foreach ($elements as $element_name ) {
            $this->add_element_to_style($style, $element_name);
        }

        if (!$this->clearCache()) 
        {
        	echo "Remember that you have to clear the midgard pagecache to see effects!\n";
        }
        
		
	}
	
	protected function clearCache() {
		if (function_exists('mgd_clear_cache')) {
			return mgd_clear_cache();
		} 
		return false;
	}
	
}
?>