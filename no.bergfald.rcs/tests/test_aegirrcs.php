<?php
/**
 * Created on Jan 11, 2006
 * @author tarjei huse
 * @package no.bergfald.rcs
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 *
 */



/** @ignore */
//require_once '../../.././tests/config/cli_config.php';
$curr_path = dirname(__FILE__);
require_once $curr_path . '/../rcs.php';
require_once $curr_path . '/../backends/aegirrcs.php';
$_MIDCOM->load_library('midcom.helper.xml');
$GLOBALS['testclasses'] = array ('no_bergfald_rcs_aegirrcs_test' => 0);

/**
 * @package no.bergfald.rcs
 */
class no_bergfald_rcs_aegirrcs_test extends UnitTestCase {


    /**
     * the rcs backend
     */
    var $rcs = null;
    var $object = null;
    function setUp() {
        $this->object = new midcom_db_topic(18);
        $this->rcs = new no_bergfald_rcs_aegirrcs($this->object->guid);
    }

    /**
     * This tests checks if the factory makes the correct classes
     *
     */
    function test_creation()
    {
        $this->assertTrue(is_object($this->rcs));
    }
    /**
     * The second test should be in a configuration interface some how. Hmm...
     */
    function test_aegirrcs_probe_rcs()
    {
        $this->assertTrue($this->rcs->rcsroot != '', "The rcsroot should be set");
        $this->assertTrue(is_writable($this->rcs->rcsroot));
    }

    function test_get_revision()
    {
        $revisions = $this->rcs->list_history();
            var_dump($revisions);
        $this->assertTrue(count($revisions) > 0, "The testtopic ({$this->object->name} must have at least one revison to be intersting");
        if (count($revisions) > 0 ) {
            $revision  = $this->rcs->get_revision(key($revisions));

            $this->assertTrue(is_array($revision), "A revison should return an array");
            $i =0;
            foreach (get_object_vars($this->object) as $key => $val) {
                if (array_key_exists($key, $revision))
                {
                    $i++;
                }
            }
            $this->assertTrue($i > 0 , "A revision should contain at least one attribute...");
        }

    }
    function test_if_rcs_is_set_up() {
        $this->assertTrue($GLOBALS['midcom_config_site']['utility_rcs']  != '', "You must have configured rcs!!");
    }

}

?>