<?php
/**
 * @package net.nehmer.static
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * n.n.static NAP interface class
 *
 * This class has been rewritten for MidCOM 2.6 utilizing all of the currently
 * available state-of-the-art technology.
 *
 * See the individual member documentations about special NAP options in use.
 *
 * @package net.nehmer.static
 */

class net_nehmer_static_navigation extends midcom_baseclasses_components_navigation
{
    /**
     * The topic in which to look for articles. This defaults to the current content topic
     * unless overridden by the symlink topic feature.
     *
     * @var midcom_db_topic
     * @access private
     */
    private $_content_topic = null;

    /**
     * Simple constructor, calls base class.
     */
    public function __construct()
    {
        parent::midcom_baseclasses_components_navigation();
    }

    /**
     * Returns all leaves for the current content topic.
     *
     * It will hide the index leaf from the NAP information unless we are in Autoindex
     * mode. The leaves' title are used as a description within NAP, and the toolbar will
     * contain edit and delete links.
     */
    public function get_leaves()
    {
        // Get the required information with midgard_collector
        $mc = midcom_db_article::new_collector('up', 0);
//        $mc->set_key_property('topic');
        $mc->add_value_property('id');
        $mc->add_value_property('guid');
        $mc->add_value_property('name');
        $mc->add_value_property('title');
        
        // Check whether to include the linked articles to navigation list
        if (!$this->_config->get('enable_article_links'))
        {
            $mc->add_constraint('topic', '=', $this->_content_topic->id);
        }
        else
        {
            // Get the linked articles as well
            $mc_link = net_nehmer_static_link_dba::new_collector('topic', $this->_content_topic->id);
            $mc_link->add_value_property('article');
            $mc_link->add_constraint('topic', '=', $this->_content_topic->id);
            $mc_link->execute();
            
            $links = $mc_link->list_keys();
            
            $mc->begin_group('OR');
                $mc->add_constraint('topic', '=', $this->_content_topic->id);
                foreach ($links as $guid => $array)
                {
                    $id = $mc_link->get_subkey($guid, 'id');
                    $mc->add_constraint('id', '=', $id);
                }
            $mc->end_group();
        }
        
        $mc->add_constraint('metadata.navnoentry', '=', 0);
        
        // Unless in Auto-Index mode or the index article is hidden, we skip the index article.
        if (   !$this->_config->get('autoindex')
            && !$this->_config->get('indexinnav'))
        {
            $mc->add_constraint('name', '<>', 'index');
        }
        
        // FIXME: This is a workaround for some MultiLang bugs
        $mc->add_order('lang', 'ASC');
        
        $mc->add_order($this->_config->get('sort_order'));

        // Sort items with the same primary sort key by title.
        $mc->add_order('title');

        $mc->execute();
        
        $articles = $mc->list_keys();
        
        $leaves = array ();
        
        foreach ($articles as $guid => $array)
        {
            $article = array
            (
                'id' => $mc->get_subkey($guid, 'id'),
                'name' => $mc->get_subkey($guid, 'name'),
                'title' => $mc->get_subkey($guid, 'title'),
            );
            
            $leaves[$article['id']] = array
            (
                MIDCOM_NAV_URL => "{$article['name']}.html",
                MIDCOM_NAV_NAME => ($article['title']) ? $article['title'] : $article['name'],
                MIDCOM_NAV_GUID => $guid,
                MIDCOM_META_CREATED => 0,
                MIDCOM_META_CREATOR => 0,
                MIDCOM_META_EDITED => 0,
                MIDCOM_META_EDITOR => 0,
            );
        }
        
        return $leaves;
    }

    /**
     * This event handler will determine the content topic, which might differ due to a
     * set content symlink.
     */
    function _on_set_object()
    {
        $this->_determine_content_topic();
        return true;
    }

    /**
     * Set the content topic to use. This will check against the configuration setting 'symlink_topic'.
     * We don't do sanity checking here for performance reasons, it is done when accessing the topic,
     * that should be enough.
     *
     * @access protected
     */
    function _determine_content_topic()
    {

        $guid = $this->_config->get('symlink_topic');
        if (is_null($guid))
        {
            // No symlink topic
            // Workaround, we should talk to a DBA object automatically here in fact.
            $this->_content_topic = new midcom_db_topic($this->_topic->id);
            debug_pop();
            return;
        }

        $this->_content_topic = new midcom_db_topic($guid);

        if (! $this->_content_topic)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Failed to open symlink content topic, (might also be an invalid object) last Midgard Error: ' . mgd_errstr(),
                MIDCOM_LOG_ERROR);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "Failed to open symlink content topic {$guid}.");
            // This will exit.
        }
    }
}
?>
