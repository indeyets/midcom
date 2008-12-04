<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM 3 exception handler
 *
 * @package midcom_core
 */
class midcom_core_exception_handler
{
    function handle($exception)
    {
        // TODO: Different HTTP error codes for different Exceptions
        $http_code = 500;
        $message = $exception->getMessage();
    
        if (headers_sent())
        {
            die("Unexpected Error, this should display an HTTP {$http_code} - {$message}");
        }
        
        switch ($http_code)
        {
            case 200:
                $header = 'HTTP/1.0 200 OK';
                break;
            case 500:
            default:
                $header = "HTTP/1.0 500 Server Error";
                break;
        }
        
        header($header);
        header('Content-Type: text/html');

        // TODO: Templating
        echo "<html><h1>{$header}</h1><p>{$message}</p>";
    }
}

set_exception_handler(array('midcom_core_exception_handler', 'handle'));
?>