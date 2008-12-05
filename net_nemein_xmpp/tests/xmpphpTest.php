<?php
/**
 * @package net_nemein_xmpp
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

require_once('tests/testcase.php');

/**
 * Test that tests XMPPHP features
 */
class net_nemein_xmpp_tests_xmpphp extends midcom_tests_testcase
{
    
    public function testSend()
    {
        if (MIDCOM_TESTS_ENABLE_OUTPUT)
        {
            echo __FUNCTION__ . "\n";
            echo "Message sending test\n\n";
        }
        
        $this->create_context('net_nemein_xmpp');
        
        if (! $_MIDCOM->context->component_instance->configuration->exists('host'))
        {            
            $_MIDCOM->context->delete();
            $this->fail("No host defined in configuration");
        }
        
        if (   empty($_MIDCOM->context->component_instance->configuration->defaults['username'])
            && empty($_MIDCOM->context->component_instance->configuration->defaults['password']))
        {
            $_MIDCOM->context->delete();
            $this->fail("default username and password empty");
        }
        
        if (empty($_MIDCOM->context->component_instance->configuration->defaults['to']))
        {
            $_MIDCOM->context->delete();
            $this->fail("no default receiver defined");
        }
        
        if (MIDCOM_TESTS_ENABLE_OUTPUT)
        {
            echo "Send message 'Hello World!' to {$_MIDCOM->context->component_instance->configuration->defaults['to']}\n";
            echo "using host {$_MIDCOM->context->component_instance->configuration->host}:{$_MIDCOM->context->component_instance->configuration->port}\n";
            echo "with user {$_MIDCOM->context->component_instance->configuration->defaults['username']}\n\n";
        }
        
        try
        {
            $conn = new net_nemein_xmpp_xmpphp(
                $_MIDCOM->context->component_instance->configuration->host,
                $_MIDCOM->context->component_instance->configuration->port,
                $_MIDCOM->context->component_instance->configuration->defaults['username'],
                $_MIDCOM->context->component_instance->configuration->defaults['password'],
                $_MIDCOM->context->component_instance->configuration->resource,
                $_MIDCOM->context->component_instance->configuration->server,
                true,
                LOGGING_VERBOSE
            );
        }
        catch(Exception $e)
        {
            $_MIDCOM->context->delete();
            $this->fail("Couldn't create new net_nemein_xmpp_xmpphp instance");
        }
        
        $conn->connect();
        $conn->processUntil('session_start');
        $conn->message(
            $_MIDCOM->context->component_instance->configuration->defaults['to'],
            'Hello World!'
        );
        $conn->disconnect();
        
        // Delete the context
        $_MIDCOM->context->delete();
 
        $this->assertTrue(true);
    }
}

?>