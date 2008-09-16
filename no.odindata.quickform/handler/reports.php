<?php
/**
 * @package no.odindata.quickform
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: configuration.php 3145 2007-03-27 10:09:00Z NetBlade $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 *
 * @package no.odindata.quickform
 */
class no_odindata_quickform_handler_reports extends midcom_baseclasses_components_handler
{
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Internal helper, loads the configured schema database into the class.
     * It is not yet evaluated by a datamanager, only the file is loaded.
     *
     * @see $_schemadb
     * @see $_schemadb_index
     * @access private
     */
    function _load_schema_database()
    {
        $path = $this->_config->get('schemadb');

        $data = midcom_get_snippet_content($path);

        eval("\$this->_schemadb = Array ({$data}\n);");

        // This is a compatibility value for the configuration system
        //TODO: remove
        //$GLOBALS['de_linkm_taviewer_schemadbs'] =& $this->_schemadbs;

        if (is_array($this->_schemadb))
        {
            if (count($this->_schemadb) == 0)
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    "Could not load the schema database associated with this topic: The schema DB in {$path} was empty.");
                // This will exit.
            }
            foreach ($this->_schemadb as $schema)
            {
                $this->_schemadb_index[$schema['name']] = $this->_l10n->get($schema['description']);
            }
        }
        else
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Could not load the schema database associated with this topic. The schema DB was no array.');
            // This will exit.
        }
    }

     /**
     * Prepares the datamanager for creation of a new article. When returning false,
     * it sets errstr and errcode accordingly.
     *
     * @param string $schema The name of the schema to initialize for
     * @return boolean Indicating success
     * @access private
     */
    function _prepare_creation_datamanager($schema)
    {
        $this->_datamanager = new midcom_helper_datamanager_getvar($this->_schemadb);
        if (! $this->_datamanager)
        {
            $this->errstr = 'Failed to create a datamanager instance, see the debug level log for details.';
            $this->errcode = MIDCOM_ERRCRIT;
            return false;
        }
        // show js if the editor needs it.
        $this->_datamanager->set_show_javascript(true);

        if (! $this->_datamanager->init_creation_mode($schema, $this))
        {
            $this->errstr = "Failed to initialize the datamanager in creation mode for schema '{$schema}'.";
            $this->errcode = MIDCOM_ERRCRIT;
            return false;
        }
        return true;
    }

    function _on_initialize()
    {
        $this->_load_schema_database();

        $this->_request_data['datamanager'] = & $this->_datamanager;

        if (   $this->_config->get('schema_name') == ''
            && array_key_exists('default', $this->_schemadb))
        {
            $this->_schema_name = 'default' ;
        }
        else
        {
            $this->_schema_name = $this->_config->get('schema_name');
        }

        if (! $this->_prepare_creation_datamanager($this->_schema_name))
        {
            debug_pop();
            return false;
        }
        $this->_load_schema_database();
        $this->_request_data['fields_for_search'] = Array();
        $this->_request_data['fields']  = $this->_datamanager->get_fieldnames();
        $this->_request_data['schema']  = $this->_datamanager->get_layout_database();
        $this->_request_data['schema_content'] = $this->_request_data['schema'][$this->_schema_name];

        $this->_request_data['datamanager'] = & $this->_datamanager;

        $this->_request_data['topic'] =& $this->_topic;
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_report_index()
    {
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => '',
            MIDCOM_NAV_NAME => $this->_request_data['topic']->extra,
        );

        $tmp[] = Array
        (
            MIDCOM_NAV_URL => 'reports/',
            MIDCOM_NAV_NAME => $this->_l10n->get('Reports'),
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        $qb_articles = midcom_db_article::new_query_builder();
        $qb_articles->add_constraint('topic', '=', $this->_request_data['topic']->id);
        $this->_request_data['articles_count'] = $qb_articles->count();


        foreach($this->_request_data['schema_content']['fields'] as $schema_field => $array_content)
        {
            if($array_content['location'] != 'parameter')
            {
                $this->_request_data['fields_for_search'][$array_content['location']] = $schema_field;
            }
        }

//        $this->_request_data['message'] = $this->_config->get('end_message');

        return true;
    }

    function _show_report_index()
    {
        midcom_show_style('show-report');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_report_list_all()
    {
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => '',
            MIDCOM_NAV_NAME => $this->_request_data['topic']->extra,
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => 'reports/',
            MIDCOM_NAV_NAME => $this->_l10n->get('Reports'),
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => 'list_all/',
            MIDCOM_NAV_NAME => $this->_l10n->get('List all'),
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        $qb_articles = midcom_db_article::new_query_builder();
        $qb_articles->add_constraint('topic', '=', $this->_request_data['topic']->id);
        $qb_articles->add_order('name');
        $this->_request_data['articles_count'] = $qb_articles->count();
        $this->_request_data['articles'] = $qb_articles->execute();


        foreach($this->_request_data['schema_content']['fields'] as $schema_field => $array_content)
        {
            if($array_content['location'] != 'parameter')
            {
                $this->_request_data['fields_for_search'][$array_content['location']] = $schema_field;
            }
        }

//        $this->_request_data['message'] = $this->_config->get('end_message');

        return true;
    }

    function _show_report_list_all()
    {
        midcom_show_style('show-report-list');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_report_list_by_key()
    {
        $this->_request_data['articles_by_key'] = Array();
        if(array_key_exists('no_odindata_quickform_reports_select_sort_key_1', $_POST))
        {
            $this->_request_data['sort_key_1'] = $_POST['no_odindata_quickform_reports_select_sort_key_1'];
        }
        else
        {
            $this->_request_data['sort_key_1'] = 'title';
        }
        if(array_key_exists('no_odindata_quickform_reports_select_sort_key_2', $_POST))
        {
            $this->_request_data['sort_key_2'] = $_POST['no_odindata_quickform_reports_select_sort_key_2'];
        }
        else
        {
            $this->_request_data['sort_key_2'] = 'title';
        }
        $tmp_sort_key_1 = $this->_request_data['sort_key_1'];
        $tmp_sort_key_2 = $this->_request_data['sort_key_2'];

        $tmp[] = Array
        (
            MIDCOM_NAV_URL => '',
            MIDCOM_NAV_NAME => $this->_request_data['topic']->extra,
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => 'reports/',
            MIDCOM_NAV_NAME => $this->_l10n->get('Reports'),
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => 'list_by_key/',
            MIDCOM_NAV_NAME => $this->_l10n->get('List by key'),
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        $qb_articles = midcom_db_article::new_query_builder();
        $qb_articles->add_constraint('topic', '=', $this->_request_data['topic']->id);
        $qb_articles->add_order('name');

        $this->_request_data['articles_count'] = $qb_articles->count();
        $this->_request_data['articles'] = $qb_articles->execute();
        foreach($this->_request_data['articles'] as $article => $article_content )
        {
//            $this->_request_data['articles_by_key'][$article_content->$tmp_sort_key_1][$article] = $article_content;
            $this->_request_data['articles_by_key'][$article_content->$tmp_sort_key_1][$article_content->$tmp_sort_key_2][$article] = $article_content;
        }

        foreach($this->_request_data['schema_content']['fields'] as $schema_field => $array_content)
        {
            if($array_content['location'] != 'parameter')
            {
                $this->_request_data['fields_for_search'][$array_content['location']] = $schema_field;
            }
        }

//        $this->_request_data['message'] = $this->_config->get('end_message');

        return true;
    }

    function _show_report_list_by_key()
    {
        midcom_show_style('show-report-list-by-key');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_report_list_by_key_distinct()
    {
        $in_csv = false;
        $this->_request_data['in_csv'] = false;


        $this->_request_data['articles_by_key'] = Array();
        if(array_key_exists('no_odindata_quickform_reports_sort_key_2', $_POST))
        {
            $this->_request_data['sort_key_1'] = $_POST['no_odindata_quickform_reports_sort_key_1'];
            $this->_request_data['sort_key_1_value'] = $_POST['no_odindata_quickform_reports_sort_key_1_value'];
        }
        else
        {
            $this->_request_data['sort_key_1'] = 'title';
            $this->_request_data['sort_key_1_value'] = '';
        }
        if(array_key_exists('no_odindata_quickform_reports_sort_key_2', $_POST))
        {
            $this->_request_data['sort_key_2'] = $_POST['no_odindata_quickform_reports_sort_key_2'];
            $this->_request_data['sort_key_2_value'] = $_POST['no_odindata_quickform_reports_sort_key_2_value'];
        }
        else
        {
            $this->_request_data['sort_key_2'] = 'title';
            $this->_request_data['sort_key_2_value'] = '';
        }
        $tmp_sort_key_1 = $this->_request_data['sort_key_1'];
        $tmp_sort_key_2 = $this->_request_data['sort_key_2'];
        $tmp_sort_key_1_value = $this->_request_data['sort_key_1_value'];
        $tmp_sort_key_2_value = $this->_request_data['sort_key_2_value'];

        if(array_key_exists('no_odindata_quickform_reports_submit_excel', $_POST))
        {
            $in_csv = true;
            $this->_request_data['in_csv'] = true;

            $headers = array
            (
                'Content-type: application/octet-stream',
                "Content-disposition: attachment; filename=report_by_key.csv",
            );

            foreach ($headers as $header)
            {
                $_MIDCOM->header($header);
            }

            $_MIDCOM->skip_page_style = true;
        }


        $tmp[] = Array
        (
            MIDCOM_NAV_URL => '',
            MIDCOM_NAV_NAME => $this->_request_data['topic']->extra,
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => 'reports/',
            MIDCOM_NAV_NAME => $this->_l10n->get('Reports'),
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => 'list_by_key_distinct/',
            MIDCOM_NAV_NAME => $this->_l10n->get('List responces by key'),
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        $qb_articles = midcom_db_article::new_query_builder();
        $qb_articles->add_constraint('topic', '=', $this->_request_data['topic']->id);
        $qb_articles->add_constraint($tmp_sort_key_1, '=', $tmp_sort_key_1_value);
        $qb_articles->add_constraint($tmp_sort_key_2, '=', $tmp_sort_key_2_value);
        $qb_articles->add_order('name');

        $this->_request_data['articles_count'] = $qb_articles->count();
        $this->_request_data['articles'] = $qb_articles->execute();


        foreach($this->_request_data['schema_content']['fields'] as $schema_field => $array_content)
        {
            if($array_content['location'] != 'parameter')
            {
                $this->_request_data['fields_for_search'][$array_content['location']] = $schema_field;
            }
        }

        return true;
    }

    function _show_report_list_by_key_distinct()
    {
        if($this->_request_data['in_csv'])
        {
            midcom_show_style('show-report-list-by-key-distinct-csv');
        }
        else
        {
            midcom_show_style('show-report-list-by-key-distinct');
        }
    }
}

?>