<?php
/**
 * @package midcom.helper.datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

class midcom_helper_datamanager_datatype_blob extends midcom_helper_datamanager_datatype
{

    var $_thousandsep;
    var $_anchorprefix;
    var $_commands;

    function _constructor (&$datamanager, &$storage, $field)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $field["location"] = "attachment";
        if (!array_key_exists("widget", $field))
        {
            $field["widget"] = "blob";
        }
        if (!array_key_exists("datatype_blob_thousandsep", $field))
        {
            $field["datatype_blob_thousandsep"] = ".";
        }
        if (!array_key_exists("datatype_blob_anchorprefix", $field))
        {
            $midgard = $_MIDCOM->get_midgard();
            $field["datatype_blob_anchorprefix"] = $midgard->self;
        }
        if (!array_key_exists("datatype_blob_autoindex", $field))
        {
            $field["datatype_blob_autoindex"] = true;
        }

        $this->_thousandsep = $field["datatype_blob_thousandsep"];
        $this->_anchorprefix = $field["datatype_blob_anchorprefix"];
        $this->_commands = null;

        parent::_constructor ($datamanager, $storage, $field);

        debug_pop();
    }

    function load_from_storage ()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $attid = null;

        if (is_null ($this->_storage))
        {
            debug_add("The storage object is null, can't do anything.", MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }

        $attachments = $this->_storage->list_attachments();
        foreach ($attachments as $att)
        {
            if ($att->parameter("midcom.helper.datamanager.datatype.blob", "fieldname") == $this->_field["name"])
            {
                $attid = $att->id;
                break;
            }
        }

        if (! is_null ($attid))
        {
            $this->_update_value($attid);
        }
        else
        {
            $this->_value = null;
        }

        debug_pop();
        return true;
    }

    function save_to_storage ()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        if (is_null ($this->_storage))
        {
            debug_add("The storage object is null, can't do anything.", MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }

        $widget =& $this->get_widget();

        if (array_key_exists("upload", $this->_commands))
        {
            debug_add("We should upload something");
            if (! is_null ($this->_value))
            {
                if (!$this->_delete_attachment($this->_value["id"]))
                {
                    $this->_update_value($this->_value["id"]);
                    $widget->set_value($this->_value);
                    debug_add("Failed to delete pre-existing attachment");
                    debug_pop();
                    return MIDCOM_DATAMGR_FAILED;
                }
            }
            $id = $this->_handle_upload($this->_commands["upload"], $this->_commands["meta"]);
            if (!$id)
            {
                debug_add("_handle_upload failed");
                debug_pop();
                return MIDCOM_DATAMGR_FAILED;
            }
            $this->_update_value($id);
            $this->autoindex();
            $widget->set_value($this->_value);
        }
        else if (array_key_exists("delete", $this->_commands))
        {
            debug_add("We should delete something");
            if (! is_null ($this->_value))
            {
                if (!$this->_delete_attachment($this->_value["id"]))
                {
                    $this->_update_value($this->_value["id"]);
                    $widget->set_value($this->_value);
                    debug_pop();
                    return MIDCOM_DATAMGR_FAILED;
                }
                $this->_value = null;
            }
            $widget->set_value($this->_value);
        }
        else if (array_key_exists("meta", $this->_commands))
        {
            debug_add("We should update the description");
            if (! is_null ($this->_value))
            {
                $att = new midcom_baseclasses_database_attachment($this->_value["id"]);
                if (! $att)
                {
                    debug_add("Attachment {$attid} not found or access denied, cannot update metadata.");
                    debug_pop();
                    return MIDCOM_DATAMGR_FAILED;
                }
                $_MIDCOM->auth->require_do('midgard:update', $att, "Write-access to Attachment {$att->id} is required to update its metadata.");

                $changes = false;

                if ($this->_commands["meta"]["description"] != $att->title)
                {
                    $att->title = $this->_commands["meta"]["description"];
                    $changes = true;
                }

                if ($this->_commands["meta"]["filename"] != $att->name)
                {
                    $att->name = $this->_commands["meta"]["filename"];
                    $changes = true;
                }

                if ($this->_commands["meta"]["mimetype"] != $att->mimetype)
                {
                    $att->mimetype = $this->_commands["meta"]["mimetype"];
                    $changes = true;
                }

                if ($changes)
                {
	                $att->update();
	                $this->_update_value($att->id);
	                $widget->set_value($this->_value);
                    $this->autoindex();
                }
            }

        }
        debug_pop();
        return MIDCOM_DATAMGR_SAVED;
    }

    function sync_data_with_widget ()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $widget =& $this->get_widget();
        $this->_commands = $widget->get_value();

        debug_pop();
    }

    function _format_filesize ($number)
    {
        return number_format($number, 0, '.', $this->_thousandsep);
    }

    function _update_value($attid)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $att = new midcom_baseclasses_database_attachment($attid);
        if (!$att)
        {
            debug_add("Attachment {$attid} not found or access denied, setting to null");
            $this->_value = null;
            debug_pop();
            return;
        }
        debug_print_r("Got this attachment: ", $att);
        $stat = $att->stat();
        debug_print_r("Got these stats:", $stat);
        $this->_value["filename"] = $att->name;
        $this->_value["mimetype"] = $att->mimetype;
        $this->_value["url"] = $this->_anchorprefix . "midcom-serveattachmentguid-" . $att->guid() . "/" . $att->name;

        $this->_value["filesize"] = $stat[7];
        $this->_value["lastmod"] = $stat[9];
        $this->_value["formattedsize"] = $this->_format_filesize($stat[7]);
        $this->_value["isoformattedlastmod"] = strftime("%Y-%m-%d %T",$stat[9]);
        $this->_value["id"] = $att->id;
        $this->_value["guid"] = $att->guid;
        $this->_value["description"] = $att->title;
        $this->_value["size_y"] = $att->parameter("midcom.helper.datamanager.datatype.blob","size_y");
        $this->_value["size_x"] = $att->parameter("midcom.helper.datamanager.datatype.blob","size_x");
        $this->_value["size_line"] = $att->parameter("midcom.helper.datamanager.datatype.blob","size_line");
        $this->_value["object"] = $att;
        debug_pop();
    }

    function get_csv_data()
    {
        $url = $this->_value["url"];
        if (substr($url,0,1) == "/")
        {
            // We didn't have a configured anchor prefix, we default to the local host.
            $protocol = array_key_exists("SSL_PROTOCOL", $_SERVER) ? "https" : "http";

            $port = "";
            if ($protocol == "http" && $_SERVER["SERVER_PORT"] != 80)
            {
                $port = ":" . $_SERVER["SERVER_PORT"];
            }
            else if ($protocol == "https" && $_SERVER["SERVER_PORT"] != 443)
            {
                $port = ":" . $_SERVER["SERVER_PORT"];
            }

            $location = "$protocol://"
                        . $_SERVER['HTTP_HOST']
                        . $port
                        . $url;
        }
        else
        {
            // We had a fully qualified URL
            $location = "$url";
        }

        return "Binary on site: $location (" . $this->_value["formattedsize"] . " Byte)";
    }

    function _delete_attachment ($id)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        if (is_null ($this->_storage))
        {
            debug_add("The storage object is null, can't do anything.", MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }

        $attachment = new midcom_baseclasses_database_attachment($id);
        if (!$attachment)
        {
            debug_add("Could not open attachment, seems that it isn't here, so we're fine. Error was:" . mgd_errstr());
            debug_pop();
            return true;
        }

        // Save GUID for index update
        $guid = $attachment->guid;

        if (! $attachment->delete())
        {
            $this->_datamanager->append_error(
                sprintf($this->_datamanager->_l10n->get("failed to delete file %s: %s") . "<br>\n",
                        $this->_value["filename"],
                        mgd_errstr()));
            $midcom_errstr = "Could not delete attachment: " . mgd_errstr();
            debug_add($midcom_errstr, MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        if ($this->_field['datatype_blob_autoindex'])
        {
            $indexer =& $_MIDCOM->get_service('indexer');
            $indexer->delete($guid);
        }

        debug_pop();
        return true;
    }

    function _handle_upload ($params, $meta)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        if (is_null ($this->_storage))
        {
            debug_add("The storage object is null, can't do anything.", MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }

        $filename = (strlen($meta["filename"]) > 0) ? $meta["filename"] : $params["name"];
        $mimetype = (strlen($meta["mimetype"]) > 0) ? $meta["mimetype"] : $params["type"];
        $description = $meta["description"];

        debug_print_r("Uploading a file as {$filename} ({$mimetype}, {$description}) at this object:", $this->_storage);

        if ($this->_storage->get_attachment($filename))
        {
            debug_add("A file with this name already exists");
            $this->_datamanager->append_error(
                sprintf($this->_datamanager->_l10n->get("failed saving field %s: %s") . "<br>\n",
                        $this->_field["location"],
                        $this->_datamanager->_l10n->get("a file with this name already exists")));
            debug_pop();
            return false;
        }

        $att = $this->_storage->create_attachment($filename, $description, $mimetype);
        if (!$att)
        {
            debug_add("Failed creating attachment, reason ".mgd_errstr());
            $this->_datamanager->append_error(
                sprintf($this->_datamanager->_l10n->get("failed saving field %s: %s") . "<br>\n",
                        $this->_field["location"],
                        mgd_errstr()));
            debug_pop();
            return false;
        }

        debug_print_r("Got this attachment:", $att);

        $dest = $att->open();
        $source = fopen ($params["tmp_name"],"r");
        if (! $dest)
        {
            debug_add("Could not open file for writing");
            $this->_datamanager->append_error(
                sprintf($this->_datamanager->_l10n->get("failed saving field %s: %s") . "<br>\n",
                        $this->_field["location"],
                        $this->_datamanager->_l10n->get("could not open file for writing")));
            debug_pop();
            $att->delete($id);
            return false;
        }
        if (! $source)
        {
            debug_add("Could not open uploaded file for reading");
            $this->_datamanager->append_error(
                sprintf($this->_datamanager->_l10n->get("failed saving field %s: %s") . "<br>\n",
                        $this->_field["location"],
                        $this->_datamanager->_l10n->get("could not open uploaded file for reading")));
            $att->close();
            $att->delete($id);
            debug_pop();
            return false;
        }

        while (! feof($source))
        {
            fwrite($dest, fread($source, 100000));
        }

        $att->close();
        fclose($source);

        if (! $att->parameter("midcom.helper.datamanager.datatype.blob", "fieldname", $this->_field["name"]))
        {
            debug_add('Failed to set a parameter to the newly created attachment, we need this to find it again later. Last Midgard error: '
                . mgd_errstr(), MIDCOM_LOG_ERROR);
            $this->_datamanager->append_error(
                sprintf($this->_datamanager->_l10n->get("failed saving field %s: %s") . "<br>\n",
                        $this->_field["location"],
                        mgd_errstr()));
            $att->delete($id);
            debug_pop();
            return false;
        }

        $data = @getimagesize($params["tmp_name"]);
        if ($data)
        {
            $att->parameter("midcom.helper.datamanager.datatype.blob", "size_x", $data[0]);
            $att->parameter("midcom.helper.datamanager.datatype.blob", "size_y", $data[1]);
            $att->parameter("midcom.helper.datamanager.datatype.blob", "size_line", $data[3]);
            switch ($data[2])
            {
                case 1:
                    $mime = "image/gif";
                    break;

                case 2:
                    $mime = "image/jpeg";
                    break;

                case 3:
                    $mime = "image/png";
                    break;

                case 6:
                    $mime = "image/bmp";
                    break;

                case 7:
                case 8:
                    $mime = "image/tiff";
                    break;

                default:
                    $mime = false;
                    break;
            }
            if ($mime !== false && strlen($meta["mimetype"]) == 0)
            {
                $att->mimetype = $mime;
                $att->update();
            }
        }

        debug_pop();
        return $att->id;
    }

    function _get_empty_value()
    {
        return null;
    }

    /**
     * Indexes the blob if and only if it is set to autoindex mode.
     */
    function autoindex()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (is_null($this->_value))
        {
            debug_add('Value is empty, no autoindex done.');
            debug_pop();
            return;
        }
        if ($this->_field['datatype_blob_autoindex'])
        {
            $document = new midcom_services_indexer_document_attachment($this->_value["object"], $this->_storage);
            $indexer =& $_MIDCOM->get_service('indexer');
            $indexer->index($document);
        }
        debug_pop();
    }
}

?>