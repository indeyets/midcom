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
 * XMPPHP XMLStream
 *
 * @package net_nemein_xmpp
 */
class net_nemein_xmpp_api_xmpphp_xmlstream
{
	protected $stream_start = '<stream>';
	protected $stream_end = '</stream';
	protected $lastid = 0;
		
	public $disconnected = false;	
	public $socket;	
	public $parser;
	public $buffer;
	public $xml_depth = 0;
	public $host;
	public $port;
	public $sent_disconnect = false;
	public $ns_map = array();
	public $current_ns = array();
	public $xmlobj = null;
	public $nshandlers = array();
	public $idhandlers = array();
	public $eventhandlers = array();
	public $default_ns;
	public $until;
	public $until_happened = false;
	public $until_payload = array();
	public $logger;
	public $reconnect = true;
	public $been_reset = false;
	
	public function __construct($host, $port, $print_log=false, $log_level=null)
	{
		$this->reconnect = true;
		$this->host = $host;
		$this->port = $port;

		$this->setup_parser();

		$this->logger = new net_nemein_xmpp_api_xmpphp_logger($print_log, $log_level);
	}

	public function connect($persistent=false, $sendinit=true)
	{
		$this->disconnected = false;
		$this->sent_disconnect = false;

		$conflag = STREAM_CLIENT_CONNECT;
		if ($persistent)
		{
			$conflag = STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT;
		}
		
		$this->logger->log("Connecting to tcp://{$this->host}:{$this->port}");
		
		try
		{
		    $this->socket = stream_socket_client("tcp://{$this->host}:{$this->port}", $flags=$conflag);
		}
		catch(Exception $e)
		{
		    throw new net_nemein_xmpp_exception_connection_failed();
		}
		
		stream_set_blocking($this->socket, 1);
		
		if ($sendinit)
		{
		    $this->send($this->stream_start);
		}
	}
	
	public function disconnect()
	{
		$this->reconnect = false;
		$this->send($this->stream_end);
		$this->sent_disconnect = true;
		$this->process_until('end_stream', 5);
		$this->disconnected = true;
	}

	public function process_until($event, $timeout=-1)
	{
		$start = time();
		if (! is_array($event))
		{
		    $event = array($event);
		}
		
		$this->until = $event;
		$this->until_happened = false;

		$updated = '';
		while(   !$this->disconnected
		      && !$this->until_happened
		      && (   (time() - $start) < $timeout
		          || $timeout == -1))
		{
			$read = array($this->socket);
			$write = NULL;
			$except = NULL;
			$updated = stream_select($read, $write, $except, 1);
			if ($updated > 0)
			{
				$buff = @fread($this->socket, 1024);
				if (! $buff)
				{
					if ($this->reconnect)
					{
						$this->do_reconnect();
					}
					else
					{
						fclose($this->socket);
						return false;
					}
				}
				
				$this->logger->log("RECV: $buff", NNX_LOGGER_VERBOSE);
				xml_parse($this->parser, $buff, false);
			}
		}
		
		$payload = $this->until_payload;
		$this->until_payload = array();
		
		return $payload;
	}

	private function apply_socket($socket)
	{
		$this->socket = $socket;
	}
	
	private function process()
	{
		$updated = '';
		while (! $this->disconnected)
		{
			$read = array($this->socket);
			$write = null;
			$except = null;
			$updated = stream_select($read, $write, $except, 1);
			if ($updated > 0)
			{
				$buff = @fread($this->socket, 1024);
				if (! $buff)
				{ 
					if ($this->reconnect)
					{
						$this->do_reconnect();
					}
					else
					{
						fclose($this->socket);
						return false;
					}
				}
				$this->logger->log("RECV: $buff", NNX_LOGGER_VERBOSE);
				xml_parse($this->parser, $buff, false);
			}
		}
	}
	
	private function do_reconnect()
	{
		$this->connect(false, false);
		$this->reset();
	}

	private function setup_parser()
	{
		$this->parser = xml_parser_create();
		xml_set_object($this->parser, $this);
		xml_set_element_handler($this->parser, 'start_xml', 'end_xml');
		xml_set_character_data_handler($this->parser, 'char_xml');
	}

	protected function get_id()
	{
		$this->lastid++;
		return $this->lastid;
	}

	protected function add_id_handler($id, $pointer, $obj=null)
	{
		$this->idhandlers[$id] = array($pointer, $obj);
	}

	protected function add_handler($name, $ns, $pointer, $obj=null)
	{
		$this->nshandlers[] = array($name, $ns, $pointer, $obj);
	}

	protected function add_event_handler($name, $pointer, $obj)
	{
		$this->eventhanders[] = array($name, $pointer, $obj);
	}
	
	protected function process_time($timeout=-1)
	{
		$start = time();
		$updated = '';
		while(   !$this->disconnected
              && (   $timeout == -1
                  || time() - $start < $timeout))
        {
			$read = array($this->socket);
			$write = NULL;
			$except = NULL;
			$updated = stream_select($read, $write, $except, 1);
			if ($updated > 0) {
				$buff = @fread($this->socket, 1024);
				if (! $buff)
				{ 
					if ($this->reconnect)
					{
						$this->do_reconnect();
					}
					else
					{
						fclose($this->socket);
						return false;
					}
				}
				
				$this->logger->log("RECV: $buff", NNX_LOGGER_VERBOSE);
				xml_parse($this->parser, $buff, false);
			}
		}
	}
	
	protected function start_xml($parser, $name, $attr)
	{
		if ($this->been_reset)
		{
			$this->been_reset = false;
			$this->xml_depth = 0;
		}
		
		$this->xml_depth++;
		if (array_key_exists('XMLNS', $attr))
		{
			$this->current_ns[$this->xml_depth] = $attr['XMLNS'];
		}
		else
		{
			$this->current_ns[$this->xml_depth] = $this->current_ns[$this->xml_depth - 1];
			if (! $this->current_ns[$this->xml_depth])
			{
			    $this->current_ns[$this->xml_depth] = $this->default_ns;
			}
		}
		
		$ns = $this->current_ns[$this->xml_depth];
		
		foreach ($attr as $key => $value)
		{
			if (strstr($key, ":"))
			{
				$key = explode(':', $key);
				$key = $key[1];
				$this->ns_map[$key] = $value;
			}
		}
		
		if (! strstr($name, ":") === false)
		{
			$name = explode(':', $name);
			$ns = $this->ns_map[$name[0]];
			$name = $name[1];
		}
		
		$obj = new net_nemein_xmpp_api_xmpphp_xmlobject($name, $ns, $attr);
		
		if ($this->xml_depth > 1)
		{
		    $this->xmlobj[$this->xml_depth - 1]->subs[] = $obj;
		}
		
		$this->xmlobj[$this->xml_depth] = $obj;
	}
	
	protected function end_xml($parser, $name)
	{
		if ($this->been_reset)
		{
			$this->been_reset = false;
			$this->xml_depth = 0;
		}
		
		$this->xml_depth--;
		if ($this->xml_depth == 1)
		{
			$found = false;
			foreach ($this->nshandlers as $handler)
			{
				if (   $this->xmlobj[2]->name == $handler[0]
				    && (   $this->xmlobj[2]->ns == $handler[1]
				        || (   !$handler[1]
				            && $this->xmlobj[2]->ns == $this->default_ns)))
				{
					if (is_null($handler[3]))
					{
					    $handler[3] = $this;
					}
					
					call_user_method($handler[2], $handler[3], $this->xmlobj[2]);
				}
			}
			
			foreach ($this->idhandlers as $id => $handler)
			{
				if ($this->xmlobj[2]->attrs['id'] == $id)
				{
					if (is_null($handler[1]))
					{
					    $handler[1] = $this;
					}
					call_user_method($handler[0], $handler[1], $this->xmlobj[2]);
					
					unset($this->idhandlers[$id]);
					break;
				}
			}
			
			if (is_array($this->xmlobj))
			{
				$this->xmlobj = array_slice($this->xmlobj, 0, 1);
				$this->xmlobj[0]->subs = Null;
			}
		}
		
		if (   $this->xml_depth == 0
		    && !$this->been_reset)
		{
			if (! $this->disconnected)
			{
				if (! $this->sent_disconnect)
				{
					$this->send($this->stream_end);
				}
				
				$this->disconnected = true;
				$this->sent_disconnect = true;
				fclose($this->socket);
				
				if ($this->reconnect)
				{
					$this->do_reconnect();
				}
			}
			$this->event('end_stream');
		}
	}
	
	protected function event($name, $payload=null)
	{
		$this->logger->log("EVENT: {$name}", NNX_LOGGER_DEBUG);
		
		foreach ($this->eventhandlers as $handler)
		{
			if ($name == $handler[0])
			{
				if (is_null($handler[2]))
				{
				    $handler[2] = $this;
				}
				call_user_method($handler[1], $handler[2], $payload);
			}
		}
		
		if (in_array($name, $this->until))
		{
			$this->until_happened = true;
			$this->until_payload[] = array($name, $payload);
		}
	}

	protected function char_xml($parser, $data)
	{
		$this->xmlobj[$this->xml_depth]->data .= $data;
	}

	protected function send($msg)
	{
		$this->logger->log("SENT: $msg", NNX_LOGGER_VERBOSE);
		
		fwrite($this->socket, $msg);
	}

	protected function reset()
	{
		$this->xml_depth = 0;
		$this->xmlobj = null;
		$this->setup_parser();
		$this->send($this->stream_start);
		$this->been_reset = true;
	}

}

?>