<?php
/**
 * @package net.nehmer.static
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
    var $_content_topic = null;

    /**
     * Simple constructor, calls base class.
     */
    function net_nehmer_static_navigation()
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
    function get_leaves()
    {
        $qb = midcom_baseclasses_database_article::new_query_builder();
        $qb->add_constraint('topic', '=', $this->_content_topic->id);
        $qb->add_constraint('up', '=', 0);

        // Unless in Auto-Index mode or the index article is hidden, we skip the index article.
        if ($this->_config->get('autoindex'))
        {
            $qb->add_constraint('name', '<>', 'index');
        }
        else
        {
            if (! $this->_config->get('indexinnav'))
            {
                $qb->add_constraint('name', '<>', 'index');
            }
        }

        $qb->add_order($this->_config->get('sort_order'));

        // Sort items with the same primary sort key by title.
        $qb->add_order('title');

        $result = $qb->execute();

        // Prepare everything
        $leaves = array ();

        foreach ($result as $article)
        {
            $leaves[$article->id] = array
            (
                MIDCOM_NAV_SITE => Array
                (
                    MIDCOM_NAV_URL => "{$article->name}.html",
                    MIDCOM_NAV_NAME => ($article->title != '') ? $article->title : $article->name
                ),
                MIDCOM_NAV_ADMIN => null,
                MIDCOM_NAV_GUID => $article->guid,
                MIDCOM_NAV_OBJECT => $article,
                MIDCOM_META_CREATOR => $article->creator,
                MIDCOM_META_EDITOR => $article->revisor,
                MIDCOM_META_CREATED => $article->created,
                MIDCOM_META_EDITED => $article->revised
            );

        }
        return $leaves;
    }

    /**
     * The node listing will add a list of create links of all schemas to the toolbar.
     *
     * @todo Convert mgd_get_article_by_name to MgdSchema.
     */
    function get_node()
    {
        // Check visibility
        /*
        if (   $this->_config->get('autoindex') != 1
            && mgd_get_article_by_name($this->_topic->id, 'index') === false)
        {
            $hidden = true;
        }
        else
        {
            $hidden = false;
        }
        */
        $hidden = false;

        // Get latest article
        $qb = midcom_db_article::new_query_builder();
        $qb->add_constraint('topic', '=', $this->_topic->id);
        $qb->add_order('revised', 'DESC');
        $qb->set_limit(1);
        $result = $qb->execute();
        if (   $result
            && $result[0]->revised > $this->_topic->revised)
        {
            $revised = $result[0];
        }
        else
        {
            $revised = $this->_topic;
        }

        return array
        (
            MIDCOM_NAV_URL => '',
            MIDCOM_NAV_NAME => $this->_topic->extra,
            MIDCOM_NAV_NOENTRY => $hidden,
            MIDCOM_NAV_CONFIGURATION => $this->_config,
            MIDCOM_META_CREATOR => $this->_topic->creator,
            MIDCOM_META_EDITOR => $revised->revisor,
            MIDCOM_META_CREATED => $this->_topic->created,
            MIDCOM_META_EDITED => $revised->revised
        );
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
