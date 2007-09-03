<?php
/**
 * @package net.nehmer.blog
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * TAViewer NAP interface class
 *
 * This class has been rewritten for MidCOM 2.6 utilizing all of the currently
 * available state-of-the-art technology.
 *
 * See the individual member documentations about special NAP options in use.
 *
 * @package net.nehmer.blog
 */

class net_nehmer_blog_navigation extends midcom_baseclasses_components_navigation
{
    /**
     * The topic in which to look for articles. This defaults to the current content topic
     * unless overridden by the symlink topic feature.
     *
     * @var midcom_db_topic
     * @access private
     */
    var $_content_topic = null;

    /**
     * Simple constructor, calls base class.
     */
    function net_nehmer_blog_navigation()
    {
        parent::midcom_baseclasses_components_navigation();
    }

    /**
     * Returns a static leaf list with access to the archive.
     */
    function get_leaves()
    {
        // Check for symlink
        if (!$this->_content_topic)
        {
            $this->_determine_content_topic();
        }
        
        $leaves = array();
        
        if (   $this->_config->get('archive_enable')
            && $this->_config->get('show_navigation_pseudo_leaves'))
        {
            $leaves["{$this->_topic->id}_ARCHIVE"] = array
            (
                MIDCOM_NAV_SITE => Array
                (
                    MIDCOM_NAV_URL => "archive.html",
                    MIDCOM_NAV_NAME => $this->_l10n->get('archive'),
                ),
                MIDCOM_NAV_ADMIN => null,
                MIDCOM_META_CREATOR => $this->_content_topic->metadata->creator,
                MIDCOM_META_EDITOR => $this->_content_topic->metadata->revisor,
                MIDCOM_META_CREATED => $this->_content_topic->metadata->created,
                MIDCOM_META_EDITED => $this->_content_topic->metadata->revised,
            );
        }
        if (   $this->_config->get('rss_enable')
            && $this->_config->get('show_navigation_pseudo_leaves'))
        {
            $leaves[NET_NEHMER_BLOG_LEAFID_FEEDS] = array
            (
                MIDCOM_NAV_URL => "feeds.html",
                MIDCOM_NAV_NAME => $this->_l10n->get('available feeds'),
                MIDCOM_META_CREATOR => $this->_content_topic->metadata->creator,
                MIDCOM_META_EDITOR => $this->_content_topic->metadata->revisor,
                MIDCOM_META_CREATED => $this->_content_topic->metadata->created,
                MIDCOM_META_EDITED => $this->_content_topic->metadata->revised,
            );
        }

        if (   $this->_config->get('show_navigation_pseudo_leaves')
            && $this->_config->get('categories') != '')
        {
            $categories = explode(',', $this->_config->get('categories'));
            foreach ($categories as $category)
            {
                $leaves["{$this->_topic->id}_CAT_{$category}"] = array
                (
                    MIDCOM_NAV_SITE => Array
                    (
                        MIDCOM_NAV_URL => "category/{$category}",
                        MIDCOM_NAV_NAME => $category,
                    ),
                    MIDCOM_NAV_ADMIN => null,
                    MIDCOM_META_CREATOR => $this->_content_topic->metadata->creator,
                    MIDCOM_META_EDITOR => $this->_content_topic->metadata->revisor,
                    MIDCOM_META_CREATED => $this->_content_topic->metadata->created,
                    MIDCOM_META_EDITED => $this->_content_topic->metadata->revised,
                );
            }
        }
        
        // Return the request here if latest items aren't requested to be shown in navigation
        if (!$this->_config->get('show_latest_in_navigation'))
        {
            return $leaves;
        }
        
        // Get the latest content topic articles
        $qb = midcom_db_article::new_query_builder();
        $qb->add_constraint('topic', '=', $this->_content_topic->id);
        $qb->add_constraint('up', '=', 0);
        
        $qb->add_order('metadata.published', 'DESC');
        $qb->set_limit((int) $this->_config->get('index_entries'));
        
        // Checkup for the url prefix
        if ($this->_config->get('view_in_url'))
        {
            $prefix = 'view/';
        }
        else
        {
            $prefix = '';
        }
        
        foreach ($qb->execute_unchecked() as $article)
        {
            $leaves[$article->id] = array
            (
                MIDCOM_NAV_URL => "{$prefix}{$article->name}.html",
                MIDCOM_NAV_NAME => ($article->title != '') ? $article->title : $article->name,
                MIDCOM_NAV_GUID => $article->guid,
                MIDCOM_NAV_OBJECT => $article,
                MIDCOM_META_CREATOR => $article->metadata->creator,
                MIDCOM_META_EDITOR => $article->metadata->revisor,
                MIDCOM_META_CREATED => $article->metadata->created,
                MIDCOM_META_EDITED => $article->metadata->published,
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
            // Workaround, we should talk to an DBA object automatically here in fact.
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
            $_MIDCOM->generate_error('Failed to open symlink content topic.');
            // This will exit.
        }

    }
}
?>