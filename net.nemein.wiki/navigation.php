<?php
/**
 * @package net.nemein.wiki
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Wiki NAP interface class.
 * 
 * @package net.nemein.wiki
 */
class net_nemein_wiki_navigation  extends midcom_baseclasses_components_navigation
{
    /**
     * Simple constructor, calls base class.
     */
	function net_nemein_wiki_navigation() 
    {
        parent::midcom_baseclasses_components_navigation();
    }

    function get_leaves()
    {
        $leaves = array();
        
        /*
        $qb = midcom_baseclasses_database_article::new_query_builder();
        $qb->add_constraint('topic', '=', $this->_topic->id);
        $qb->add_order('title', 'ASC');
        $result = $qb->execute();

        // Prep toolbar
        $toolbar[49] = array
        (
            MIDCOM_TOOLBAR_URL => '',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('view'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-text.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_OPTIONS  => array
            (
                'rel' => 'directlink',          
            ),
        );        
        $toolbar[50] = array
        (
            MIDCOM_TOOLBAR_URL => '',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_OPTIONS  => array
            (
                'rel' => 'directlink',          
            ),
        );
        $toolbar[51] = array
        (
            MIDCOM_TOOLBAR_URL => '',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
            MIDCOM_TOOLBAR_ENABLED => true
        );
        
        foreach ($result as $wikipage)
        {
            $toolbar[49][MIDCOM_TOOLBAR_URL] = "{$wikipage->name}.html";               
            $toolbar[50][MIDCOM_TOOLBAR_URL] = "edit/{$wikipage->name}.html";
            $toolbar[50][MIDCOM_TOOLBAR_HIDDEN] = ($_MIDCOM->auth->can_do('midgard:update', $wikipage) == false);            
            $toolbar[51][MIDCOM_TOOLBAR_URL] = "delete/{$wikipage->id}.html";
            $toolbar[51][MIDCOM_TOOLBAR_HIDDEN] = ($_MIDCOM->auth->can_do('midgard:delete', $wikipage) == false);        

            if ($wikipage->name == 'index')
            {
                // Wiki index article
                $toolbar[49][MIDCOM_TOOLBAR_URL] = '';  
                $leaves[$wikipage->id] = array
                (
                    MIDCOM_NAV_SITE => array
                    (
                        MIDCOM_NAV_URL => '',
                        MIDCOM_NAV_NAME => $wikipage->title
                    ),
                    MIDCOM_NAV_ADMIN => array
                    (
                        MIDCOM_NAV_URL => "view/{$wikipage->id}",
                        MIDCOM_NAV_NAME => $wikipage->title
                    ),
                    MIDCOM_NAV_GUID => $wikipage->guid,
                    MIDCOM_NAV_TOOLBAR => $toolbar,
                    MIDCOM_NAV_NOENTRY => true,
                    MIDCOM_META_CREATOR => $wikipage->creator,
                    MIDCOM_META_EDITOR => $wikipage->revisor,
                    MIDCOM_META_CREATED => $wikipage->created,
                    MIDCOM_META_EDITED => $wikipage->revised
                );
            } 
            else 
            {
                // Regular Wiki page
                $leaves[$wikipage->id] = array
                (
                    MIDCOM_NAV_SITE => array
                    (
                        MIDCOM_NAV_URL => "{$wikipage->id}.html",
                        MIDCOM_NAV_NAME => $wikipage->title
                    ),
                    MIDCOM_NAV_ADMIN => array
                    (
                        MIDCOM_NAV_URL => "view/{$wikipage->id}",
                        MIDCOM_NAV_NAME => $wikipage->title
                    ),
                    MIDCOM_NAV_GUID => $wikipage->guid,
                    MIDCOM_NAV_TOOLBAR => $toolbar,
                    MIDCOM_NAV_NOENTRY => true,
                    MIDCOM_META_CREATOR => $wikipage->creator,
                    MIDCOM_META_EDITOR => $wikipage->revisor,
                    MIDCOM_META_CREATED => $wikipage->created,
                    MIDCOM_META_EDITED => $wikipage->revised
                );
            }
        }*/
        return $leaves;
    }
}
?>