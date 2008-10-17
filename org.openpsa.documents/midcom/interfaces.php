<?php
/**
 * @package org.openpsa.documents
 * @author Nemein Oy http://www.nemein.com/
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * OpenPSA Documents document management system
 *
 * @package org.openpsa.documents
 */
class org_openpsa_documents_interface extends midcom_baseclasses_components_interface
{

    function __construct()
    {
        parent::__construct();

        $this->_component = 'org.openpsa.documents';
        $this->_autoload_class_definitions = array('midcom_dba_classes.inc');
        $this->_autoload_files = array();
        $this->_autoload_libraries = array
        (
            'org.openpsa.core',
            'midcom.helper.datamanager2',
        );

    }

    function _on_initialize()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_pop();
        return true;
    }

    /**
     * Iterate over all documents and create index record using the datamanager indexer
     * method.
     */
    function _on_reindex($topic, $config, &$indexer)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $qb = org_openpsa_documents_document_dba::new_query_builder();
        $qb->add_constraint('topic', '=', $topic->id);
        $qb->add_constraint('nextVersion', '=', 0);
        $qb->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_DOCUMENT);
        $ret = $qb->execute();
        if (   is_array($ret)
            && count($ret) > 0)
        {
            foreach ($ret as $document)
            {
                $datamanager = new midcom_helper_datamanager($config->get('schemadb_document'));
                if (!$datamanager)
                {
                    debug_add('Warning, failed to create a datamanager instance with this schemapath:' . $this->_config->get('schemadb'),
                        MIDCOM_LOG_WARN);
                    continue;
                }

                if (!$datamanager->init($document))
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

    function _on_resolve_permalink($topic, $config, $guid)
    {
        $document = new org_openpsa_documents_document_dba($guid);
        if (   ! $document
            || $document->topic != $topic->id)
        {
            return null;
        }
        return "document/{$document->guid}/";
    }
}
?>