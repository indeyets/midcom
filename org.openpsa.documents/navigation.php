<?php
/**
 * @package org.openpsa.documents
 * @author Nemein Oy, http://www.nemein.com/
 * @version $Id: navigation.php,v 1.9 2006/05/11 15:46:38 rambo Exp $
 * @copyright Nemein Oy, http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * org.openpsa.documents NAP interface class.
 * @package org.openpsa.documents
 */
class org_openpsa_documents_navigation extends midcom_baseclasses_components_navigation
{

    function get_leaves() 
    {

        $leaves = array ();
        return $leaves;

        // OLD STUFF:
        // List the documents
        $qb = org_openpsa_documents_document_dba::new_query_builder();
        $qb->add_constraint('topic', '=', $this->_topic->id);
        $qb->add_constraint('nextVersion', '=', 0);
        $qb->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_DOCUMENT);
        $ret = $qb->execute();
        if (   is_array($ret)
            && count($ret) > 0)
        {
            // Prep toolbar
            $toolbar = array();
            /*$toolbar[50] = Array(
                MIDCOM_TOOLBAR_URL => '',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                MIDCOM_TOOLBAR_ENABLED => true,
                MIDCOM_TOOLBAR_OPTIONS => Array(
                    'rel' => 'directlink',
                ),
            );*/
            $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

            foreach ($ret as $document)
            {
                // Match the toolbar to the correct URL.
                //$toolbar[50][MIDCOM_TOOLBAR_URL] = "{$prefix}document/{$document->guid}/edit.html";
                //$toolbar[51][MIDCOM_TOOLBAR_URL] = "{$prefix}document/{$documents->id}/delete.html";

                $leaves[$document->id] = array 
                (
                    MIDCOM_NAV_SITE => array 
                    (
                        MIDCOM_NAV_URL => 'document/' . $document->guid . '/',
                        MIDCOM_NAV_NAME => ($document->title != "") ? $document->title : "document #".$document->id
                    ),
                    MIDCOM_NAV_ADMIN => array 
                    (
                        MIDCOM_NAV_URL => null,
                        MIDCOM_NAV_NAME => ($document->title != "") ? $document->title : "document #".$document->id
                    ),
                    MIDCOM_NAV_OBJECT => $document,
                    MIDCOM_NAV_GUID => $document->guid,
                    MIDCOM_NAV_TOOLBAR => $toolbar,
                    MIDCOM_META_CREATOR => $document->creator,
                    MIDCOM_META_EDITOR => $document->revisor,
                    MIDCOM_META_CREATED => $document->created,
                    MIDCOM_META_EDITED => $document->revised
                );
            }
        }
        return $leaves;
    }

    function get_node($toolbar = null)
    {
        $toolbar = Array();
        /*$toolbar[100] = Array
        (
            MIDCOM_TOOLBAR_URL => 'document/new/',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('new document'),
            MIDCOM_TOOLBAR_HELPTEXT => '',
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-text.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_OPTIONS => Array(
                'rel' => 'directlink',
            ),
        );
        $toolbar[101] = Array
        (
            MIDCOM_TOOLBAR_URL => 'new/',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('new directory'),
            MIDCOM_TOOLBAR_HELPTEXT => '',
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-dir.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_OPTIONS => Array(
                'rel' => 'directlink',
            ),
        );*/
        return parent::get_node($toolbar);
    }

}

?>