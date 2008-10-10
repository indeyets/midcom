<?php
/**
 * midcom_exception_handler
 * 
 * Class for intercepting PHP errors and unhandled exceptions. Each fault is caught
 * and converten into Exception handled by $_MIDCOM->generate_error() with 
 * code 500 thus can be customized and make user friendly.
 *
 */

class midcom_exception_handler 
{  
    public static function print_exception(Exception $e)
    {
        $_MIDCOM->generate_error(MIDCOM_ERRCRIT,$e->getMessage());
    }
  
    public static function handle_exception(Exception $e)
    {
         self::print_exception($e);
    }

    public static function handle_error($errno, $errstr, $errfile, $errline, $errcontext)
    {
        $msg = "PHP Error: {$errstr} \n in {$errfile} line {$errline}";
        if (MIDCOM_XDEBUG)
        {
            $msg .= "\n";
            $msg .= var_dump($errcontext);
        }
        throw new Exception($msg,$errno);
        return true;
    }
}

set_error_handler(array("midcom_exception_handler", "handle_error"), E_ALL & ~E_NOTICE | E_WARNING);
set_exception_handler(array("midcom_exception_handler", "handle_exception"));
?>