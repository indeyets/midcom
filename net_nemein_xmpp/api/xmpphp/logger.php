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

define('NNX_LOGGER_ERROR', 0);
define('NNX_LOGGER_WARNING', 1);
define('NNX_LOGGER_INFO', 2);
define('NNX_LOGGER_DEBUG', 3);
define('NNX_LOGGER_VERBOSE', 4);

/**
 * XMPPHP Logger
 *
 * @package net_nemein_xmpp
 */
class net_nemein_xmpp_api_xmpphp_logger
{
	protected $data = array();
	protected $names = array();
	protected $runlevel;
	protected $printout;
	
	public function __construct($printout=false, $runlevel=NNX_LOGGER_INFO)
	{
		$this->names = array('ERROR  ', 'WARNING', 'INFO   ', 'DEBUG  ', 'VERBOSE');
		$this->runlevel = $runlevel;
		$this->printout = $printout;
	}
	
	public function log($msg, $runlevel=null)
	{
		if (is_null($runlevel))
		{
		    $runlevel = NNX_LOGGER_INFO;
		}
		
		$data[] = array($this->runlevel, $msg);
		
		if (   $this->printout
		    && $runlevel <= $this->runlevel)
		{
		    echo "{$this->names[$runlevel]}: $msg\n";
	    }
	}

	public function printout($clear=true, $runlevel=null)
	{
		if (is_null($runlevel))
		{
		    $runlevel = $this->runlevel;
		}
		
		foreach ($this->data as $data)
		{
			if ($runlevel <= $data[0])
			{
			    echo "{$this->names[$runlevel]}: $data[1]\n";
		    }
		}
		
		if ($clear)
		{
		    $this->data = array();
		}
	}
}

?>