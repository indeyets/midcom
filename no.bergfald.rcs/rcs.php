<?php
/**
 * Created on Aug 16, 2005
 *
 * @author tarjei Huse
 * @package no.bergfald.no rcs
 * 
 * Abstract class for the rcs interface.
 */

class no_bergfald_rcs {

    /**
     * The guid of the object in question
     * @var string buid
     * @access private
     */
     var $_guid = null;
     
    /**
     * Backend object
     * @var object no_nu_versoning_backend
     * @access private
     */
     var $backend = null;
     
    /**
     * Pointer to thhe diff object
     * @access private
     * @var object text_diff
     */ 
     var $_diff = null;
    /**
     *  
     * 
     * @param string guid
     * @param string backend to use
     */
    /**
     * History arrayy of object
     */
    var $_history = null;
    
    /**
     * Array of errormessages;
     */
    var $error = array();

    function no_bergfald_rcs ( $guid = null)   
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (!is_null($guid)) {
            $this->_guid = $guid;
        }
    
        debug_pop();
        
    } 

    /**
     * Factory function
     * @return object no_bergfald_versoning
     */
    function & factory ($backend,$guid = '') 
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $object = null;
        if (class_exists('no_bergfald_rcs_'. $backend)) {
            $object = new $backend($guid);
        }
        
        return $object;
    }
    /**
     * Get a html diff between two versions.
     * 
     * @param string latest_revision id of the latest revision
     * @param string oldest_revision id of the oldest revision
     * @access public
     * @return array array with the original value, the new value and a diff -u
     */
    
    function get_diff($oldest_revision, $latest_revision) 
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
            }
            
            $return[$attribute] = array
            (
                'old' => $oldest_value, 
                'new' => $newest[$attribute]
            );
            
            if ( $oldest_value != $newest[$attribute] ) {
                if (class_exists('Text_Diff')) {
                
                    $lines1 = explode ("\n", $oldest_value);
                    $lines2 = explode ("\n", $newest[$attribute]);
                
                    $diff = &new Text_Diff($lines1, $lines2);
                    
                    $renderer = &new Text_Diff_Renderer_inline();
                
                    if (!$diff->isEmpty()) 
                    {
                        // Run the diff
                        $return[$attribute]['diff'] = $renderer->render($diff);
                        
                        // Mofify the output for nicer rendering
                        $return[$attribute]['diff'] = str_replace('<del>', "<span class=\"deleted\" title=\"removed in {$latest_revision}\">", $return[$attribute]['diff']);
                        $return[$attribute]['diff'] = str_replace('</del>', '</span>', $return[$attribute]['diff']);
                        $return[$attribute]['diff'] = str_replace('<ins>', "<span class=\"inserted\" title=\"added in {$latest_revision}\">", $return[$attribute]['diff']);
                        $return[$attribute]['diff'] = str_replace('</ins>', '</span>', $return[$attribute]['diff']);
                    }
                } elseif (!is_null($GLOBALS['midcom_config']['utility_diff'])){
                    /* this doesnt work */
                    $command = $GLOBALS['midcom_config']['utility_diff'] . " -u <(echo \"$oldest_value\") <(echo \"{$newest[$attribute]}\") ";
                    
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
        if (is_null($this->_history)) {
            $this->_history = $this->list_history();
        }
        return $this->_history[$revision];
        
    }

    /**
     * Get the object of a revision
     * @param string revision identifier of revision wanted
     * @return array arrray representation of the object 
     */
     function get_revision( $revision) 
     {
        return array();
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
        
        debug_pop();
        return false;
    }
    
    /**
     * Save a new revision
     * @param object object to be saved
     * @return boolean true on success.
     */
    function save_object() {
        return false;
    }
    
    /**
     * Lists the number of changes that has been done to the object
     * @param none
     * @return array list of changeids
     */
    function list_diffs()
    {
        return array();
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
     * Lists the number of changes that has been done to the object
     * @param none
     * @return array list of changeids or empty array if no changes.
     */
    function list_history()
    {
        return array(); 
    }
 
    

    
    /**
     * Helper to get the lastest errormessages out of the backend.
     * @param separator default = <br/>
     * @return string errormessage
     */
     function get_error($sep = '<br/>') {
        return join ($sep,$this->error);
     }

}
?>