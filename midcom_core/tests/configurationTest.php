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
class midcom_core_tests_configuration extends midcom_tests_testcase
{
    public function setUp()
    {        
        $path = realpath(dirname(__FILE__)).'/../configuration/defaults.yml';
        $this->testConfiguration = syck_load(file_get_contents($path));
        parent::setUp();
    }
    
    public function test_get()
    {
        foreach($this->testConfiguration as $key => $conf)
        {
            $this->assertEquals($_MIDCOM->configuration->get($key), $this->testConfiguration[$key]);
        }
    }
    public function test_magic_getter()
    {
        foreach($this->testConfiguration as $key => $conf)
        {
            $this->assertEquals($_MIDCOM->configuration->$key, $this->testConfiguration[$key]);
        }
    }
    public function test_exists()
    {
        foreach($this->testConfiguration as $key => $conf)
        {
            $this->assertTrue($_MIDCOM->configuration->exists($key));
        }
    }
    public function test_isset()
    {
        foreach($this->testConfiguration as $key => $conf)
        {
            $this->assertTrue( isset($_MIDCOM->configuration->$key));
        }
    }
    public function test_unserialize()
    {
        $path = realpath(dirname(__FILE__)).'/../configuration/defaults.yml';
        $data = syck_load(file_get_contents($path));
        $data2 = $_MIDCOM->configuration->unserialize(file_get_contents($path));
        if ($data === $data2)
        {
            $this->assertTrue(true);
        }
        else
        {
            $this->assertTrue(false);
        }
    }
    public function test_serialization()
    {
        $path = realpath(dirname(__FILE__)).'/../configuration/defaults.yml';
        $data = syck_load(file_get_contents($path));
        $serialized = syck_dump($data);
        $serialized2 = $_MIDCOM->configuration->serialize($data);

        if ($serialized === $serialized2)
        {
            $this->assertTrue(true);
        }
        else
        {
            $this->assertTrue(false);
        }
    }
}
?>