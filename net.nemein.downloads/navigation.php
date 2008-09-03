<?php
/**
 * @package net.nemein.downloads
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Download manager NAP interface class.
 * 
 * @package net.nemein.downloads
 */
class net_nemein_downloads_navigation extends midcom_baseclasses_components_navigation
{
    /**
     * Simple constructor, calls base class.
     */
    function net_nemein_downloads_navigation() 
    {
        parent::__construct();
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
        $qb->add_constraint('topic', '=', $this->_topic->id);
        $qb->add_constraint('up', '=', 0);
        $qb->add_order('title', 'DESC');

        // Sort items with the same primary sort key by title.
        $qb->add_order('title');

        $result = $qb->execute();

        // Prepare everything
        $leaves = array();

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
}
?>