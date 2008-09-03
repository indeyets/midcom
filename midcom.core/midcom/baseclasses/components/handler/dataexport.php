<?php
/**
 * @package midcom.baseclasses
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: view.php,v 1.1 2006/05/10 13:00:45 rambo Exp $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Generic CSV export handler baseclass
 *
 * @package midcom.baseclasses
 */
class midcom_baseclasses_components_handler_dataexport extends midcom_baseclasses_components_handler
{    
    /**
     * The Datamanager of the project to display.
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_datamanager = null;
    
    var $_schema = null;
    
    var $_objects = array();
    
    function midcom_baseclasses_components_handler_dataexport()
    {
        parent::__construct();
    }
    
    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['datamanager'] =& $this->_datamanager;
        $this->_request_data['objects'] =& $this->_objects;
    }
    
    /**
     * Internal helper, loads the datamanager for the current article. Any error triggers a 500.
     *
     * @access private
     */
    function _load_datamanager($schemadb)
    {
        if (empty($this->_schema))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Export schema ($this->_schema) must be defined, hint: do it in "_load_schemadb"');
            // This will exit
        }
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($schemadb);

        if (   ! $this->_datamanager
            || ! $this->_datamanager->set_schema($this->_schema))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for schemadb schema '{$this->_schema}'.");
            // This will exit.
        }
    }
    
    function _load_schemadb()
    {
        $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Method "_load_schemadb" must be overridden in implementation');
    }

    function _load_data()
    {
        $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Method "_load_data" must be overridden in implementation');
    }
    
    function _handler_csv($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
            
        //Disable limits
        @ini_set('memory_limit', -1);
        @ini_set('max_execution_time', 0);
            
        $this->_load_datamanager($this->_load_schemadb($handler_id, $args, $data));
        $this->_objects = $this->_load_data($handler_id, $args, $data);
        
        $_MIDCOM->skip_page_style = true;

        if (   !isset($args[0])
            || empty($args[0]))
        {
            //We do not have filename in URL, generate one and redirect
            $fname = preg_replace('/[^a-z0-9-]/i', '_', strtolower($this->_topic->extra)) . '_' . date('Y-m-d') . '.csv';
            if(strpos($_MIDGARD['uri'], '/', strlen($_MIDGARD['uri'])-2))
            {
                $_MIDCOM->relocate("{$_MIDGARD['uri']}{$fname}");
            }
            else
            {
                $_MIDCOM->relocate("{$_MIDGARD['uri']}/{$fname}");
            }
            // This will exit
        }

        if(   !isset($data['filename'])
           || $data['filename'] == '')
        {
            $data['filename'] = str_replace('.csv', '', $args[0]);
        }

        $this->_init_csv_variables();
        $_MIDCOM->skip_page_style = true;

//        $_MIDCOM->cache->content->content_type('text/plain');
        // FIXME: Use global configuration
        $_MIDCOM->cache->content->content_type('application/csv');
        //$_MIDCOM->cache->content->content_type($this->_config->get('csv_export_content_type'));

        return true;
    }

    function _init_csv_variables()
    {
        // FIXME: Use global configuration
        if (   !isset($this->csv['s'])
            || empty($this->csv['s']))
        {
            $this->csv['s'] = ';';
            //$this->csv['s'] = $this->_config->get('csv_export_separator');
        }
        if (   !isset($this->csv['q'])
            || empty($this->csv['q']))
        {
            $this->csv['q'] = '"';
            //$this->csv['q'] = $this->_config->get('csv_export_quote');
        }
        if (   !isset($this->csv['d'])
            || empty($this->csv['d']))
        {
            $this->csv['d'] = '.';
            //$this->csv['d'] = $this->_config->get('csv_export_decimal');
        }
        if (   !isset($this->csv['nl'])
            || empty($this->csv['nl']))
        {
            $this->csv['nl'] = "\n";
            //$this->csv['nl'] = $this->_config->get('csv_export_newline');
        }
        if (   !isset($this->csv['charset'])
            || empty($this->csv['charset']))
        {
            $this->csv['charset'] = 'iso-8859-15';
            //$this->csv['charset'] = $this->_config->get('csv_export_charset');
        }
        if ($this->csv['s'] == $this->csv['d'])
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "CSV decimal separator (configured as '{$this->csv['d']}') may not be the same as field separator (configured as '{$this->csv['s']}')");
        }
    }

    function _encode_csv($data, $add_separator = true, $add_newline = false)
    {
        /* START: Quick'n'Dirty on-the-fly charset conversion */
        if (function_exists('iconv'))
        {
            $append_target = '//TRANSLIT';
            //$append_target = $this->_config->get('iconv_append_target');
            $to_charset = strtolower($this->csv['charset']) . $append_target;
            /*
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("calling iconv('utf-8', {$to_charset}, {$data})");
            */
            $stat = @iconv('utf-8', $to_charset, $data);
            //debug_add("got back '{$stat}'");
            if (!empty($stat))
            {
                debug_add('overwriting $data');
                $data = $stat;
            }
            debug_pop();
        }
        /* END: Quick'n'Dirty on-the-fly charset conversion */
        
        // Strings and numbers beginning with zero are quoted
        if (   (   !is_numeric($data)
                || preg_match('/^[0+]/', $data))
            && !empty($data))
        {
            // Make sure we have only newlines in data
            $data = preg_replace("/\n\r|\r\n|\r/", "\n", $data);
            // Escape quotes (PONDER: make configurable between doubling the character and escaping)
            $data = str_replace($this->csv['q'], '\\' . $this->csv['q'], $data);
            // Escape newlines
            $data = str_replace("\n", '\\n', $data);
            // Quote
            $data = "{$this->csv['q']}{$data}{$this->csv['q']}";
        }
        else
        {
            // Decimal point format
            $data = str_replace('.', $this->csv['d'], $data);
        }
        if ($add_separator)
        {
            $data .= $this->csv['s'];
        }
        if ($add_newline)
        {
            $data .= $this->csv['nl'];
        }
        return $data;
    }

    /**
     * Sets given object as storage object for DM2
     */
    function set_dm_storage(&$object)
    {
        return $this->_datamanager->set_storage($object);
    }

    function _show_csv($handler_id, &$data)
    {
        // Make real sure we're dumping data live
        $_MIDCOM->cache->content->enable_live_mode();
        while(@ob_end_flush());

        // Dump headers
        echo $this->_encode_csv('GUID', true, false);
        $i = 0;
        $datamanager =& $this->_datamanager;
        foreach ($datamanager->schema->field_order as $name)
        {
            $field =& $datamanager->schema->fields[$name];
            $i++;
            if ($i < count($datamanager->schema->field_order))
            {
                echo $this->_encode_csv($field['title'], true, false);
            }
            else
            {
                echo $this->_encode_csv($field['title'], false, true);
            }
        }

        // Dump objects
        foreach ($this->_objects as $object)
        {
            if (!$this->set_dm_storage($object))
            {
                // Object failed to load, skip
                continue;
            }

            echo $this->_encode_csv($object->guid, true, false);
            $i = 0;
            foreach ($datamanager->schema->field_order as $fieldname)
            {
                $type =& $datamanager->types[$fieldname];
                $i++;
                $data = '';
                $data = $type->convert_to_csv();
                if ($i < count($datamanager->schema->field_order))
                {
                    echo $this->_encode_csv($data, true, false);
                }
                else
                {
                    echo $this->_encode_csv($data, false, true);
                }
                $data = '';
                // Prevent buggy types from leaking their old value over
                $datamanager->types[$fieldname]->value = false;
            }
            flush();
        }
        // restart ob to keep MidCOM happy
        ob_start();
    }
}
?>