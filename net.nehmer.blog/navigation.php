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
        $leaves = array();
        
        if ($this->_config->get('archive_enable'))
        {
            $leaves[NET_NEHMER_BLOG_LEAFID_ARCHIVE] = array
            (
                MIDCOM_NAV_SITE => Array
                (
                    MIDCOM_NAV_URL => "archive.html",
                    MIDCOM_NAV_NAME => $this->_l10n->get('archive'),
                ),
                MIDCOM_NAV_ADMIN => null,
                MIDCOM_META_CREATOR => $this->_topic->metadata->creator,
                MIDCOM_META_EDITOR => $this->_topic->metadata->revisor,
                MIDCOM_META_CREATED => $this->_topic->metadata->created,
                MIDCOM_META_EDITED => $this->_topic->metadata->revised
            );
        }
        if ($this->_config->get('rss_enable'))
        {
            $leaves[NET_NEHMER_BLOG_LEAFID_FEEDS] = array
            (
                MIDCOM_NAV_SITE => Array
                (
                    MIDCOM_NAV_URL => "feeds.html",
                    MIDCOM_NAV_NAME => $this->_l10n->get('available feeds'),
                ),
                MIDCOM_NAV_ADMIN => null,
                MIDCOM_META_CREATOR => $this->_topic->metadata->creator,
                MIDCOM_META_EDITOR => $this->_topic->metadata->revisor,
                MIDCOM_META_CREATED => $this->_topic->metadata->created,
                MIDCOM_META_EDITED => $this->_topic->metadata->revised
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
