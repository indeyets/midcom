<?php
/**
 * @package pl.olga.windguru
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * @package pl.olga.windguru
 */
class pl_olga_windguru_navigation extends midcom_baseclasses_components_navigation
{
    /**
     * Simple constructor, calls base class.
     */
    function __construct()
    {
        parent::__construct();
    }

    function get_leaves()
    {
        $qb = midcom_baseclasses_database_article::new_query_builder();
        $qb->add_constraint('topic', '=', $this->_topic->id);
        $qb->add_constraint('up', '=', 0);
        $qb->add_order('title');

        $result = $qb->execute();

        // Prepare everything
        $leaves = array();

        foreach ($result as $article)
        {
            $metadata =& midcom_helper_metadata::retrieve($article);

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
                MIDCOM_NAV_NOENTRY => (bool) $metadata->get('navnoentry'),
                MIDCOM_META_CREATOR => $metadata->get('creator'),
                MIDCOM_META_EDITOR => $metadata->get('revisor'),
                MIDCOM_META_CREATED => $metadata->get('created'),
                MIDCOM_META_EDITED => $metadata->get('edited')
            );

        }
        return $leaves;
    }


}

?>