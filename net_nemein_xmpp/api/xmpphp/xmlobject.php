<?php
/**
 * @package net_nemein_xmpp
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * 
 * This is rewrite of XMPPHP: The PHP XMPP Library
 * from Nathanael C. Fritz
 * Rewritten for better PHP5 support and MidCOM compability
 * by Jerry Jalava <jerry.jalava@gmail.com>
 */

/**
 * XMPPHP XMLObject
 *
 * @package net_nemein_xmpp
 */
class net_nemein_xmpp_api_xmpphp_xmlobject
{
	public $name;
	public $ns;
	public $attrs = array();
	public $subs = array();
	public $data = '';
	
	public function __construct($name, $ns='', $attrs=array(), $data='')
	{
		$this->name = strtolower($name);
		$this->ns = $ns;
		
		if (is_array($attrs))
		{
			foreach ($attrs as $key => $value)
			{
				$this->attrs[strtolower($key)] = $value;
			}
		}
		
		$this->data = $data;
	}
	
	protected function printout($depth=0)
	{
		echo str_repeat("\t", $depth) . "{$this->name} {$this->ns} {$this->data}\n";
		
		foreach ($this->subs as $sub)
		{
			$sub->printout($depth + 1);
		}
	}

	public function hassub($name)
	{
		foreach ($this->subs as $sub)
		{
			if ($sub->name == $name)
			{
			    return true;
		    }
		}
		
		return false;
	}

	public function sub($name, $attrs=null, $ns=null)
	{
		foreach ($this->subs as $sub)
		{
			if ($sub->name == $name)
			{
			    return $sub;
		    }
		}
	}
}

?>