<?php

/**
 * @package midcom.services
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id:backend.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This class provides an abstract base class for all indexer backends.
 * 
 * 
 * ...
 * 
 * @abstract Abstract indexer backend class
 * @package midcom.services
 * @see midcom_services_indexer
 */

class midcom_services_indexer_backend
{
    
    function midcom_services_indexer_backend()
    {
        // empty yet.
    }
    
    /**
     * Adds a document to the index.
     * 
     * ...
     * 
     * @param Array $documents A list of midcom_services_indexer_document objects.
     * @return bool Indicating success.
     */   
    function index ($documents)
    {
        die ('The method midcom_service_indexer_backend::index must be implemented.');
    }
    
    /**
     * Removes the document with the given resource identifier from the index.
     * 
     * @param string $RI The resource identifier of the document that should be deleted.
     * @return bool Indicating success.
     */
    function delete ($RI)
    {
    	die ('The method midcom_service_indexer_backend::remove must be implemented.');
    }
    
    /**
     * Clear the index completly.
     * 
     * This will drop the current index.
     * 
     * @return bool Indicating success.
     */
    function delete_all()
    {
    	die ('The method midcom_service_indexer_backend::remove_all must be implemented.');
    }
    
    /**
     * Query the index and, if set, restrict the query by a given filter.
     * 
     * ...
     * 
     * @param string $query The query, which must suite the backends query syntax.
     * @param midcom_services_indexer_filter $filter An optional filter used to restrict the query. This may be null indicating no filter.
     * @return Array An arary of documents matching the query, or false on a failure.
     */
    function query ($query, $filter)
    {
     	die ('The method midcom_service_indexer_backend::query must be implemented.');
    }
    
    
    
    
}

?>