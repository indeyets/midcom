<?php
/**
 * @package net.nemein.orders
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Orders MidCOM interface class.
 * 
 * Be aware of the overriden configure component UI call, which add support
 * for the symlink config topic feature.
 * 
 * @package net.nemein.orders
 */
class net_nemein_orders_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     * 
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function net_nemein_orders_interface()
    {
        parent::midcom_baseclasses_components_interface();
        
        $this->_component = 'net.nemein.orders';
        $this->_autoload_files = Array('viewer.php', 'admin.php', 'navigation.php',
            '_base.php', 'auth.php', 'cart.php', 'order.php', 'order_factory.php', 'product.php');
        $this->_autoload_libraries = Array('midcom.helper.datamanager');
    }
    
    /**
     * Override the configure component API function to support the symlink config topic
     * feature ("product categories"). This will load the configuration from the symlinked
     * topic instead of the current one. The symlink_config_topic configuration key will
     * contain the loaded topic object or null, if no symlick config topic is set. 
     */
    function configure ($configuration, $contextid, $adminmode = false)
    {
        if (   array_key_exists('symlink_config_topic', $configuration)
            && ! is_null($configuration['symlink_config_topic']))
        {
            // Reconfigure and overwrite $configuration.
            $topic = mgd_get_object_by_guid($configuration['symlink_config_topic']);
            if (! $topic || $topic->__table__ != 'topic')
            {
                debug_add("Failed to load the symlik config topic {$configuration['symlink_config_topic']}: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                debug_print_r('Loaded object was:', $topic);
                return false; 
            }
            
            $config = new midcom_helper_configuration($topic, $this->_component);
            $configuration = $config->get_all();
            $configuration['symlink_config_topic'] = $topic;
            
            debug_print_r("Loaded this configuration data from the symlinked topic {$configuration['symlink_config_topic']}:", $configuration);
        }
        
        // Let the rest be done by the base class.
        return parent::configure($configuration, $contextid, $adminmode);
    }
    
    /**
     * Iterate over all articles and create index record using the datamanger indexer
     * method.
     */
    function _on_reindex($topic, $config, &$indexer)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $articles = mgd_list_topic_articles($topic->id);
        if ($articles)
        {
            while ($articles->fetch())
            {
                $article = mgd_get_article($articles->id);
                $datamanager = new midcom_helper_datamanager($config->get('schemadb'));
                if (! $datamanager)
                {
                    debug_add('Warning, failed to create a datamanager instance with this schemapath:' . $this->_config->get('schemadb'),
                        MIDCOM_LOG_WARN);
                    continue;
                }
             
                if (! $datamanager->init($article))
                {
                    debug_add("Warning, failed to initialize datamanager for Article {$article->id}. See Debug Log for details.", MIDCOM_LOG_WARN);
                    debug_print_r('Article dump:', $article);
                    continue;
                }
             
                $indexer->index($datamanager);
                $datamanager->destroy();
            }
        }
        debug_pop();
        return true;
    }
}

?>