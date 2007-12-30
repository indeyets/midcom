<?php
/**
 * Created on Aug 16, 2005
 * @author tarjei huse
 * @package no.bergfald.rcs
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * @package no.bergfald.rcs
 */
class no_bergfald_rcs_aegirrcs extends no_bergfald_rcs {

    /**
     * Default rcsroot
     */
    var $rcsroot = '';

    function no_bergfald_rcs_aegirrcs($guid)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        parent::no_bergfald_rcs($guid);
        $this->_probe_nemein_rcs();

        if (   $_MIDGARD['config']['prefix'] == '/usr'
                    || $_MIDGARD['config']['prefix'] == '/usr/local')
        {
            $this->rcsroot = '/var/lib/midgard/rcs';
        }
        else
        {
            $this->rcsroot = "{$_MIDGARD['config']['prefix']}/var/lib/midgard/rcs";
        }
        //$GLOBALS['links2guids_default'] = 0;
        // Load libaries
        // HA! We're the libary now!'
        //require_once('nemein_rcs_functions.php');
        //require_once('aegir_rcs_functions.php');

        debug_pop();
    }


   /**
    * Get the object of a revision
    * @param string object guid (or other identifier)
    * @param string revision identifier of revision wanted
    * @return array array representation of the object
    */
    function get_revision( $revision)
    {
        $filepath = $this->rcsroot . '/' . $this->_guid;
        $return = array();


        // , must become . to work. Therefore this:
        str_replace(',', '.', $revision );
        // this seems to cause problems:
        //settype ($revision, "float");

        $command = "co -r" . trim($revision) .  " " . $filepath;
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
     * Restore an object to a certain revision.
     *
     * @param string id of revision to restore object to.
     * @return boolean true on success.
     */

    function restore_to_revision($revision)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $new = $this->get_revision($revision);
        /* commented out for now  , it only returned null :/ */
        $object = $_MIDCOM->dbfactory->get_object_by_guid($this->_guid);
        if (!is_object($object))
        {
            debug_add("{$this->_guid} could not be resolved to object", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $attributes = get_object_vars ($object);
        $revs = array();


        if(1) foreach ($new as $attribute => $value) {
            if (trim($value) != "" && array_key_exists($attribute, $attributes) ) {

                if ($object->{$attribute} != "" && $object->$attribute != $value) {
                    $revs[] ="$attribute (old: {$object->$attribute} new: $value" ;
                    $object->{$attribute} = $value;
                }

                if ($object->{$attribute} == '') {
                    $revs[] ="$attribute (old: {$object->$attribute} new: $value" ;

                    $object->{$attribute} = $value;
                }

            }

        }

        $this->error[] = join( ", ", $revs);

        if ($object->update())
        {
            return $this->save_object(&$object, "Reverted to revision {$revision}");
        }
        $this->error[]  = "Object not updated. " . mgd_errstr() . " " . $this->_guid;
        debug_pop();
        return false;
    }

    /**
     * Save a new revision
     * @param object object to be saved
     * @return boolean true on success.
     */
    function save_object(&$object, $updatemessage = null)
    {
        //todo: add time of day
        $update_string = "{$_MIDCOM->auth->user->id}|{$_SERVER['REMOTE_ADDR']}";
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


        if ($result > 0 ) {
            return false;
        }
        return true;
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
     * @param string version
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
        $return = array();
        foreach( $revs as $id => $desc) {
            $return[$i] = $id;
            $i++;
        }
        return $return;

    }
    /**
     * Lists the number of changes that has been done to the object
     * @param none
     * @return array list of changeids
     */
    function list_history()
    {
        $filepath = $this->rcsroot ."/". $this->_guid ;
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Returning history for $filepath");
        debug_pop();
        return $this->rcs_gethistory($filepath);
    }

    function _probe_nemein_rcs()
    {



        debug_add ("Bergfald RCS interface: checking for /AegirCore/config/config");
        if (mgd_snippet_exists("/AegirCore/config/config")) {

          debug_add ("Bergfald RCS interface: Including /AegirCore/config/config");
          mgd_include_snippet_php("/AegirCore/config/config");
        }

        debug_add ("Bargfald RCS interface: Checking configuration");
        if (!isset($set) || !is_array($set) || !array_key_exists("rcsroot", $set)) {
            debug_add("NemeinRCS interface: Aegir rcsroot not set by Aegir, going to default");
            $set["rcsroot"] = $this->rcsroot;
        } else {
            $this->rcsroot = $set['rcsroot'];
        }

        if (!file_exists($set["rcsroot"])) {
            debug_add("Bergfald RCS interface: Aegir rcsroot directory ".$set["rcsroot"]." not found on system");
            return false;
        }

        if (!is_writable($set["rcsroot"])) {
            debug_add("Bergfald RCS interface: Aegir rcsroot directory ".$set["rcsroot"]." is not writable by the Apache process");
            return false;
        }

        debug_add("Bergfald RCS interface: Set rcsroot to " . $set["rcsroot"]);

        $rcsroot = $set["rcsroot"];
        return $rcsroot;
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
     * Get a list of the objects history
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

        /*foreach ($revisions as $revision_number => $revision)
        {
            $revisions['prev'] = $this->get_prev_version($revision_number);
            $revisions['next'] = $this->get_next_version($revision_number);
        }*/

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
        if (!is_writable($this->rcsroot))
        {
            return false;
        }
        $filename = "{$this->rcsroot}/{$guid}";
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

        $filename = "{$this->rcsroot}/{$guid}";
        if (!file_exists($filename)) {
            return "";
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

    function rcs_create ($object, $description)
    {
        $output = null;
        $status = null;
        $guid = $object->guid;

        $type = $object->__table__;

        $data = $this->rcs_object2data($object, $type);

        $this->rcs_writefile($guid, $data);
        $command = "ci -i -t-'{$description}' {$this->rcsroot}/{$guid}";
        exec ($command, $output, $status);

        $filename = "{$this->rcsroot}/{$guid},v";
        chmod ($filename, 0770);
        return $status;
    }

    /**
     * This function takes an object and updates it to RCS, it should be
     * called just before $object->update(), if the type parameter is omitted
     * the function will use GUID to determine the type, this makes an
     * extra DB query.
     * @param string root of rcs directory.
     * @param object object to be updated.
     * @param boolean links2guids - whether to turn links into guids. NOT IN USE
     * @return int :
     *      0 on success
     *      3 on missing object->guid
     *      nonzero on error in one of the commands.
     */
    function rcs_update ($object,$message = "Changed via Midgard")
    {

        $output = null;
        $status = null;
        unset ($output);
        unset($status);

        $guid = $object->guid;


        if (!($guid <> "")) {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Missing guid returning error");
            debug_pop();
            return 3;
        }



        $rcsfilename = $this->rcsroot . "/" . $guid . ",v";

        if (!file_exists($rcsfilename)){
            if ($this->rcs_create($object, $message)) {
                return 0;
            }
            return 2;
        }

        $command = "co -l " . $this->rcsroot . "/" . $guid;
        exec ($command, $output, $status);

        $data = $this->rcs_object2data($object);


        $this->rcs_writefile($guid, $data);
        $command = "ci -m'" . $message . "' " . $this->rcsroot . "/" . $guid;
        unset ($output);
        unset ($status);
        exec ($command, $output, $status);

        $filename = $this->rcsroot . "/" . $guid . ",v";
        chmod ($filename, 0770);
        return $status;
    }

}
?>