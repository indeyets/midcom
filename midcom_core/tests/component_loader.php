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
class midcom_core_tests_component_loader extends midcom_tests_testcase
{   

    public function setUp()
    {
        $this->loader = new midcom_core_component_loader();
        parent::setUp();
    }

    public function test_can_load_nonexisting_component()
    {
        $loader = new midcom_core_component_loader();
        $this->assertTrue( !$loader->can_load(md5(time())));
    }
    
    public function test_load_nonexisting_component()
    {
        $loader = new midcom_core_component_loader();
        $this->assertTrue( !$loader->load(md5(time())));
    }

    
    public function test_can_load_nonexisting_component_twice()
    {
        $loader = new midcom_core_component_loader();
        $component_name = md5(time());
        $this->assertTrue( !$loader->can_load($component_name));
        $this->assertTrue( !$loader->can_load($component_name));
    }
    
    public function test_can_load_midcom_core_failsafe()
    {
        $this->assertTrue( !$this->loader->load('midcom_core'));
    }
    
    public function test_load_manifests()
    {
        $this->loader->manifests = null;
        $this->loader->__construct();
        $this->assertTrue( is_array($this->loader->manifests));
    }
    
    public function test_load_invalid_characters_in_name()
    {
        $this->loader->manifests[] = 'invalid()name';
        $this->assertTrue( !$this->loader->load('invalid()name'));
    }
    
    
    /*public function test_load_component()
    {
        var_dump($this->loader->manifests);
        var_dump($this->loader->load('midcom'));
        die("aeg");
    }*/
}
?>