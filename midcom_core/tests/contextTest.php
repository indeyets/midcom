<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

require_once('tests/testcase.php');

/**
 * Test to see if contexts are working
 */
class midcom_core_tests_context extends midcom_tests_testcase
{
    public function setUp()
    {        
        if (MIDCOM_TESTS_ENABLE_OUTPUT)
        {
            echo __CLASS__ . "\n";
        }
        parent::setUp();
    }
    
    public function testCreate()
    {
        if (MIDCOM_TESTS_ENABLE_OUTPUT)
        {
            echo __FUNCTION__ . "\n";
        }
        
        $_MIDCOM->context->create();        
        $context =& $_MIDCOM->context->get();
        $this->assertEquals($_MIDCOM->context->get_current_context(), 1);
        $this->assertTrue(!empty($context));
        
        $_MIDCOM->context->delete();
    }

    public function testGet()
    {
        if (MIDCOM_TESTS_ENABLE_OUTPUT)
        {
            echo __FUNCTION__ . "\n";
        }
        
        $_MIDCOM->context->create();
        $current = $_MIDCOM->context->get_current_context();
        
        $context = array();
        try
        {
            $context =& $_MIDCOM->context->get($current);
        }
        catch (OutOfBoundsException $e)
        {
            $this->fail('An unexpected OutOfBoundsException has been raised.');
        }
        
        $this->assertTrue(!empty($context));
        
        $_MIDCOM->context->delete();
    }
    
    public function testDelete()
    {
        if (MIDCOM_TESTS_ENABLE_OUTPUT)
        {
            echo __FUNCTION__ . "\n";
        }
        
        $_MIDCOM->context->create();
        $current = $_MIDCOM->context->get_current_context();
        $_MIDCOM->context->delete();
        
        try
        {
            $context =& $_MIDCOM->context->get($current);
        }
        catch (OutOfBoundsException $e)
        {
            return;
        }
        
        $this->fail('An expected OutOfBoundsException has not been raised.');
    }
    
    public function testGetSet()
    {
        if (MIDCOM_TESTS_ENABLE_OUTPUT)
        {
            echo __FUNCTION__ . "\n";
        }
        
        $_MIDCOM->context->create();
        
        $_MIDCOM->context->setted = true;
        
        $this->assertEquals($_MIDCOM->context->setted, true);
        
        $_MIDCOM->context->delete();
    }

}
?>