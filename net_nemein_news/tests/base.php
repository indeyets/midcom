<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

require_once('tests/testcase.php');

/**
 * Baseclass for net_nemein_news tests
 */
class net_nemein_news_tests_base extends midcom_tests_testcase
{
    public static $populated = null;
    
    public function setUp()
    {
        parent::setUp();
        
        $this->populate_data();        
    }
    
    public function populate_data()
    {        
        if (! is_null(net_nemein_news_tests_base::$populated))
        {
            return true;
        }
        
        $data = array();
        
        //Create news component topic        
        $data['topic'] = new midgard_topic();
        $data['topic']->name = $data['topic']->extra = 'net_nemein_news_test';
        $data['topic']->title = 'mnet nemein news test';

        try {
            $data['topic']->create();
        } catch (exception $e) {
            $this->markTestSkipped('Error creating news topic! Reason: '.$e->getMessage());
        }
        
        $data['articles'] = array();
        
        $data['articles'][0] = new midgard_article();
        $data['articles'][0]->topic = $data['topic']->id;
        $data['articles'][0]->name = 'net_nemein_news_test_article_1';
        $data['articles'][0]->title = 'net nemein news test article 1';
        $data['articles'][0]->content = 'net_nemein_news_test_article_1 <br /> <strong>content</strong>';
        
        try {
            $data['articles'][0]->create();
        } catch (execption $e) {
            $this->markTestSkipped('Error creating news topic! Reason: '.$e->getMessage());            
        }
        
        net_nemein_news_tests_base::$populated = $data;
    }
}

?>