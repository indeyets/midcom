<?php
if (!class_exists('HTMLPurifier'))
{
    require('HTMLPurifier.php');
}
/**
 * Helper for importing directory of HTML files as n.n.static content tree
 *
 * @package fi.hut.htmlimport
 */
class fi_hut_htmlimport_importer extends midcom_baseclasses_components_purecode
{
    var $purifier = false;
    var $purifier2 = false;
    var $_schemadb = false;
    var $_schema = false;
    var $_dm2 = false;
    var $rulesets = false;
    var $ruleset = false;
    var $field_map = false;
    var $encoding = 'UTF-8';

    /**
     * Contsructors, loads configurations and tries to select the default
     * ruleset as active
     *
     * @see select_ruleset()
     */
    function __construct()
    {
        $this->_component = 'fi.hut.htmlimport';
        parent::midcom_baseclasses_components_purecode();

        $purifier_common_config = array
        (
            'Cache' => array
            (
                'SerializerPath' => $GLOBALS['midcom_config']['cache_base_directory'] . 'htmlpurifier',
            ),
        );

        if (isset($purifier_common_config['Cache']['SerializerPath']) 
            && !file_exists($purifier_common_config['Cache']['SerializerPath']))
        {
            mkdir($purifier_common_config['Cache']['SerializerPath']);
        }

        $this->purifier = new HTMLPurifier($purifier_common_config);
        $this->purifier->config->set('HTML', 'EnableAttrID', true);
        $this->purifier->config->set('HTML', 'Doctype', 'XHTML 1.0 Strict');
        $this->purifier->config->set('HTML', 'TidyLevel', 'light');
        $this->purifier->config->set('Core', 'EscapeNonASCIICharacters', true);

        $this->purifier2 = new HTMLPurifier($purifier_common_config);
        $this->purifier2->config->set('HTML', 'Doctype', 'XHTML 1.0 Strict');
        $this->purifier2->config->set('HTML', 'TidyLevel', 'heavy');
        $this->purifier2->config->set('Core', 'EscapeNonASCIICharacters', true);

        $this->rulesets = $this->_config->get('rulesets');
        $this->select_ruleset($this->_config->get('default_ruleset'));
    }

    /**
     * Selects given ruleset as active ruleset
     *
     * @param string $ruleset name of ruleset (key of the config rulesets array)
     * @return bool indicating success/failure
     */
    function select_ruleset($ruleset)
    {
        if (!isset($this->rulesets[$ruleset]))
        {
            return false;
        }
        $this->ruleset = $ruleset;
        $this->field_map = $this->rulesets[$this->ruleset]['field_map'];
        $this->_load_dm2();
        return true;
    }

    /**
     * Converts given string to $this->encoding, copied from org_openpsa_mail
     *
     * @param string to be converted
     * @param string encoding from header or such, used as default in case mb_detect_endoding is not available
     * @return string converted string (or original string in case we cannot convert for some reason)
     */
    function charset_convert($data, $given_encoding = false)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        // Some headers are multi-dimensional, recurse if needed
        if (is_array($data))
        {
            debug_add('Given data is an array, iterating trough it');
            foreach($data as $k => $v)
            {
                debug_add("Recursing key {$k}");
                $data[$k] = $this->charset_convert($v, $given_encoding);
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
        $encoding = false;
        if (   !function_exists('mb_detect_encoding')
            && !empty($given_encoding))
        {
            $encoding =& $given_encoding;
        }
        else
        {
            $encoding = mb_detect_encoding($data, $this->_config->get('mb_detect_encoding_list'));
        }
        if (empty($encoding))
        {
            debug('Given/Detected encoding is empty, cannot convert, aborting', MIDCOM_LOG_WARN);
            debug_pop();
            return $data;
        }
        $encoding_lower = strtolower($encoding);
        $this_encoding_lower = strtolower($this->encoding);
        if (   $encoding_lower == $this_encoding_lower
            || (   $encoding_lower == 'ascii'
                /* ASCII is a subset of the following encodings, and thus requires no conversion to them */
                && (   $this_encoding_lower == 'utf-8'
                    || $this_encoding_lower == 'iso-8859-1'
                    || $this_encoding_lower == 'iso-8859-15')
                )
            )
        {
            debug_add("Given/Detected encoding '{$encoding}' and desired encoding '{$this->encoding}' require no conversion between them", MIDCOM_LOG_INFO);
            debug_pop();
            return $data;
        }
        $append_target = $this->_config->get('iconv_append_target');
        debug_add("Calling iconv('{$encoding_lower}', '{$this_encoding_lower}{$append_target}', \$data)");
        $stat = @iconv($encoding_lower, $this_encoding_lower . $append_target, $data);
        if (empty($stat))
        {
            debug_add("Failed to convert from '{$encoding}' to '{$this->encoding}'", MIDCOM_LOG_WARN);
            debug_pop();
            return $data;
        }
        debug_add("Converted from '{$encoding}' to '{$this->encoding}'", MIDCOM_LOG_INFO);
        debug_pop();
        return $stat;
    }

    /**
     * Internal helper to instance DM2 for active configruation
     * @see select_ruleset()
     */
    function _load_dm2()
    {
        $this->_load_schemadb();
        $this->_dm2 = new midcom_helper_datamanager2_datamanager($this->_schemadb);
        if (!$this->_dm2->set_schema($this->_schema))
        {
            /*
            echo "DEBUG: \$this->_schemadb<pre>\n";
            ob_start();
            var_dump($this->_schemadb);
            $schemadb_r = ob_get_contents();
            ob_end_clean();
            echo htmlentities($schemadb_r);
            unset($schemadb_r);
            echo "</pre>\n";
            */
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "\$this->_dm2->set_schema({$this->_schema}) failed");
        }
    }

    /**
     * Internal helper to load DM2 schemadb for active configruation
     * @see _load_dm2()
     */
    function _load_schemadb()
    {
        $this->_schemadb = midcom_helper_datamanager2_schema::load_database($this->rulesets[$this->ruleset]['schemadb']);
        if (!$this->_schemadb)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to load schemadb from {$this->rulesets[$this->ruleset]['schemadb']}");
        }
        $this->_schema = $this->rulesets[$this->ruleset]['schema_name'];
        return true;
    }

    /**
     * Parses given path to fi_hut_htmlimport_importer_file object populated according
     * to the selected ruleset
     * 
     * @param string $path full path to file
     * @return object populated fi_hut_htmlimport_importer_file instance, or null on failure
     */
    function parse_file($path)
    {
        //echo "DEBUG: parsing file {$path}<br>\n";
        $file = new fi_hut_htmlimport_importer_file();
        $file->name = preg_replace('/\.html?$/', '', basename($path));
        $file->schema = $this->_schema;

        $file_data_raw = file_get_contents($path);
        $file_data_raw = $this->charset_convert($file_data_raw);
        /*
        echo "DEBUG: file_data_raw<pre>\n";
        echo htmlentities($file_data_raw);
        echo "</pre>\n";
        */
        // HTMLpurifier doesn't allow IDs starting with underscore
        $file_data_purified = preg_replace("%id=(['\"])_(.*?)\\1%", "id=\\1\\2\\1", $file_data_raw);
        /* I wonder about this, maybe xpath doesn't like spans ??
        $file_data_purified = str_replace('span', 'div', $file_data_purified);
        */
        // Sanitize XHTML here
        $file_data_purified = $this->purifier->purify($file_data_purified);

        // PONDER: we could skip this if we have no xpath rules
        $simplexml = @simplexml_load_string($file_data_purified);
        if (!$simplexml)
        {
            echo "WARN: Could not parse file {$path} with simplexml<br>\n";
            return;
        }

        // PONDER: some logic for schema selection based on file_data_purified ??

/*
        echo "DEBUG: file_data_purified<pre>\n";
        echo htmlentities($file_data_purified);
        echo "</pre>\n";
*/

        $field_set = array();
        foreach ($this->field_map as $map)
        {
            $field_value = '';
            if (   !isset($map['field'])
                || empty($map['field']))
            {
                // No field set at all!
                continue;
            }
            if (isset($field_set[$map['field']]))
            {
                // Field already has value set by us
                continue;
            }
            if (   !isset($map['type'])
                || empty($map['type']))
            {
                // No type set at all!
                continue;
            }
            switch ($map['type'])
            {
                /**
                 * NOTE: When adding new match types remember to update the USAGE.lang.txt file(s)
                 * in the documentation folder
                 */
                case 'preg_match':
                    if (   !isset($map['regex'])
                        || empty($map['regex']))
                    {
                        // invalid type config
                        continue 2;
                    }
                    if (!isset($map['matches_key']))
                    {
                        $map['matches_key'] = 0;
                    }
                    $matches = array();
                    if (   !preg_match($map['regex'], $file_data_raw, $matches)
                        || !isset($matches[$map['matches_key']]))
                    {
                        $regex_safe = htmlentities($map['regex']);
                        //echo "DEBUG: no preg_match for {$regex_safe} (field: {$map['field']})<br>\n";
                        // no valid match
                        continue 2;
                    }
                    /*
                    $regex_safe = htmlentities($map['regex']);
                    echo "DEBUG: matches for {$regex_safe}<pre>\n";
                    ob_start();
                    var_dump($matches);
                    $matches_r = ob_get_contents();
                    ob_end_clean();
                    echo htmlentities($matches_r);
                    unset($matches_r);
                    echo "</pre>\n";
                    */
                    $field_value = $matches[$map['matches_key']]; 
                    break;
                case 'xpath':
                    if (   !isset($map['path'])
                        || empty($map['path']))
                    {
                        // invalid type config
                        continue 2;
                    }
                    if (!isset($map['matches_key']))
                    {
                        $map['matches_key'] = -1;
                    }
                    $matches = $simplexml->xpath($map['path']);
                    if (   empty($matches)
                        || (   $map['matches_key'] !== -1
                            && !isset($matches[$map['matches_key']]))
                        )
                    {
                        // No valid match
                        $path_safe = htmlentities($map['path']);
                        //echo "DEBUG: no xpath match for {$path_safe} (field: {$map['field']})<br>\n";
                        continue 2;
                    }
                    /*
                    echo "DEBUG: matches for {$map['path']}<pre>\n";
                    ob_start();
                    var_dump($matches);
                    $matches_r = ob_get_contents();
                    ob_end_clean();
                    echo htmlentities($matches_r);
                    unset($matches_r);
                    echo "</pre>\n";
                    */
                    if ($map['matches_key'] !== -1)
                    {
                        // specific key
                        $field_value = (string)$matches[$map['matches_key']];
                        break;
                    }
                    // all keys
                    foreach ($matches as $match)
                    {
                        /* I have no iade why the original code did something like this, probably to avoid htmlpurifier errors
                        $match_string = (string)$match;
                        if (empty($match_string))
                        {
                            continue;
                        }
                        */
                        $field_value .= (string)$match->asXml();
                    }
                    break;
                default:
                    // type not supported
                    echo "WARN: mapping type {$map['type']} not supported<br>\n";
                    continue 2;
            }
            $field_value = trim($field_value);
            // PONDER: allow empty values ??
            if (empty($field_value))
            {
                echo "WARN: mapped to empty value, skipping<br>\n";
                continue;
            }
            if (!isset($map['purify']))
            {
                $map['purify'] = true;
            }

            // In fact this should be unneccessary since DM2 does it for us...
            if ($map['purify'])
            {
                $field_value = trim($this->purifier2->purify($field_value));
            }

            // Store value for future saving via DM2
            $file->field_data[$map['field']] = $field_value;
            if (property_exists($file, $map['field']))
            {
                // Set any fields that we have also in the file object
                $file->$map['field'] = $field_value;
            }

            $field_set[$map['field']] = true;
        }

        if (empty($file->title))
        {
            $file->title = ucfirst($file->name);
        }

        return $file;
    }

    /**
     * Parses files in given path, returns fi_hut_htmlimport_importer_folder object usable with import_folder
     *
     * @param string $path full path to directory to parse
     * @return object instance of fi_hut_htmlimport_importer_folder or false on critical failure
     * @see parse_file()
     * @see import_folder()
     */
    function list_files($path)
    {
        if (empty($this->field_map))
        {
            echo "ERROR: no filed map, have you called select_ruleset yet ?<br>\n";
            return false;
        }

        $files = array();
        $directory = dir($path);
        
        $folder = new fi_hut_htmlimport_importer_folder();
        $folder->name = basename($path);
        $folder->title = ucfirst(basename($path));
        
        $index = false;

        while (false !== ($entry = $directory->read())) 
        {
            if (substr($entry, 0, 1) == '.')
            {
                // Ignore dotfiles
                continue;
            }
    
            if (is_dir("{$path}/{$entry}"))
            {
                // Recurse deeper
                $folder->folders[] = $this->list_files("{$path}/{$entry}");
            }
            else
            {
                $path_parts = pathinfo($entry);

                if (preg_match('/^index\.html?$/', $path_parts['basename']))
                {
                    $folder->has_index = true;
                }
                if (preg_match('/html?$/', $path_parts['extension']))
                {
                    
                    $file = $this->parse_file("{$path}/{$entry}");
                    if (!is_null($file))
                    {
                        $folder->files[] = $file;
                    }
                }        
            }
        }
        
        $directory->close();
        
        return $folder;
    }

    /**
     * Import a given fi_hut_htmlimport_importer_folder object (including
     * files and subfolders)
     *
     * @see list_files()
     * @see import_file()
     * @param fi_hut_htmlimport_importer_folder $folder to import
     * @param int $parent_id id of topic to import to
     * @return bool indicating success/failure
     */
    function import_folder($folder, $parent_id)
    {
        //echo "DEBUG: importing folder '{$folder->name}' to parent #{$parent_id}<br>\n";
        $qb = midcom_db_topic::new_query_builder();
        $qb->add_constraint('up', '=', (int) $parent_id);
        $qb->add_constraint('name', '=', $folder->name);
        $existing = $qb->execute();
        if (   count($existing) > 0
            && $existing[0]->up == $parent_id)
        {
            $topic = $existing[0];
            echo "Using existing topic {$topic->name} (#{$topic->id}) from #{$topic->up}<br/>\n";
        }
        else
        {
            $topic = new midcom_db_topic();
            $topic->up = $parent_id;
            $topic->name = $folder->name;
            if (!$topic->create())
            {
                echo "Failed to create folder {$folder->name}: " . mgd_errstr() . "<br/>\n";
                return false;
            }
            echo "Created folder {$topic->name} (#{$topic->id}) under #{$topic->up}<br/>\n";
        }

        $topic->extra = $folder->title;
        $topic->component = $folder->component;
        $topic->update();

        if ($folder->component == 'net.nehmer.static')
        {
            if (!$folder->has_index)
            {
                $topic->parameter('net.nehmer.static', 'autoindex', 1);
            }
            else
            {
                $topic->parameter('net.nehmer.static', 'autoindex', '');
            }
        }

        foreach ($folder->files as $file)
        {
            if (!$this->import_file($file, $topic->id))
            {
                echo "ERROR: Failed to import file {$file->name} to #{$topic->id}<br>\n";
                // PONDER: abort ??
            }
        }

        foreach ($folder->folders as $subfolder)
        {
            if (!$this->import_folder($subfolder, $topic->id))
            {
                echo "ERROR: Failed to import subfolder {$subfolder->name} to #{$topic->id}<br>\n";
                // PONDER: abort ??
            }
        }
        
        return true;
    }

    /**
     * Import a given fi_hut_htmlimport_importer_file object
     *
     * @see list_files()
     * @see import_folder()
     * @param fi_hut_htmlimport_importer_file $file to import
     * @param int $parent_id id of topic to import to
     * @return bool indicating success/failure
     */
    function import_file($file, $parent_id)
    {
        //echo "DEBUG: importing file '{$file->name}' to topic #{$parent_id}<br>\n";
        $qb = midcom_db_article::new_query_builder();
        $qb->add_constraint('topic', '=', $parent_id);
        $qb->add_constraint('name', '=', $file->name);
        $existing = $qb->execute();
        if (   count($existing) > 0
            && $existing[0]->topic == $parent_id)
        {
            $article = $existing[0];
            echo "Using existing article {$article->name} (#{$article->id}) from #{$article->topic}<br/>\n";
        }
        else
        {
            $article = new midcom_db_article();
            $article->topic = $parent_id;
            $article->name = $file->name;
            if (!$article->create())
            {
                echo "Failed to create article {$article->name}: " . mgd_errstr() . "<br/>\n";
                return false;
            }
            echo "Created article {$article->name} (#{$article->id}) under #{$article->topic}<br/>\n";
        }
        $article->set_parameter('midcom.helper.datamanager2', 'schema_name', $file->schema);

        if (!$this->_dm2->autoset_storage($article))
        {
            echo "ERROR: \$this->_dm2->autoset_storage(\$article) failed<br>\n";
            /*
            echo "DEBUG: \$article<pre>\n";
            ob_start();
            var_dump($article);
            $article_r = ob_get_contents();
            ob_end_clean();
            echo htmlentities($article_r);
            unset($article_r);
            echo "</pre>\n";
            echo "DEBUG: \$file<pre>\n";
            ob_start();
            var_dump($file);
            $file_r = ob_get_contents();
            ob_end_clean();
            echo htmlentities($file_r);
            unset($file_r);
            echo "</pre>\n";
            echo "DEBUG: \$this->_dm2<pre>\n";
            ob_start();
            var_dump($this->_dm2);
            $dm2_r = ob_get_contents();
            ob_end_clean();
            echo htmlentities($dm2_r);
            unset($dm2_r);
            echo "</pre>\n";
            */
            return false;
        }
        $types =& $this->_dm2->types;

        foreach($file->field_data as $field => $value)
        {
            if (!isset($types[$field]))
            {
                continue;
            }
            $type =& $types[$field];
            switch (true)
            {
                /**
                 * NOTE: When adding support for new datatypes remember to update the 
                 * USAGE.lang.txt file(s) in the documentation folder
                 */
                case (is_a($type, 'midcom_helper_datamanager2_type_text')):
                    $type->value = $value;
                    break;
                default:
                    // Don't know how to handle type xxx
                    echo "WARN: DM2 datatype " . get_class($type) . " not supported <br>\n";
                    continue 2;
            }
            // Any other per-field processing ??
        }

        if (!$this->_dm2->save())
        {
            echo "ERROR: Saving article #{$article->id} failed, errstr: " . mgd_errstr() . "<br>\n";
            return false;
        }

        // PONDER: Call net_nehmer_static_viewer::index ??

        return true;
    }
}

/**
 * Trivial helper classes used by fi_hut_htmlimport_importer
 */

/**
 * Helper class, this represents a folder in the hierarchy
 *
 * @package fi.hut.htmlimport
 * @see fi_hut_htmlimport_importer::import_folder()
 */
class fi_hut_htmlimport_importer_folder
{
    var $name = '';
    var $title = '';
    var $has_index = false;
    var $component = 'net.nehmer.static';
    var $folders = array();
    var $files = array();
}

/**
 * Helper class, this represents a folder in the hierarchy
 *
 * @package fi.hut.htmlimport
 * @see fi_hut_htmlimport_importer_folder
 * @see fi_hut_htmlimport_importer::import_file()
 */
class fi_hut_htmlimport_importer_file
{
    var $name = '';
    var $title = '';
    var $abstract = '';
    var $content = '';
    var $schema = 'default';
    /**
     * Keyed by schema field names, values are passed on to the datatype of the field
     */
    var $field_data = array();
}
?>