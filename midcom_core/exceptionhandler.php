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
    public static function handle(Exception $exception)
    {
        // Different HTTP error codes for different Exceptions
        $message_type = get_class($exception);
        switch ($message_type)
        {
            case 'midcom_exception_notfound':
            case 'midcom_exception_unauthorized':
            case 'midcom_exception_httperror':
                $http_code = $exception->getCode();
                break;
            default:
                $http_code = 500;
                break;
        }

        $message = $exception->getMessage();

        if (headers_sent())
        {
            die("<h1>Unexpected Error</h1>\n\n<p>Headers were sent so we don't have correct HTTP code ({$http_code}).</p>\n\n<p>{$message_type}: {$message}</p>\n");
        }

        header("X-MidCOM-Error: {$message}");

        $header = self::header_by_code($http_code);

        header($header);
        if ($http_code != 304)
        {
            header('Content-Type: text/html; charset=utf-8');
            
            $data['header'] = $header;
            $data['message_type'] = $message_type;
            $data['message'] = $message;
            $data['exception'] = $exception;
            
            try
            {
                if (!isset($_MIDCOM))
                {
                    throw new Exception("MidCOM not loaded");
                }
                $_MIDCOM->context->set_item('midcom_core_exceptionhandler', $data);
                $_MIDCOM->context->set_item('template_entry_point', 'midcom-show-error');
                $_MIDCOM->context->set_item('cache_enabled', false);
                
                $_MIDCOM->templating->template();
                $_MIDCOM->templating->display();
            }
            catch (Exception $e)
            {
                // Templating isn't working
                echo "<!DOCTYPE html>\n";
                echo "<html>\n";
                echo "    <head>\n";
                echo "        <title>{$header}</title>\n";
                echo "    </head>\n";
                echo "    <body class=\"{$message_type}\">\n";
                echo "        <h1>{$header}</h1>\n";
                echo "        <p>{$message}</p>\n";
                echo "    </body>\n";
                echo "</html>";
            }
        }
    }

    private static function header_by_code($code)
    {
        $headers = array
        (
            200 => 'HTTP/1.0 200 OK',
            303 => 'HTTP/1.0 303 See Other',
            304 => 'HTTP/1.0 304 Not Modified',
            401 => 'HTTP/1.0 401 Unauthorized',
            404 => 'HTTP/1.0 404 Not Found',
            405 => 'HTTP/1.0 405 Method not allowed',
            500 => 'HTTP/1.0 500 Server Error',
            503 => 'HTTP/1.0 503 Service Unavailable',
        );

        if (!isset($headers[$code]))
        {
            $code = 500;
        }

        return $headers[$code];
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

/**
 * MidCOM 3 generic HTTP error exception
 *
 * @package midcom_core
 */
class midcom_exception_httperror extends Exception
{
    // Redefine the exception so message isn't optional
    public function __construct($message, $code = 500) 
    {
        parent::__construct($message, $code);
    }
}

set_exception_handler(array('midcom_core_exceptionhandler', 'handle'));
?>