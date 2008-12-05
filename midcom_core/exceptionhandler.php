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
class midcom_core_exceptionhandler
{
    function handle($exception)
    {
        $http_code = 500;
        
        // Different HTTP error codes for different Exceptions
        $message_type = get_class($exception);        
        switch ($message_type)
        {
            case 'midcom_exception_notfound':
                $http_code = $exception->getCode();
                break;
            case 'midcom_exception_unauthorized':
                $http_code = $exception->getCode();
                break;
        }

        $message = $exception->getMessage();

        if (headers_sent())
        {
            die("Unexpected Error, this should display an HTTP {$http_code} - {$message_type}: {$message}");
        }

        switch ($http_code)
        {
            case 200:
                $header = 'HTTP/1.0 200 OK';
                break;
            case 304:
                $header = 'HTTP/1.0 304 Not Modified';
                break;
            case 404:
                $header = 'HTTP/1.0 404 Not Found';
                break;
            case 500:
            default:
                $header = 'HTTP/1.0 500 Server Error';
                break;
        }

        header($header);
        if ($http_code != 304)
        {
            header('Content-Type: text/html');

            // TODO: Templating
            echo "<html><h1>{$header}</h1><p>{$message_type}: {$message}</p>";
        }
    }
}

/**
 * MidCOM 3 "not found" exception
 *
 * @package midcom_core
 */
class midcom_exception_notfound extends Exception
{
    // Redefine the exception so message isn't optional
    public function __construct($message, $code = 404) 
    {
        parent::__construct($message, $code);
    }
}

/**
 * MidCOM 3 "unauthorized" exception
 *
 * @package midcom_core
 */
class midcom_exception_unauthorized extends Exception
{
    // Redefine the exception so message isn't optional
    public function __construct($message, $code = 401) 
    {
        parent::__construct($message, $code);
    }
}

set_exception_handler(array('midcom_core_exceptionhandler', 'handle'));
?>