<?php
/**
 * Created on Aug 16, 2005
 * @author tarjei huse
 * @package no.bergfald.rcs
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
class midcom_services_rcs_backend_rcs extends midcom_services_rcs_backend 
{
    /**
     * GUID of the current object
     */
    var $_guid;
    
    /**
     * Cached revision history for the object
     */
    var $_history;

    function midcom_services_rcs_backend_rcs(&$object, &$config)
    {
        $this->_guid = $object->guid;
        parent::midcom_services_rcs_backend($object, $config);
    }
    
    function _generate_rcs_filename($object)
    {
        if (!isset($object->guid))
        {
            return null;
        }
    
        $filename = $this->config->get_rcs_root() . "/{$object->guid}";
        
        if (   isset($object->lang)
            && $object->lang != 0)
        {
            // Append language code to the filename
            $filename .= '_' . $_MIDCOM->i18n->get_content_language();
        }
        
        return $filename;
    }
    
    /**
     * Save a new revision
     * @param object object to be saved
     * @return boolean true on success.
     */
    function update(&$object, $updatemessage = null) 
    {
        // Store user idenfitier and IP address to the update string
        if ($_MIDCOM->auth->user)
        {
            $update_string = "{$_MIDCOM->auth->user->id}|{$_SERVER['REMOTE_ADDR']}";
        }
        else
        {
            $update_string = "NOBODY|{$_SERVER['REMOTE_ADDR']}";
        }
        
        // Generate update message if needed
        if (!$updatemessage)
        {
            if ($_MIDCOM->auth->user !== null) 
            {
                $updatemessage = sprintf("Updated on %s by %s", date("D d.M Y",time()), $_MIDCOM->auth->user->name);
            } 
            else 
            {
                $updatemessage = sprintf("Updated on %s.", date("D d.M Y",time()));
            }
        }
        $update_string .= "|{$updatemessage}";
        
        $result = $this->rcs_update(&$object, $update_string);
        
        if ($result > 0 ) 
        { 
            return false;
        } 
        return true;     
    }
    
    /**
     * This function takes an object and updates it to RCS, it should be
     * called just before $object->update(), if the type parameter is omitted
     * the function will use GUID to determine the type, this makes an
     * extra DB query.
     * @param string root of rcs directory.
     * @param object object to be updated.
     * @param boolean links2guids - wther to turn links into guids. NOT IN USE
     * @return int :
     *      0 on success
     *      3 on missing object->guid
     *      nonzero on error in one of the commands.
     */
    function rcs_update ($object, $message)
    {  
        $status = null;
     
        $guid = $object->guid;
    
        if (!($guid <> "")) 
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Missing GUID, returning error");
            debug_pop();
            return 3;
        }
     
        $filename = $this->_generate_rcs_filename($object);
        if (is_null($filename))
        {
            return 0;
        }
        
        $rcsfilename =  "{$filename},v";
     
        if (!file_exists($rcsfilename))
        {
            if (!$this->rcs_create($object, $message)) 
            {
                return 0;
            } 
            return 2;
        }
        
        $command = "co -l {$filename} 2>&1";
        $status = $this->exec($command);
        
        $data = $this->rcs_object2data($object);
        
     
        $this->rcs_writefile($guid, $data);
        $command = "ci -m'" . $message . "' {$filename} 2>&1";
        $status = $this->exec($command);
    
        chmod ($rcsfilename, 0770);
        return $status;
    }

   /**
    * Get the object of a revision
    * @param string object guid (or other identifier)
    * @param string revision identifier of revision wanted
    * @return array arrray representation of the object 
    */
    function get_revision( $revision) 
    {
        $object = $_MIDCOM->dbfactory->get_object_by_guid($this->_guid);
        
        $filepath = $this->_generate_rcs_filename($object);
        $return = array();
        if (is_null($filepath))
        {
            return $return;
        }

        
        // , must become . to work. Therefore this:
        str_replace(',', '.', $revision );
        // this seems to cause problems:
        //settype ($revision, "float");
        
        $command = "co -r" . trim($revision) .  " " . $filepath . "2>&1";
        $output = null;
        $status = null;
        unset($output);
        unset ($status);
        exec ($command, $output, $status);
         
        $data = $this->rcs_readfile($this->_guid);
        
        $revision = $this->rcs_data2object($data);
       
        $command = "rm -f " . $filepath;
        $output = null;
        $status = null;
        exec ($command, $output, $status);
        
        return $revision;
    }
    
    
    /**
     * Check if a revision exists
     * @param string  version
     * @return booleann true if exists
     */
    function version_exists($version) 
    {
        $history = $this->list_history();
        return array_key_exists($version,$history);
    }
    
    /** 
     * Get the previous versionID
     * @param string verison
     * @return string versionid before this one or empty string.
     */
    function get_prev_version($version) 
    {
        $versions = $this->list_history_numeric();
        for ($i = 0; $i < count($versions); $i++) {
            if ($versions[$i] == $version)  {
                if ($i < count($versions)-1) {
                    return $versions[$i+1];
                } else {
                    return "";
                }
            }
        }
        return "";
    }
    /**
     * Get the next id
     */
    function get_next_version($version) 
    {
        $versions = $this->list_history_numeric();
        for ($i = 0; $i < count($versions); $i++) {
            if ($versions[$i] == $version)  {
                if ($i > 0) {
                    return $versions[$i-1];
                } else {
                    return "";
                }
            }
        }
        return "";
    }
    /**
     * This function returns a list of the revisions as a 
     * key => value par where the key is the index of thhe revision
     * and the value is the revision id.
     * Order: revision 0 is the newest.
     * @return array 
     * @access public
     */
    function list_history_numeric()
    {
        $revs = $this->list_history();
        $i = 0;
        $revisions = array();
        foreach($revs as $id => $desc) 
        {
            $revisions[$i] = $id;
            $i++;
        }
        return $revisions;
    }
    /**
     * Lists the number of changes that has been done to the object
     * @param none
     * @return array list of changeids
     */
    function list_history()
    {
        $object = $_MIDCOM->dbfactory->get_object_by_guid($this->_guid);
        $filepath = $this->_generate_rcs_filename($object);
        if (is_null($filepath))
        {
            return array();
        }
        
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Returning history for $filepath");
        debug_pop();
        return $this->rcs_gethistory($filepath);
    }
    
    /* it is debatable to move this into the object when it resides nicely in a libary... */
    
    function rcs_parse_history_entry($entry)
    {
        // Create the empty history array
        $history = array(
            'revision' => null,
            'date'     => null,
            'lines'    => null,
            'user'     => null,
            'ip'       => null,
            'message'  => null,
        );
        
        // Revision number is in format
        // revision 1.11
        $history['revision'] = substr($entry[0], 9);
        
        // Entry metadata is in format
        // date: 2006/01/10 09:40:49;  author: www-data;  state: Exp;  lines: +2 -2
        // NOTE: Time here appears to be stored as UTC according to http://parand.com/docs/rcs.html
        $metadata_array = explode(';',$entry[1]);
        foreach ($metadata_array as $metadata)
        {
            $metadata = trim($metadata);
            if (substr($metadata, 0, 5) == 'date:')
            {
                $history['date'] = strtotime(substr($metadata, 6));
            }
            elseif (substr($metadata, 0, 6) == 'lines:')
            {
                $history['lines'] = substr($metadata, 7);            
            }
        }
        
        // Entry message is in format
        // user:27b841929d1e04118d53dd0a45e4b93a|84.34.133.194|Updated on Tue 10.Jan 2006 by admin kw
        $message_array = explode('|', $entry[2]);
        if (count($message_array) == 1)
        {
            $history['message'] = $message_array[0];
        }
        else
        {
            if ($message_array[0] != 'Object')
            {
                $history['user'] = $message_array[0];
            }
            $history['ip']   = $message_array[1];
            $history['message'] = $message_array[2];
        }
        return $history;
    }

    /*
     * the functions below are mostly rcs functions moved into the class. Someday I'll get rid of the 
     * old files.... 
     * 
     */
    /**
     * Get a list of the obejcts history
     * @param string objectid (usually the guid)
     * @return array list of revisions and revision comment.
     */
    function rcs_gethistory($what)
    {
        $history = $this->rcs_exec('rlog "' . $what . ',v"');
        $revisions = array();
        $lines = explode("\n", $history);
        
        for ($i = 0; $i < count($lines); $i++)
        {
            if (substr($lines[$i], 0, 9) == "revision ")
            {
                $history_entry[0] = $lines[$i];
                $history_entry[1] = $lines[$i+1];
                $history_entry[2] = $lines[$i+2];
                $history = $this->rcs_parse_history_entry($history_entry);
                
                $revisions[$history['revision']] = $history;
                
                $i += 3;
                
                while (   $i < count($lines) 
                       && substr($lines[$i], 0, 4) != '----' 
                       && substr($lines[$i], 0, 5) != '=====')
                {
                     $i++;
                }
            }
        }
        return $revisions;
    }
    
    /**
     * execute a command
     * @param string command
     * @return string command result.
     */
    function rcs_exec($command)
    {
        $fh = popen($command, "r");
        $ret = "";
        while ($reta = fgets($fh, 1024))
            $ret .= $reta;
        
        pclose($fh);
        return $ret;
    }
     
    /**
     * Writes $data to file $guid, does not return anything.
     */
    function rcs_writefile ($guid, $data)
    {
        if (!is_writable($this->config->get_rcs_root()))
        {
            return false;
        }
        $object = $_MIDCOM->dbfactory->get_object_by_guid($guid);
        $filename = $this->_generate_rcs_filename($object);
        if (is_null($filename))
        {
            return false;
        }
        $fp = fopen ($filename, "w");
        fwrite ($fp, $data);
        fclose ($fp);
    }
    
    /**
     * Reads data from file $guid and returns it.
     * @param string guid
     * @return string xml representation of guid
     */
     
    function rcs_readfile ($guid)
    {
        
        $object = $_MIDCOM->dbfactory->get_object_by_guid($guid);
        $filename = $this->_generate_rcs_filename($object);
        if (is_null($filename))
        {
            return '';
        }
        
        if (!file_exists($filename)) 
        {
            return '';
        }
        
        $fd = fopen ($filename, "r");
        $data = fread ($fd, filesize ($filename));
        fclose ($fd);
        return $data;
    }

    /**
     * rcs_data2object 
     * @param string xmldata 
     * @return array of attribute=> value pairs.
     */
    function rcs_data2object($data)
    {
        require_once(MIDCOM_ROOT . '/midcom/helper/xml/objectmapper.php');              
        $mapper = new midcom_helper_xml_objectmapper();
        
        return $mapper->data2array($data);        
        /*
        if (strpos($data, '<array>')) {
            $result = $unserializer->unserialize( $data );
        } else {
            $result = $unserializer->unserialize( '<array>' . $data . '</array>');
        }
        if (!is_a($result, 'PEAR_error')) {
            return array_shift($unserializer->getUnserializedData());
        }
        */
        //return array();
        
    }    
    /**
     * Make xml out of an object.
     * @param object
     * @return xmldata
     */
    function rcs_object2data($object) 
    {
        
        if (!is_object($object))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Missing object needed as parameter.", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        require_once(MIDCOM_ROOT . '/midcom/helper/xml/objectmapper.php');
        $mapper = new midcom_helper_xml_objectmapper();
        $result = $mapper->object2data($object);
        if ($result) {
            return $result;
        }
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Objectmapper returned false.");
        debug_pop();
        return false;
    }

    
    /**
     * This function takes an object and adds it to RCS, it should be
     * called just after $object->create(). Remember that you first need
     * to mgd_get the object since $object->create() returns only the id, 
     * one way of doing this is:
     * @param object object to be saved
     * @param string changelog comment.-
     * @return int :
     *      0 on success
     *      3 on missing object->guid
     *      nonzero on error in one of the commands. 
     */
     
    function rcs_create($object, $description)
    {
        $output = null;
        $status = null;
        $guid = $object->guid;
        
        $type = $object->__table__;
   
        $data = $this->rcs_object2data($object, $type);
     
        $this->rcs_writefile($guid, $data);
        $filepath = $this->_generate_rcs_filename($object);
        if (is_null($filepath))
        {
            return 3;
        }
        
        $command = sprintf("ci -i -t-'%s' %s 2>&1", $description, $filepath);
        $status = $this->exec($command);
        
        $filename = $filepath . ",v";
        
        if (file_exists($filename))
        {
            chmod ($filename, 0770);
        }
        return $status;
    }
    
    function exec($command) 
    {
        $status = null;
        $output = null;

        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Executing '{$command}'");
        debug_pop();
        
        @exec($command, $output, $status);                
        return $status;   
    }

    /**
     * Get a html diff between two versions.
     * 
     * @param string latest_revision id of the latest revision
     * @param string oldest_revision id of the oldest revision
     * @access public
     * @return array array with the original value, the new value and a diff -u
     */    
    function get_diff($oldest_revision, $latest_revision, $renderer_style = 'inline') 
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $oldest = $this->get_revision($oldest_revision);
        $newest = $this->get_revision($latest_revision);
        
        $return = array();
       
        foreach ($oldest as $attribute => $oldest_value) 
        {
            
            if (!array_key_exists($attribute, $newest))
            {
                continue;
                // This isn't in the newer version, skip
            }
            
            if (is_array($oldest_value))
            {
                continue;
                // Skip
            }
            
            $return[$attribute] = array
            (
                'old' => $oldest_value, 
                'new' => $newest[$attribute]
            );
            
            if ($oldest_value != $newest[$attribute]) 
            {
                if (class_exists('Text_Diff')) 
                {
                
                    $lines1 = explode ("\n", $oldest_value);
                    $lines2 = explode ("\n", $newest[$attribute]);
                
                    $diff = &new Text_Diff($lines1, $lines2);
                    
                    if ($renderer_style == 'unified')
                    {
                        $renderer = &new Text_Diff_Renderer_unified();
                    }
                    else
                    {
                        $renderer = &new Text_Diff_Renderer_inline();
                    }
                
                    if (!$diff->isEmpty()) 
                    {
                        // Run the diff
                        $return[$attribute]['diff'] = $renderer->render($diff);
                        
                        if ($renderer_style == 'inline')
                        {
                            // Mofify the output for nicer rendering
                            $return[$attribute]['diff'] = str_replace('<del>', "<span class=\"deleted\" title=\"removed in {$latest_revision}\">", $return[$attribute]['diff']);
                            $return[$attribute]['diff'] = str_replace('</del>', '</span>', $return[$attribute]['diff']);
                            $return[$attribute]['diff'] = str_replace('<ins>', "<span class=\"inserted\" title=\"added in {$latest_revision}\">", $return[$attribute]['diff']);
                            $return[$attribute]['diff'] = str_replace('</ins>', '</span>', $return[$attribute]['diff']);
                        }
                    }
                } 
                elseif (!is_null($GLOBALS['midcom_config']['utility_diff']))
                {
                    /* this doesnt work */
                    $command = $GLOBALS['midcom_config']['utility_diff'] . " -u <(echo \"$oldest_value\") <(echo \"{$newest[$attribute]}\")";
                    
                    $output = array();
                    $result = shell_exec($command);
                    
                        //$return[$attribute]['diff'] = implode ("\n", $output);
                        $return[$attribute]['diff'] = $command. "\n'".$result . "'";
                    
                } else {
                    $return[$attribute]['diff'] = "THIS IS AN OUTRAGE!";
                }
            }
        }
        
        debug_pop();
        return $return;
    
    }

    /** 
     * Get the comment of one revision.
     * @param string revison id
     * @return string comment
     */
    function get_comment($revision) 
    {
        if (is_null($this->_history)) 
        {
            $this->_history = $this->list_history();
        }
        return $this->_history[$revision];   
    }   
    
    /**
     * Restore an object to a certain revision.
     * 
     * @param string id of revision to restore object to.
     * @return boolean true on success.
     */
    function restore_to_revision($revision) 
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $new = $this->get_revision($revision);

        $object = $_MIDCOM->dbfactory->get_object_by_guid($this->_guid);
        if (!is_object($object))
        {
            debug_add("{$this->_guid} could not be resolved to object", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $attributes = get_object_vars ($object);
        $revs = array();
        
        foreach ($new as $attribute => $value) 
        {
            if (is_object($object->$attribute))
            {
                continue;
            }
            if (trim($value) != "" && array_key_exists($attribute, $attributes) ) 
            {        
                if ($object->{$attribute} != '' && $object->$attribute != $value) 
                {
                    $revs[] ="$attribute (old: {$object->$attribute} new: $value" ;
                    $object->{$attribute} = $value;
                }
                
                if ($object->{$attribute} == '') 
                {
                    $revs[] ="$attribute (old: {$object->$attribute} new: $value" ;
                    
                    $object->{$attribute} = $value;
                }
                
            }
            
        }

        $this->error[] = join(", ", $revs);
        
        // TODO: Set revision comment to "Reverted to revision {$revision}"
        
        if ($object->update()) 
        {
            return true;
        }
        $this->error[]  = "Object not updated. " . mgd_errstr() . " " . $this->_guid;
        debug_pop();
        return false;
    }
 
}
?>