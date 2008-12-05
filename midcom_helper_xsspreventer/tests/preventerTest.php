<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

require_once('tests/testcase.php');

/**
 * Test that
 */
class midcom_helper_xsspreventer_tests_preventer extends midcom_tests_testcase
{
    /**
      * Testing XML attribute escaping. Case tested is
      * "something "escape" more text" should be escaped and returned
      * "something &quot;escape&quot; more text
      */
            
    public function test_escape_attribute()
    {
        $testinput = 'some content that is "escaped" right';
        $testoutput = midcom_helper_xsspreventer_helper::escape_attribute($testinput);
        $testoutput = substr($testoutput, 1,-1);
        $quote_present = strstr('"', $testoutput);
        $this->assertTrue( !$quote_present);
    }
    
    public function test_escape_element()
    {
        $teststring = "function(){} </script> testing testing";
        $output = midcom_helper_xsspreventer_helper::escape_element('script', $teststring);
        $this->assertTrue(! strstr("</script>", $output));

    }
    
    public function test_escape_element_spacefirst()
    {
        $teststring = "function(){} < /script> testing testing";
        $output = midcom_helper_xsspreventer_helper::escape_element('script', $teststring);
        $this->assertTrue(! strstr("< /script>", $output));
    }

    public function test_escape_element_spacefirst_after_slash()
    {
        $teststring = "function(){} </ script> testing testing";
        $output = midcom_helper_xsspreventer_helper::escape_element('script', $teststring);
        $this->assertTrue(! strstr("</ script>", $output));
    }
    
    public function test_escape_element_space_before_and_after_slash()
    {
        $teststring = "function(){} < / script> testing testing";
        $output = midcom_helper_xsspreventer_helper::escape_element('script', $teststring);
        $this->assertTrue(! strstr("< / script>", $output));
    }
    
    public function test_escape_element_space_after_tag()
    {
        $teststring = "function(){} </script > testing testing";
        $output = midcom_helper_xsspreventer_helper::escape_element('script', $teststring);
        $this->assertTrue(! strstr("</script >", $output));
    }

    public function test_escape_element_space_beginning_and_after_tag()
    {
        $teststring = "function(){} < /script > testing testing";
        $output = midcom_helper_xsspreventer_helper::escape_element('script', $teststring);
        $this->assertTrue(! strstr("< /script >", $output));
    }
    
    public function test_escape_element_space_beginning_and_after_slash_and_after_tag()
    {
        $teststring = "function(){} < / script > testing testing";
        $output = midcom_helper_xsspreventer_helper::escape_element('script', $teststring);
        $this->assertTrue(! strstr("< / script >", $output));
    }

}

?>