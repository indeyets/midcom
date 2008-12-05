<?php
/**
 * @package midcom_tests
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

require_once('tests/seleniumcase.php');

/**
 * @package midcom_tests
 */
class midcom_tests_selenium_localhost extends midcom_tests_seleniumcase
{
    public function setUp()
    {
        try
        {
            //$this->selenium = new Testing_Selenium("*firefox", "http://www.google.com");
            $this->selenium = new Testing_Selenium("*safari", "http://midcom3");
            $this->selenium->start();
        }
        catch(Testing_Selenium_Exception $e)
        {
            $this->selenium->stop();
            $this->fail("Unexcepted exception thrown!\n{$e}\n");
        }
    }

    public function testFrontpage()
    {
        $this->selenium->open("/");
        $this->selenium->waitForPageToLoad(10000);
        
        $this->assertRegExp("/Midcom3/", $this->selenium->getTitle());
    }

}
?>
