<?php
/**
 * @package net.nemein.simpledb
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: search.php 4051 2006-09-12 07:32:51Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * simpledb forum search
 *
 * @package net.nemein.simpledb
 */
class net_nemein_simpledb_handler_export extends midcom_baseclasses_components_handler
{
    /**
     * Schema field names and their storage locations
     *
     * @access private
     * @var array
     */
    var $_fields = array ();

    /**
     * Stores the parameter field names
     *
     * @access private
     * @var array
     */
    var $_parameters = array ();

    /**
     * Queery Builder for common use inside the handler class. Helps in breaking methods
     * into smaller pieces.
     *
     * @access private
     */
    var $_qb = null;

    /**
     * Simple default constructor.
     */
    function net_nemein_simpledb_handler_export()
    {
        parent::__construct();
    }

    /**
     * Get the column names from the schema fields
     *
     * @access private
     */
    function _get_columns()
    {
        // Make layout array visible to elements
        $columns = array();

        foreach ($this->_request_data['schema_fields'] as $key => $field)
        {
            $viewable = true;
            if (   isset($field['hidden'])
                && $field['hidden'])
            {
                // Hidden field, skip
                continue;
            }
            if (   isset($field['net_nemein_simpledb_list'])
                && $field['net_nemein_simpledb_list'] == false)
            {
                // View not to be listed, skip
                continue;
            }

            $columns[$key] = $this->_l10n->get($field['description']);
        }

        return $columns;
    }

    /**
     * Get the topic articles
     *
     * @access private
     */
    function _get_articles()
    {
        $qb = midcom_db_article::new_query_builder();
        $qb->add_constraint('topic', '=', $this->_topic->id);

        return $qb->execute();
    }

    /**
     * Checks the integrity and permissions for exporting the simpledb fields
     *
     * @access private
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success
     */
    function _handler_export($handler_id, $args, &$data)
    {
        $this->_topic->require_do('midgard:read');

        $headers = array ();

        // At the moment no blind exporting guess is provided
        if (!array_key_exists(0, $args))
        {
            return false;
        }

        switch ($args[0])
        {
            case 'excel':
                $this->_type = 'excel';
                $headers = array
                (
                    'Content-type: application/vnd.ms-excel',
                    "Content-disposition: attachment; filename={$this->_topic->name}.xls",
                );
                break;

            default:
                return false;
        }

        // Check if it is possible
        if (!method_exists($this, "_show_{$this->_type}"))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Requested export method is not available!");
        }

        $this->_show_method = "_show_{$this->_type}";

        // Set the page headers
        foreach ($headers as $header)
        {
            $_MIDCOM->header($header);
        }

        $_MIDCOM->skip_page_style = true;
        $this->_request_data['columns'] = $this->_get_columns();

        return true;
    }

    /**
     * Show the style in accordance to the to the requested type.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     * @access private
     */
    function _show_export($handler_id, &$data)
    {
        $this->_request_data['schema_fields'] = $this->_get_columns();

        switch ($this->_type)
        {
            case 'excel':
                $this->_show_excel($data);
                break;
        }
    }

    /**
     * Shows the Microsoft Excel
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     * @access private
     */
    function _show_excel(&$data)
    {
        midcom_show_style('view-export-excel-header');

        foreach ($this->_get_articles() as $article)
        {
            $this->_request_data['article'] =& $article;
            $data['entry'] =& $article;

            $data['datamanager']->init($data['entry']);
            $data['view'] = $data['datamanager']->get_array();

            midcom_show_style('view-export-excel-item');
        }
        midcom_show_style('view-export-excel-footer');
    }
}
?>