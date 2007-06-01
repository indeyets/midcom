<?php
/**
 * @author Eero af Heurlin
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * 
 */
error_reporting(E_ALL & ~E_NOTICE);
require_once('XML/RPC.php');
error_reporting(E_ALL);
echo "\n";
$server = false;
$path = false;
$port = false;
$ssl = false;

if (strpos($argv[1], 'https://') !== false)
{
    $ssl = true;
}
list ($server, $path) = explode('/', preg_replace('%^.*?://%', '', $argv[1]), 2);
$path = '/' . $path;
if (strpos($server, ':') !== false)
{
    list ($server, $port) = explode(':', $server, 2);
}

echo "Client params:\n";
echo "  Server: {$server}\n";
echo "  Path: {$path}\n";
echo "  Port: {$port}\n";
echo "  SSL: {$ssl}\n\n";


$client = new XML_RPC_Client($path, $server);
$client->setDebug(1);

// Get method list
$msg = new XML_RPC_Message('system.listMethods');

$response = $client->send($msg);
if (!$response)
{
    echo "Communication error: {$client->errstr}\n";
    exit(1);
}

if ($response->faultCode())
{
    echo "Response fault (when calling system.listMethods):\n";
    echo '  Code: ' . $response->faultCode() . "\n";
    echo '  Reason: ' . $response->faultString() . "\n";
    exit(1);
}

$response_data = XML_RPC_decode($response->value());
print_r($response_data);


?>