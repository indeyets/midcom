<?php
/**
 * @package cc.kaktus_pearserver
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: navigation.php 4198 2006-09-25 14:20:07Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * PEAR server NAP interface class
 *
 * @package cc.kaktus_pearserver
 */
class cc_kaktus_pearserver_navigation extends midcom_baseclasses_components_navigation
{
    /**
     * Simple constructor, calls base class.
     */
    function cc_kaktus_pearserver_navigation()
    {
        parent::midcom_baseclasses_components_navigation();
    }

    /**
     * Returns all leaves for the current content topic.
     * 
     * @TODO: This needs to be written to show the pseudo leaves
     */
    function get_leaves()
    {
        // Prepare everything
        $leaves = array ();
        
        return $leaves;
        
        /*
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
        /* */
    }
}
?>
