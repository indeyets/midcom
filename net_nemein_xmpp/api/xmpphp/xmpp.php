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
 * XMPPHP
 *
 * @package net_nemein_xmpp
 */
class net_nemein_xmpp_api_xmpphp_xmpp extends net_nemein_xmpp_api_xmpphp_xmlstream
{
    protected $server;
	protected $user;
	protected $password;
	protected $resource;
	protected $fulljid;
	
	public function __construct($host, $port, $user, $password, $resource, $server=null, $print_log=false, $log_level=null)
	{
	    parent::__construct($host, $port, $print_log, $log_level);
	    
		$this->user = $user;
		$this->password = $password;
		$this->resource = $resource;
		
		if (! $server)		
		{
		    $server = $host;
		}
		
		$this->stream_start = '<stream:stream to="' . $server . '" xmlns:stream="http://etherx.jabber.org/streams" xmlns="jabber:client" version="1.0">';
		$this->stream_end = '</stream:stream>';

		$this->default_ns = 'jabber:client';

		$this->add_handler('features', 'http://etherx.jabber.org/streams', 'features_handler');
		$this->add_handler('success', 'urn:ietf:params:xml:ns:xmpp-sasl', 'sasl_success_handler');
		$this->add_handler('failure', 'urn:ietf:params:xml:ns:xmpp-sasl', 'sasl_failure_handler');
		$this->add_handler('proceed', 'urn:ietf:params:xml:ns:xmpp-tls', 'tls_proceed_handler');
		$this->add_handler('message', 'jabber:client', 'message_handler');
		$this->add_handler('presence', 'jabber:client', 'presence_handler');
	}
	
	public function message($to, $body, $type='chat', $subject=null)
	{
		$to = htmlentities($to);
		$body = htmlentities($body);
		$subject = htmlentities($subject);
		
		$out = "<message from='{$this->fulljid}' to='$to' type='$type'>";
		
		if ($subject)
		{
		    $out .= "<subject>$subject</subject>";
		}
		
		$out .= "<body>$body</body></message>";
		
		$this->send($out);
	}
	
	public function presence($status=null, $show='available', $to=null)
	{
		$to = htmlentities($to);
		$status = htmlentities($status);
		
		if ($show == 'unavailable')
		{
		    $type = 'unavailable';
		}
		
		$out = "<presence";
		
		if ($to)
		{
		    $out .= " to='$to'";
		}
		
		if ($type)
		{
		    $out .= " type='$type'";
		}
		
		if (   $show == 'available'
		    && !$status)
		{
			$out .= "/>";
		}
		else
		{
			$out .= ">";

			if ($show != 'available')
			{
			    $out .= "<show>$show</show>";
			}
			
			if ($status)
			{
			    $out .= "<status>$status</status>";
			}
			
			$out .= "</presence>";
		}
		
		$this->send($out);
	}
	
	private function message_handler($xml)
	{
		$payload['type'] = $xml->attrs['type'];
		
		if (! $paytload['type'])
		{
		    $payload['type'] = 'chat';
		}
		
		$payload['from'] = $xml->attrs['from'];
		$payload['body'] = $xml->sub('body')->data;
		
		$this->logger->log('Message: ' . $xml->sub('body')->data, NNX_LOGGER_DEBUG);
		$this->event('message', $payload);
	}
	
	protected function presence_handler($xml)
	{
		$payload['type'] = $xml->attrs['type'];
		
		if (! $payload['type'])
		{
		    $payload['type'] = 'available';
		}
		
		$payload['show'] = $xml->sub('show')->data;
		
		if (! $payload['show'])
		{
		    $payload['show'] = $payload['type'];
		}
		
		$payload['from'] = $xml->attrs['from'];
		$payload['status'] = $xml->sub('status')->data;
		
		$this->logger->log("Presence: {$payload['from']} [{$payload['show']}] {$payload['status']}", NNX_LOGGER_DEBUG);

		$this->event('presence', $payload);
	}
	
	protected function features_handler($xml)
	{
		if ($xml->hassub('starttls'))
		{
			$this->send("<starttls xmlns='urn:ietf:params:xml:ns:xmpp-tls'><required /></starttls>");
		}
		elseif ($xml->hassub('bind'))
		{
			$id = $this->get_id();
			$this->add_id_handler($id, 'resource_bind_handler');
			
			$this->send("<iq xmlns=\"jabber:client\" type=\"set\" id=\"$id\"><bind xmlns=\"urn:ietf:params:xml:ns:xmpp-bind\"><resource>{$this->resource}</resource></bind></iq>");
		}
		else
		{
			$this->logger->log("Attempting Auth...");
			
			$this->send("<auth xmlns='urn:ietf:params:xml:ns:xmpp-sasl' mechanism='PLAIN'>" . base64_encode("\x00" . $this->user . "\x00" . $this->password) . "</auth>");
		}
	}

	protected function sasl_success_handler($xml)
	{
		$this->logger->log("Auth success!");
		$this->reset();
	}
	
	protected function resource_bind_handler($xml)
	{
		if ($xml->attrs['type'] == 'result')
		{
			$this->logger->log("Bound to " . $xml->sub('bind')->sub('jid')->data);
			$this->fulljid = $xml->sub('bind')->sub('jid')->data;
		}
		
		$id = $this->get_id();
		$this->add_id_handler($id, 'session_start_handler');
		
		$this->send("<iq xmlns='jabber:client' type='set' id='$id'><session xmlns='urn:ietf:params:xml:ns:xmpp-session' /></iq>");
	}

	protected function session_start_handler($xml)
	{
		$this->logger->log("Session started");

		$this->event('session_start');
	}

	protected function tls_proceed_handler($xml)
	{
		$this->logger->log("Starting TLS encryption");
		
		stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);

		$this->reset();
	}
}

/**
 * MidCOM 3 XMPP "connection failed" exception
 *
 * @package net_nemein_xmpp
 */
class net_nemein_xmpp_exception_connection_failed extends Exception
{
}

?>