<?php
/**
 * @package net.nemein.personnel
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: admin.php,v 1.1 2006/11/19 12:11:32 torben Exp $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * CVS import/export handler
 *
 * @package net.nemein.personnel
 */

class net_nemein_personnel_handler_csv extends midcom_baseclasses_components_handler
{
    /**
     * The schema database in use, available only while a controller is loaded.
     *
     * @var Array
     * @access private
     */
    var $_schemadb = null;

    /**
     * The person to operate on
     *
     * @var midcom_db_person
     * @access private
     */
    var $_person = null;
    
    var $_group = null;

    /**
     * The Controller of the article used for editing and (in frozen mode) for
     * delete preview.
     *
     * @var midcom_helper_datamanager2_controller_simple
     * @access private
     */
    var $_controller = null;

    var $_datamanager = null;

    /**
     * Converts given string to UTF-8
     *
     * @param string to be converted
     * @return string converted string (or original string in case we cannot convert for some reason)
     */
    function charset_convert($data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        // Some headers are multi-dimensional, recurse if needed
        if (is_array($data))
        {
            debug_add('Given data is an array, iterating trough it');
            foreach($data as $k => $v)
            {
                debug_add("Recursing key {$k}");
                $data[$k] = $this->charset_convert($v);
            }
            debug_add('Done');
            debug_pop();
            return $data;
        }
        if (empty($data))
        {
            debug_add('Data is empty, returning as is',  MIDCOM_LOG_WARN);
            debug_pop();
            return $data;
        }
        if (!function_exists('iconv'))
        {
            debug_add('Function \'iconv()\' not available, returning data as is',  MIDCOM_LOG_WARN);
            debug_pop();
            return $data;
        }
        if (!function_exists('mb_detect_encoding'))
        {
            debug_add('Function \'mb_detect_encoding()\' not available, returning data as is',  MIDCOM_LOG_WARN);
            debug_pop();
            return $data;
        }
        $encoding = false;
        $encoding = mb_detect_encoding($data, 'ASCII,JIS,UTF-8,ISO-8859-1,EUC-JP,SJIS');
        if (empty($encoding))
        {
            debug('Given/Detected encoding is empty, cannot convert, aborting', MIDCOM_LOG_WARN);
            debug_pop();
            return $data;
        }
        $encoding_lower = strtolower($encoding);
        $this_encoding_lower = strtolower('UTF-8');
        if (   $encoding_lower == $this_encoding_lower
            || (   $encoding_lower == 'ascii'
                /* ASCII is a subset of the following encodings, and thus requires no conversion to them */
                && (   $this_encoding_lower == 'utf-8'
                    || $this_encoding_lower == 'iso-8859-1'
                    || $this_encoding_lower == 'iso-8859-15')
                )
            )
        {
            debug_add("Given/Detected encoding '{$encoding}' and desired encoding 'UTF-8' require no conversion between them", MIDCOM_LOG_INFO);
            debug_pop();
            return $data;
        }
        $append_target = '//TRANSLIT';
        debug_add("Calling iconv('{$encoding_lower}', '{$this_encoding_lower}{$append_target}', \$data)");
        $stat = @iconv($encoding_lower, $this_encoding_lower . $append_target, $data);
        if (empty($stat))
        {
            debug_add("Failed to convert from '{$encoding}' to 'UTF-8'", MIDCOM_LOG_WARN);
            debug_pop();
            return $data;
        }
        debug_add("Converted from '{$encoding}' to 'UTF-8'", MIDCOM_LOG_INFO);
        debug_pop();
        return $stat;
    }

    function _make_tmp_file($frompath)
    {
        $fp_from = fopen($frompath, 'r');
        if (!$fp_from)
        {
            return false;
        }
        $topath = tempnam('', 'nnp_import_csv_');
        if (!$topath)
        {
            return false;
        }
        $fp_to = fopen($topath, 'w');
        if (!$fp_to)
        {
            return false;
        }
        
        while (!feof($fp_from))
        {
            $buffer = fread($fp_from, 1048576); // 1M buffer, CAVEAT: we might split data in the middle of a doublebyte char
            // Normalize data at this point
            $buffer = preg_replace("/\n\r|\r\n|\r/", "\n", $buffer);
            $buffer = $this->charset_convert($buffer);
            fwrite($fp_to, $buffer);
        }
        unset($buffer);
        fclose($fp_from);
        fclose($fp_to);
        
        return $topath;
    }

    function _get_mapping_info(&$data)
    {
        $data['csv_map'] = array();
        $fp = fopen($data['filepath'], 'r');
        if (!$fp)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, '_get_mapping_info() failed to open tmp file');
            // this will exit
        }
        // Read one line for comlumns
        $columns_line = fgetcsv($fp, 4096, $data['separator']);
        $data['csv_full_map'] = $columns_line;
        fclose($fp);
        foreach ($columns_line as $num => $label)
        {
            if ($label === 'GUID')
            {
                continue;
            }
            $data['csv_map'][$num] = $label;
        }
    }

    function _handler_import($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_admin_user();
        $data['l10n'] =& $this->_l10n;
        $data['l10n_midcom'] =& $this->_l10n_midcom;
        $this->_load_datamanager();
        $data['datamanager'] =& $this->_datamanager;
        switch (true)
        {
            // file and map set, do import
            case (   isset($_POST['net_nemein_personnel_import_map'])
                  && isset($_POST['net_nemein_personnel_import_tmpfile'])
                  && isset($_POST['net_nemein_personnel_import_separator'])):
                if (   !is_array($_POST['net_nemein_personnel_import_map'])
                    || empty($_POST['net_nemein_personnel_import_map']))
                {
                    // invalid column map
                    return false;
                }
                $data['column_map'] = $_POST['net_nemein_personnel_import_map'];
                if (   !is_writable($_POST['net_nemein_personnel_import_tmpfile'])
                    && strpos($_POST['net_nemein_personnel_import_tmpfile'], 'nnp_import_csv_') !== false)
                {
                    // Invalid tmpfile
                    return false;
                }
                $data['filepath'] = $_POST['net_nemein_personnel_import_tmpfile'];
                $data['separator'] = $_POST['net_nemein_personnel_import_separator'];
                // Just to validate the file
                $this->_get_mapping_info($data);
                $data['view'] = 'import-file';
                break;
            // file sent, ask for mapping info
            case (   isset($_FILES['net_nemein_personnel_import'])
                  && isset($_FILES['net_nemein_personnel_import']['tmp_name'])
                  && is_uploaded_file($_FILES['net_nemein_personnel_import']['tmp_name'])
                  && isset($_POST['net_nemein_personnel_import_separator'])):
                $data['filepath'] = $this->_make_tmp_file($_FILES['net_nemein_personnel_import']['tmp_name']);
                if (!$data['filepath'])
                {
                    return false;
                }
                $data['separator'] = $_POST['net_nemein_personnel_import_separator'];
                
                $this->_get_mapping_info($data);
                $data['view'] = 'map-columns';
                break;
            default:
                // Nothing else matches, show upload form
                $data['view'] = 'upload-form';
        }
        return true;
    }

    function _import_file(&$data)
    {
        $fp = fopen($data['filepath'], 'r');
        if (!$fp)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, '_import_file() failed to open tmp file');
            // this will exit
        }
        $this->_group = new midcom_db_group($this->_config->get('group'));
        if (!$this->_group)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, '_import_file() failed to load group ' . $this->_config->get('group'));
            // this will exit
        }
        // Read one line for comlumns
        $columns_line = fgetcsv($fp, 4096, $data['separator']);
        $data['line_no'] = 1;
        $data['person'] =& $this->_person;
        while($csv_line = fgetcsv($fp, 4096, $data['separator']))
        {
            flush();
            $data['line_no']++;
            if (!$this->_import_line($csv_line))
            {
                midcom_show_style('show-import-line-failed');
                continue;
            }
            midcom_show_style('show-import-line-ok');
        }
        fclose($fp);
        unlink($data['filepath']);
        $this->_person = null;
    }

    function _import_line_resolve_person($csv_line)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $this->_person = null;
        $data =& $this->_request_data;
        $data['new_person'] = false;
        // check for column 'GUID', mapped to: storage => 'username', storage => 'email'
        debug_print_r('Called with: ', $csv_line);
        reset($csv_line);
        foreach ($csv_line as $column_no => $column_data)
        {
            debug_add("column {$column_no}:{$column_data}");
            if (empty($column_data))
            {
                // no data, don't bother
                continue;
            }
            if (!isset($data['csv_full_map'][$column_no]))
            {
                debug_add("Couldn't figure column name for column {$column_no}", MIDCOM_LOG_WARN);
                continue;
            }
            $column_name =& $data['csv_full_map'][$column_no];
            debug_add("column name is '{$column_name}'");

            // Column name is GUID, try to fetch person
            if ($column_name == 'GUID')
            {
                debug_add("column name is 'GUID', trying fetch person by value '{$column_data}'");
                $this->_person = new midcom_db_person($column_data);
                if (!is_a($this->_person, 'midcom_db_person'))
                {
                    // Failed to get person, abort this line
                    debug_add("Failed to fetch person '{$column_data}', errstr: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                    debug_pop();
                    return false;
                }
                // Fetch ok, no further checks needed
                debug_add("Found person #{$this->_person->id} ({$this->_person->username})", MIDCOM_LOG_INFO);
                debug_pop();
                return true;
            }

            // next check where fields map to
            if (!isset($data['column_map'][$column_no]))
            {
                debug_add("Couldn't figure DM2 schema field name for column {$column_no}", MIDCOM_LOG_WARN);
                continue;
            }
            $fieldname =& $data['column_map'][$column_no];
            debug_add("DM2 schema field name is '{$fieldname}'");
            if (!isset($this->_datamanager->schema->fields[$fieldname]))
            {
                debug_add("Field '{$fieldname}' not found in DM2 schema", MIDCOM_LOG_WARN);
                continue;
            }
            $schemafield =& $this->_datamanager->schema->fields[$fieldname];
            if (   !isset($schemafield['storage'])
                || !isset($schemafield['storage']['location']))
            {
                debug_add("Field '{$fieldname}' does not have storage set", MIDCOM_LOG_WARN);
                continue;
            }
            $storage =& $schemafield['storage']['location'];
            switch ($storage)
            {
                // username and primary email considered unique enough
                case 'username':
                case 'email':
                    debug_add("Trying to find the person by field '{$storage}' (search value '{$column_data}')");
                    $qb = midcom_db_person::new_query_builder();
                    $qb->add_constraint($storage, '=', $column_data);
                    $results = $qb->execute();
                    unset($qb);
                    if (empty($results))
                    {
                        // No results, move on (switch is also loop, hence continue *2*)
                        debug_add('No results');
                        continue 2;
                    }
                    if (count($results) > 1)
                    {
                        // More then one result, move on (switch is also loop, hence continue *2*)
                        debug_add("Found more than one person with constraint '{$storage}' = '{$column_data}'", MIDCOM_LOG_WARN);
                        continue 2;
                    }
                    // Got one result, set to person and return success
                    $this->_person = $results[0];
                    debug_add("Found person #{$this->_person->id} ({$this->_person->username})", MIDCOM_LOG_INFO);
                    debug_pop();
                    return true;
                    break;
                default:
                    debug_add("Don't know how to search persons with storage '{$storage}' (in field '{$fieldname}')");
                    break;
            }
        }
        unset($column_name, $fieldname, $schemafield, $storage);
        // Could not resolve existing person, create a new one
        debug_add('No persons found, creating a new one');
        $data['new_person'] = true;

        /*
        // do this for now, we need to know why we can't resolve even existing persons
        debug_add('Creation disabled until I figure out why existing persons are not found', MIDCOM_LOG_ERROR);
        debug_pop();
        return false;
        */

        $person = new midcom_db_person();
        $person->firstname = date('Y-m-d H:i') . " import line #{$data['line_no']}";
        $person->lastname = 'UPDATEME';
        if (!$person->create())
        {
            debug_add('Failed to create new person, errstr: ' . mgd_errstr(), MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $person->set_parameter('midcom.helper.datamanager2', 'schema_name', $this->_config->get('schema'));
        $this->_person = $person;
        debug_add("Created person #{$this->_person->id}", MIDCOM_LOG_INFO);
        debug_pop();
        return true;
    }

    function _import_line_verify_membership()
    {
        $this->_group->add_member($this->_person);
    }

    function _import_line(&$csv_line)
    {
        $data =& $this->_request_data;
        if (!$this->_import_line_resolve_person($csv_line))
        {
            return false;
        }
        if (!$this->_datamanager->autoset_storage($this->_person))
        {
            return false;
        }
        reset($csv_line);
        foreach ($csv_line as $column_no => $column_data)
        {
            if (!isset($data['column_map'][$column_no]))
            {
                continue;
            }
            $fieldname =& $data['column_map'][$column_no];
            if (!isset($this->_datamanager->types[$fieldname]))
            {
                continue;
            }
            $type =& $this->_datamanager->types[$fieldname];
            switch (true)
            {
                case (is_a($type, 'midcom_helper_datamanager2_type_select')):
                    /* this method is b0rken
                    $type->convert_from_csv($column_data);
                    */
                    $value_labels = explode(',', $column_data);
                    $type->selection = array();
                    foreach ($value_labels as $label)
                    {
                        $label_key = array_search(trim($label), $type->options);
                        if ($label_key !== false)
                        {
                            $type->selection[] = $label_key;
                        }
                    }
                    break;
                default:
                    $type->value = str_replace('\\n', "\n", $column_data);
            }
        }
        unset($fieldname, $type);
        if (!$this->_datamanager->save())
        {
            return false;
        }
        $this->_import_line_verify_membership();
        return true;
    }

    function _show_import($handler_id, &$data)
    {
        switch ($data['view'])
        {
            case 'import-file':
                // Get us to live mode for real
                $_MIDCOM->cache->content->enable_live_mode();
                while(@ob_end_flush());
                // Do the import
                $this->_import_file($data);
                // re-enable ob to keep $_MIDCOM->cache->content happy
                ob_start();
                break;
            default:
                midcom_show_style("show-import-{$data['view']}");
        }
    }

    /**
     * This function creates a DM2 Datamanager instance to without any set storage so far.
     * The configured schema will be selected, but no set_storage is done. The various
     * view handlers treat this differently.
     */
    function _load_datamanager()
    {
        $this->_datamanager = new midcom_helper_datamanager2_datamanager(
            midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb')));

        if (   ! $this->_datamanager
            || ! $this->_datamanager->set_schema($this->_config->get('schema')))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to create a DM2 instance.');
            // This will exit.
        }
    }

    /**
     * Internal helper which wraps the membership->person transformation in an
     * ACL safe way.
     *
     * @param Array $membership A resultset that was queried using midcom_baseclasses_database_member::new_query_builder()
     * @return Array An array of midcom_baseclasses_database_person() objects.
     */
    function _get_persons_for_memberships($memberships)
    {
        $result = Array();
        foreach ($memberships as $membership)
        {
            $person = new midcom_db_person($membership->uid);
            if (   $person
                && $person->is_object_visible_onsite())
            {
                // We have access to the person.
                $result[] = $person;
            }
        }
        return $result;
    }

    function _encode_csv($data, $add_separator = true, $add_newline = false)
    {
        /* START: Quick'n'Dirty on-the-fly charset conversion */
        if (function_exists('iconv'))
        {
            $append_target = $this->_config->get('iconv_append_target');
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
            $data = str_replace('.', $this->csv['s'], $data);
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

    function _init_csv_variables()
    {
        if (   !isset($this->csv['s'])
            || empty($this->csv['s']))
        {
            $this->csv['s'] = $this->_config->get('csv_export_separator');
        }
        if (   !isset($this->csv['q'])
            || empty($this->csv['q']))
        {
            $this->csv['q'] = $this->_config->get('csv_export_quote');
        }
        if (   !isset($this->csv['d'])
            || empty($this->csv['d']))
        {
            $this->csv['d'] = $this->_config->get('csv_export_decimal');
        }
        if (   !isset($this->csv['nl'])
            || empty($this->csv['nl']))
        {
            $this->csv['nl'] = $this->_config->get('csv_export_newline');
        }
        if (   !isset($this->csv['charset'])
            || empty($this->csv['charset']))
        {
            $this->csv['charset'] = $this->_config->get('csv_export_charset');
        }
        if ($this->csv['s'] == $this->csv['d'])
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "CSV decimal separator (configured as '{$this->csv['d']}') may not be the same as field separator (configured as '{$this->csv['s']}')");
        }
    }


    function _handler_export($handler_id, $args, &$data)
    {
        //Disable limits
        @ini_set('memory_limit', -1);
        @ini_set('max_execution_time', 0);
        debug_push_class(__CLASS__, __FUNCTION__);
        if (   !isset($args[0])
            || empty($args[0]))
        {
            debug_add('Filename part not specified in URL, generating');
            //We do not have filename in URL, generate one and redirect
            $fname = preg_replace('/[^a-z0-9-]/i', '_', strtolower($this->_topic->extra)) . '_' . date('Y-m-d') . '.csv';
            $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
            debug_pop();
            $_MIDCOM->relocate("{$prefix}csv/export/{$fname}");
            // This will exit
        }
        $this->_load_datamanager();

        $qb = midcom_db_member::new_query_builder();
        $qb->add_constraint('gid.guid', '=', $this->_config->get('group'));

        foreach ($this->_config->get('index_order') as $ordering)
        {
            $qb->add_order($ordering);
        }

        $qb->hide_invisible = false;

        $this->_persons = $this->_get_persons_for_memberships($qb->execute());

        $this->_init_csv_variables();
        $_MIDCOM->skip_page_style = true;
        $_MIDCOM->cache->content->content_type($this->_config->get('csv_export_content_type'));
        //$_MIDCOM->cache->content->content_type('text/plain');
        debug_pop();
        return true;
    }

    function _show_export($handler_id, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        // Make absolutely sure we're in live output
        $_MIDCOM->cache->content->enable_live_mode();
        while(@ob_end_flush());
        // Output headers
        echo $this->_encode_csv('GUID', true, false);
        $i = 0;
        $datamanager =& $this->_datamanager;
        foreach ($datamanager->schema->field_order as $name)
        {
            $i++;
            if ($i < count($datamanager->schema->field_order))
            {
                echo $this->_encode_csv($datamanager->schema->fields[$name]['title'], true, false);
            }
            else
            {
                echo $this->_encode_csv($datamanager->schema->fields[$name]['title'], false, true);
            }
        }

        foreach ($this->_persons as $person)
        {
            // Finalize the request data
            $this->_person = $person;
            $datamanager->set_storage($this->_person);
            echo $this->_encode_csv($person->guid, true, false);
            $i = 0;
            foreach ($datamanager->schema->field_order as $fieldname)
            {
                $i++;
                $data = '';
                $data = $datamanager->types[$fieldname]->convert_to_csv();
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
