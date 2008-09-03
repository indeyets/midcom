<?php
/**
 * @package org.openpsa.products
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: groups.php 3991 2006-09-07 11:28:16Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * @package org.openpsa.products
 */
class org_openpsa_products_handler_product_csvimport extends midcom_baseclasses_components_handler
{
    var $_datamanager = null;
    var $_products_processed = array();

    function __construct()
    {
        parent::midcom_baseclasses_components_handler();
    }

    function _prepare_handler($args)
    {
        // Mass importing is for now better left for admins only
        // TODO: Add smarter per-type ACL checks
        $_MIDCOM->auth->require_admin_user();
        $this->_request_data['type'] = 'product';

        $this->_request_data['import_status'] = array
        (
            'updated' => 0,
            'created' => 0,
            'failed_create' => 0,
            'failed_update' => 0,
        );

        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_request_data['schemadb_product']);

        //Disable limits
        // TODO: Could this be done more safely somehow
        @ini_set('memory_limit', -1);
        @ini_set('max_execution_time', 0);
    }

    function _datamanager_process($productdata, $object)
    {
        // Load datamanager2 for the object
        if (!$this->_datamanager->set_storage($object))
        {
            return false;
        }

        // Set all given values into DM2
        foreach ($productdata as $key => $value)
        {
            if (array_key_exists($key, $this->_datamanager->types))
            {
                if (is_a($this->_datamanager->types[$key], 'midcom_helper_datamanager2_type_date'))
                {
                    $this->_datamanager->types[$key]->value = new Date($value);
                }
                else
                {
                    $this->_datamanager->types[$key]->value = $value;
                }
            }
        }

        // Save the object
        if (!$this->_datamanager->save())
        {
            return false;
        }

        return true;
    }


    function _import_product($productdata)
    {
        // Convert fields from latin-1 to MidCOM charset (usually utf-8)
        foreach ($productdata as $key => $value)
        {
            $productdata[$key] = iconv('ISO-8859-1', $_MIDCOM->i18n->get_current_charset(), $value);
        }

        $product = null;
        $new = false;

        if (isset($productdata['unixname']))
        {
            $productdata['code'] = $productdata['unixname'];
        }

        if (isset($productdata['code']))
        {
            $qb = org_openpsa_products_product_dba::new_query_builder();
            $qb->add_constraint('code', '=', (string) $productdata['code']);
            
            $products = $qb->execute();
            if (count($products) > 0)
            {
                // Match found, use it
                $product = $products[0];
            }
        }

        if (!$product)
        {
            // We didn't have group matching the code in DB. Create a new one.
            $product = new org_openpsa_products_product_dba();

            if (!$product->create())
            {
                debug_add("Failed to create group, reason " . mgd_errstr());
                $this->_request_data['import_status']['failed_create']++;
                return false;
                // This will skip to next
            }
            $new = true;
        }

        if (isset($productdata['org_openpsa_products_import_parent_group']))
        {
            // Validate and set parent group
            $parent = null;
            if (is_numeric(trim($productdata['org_openpsa_products_import_parent_group'])))
            {
                // Try if the parent identifier is a database ID
                $parent = new org_openpsa_products_product_group_dba((int) $productdata['org_openpsa_products_import_parent_group']);
            }
            
            if (   !$parent
                && mgd_is_guid($productdata['org_openpsa_products_import_parent_group']))
            {
                // The parent identifier is a GUID
                $parent = new org_openpsa_products_product_group_dba($productdata['org_openpsa_products_import_parent_group']);
            }
            
            if (!$parent)
            {
                // Seek via code
                $qb = org_openpsa_products_product_group_dba::new_query_builder();
                $qb->add_constraint('code', '=', (string) $productdata['org_openpsa_products_import_parent_group']);
                $parents = $qb->execute();
                if (count($parents) > 0)
                {
                    $parent = $parents[0];
                }
            }
            
            if (!$parent)
            {
                // Invalid parent, delete
                if ($new)
                {
                    $product->delete();
                    $this->_request_data['import_status']['failed_create']++;
                }
                else
                {
                    $this->_request_data['import_status']['failed_update']++;
                }
                return false;
            }

            $product->productGroup = $parent->id;
            $groupdata['productGroup'] = $parent->id;
            $product->update();
        }

        if (!$this->_datamanager_process($productdata, $product))
        {
            if ($new)
            {
                $product->delete();
                $this->_request_data['import_status']['failed_create']++;
            }
            else
            {
                $this->_request_data['import_status']['failed_update']++;
            }
            return false;
        }

        $this->_products_processed[$product->code] = $product;

        if ($new)
        {
            $this->_request_data['import_status']['created']++;
        }
        else
        {
            $this->_request_data['import_status']['updated']++;
        }

        return $product;
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_csv_select($handler_id, $args, &$data)
    {
        $this->_prepare_handler($args);

        if (isset($_POST['org_openpsa_products_import_schema']))
        {
            $data['schema'] = $_POST['org_openpsa_products_import_schema'];
        }
        else
        {
            $data['schema'] = 'default';
        }
        $this->_datamanager->set_schema($data['schema']);

        if (array_key_exists('org_openpsa_products_import_separator', $_POST))
        {
            $data['time_start'] = time();

            $data['rows'] = array();

            switch ($_POST['org_openpsa_products_import_separator'])
            {
                case ';':
                    $data['separator'] = ';';
                    break;

                case ',':
                default:
                    $data['separator'] = ',';
                    break;
            }


            if (is_uploaded_file($_FILES['org_openpsa_products_import_upload']['tmp_name']))
            {
                // Copy the file for later processing
                $data['tmp_file'] = tempnam($GLOBALS['midcom_config']['midcom_tempdir'], 'org_openpsa_products_import_csv');
                $src = fopen($_FILES['org_openpsa_products_import_upload']['tmp_name'], 'r');
                $dst = fopen($data['tmp_file'], 'w+');
                while (! feof($src))
                {
                    $buffer = fread($src, 131072); /* 128 kB */
                    fwrite($dst, $buffer, 131072);
                }
                fclose($src);
                fclose($dst);

                // Read cell headers from the file
                $read_rows = 0;
                $handle = fopen($_FILES['org_openpsa_products_import_upload']['tmp_name'], 'r');
                $separator = $data['separator'];
                $total_columns = 0;
                while (   $read_rows < 2
                       && $csv_line = fgetcsv($handle, 3000, $separator))
                {
                    if ($total_columns == 0)
                    {
                        $total_columns = count($csv_line);
                    }
                    $columns_with_content = 0;
                    foreach ($csv_line as $value)
                    {
                        if ($value != '')
                        {
                            $columns_with_content++;
                        }
                    }
                    $percentage = round(100 / $total_columns * $columns_with_content);

                    if ($percentage >= $this->_config->get('import_csv_data_percentage'))
                    {
                        $data['rows'][] = $csv_line;
                        $read_rows++;
                    }
                }
            }

            $data['time_end'] = time();
        }

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_csv_select($handler_id, &$data)
    {
        if (array_key_exists('rows', $data))
        {
            // Present user with the field matching form
            $data['schemadb'] = $data['schemadb_product'];
            midcom_show_style('show-import-csv-select');
        }
        else
        {
            // Present user with upload form
            midcom_show_style('show-import-csv-form');
        }
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_csv($handler_id, $args, &$data)
    {
        $this->_prepare_handler($args);

        $data['groups'] = array();

        if (!array_key_exists('org_openpsa_products_import_separator', $_POST))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'No CSV separator specified.');
            // This will exit.
        }
        
        if (!array_key_exists('org_openpsa_products_import_schema', $_POST))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'No schema specified.');
            // This will exit.
        }

        if (!file_exists($_POST['org_openpsa_products_import_tmp_file']))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'No CSV file available.');
            // This will exit.
        }

        $data['time_start'] = time();

        $data['rows'] = array();
        $data['separator'] = $_POST['org_openpsa_products_import_separator'];
        $data['schema'] = $_POST['org_openpsa_products_import_schema']; 
        $this->_datamanager->set_schema($data['schema']);       

        // Start processing the file
        $read_rows = 0;
        $total_columns = 0;
        $handle = fopen($_POST['org_openpsa_products_import_tmp_file'], 'r');
        $separator = $data['separator'];

        while ($csv_line = fgetcsv($handle, 3000, $separator))
        {

            if ($total_columns == 0)
            {
                $total_columns = count($csv_line);
            }
            $columns_with_content = 0;
            foreach ($csv_line as $value)
            {
                if ($value != '')
                {
                    $columns_with_content++;
                }
            }
            $percentage = round(100 / $total_columns * $columns_with_content);

            if ($percentage >= $this->_config->get('import_csv_data_percentage'))
            {
                $data['rows'][] = $csv_line;
                $read_rows++;
            }
            else
            {
                // This line has no proper content, skip
                continue;
            }

            $product = array();

            if ($read_rows == 1)
            {
                // First line is headers, skip
                continue;
            }
            foreach ($csv_line as $field => $value)
            {
                // Some basic CSV format cleanup
                $value = str_replace('\\n', "\n", $value);
                $value = str_replace("\\\n", "\n", $value);

                // Process the row accordingly
                $field_matching = $_POST['org_openpsa_products_import_csv_field'][$field];
                if ($field_matching)
                {
                    $schema_field = $field_matching;

                    if (   !array_key_exists($schema_field, $data['schemadb_product'][$data['schema']]->fields)
                        && $schema_field != 'org_openpsa_products_import_parent_group')
                    {
                        // Invalid matching, skip
                        continue;
                    }

                    if (   $value == ''
                        || $value == 'NULL'
                        || preg_match('/^#+$/',  $value))
                    {
                        // No value, skip
                        continue;
                    }

                    $product[$schema_field] = $value;
                }
            }

            if (count($product) > 0)
            {
                $data['groups'][] = $product;
            }
        }

        $secondary_products = array();
        $tertiary_products = array();

        if (count($data['groups']) > 0)
        {
            foreach ($data['groups'] as $product)
            {
                if (isset($product['org_openpsa_products_import_parent_group']))
                {
                    $qb = org_openpsa_products_product_group_dba::new_query_builder();
                    $qb->add_constraint('code', '=', (string) $product['org_openpsa_products_import_parent_group']);
                    if ($qb->count() == 0)
                    {
                        // Parent not found, process later
                        $secondary_products[] = $product;
                        continue;
                    }
                }
                $this->_import_product($product);
            }
        }

        if (count($secondary_products) > 0)
        {
            foreach ($secondary_products as $product)
            {
                if (isset($product['org_openpsa_products_import_parent_group']))
                {
                    $qb = org_openpsa_products_product_group_dba::new_query_builder();
                    $qb->add_constraint('code', '=', (string) $product['org_openpsa_products_import_parent_group']);
                    if ($qb->count() == 0)
                    {
                        // Parent not found, process later
                        $tertiary_products[] = $product;
                        continue;
                    }
                }
                $this->_import_product($product);
            }
        }

        $data['time_end'] = time();

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_csv($handler_id, &$data)
    {
        midcom_show_style('show-import-status');
    }
}
?>