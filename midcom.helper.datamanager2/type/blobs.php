<?php
/**
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanager 2 Blob management type.
 *
 * This type allows you to control an arbitrary number of attachments on a given object.
 * It can only operate if the storage implementation provides it with a Midgard Object.
 * The storage location provided by the schema is unused at this time, as attachment
 * operations cannot be undone. Instead, the direct parameter calls are used to manage
 * the list of attachments in a parameter associated to the domain of the type. The
 * storage IO calls will not do much, except synchronizing data where necessary.
 *
 * The type can manage an arbitrary number of attachments. Each attachment is identified
 * by a handle (not its name!). It provides management functions for existing attachments,
 * which allow you to add, delete and update them in all variants. These functions
 * are executed immediately on the storage object, no undo is possible.
 *
 * This type serves as a base class for other, more advanced blob types, like the image type.
 *
 * <b>Available configuration options:</b>
 *
 * - <b>string attachment_server_url:</b> This is the base URL used for attachment serving.
 *   It defaults to the global MidCOM attachment handler at the sites root. When constructing
 *   Attachment Info blocks, this URL is completed using "{$baseurl}{$guid}/{$filename}".
 *
 * @package midcom.helper.datamanager2
 */
class midcom_helper_datamanager2_type_blobs extends midcom_helper_datamanager2_type
{
    /**
     * All attachments covered by this field. 
     * The array contains midcom_baseclasses_database_attachment objects indexed by their 
     * identifier within the field.
     *
     * See the $attachments_info member for a more general approach easily usable
     * within styles.
     *
     * @var Array
     * @access public
     */
    var $attachments = Array();

    /**
     * This is the base URL used for attachment serving. 
     *
     * It defaults to the global MidCOM attachment handler at the sites root. 
     * When constructing Attachment Info blocks, this URL is completed using 
     * "{$baseurl}{$guid}/{$filename}".
     *
     * @var string
     * @access public
     */
    var $attachment_server_url = null;

    /**
     * This member is populated and synchronized with all known changes to the
     * attachments listing. 
     *
     * It contains a batch of metadata that makes presenting them easy. The 
     * information is kept in an array per attachment, again indexed
     * by their identifiers. The following keys are defined:
     *
     * - filename: The name of the file (useful to produce nice links).
     * - mimetype: The MIME Type.
     * - url: A complete URL valid for the current site, which delivers the attachment.
     * - filesize and formattedsize: The size of the file, as integer, and as formatted number
     *   with thousand-separator.
     * - lastmod and isoformattedlastmod: The UNIX- and ISO-formatted timestamp of the
     *   last modification to the attachment file.
     * - id, guid: The ID and GUID of the attachment.
     * - description: The title of the attachment, usually used as a caption.
     * - size_x, size_y and size_line: Only applicable for images, holds the x and y
     *   sizes of the image, along with a line suitable for inclusion in the <img />
     *   tags.
     * - object: This is a reference to the attachment object (in $attachments).
     * - identifier: The identifier of the attachment (for reverse-lookup purposes).
     *
     * The information in this listing should be considered read-only. If you want to
     * change information like the Title of an attachment, you need to do this using
     * the attachment object directly.
     *
     * @var Array
     * @access public
     */
    var $attachments_info = Array();

    /**
     * Maximum amount of blobs allowed to be stored in the same field
     *
     * @access public
     * @var integer
     */
    var $max_count = 0;

    /**
     * Set the base URL accordingly
     *
     * this requires midcom_config access and is thus not possible using class member initializers.
     */
    function _on_configuring($config)
    {
        parent::_on_configuring($config);

        $this->attachment_server_url =
            "{$GLOBALS['midcom_config']['midcom_site_url']}midcom-serveattachmentguid-";
    }

    /**
     * This function loads all known attachments from the storage object. 
     *
     * It will leave the field empty in case the storage object is null.
     */
    function convert_from_storage ($source)
    {
        $this->attachments = Array();
        $this->attachments_info = Array();

        if ($this->storage->object === null)
        {
            // We don't have a storage object, skip the rest of the operations.
            return;
        }

        $raw_list = $this->storage->object->get_parameter('midcom.helper.datamanager2.type.blobs', "guids_{$this->name}");
        if (! $raw_list)
        {
            // No attachments found.
            return;
        }

        $items = explode(',', $raw_list);

        foreach ($items as $item)
        {
            $info = explode(':', $item);
            if (   !is_array($info)
                || !array_key_exists(0, $info)
                || !array_key_exists(1, $info))
            {
                // Broken item
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("item '{$item}' is broken!", MIDCOM_LOG_ERROR);
                debug_pop();
                continue;
            }
            $identifier = $info[0];
            $guid = $info[1];
            $this->_load_attachment($identifier, $guid);
        }

        $this->_sort_attachments();
    }

    /**
     * This function sorts the attachment lists by filename. 
     *
     * It has to be called after each attachment operation. It uses a 
     * user-defined ordering function for each of the two arrays to be sorted: 
     * _sort_attachments_callback() and _sort_attachments_info_callback().
     *
     * @access protected
     */
    function _sort_attachments()
    {
        uasort($this->attachments,
            Array('midcom_helper_datamanager2_type_blobs', '_sort_attachments_callback'));
        uasort($this->attachments_info,
            Array('midcom_helper_datamanager2_type_blobs', '_sort_attachments_info_callback'));
    }

    /**
     * User-defined array sorting callback, used for sorting $attachments. 
     *
     * See the usort() documentation for further details.
     *
     * @access protected
     * @param midcom_baseclasses_database_attachment $a The first attachment.
     * @param midcom_baseclasses_database_attachment $b The second attachment.
     * @return int A value according to the rules from strcmp().
     */
    function _sort_attachments_callback($a, $b)
    {
        return strcasecmp($a->name, $b->name);
    }

    /**
     * User-defined array sorting callback, used for sorting $attachments_info. 
     *
     * See the usort() documentation for further details.
     *
     * @access protected
     * @param Array $a The first attachment.
     * @param Array $b The second attachment.
     * @return int A value according to the rules from strcmp().
     */
    function _sort_attachments_info_callback($a, $b)
    {
        return strcasecmp($a['filename'], $b['filename']);
    }

    /**
     * This function will load a given attachment from the disk, and then calls
     * a function which updates the $attachments_info listing.
     *
     * @param string $identifier The identifier of the attachment to load.
     * @param string $guid The guid of the attachment to load.
     */
    function _load_attachment($identifier, $guid)
    {
        $attachment = new midcom_baseclasses_database_attachment($guid);
        if (   ! $attachment
            || !$attachment->guid)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to load the attachment {$guid} from disk, aborting.", MIDCOM_LOG_INFO);
            debug_add('Last Midgard error was: ' . mgd_errstr(), MIDCOM_LOG_INFO);
            debug_pop();
            return;
        }

        $this->attachments[$identifier] = $attachment;
        $this->_update_attachment_info($identifier);
    }

    /**
     * Synchronizes the attachments info array with the attachment referenced by the
     * identifier.
     *
     * @param mixed $identifier The identifier of the attachment to update
     * @access protected
     */
    function _update_attachment_info($identifier)
    {
        // Shortcuts
        $att =& $this->attachments[$identifier];
        $stats = $att->stat();

        $info = Array();
        $info['filename'] = $att->name;
        $info['description'] = $att->title;
        $info['mimetype'] = $att->mimetype;
        $name = urlencode($att->name);

        if ($GLOBALS['midcom_config']['attachment_cache_enabled'])
        {
            $subdir = substr($att->guid, 0, 1);
            if (file_exists("{$GLOBALS['midcom_config']['attachment_cache_root']}/{$subdir}/{$att->guid}_{$att->name}"))
            {
                // Attachment coming from the cache URL
                $info['url'] = "{$GLOBALS['midcom_config']['attachment_cache_url']}/{$subdir}/{$att->guid}_{$att->name}";
            }
        }

        if (!isset($info['url']))
        {
            // Uncached attachment served straight out of MidCOM
            // FIXME: ptable is a deprecated property
            if ($att->ptable == 'topic')
            {
                // Topic attachment, try to generate "clean" URL
                $nap = new midcom_helper_nav();
                $parent = $nap->resolve_guid($att->parentguid);
                if (   is_array($parent)
                    && $parent[MIDCOM_NAV_TYPE] == 'node')
                {
                    $info['url'] = "{$_MIDGARD['self']}{$parent[MIDCOM_NAV_RELATIVEURL]}{$name}";
                }
            }
        }

        if (!isset($info['url']))
        {
            // Use regular MidCOM attachment server
            $info['url'] = "{$this->attachment_server_url}{$att->guid}/{$name}";
        }

        $info['id'] = $att->id;
        $info['guid'] = $att->guid;

        $info['filesize'] = $stats[7];
        $info['formattedsize'] = number_format($stats[7], 0, ',', '.');
        $info['lastmod'] = $stats[9];
        $info['isoformattedlastmod'] = strftime('%Y-%m-%d %T', $stats[9]);

        $info['size_x'] = $att->get_parameter('midcom.helper.datamanager2.type.blobs', 'size_x');
        $info['size_y'] = $att->get_parameter('midcom.helper.datamanager2.type.blobs', 'size_y');
        $info['size_line'] = $att->get_parameter('midcom.helper.datamanager2.type.blobs', 'size_line');

        $info['object'] =& $this->attachments[$identifier];
        $info['identifier'] = $identifier;

        $this->attachments_info[$identifier] = $info;
    }

    function convert_to_storage()
    {
        // Synchronize the parameters again with the current attachment listing, just to
        // be on the safe side.
        $this->_save_attachment_listing();

        return '';
    }

    /**
     * This function synchronizes the attachment listing parameter of this field with the
     * current attachment state.
     */
    function _save_attachment_listing()
    {
        $data = Array();
        foreach ($this->attachments as $identifier => $attachment)
        {
            if (!mgd_is_guid($attachment->guid))
            {
                continue;
            }

            $data[] = "{$identifier}:{$attachment->guid}";
        }

        // We need to be selective when saving, excluding one case: empty list
        // with empty storage object. In that case we store nothing. If we have
        // an object, we set the parameter unconditionally, to get all deletions.
        if ($this->storage->object)
        {
            $this->storage->object->set_parameter('midcom.helper.datamanager2.type.blobs', "guids_{$this->name}", implode(',', $data));
        }
        else if ($data)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('We were told to store attachment GUIDs, but no storage object was present. This should not happen, ignoring silently.',
                MIDCOM_LOG_WARN);
            debug_print_r('This data should have been stored:', $data);
            debug_pop();
        }
    }

    /**
     * Adds a new attachment based on a file on-disk.
     *
     * This is a wrapper for add_attachment_by_handle() which works with an existing
     * file based on its name, not handle. The file is deleted after successful processing,
     * unless you set the fourth parameter to false.
     *
     * This file version will automatically evaluate the file with getimagesize so that
     * convenience methods of them are available.
     *
     * @param string $identifier The identifier of the new attachment.
     * @param string $filename The filename to use after processing.
     * @param string $title The title of the attachment to use.
     * @param string $mimetype The MIME Type of the file.
     * @param string $tmpname The name of the source file.
     * @param boolean $autodelete Set this to true (the default) to automatically delete the
     *     file after successful processing.
     * @return boolean Indicating success.
     */
    function add_attachment($identifier, $filename, $title, $mimetype, $tmpname, $autodelete = true)
    {
        if (! file_exists($tmpname))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Cannot add attachment, the file {$tmpname} was not found.", MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }

        if (!$this->file_sanity_checks($tmpname))
        {
            // the method will log errors and raise uimessages as needed
            return false;
        }

        // Ensure that the filename is URL safe (but allow multiple extensions)
        // PONDER: make use of this configurable in type-config ??
        $filename = midcom_helper_datamanager2_type_blobs::safe_filename($filename, false);

        $handle = @fopen($tmpname, 'r');
        if (! $handle)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Cannot add attachment, could not open {$tmpname} for reading.", MIDCOM_LOG_INFO);
            if (isset($php_errormsg))
            {
                debug_add("Last PHP error was: {$php_errormsg}", MIDCOM_LOG_INFO);
            }
            debug_pop();
            return false;
        }
        if (! $this->add_attachment_by_handle($identifier, $filename, $title, $mimetype, $handle, true, $tmpname))
        {
            fclose($handle);
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Failed to create attachment, see above for details.');
            debug_pop();
            return false;
        }

        if ($autodelete)
        {
            if (! @unlink($tmpfile))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add('Failed to automatically delete the source file, ignoring silently.', MIDCOM_LOG_WARN);
                if (isset($php_errormsg))
                {
                    debug_add("Last PHP error was: {$php_errormsg}", MIDCOM_LOG_WARN);
                }
                debug_pop();
            }
        }

        return true;
    }

    /**
     * This is a simple helper which evaluates the imagesize information of a given
     * file and adds that information as parameters to the attachment identified by
     * its identifier.
     *
     * @param string $identifier
     */
    function _set_image_size($identifier, $filename)
    {
        $data = @getimagesize($filename);
        if ($data)
        {
            $this->attachments[$identifier]->parameter('midcom.helper.datamanager2.type.blobs', 'size_x', $data[0]);
            $this->attachments[$identifier]->parameter('midcom.helper.datamanager2.type.blobs', 'size_y', $data[1]);
            $this->attachments[$identifier]->parameter('midcom.helper.datamanager2.type.blobs', 'size_line', $data[3]);
            if (! $this->attachments[$identifier]->mimetype)
            {
                switch ($data[2])
                {
                    case 1:
                        $this->attachments[$identifier]->mimetype = 'image/gif';
                        $this->attachments[$identifier]->update();
                        break;

                    case 2:
                        $this->attachments[$identifier]->mimetype = 'image/jpeg';
                        $this->attachments[$identifier]->update();
                        break;

                    case 3:
                        $this->attachments[$identifier]->mimetype = 'image/png';
                        $this->attachments[$identifier]->update();
                        break;

                    case 6:
                        $this->attachments[$identifier]->mimetype = 'image/bmp';
                        $this->attachments[$identifier]->update();
                        break;

                    case 7:
                    case 8:
                        $this->attachments[$identifier]->mimetype = 'image/tiff';
                        $this->attachments[$identifier]->update();
                        break;
                }
            }
        }
    }

    /**
     * Adds a new attachment based on a file on-disk.
     *
     * This call will create a new attachment object based on the given file, add it to the
     * attachment list and synchronize all attachment operations. It works on an open file
     * handle, which is closed after successful processing, unless you set the forth parameter
     * to false.
     *
     * @param string $identifier The identifier of the new attachment.
     * @param string $filename The filename to use after processing.
     * @param string $title The title of the attachment to use.
     * @param string $mimetype The MIME Type of the file.
     * @param resource $source A file handle prepared to read of the the source file.
     * @param boolean $autoclose Set this to true if the file handle should automatically be closed
     *     after successful processing.
     * @param string $tmpfile In case you have a filename to the source handle, you should specify
     *     it here. It will be used to load getimagesize information directly (rather then doing a
     *     temporary copy). The default null indicates that the source file location is unknown.
     * @return boolean Indicating success.
     */
    function add_attachment_by_handle($identifier, $filename, $title, $mimetype, $source, $autoclose = true, $tmpfile = null)
    {
        if ( array_key_exists($identifier, $this->attachments))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to add the attachment record: The identifier '{$identifier}' is already in use.", MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }

        // Ensure that the filename is URL safe (but allow multiple extensions)
        // PONDER: make use of this configurable in type-config ??
        $filename = midcom_helper_datamanager2_type_blobs::safe_filename($filename, false);

        // Obtain a temporary object if necessary. This is the only place where this needs to be
        // done (all other I/O ops are logically behind the add operation).
        if (! $this->storage->object)
        {
            $this->storage->create_temporary_object();
        }

        // Try to create a new attachment.
        $attachment = $this->storage->object->create_attachment($filename, $title, $mimetype);
        if (! $attachment)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Failed to create attachment record, see above for details.', MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }

        $this->attachments[$identifier] =& $attachment;
        $this->_save_attachment_listing();

        $attachment->copy_from_handle($source);

        if ($autoclose)
        {
            fclose($source);
        }

        // Add the following parameters for backup purposes only, so that the attachment
        // listing can be reconstructed when required. They are not required for regular
        // operation.
        $attachment->set_parameter('midcom.helper.datamanager2.type.blobs', 'fieldname', $this->name);
        $attachment->set_parameter('midcom.helper.datamanager2.type.blobs', 'identifier', $identifier);

        if ($tmpfile !== null)
        {
            $this->_set_image_size($identifier, $tmpfile);
        }
        else
        {
            // TODO: needs create temporary copy function.
            die ('TODO');
        }

        $this->_update_attachment_info($identifier);
        $this->_sort_attachments();

        if ($this->storage->object)
        {
            $document = new midcom_services_indexer_document_attachment($attachment, $this->storage->object);
            $indexer =& $_MIDCOM->get_service('indexer');
            $indexer->index($document);
        }

        return true;
    }

    /**
     * Updates the title field of the specified attachment. 
     *
     * This will automatically update the attachment info as well.
     *
     * @param string $identifier The identifier of the new attachment.
     * @param string $title The new title of the attachment, set this to null to
     *     keep the original title unchanged.
     * @return boolean Indicating success.
     */
    function update_attachment_title($identifier, $title)
    {
        if (! array_key_exists($identifier, $this->attachments))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to update the attachment title: The identifier {$identifier} is unknown", MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }

        $this->attachments[$identifier]->title = $title;
        if (! $this->attachments[$identifier]->update())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Failed to update the attachment title: Last Midgard error was: ' . mgd_errstr(), MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }

        $this->_update_attachment_info($identifier);

        return true;
    }

    /**
     * Update an existing attachment with a new file (this keeps GUIDs stable).
     *
     * @param string $identifier The identifier of the attachment to update.
     * @param string $tmpname The name of the source file.
     * @param string $filename The filename to use after processing.
     * @param string $title The new title of the attachment, set this to null to
     *     keep the original title unchanged.
     * @param string $mimetype The new MIME Type of the file, set this to null to
     *     keep the original title unchanged. If you are unsure of the mime type,
     *     set this to '' not null, this will enforce a redetection.
     * @param boolean $autodelete Set this to true (the default) to automatically delete the
     *     file after successful processing.
     * @return boolean Indicating success.
     */
    function update_attachment($identifier, $filename, $title, $mimetype, $tmpname, $autodelete = true)
    {
        if (! file_exists($tmpname))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Cannot add attachment, the file {$tmpname} was not found.", MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }

        if (!$this->file_sanity_checks($tmpname))
        {
            // the method will log errors and raise uimessages as needed
            return false;
        }

        $handle = @fopen($tmpname, 'r');
        if (! $handle)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Cannot add attachment, could not open {$tmpname} for reading.", MIDCOM_LOG_INFO);
            if (isset($php_errormsg))
            {
                debug_add("Last PHP error was: {$php_errormsg}", MIDCOM_LOG_INFO);
            }
            debug_pop();
            return false;
        }

        if (! $this->update_attachment_by_handle($identifier, $filename, $title, $mimetype, $handle, true, $tmpname))
        {
            fclose($handle);
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Failed to create attachment, see above for details.');
            debug_pop();
            return false;
        }

        if ($autodelete)
        {
            if (! @unlink($tmpfile))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add('Failed to automatically delete the source file, ignoring silently.', MIDCOM_LOG_WARN);
                if (isset($php_errormsg))
                {
                    debug_add("Last PHP error was: {$php_errormsg}", MIDCOM_LOG_WARN);
                }
                debug_pop();
            }
        }

        return true;
    }

    /**
     * Update an existing attachment with a new file (this keeps GUIDs stable).
     *
     * @param string $identifier The identifier of the new attachment.
     * @param resource $handle A file handle prepared to read of the the source file.
     * @param string $title The new title of the attachment, set this to null to
     *     keep the original title unchanged.
     * @param string $mimetype The new MIME Type of the file, set this to null to
     *     keep the original title unchanged. If you are unsure of the mime type,
     *     set this to '' not null, this will enforce a redetection.
     * @param boolean $autoclose Set this to true if the file handle should automatically be closed
     *     after successful processing.
     * @param string $tmpfile In case you have a filename to the source handle, you should specify
     *     it here. It will be used to load getimagesize information directly (rather then doing a
     *     temporary copy). The default null indicates that the source file location is unknown.
     * @return boolean Indicating success.
     */
    function update_attachment_by_handle($identifier, $filename, $title, $mimetype, $source, $autoclose = true, $tmpfile = null)
    {
        if (! array_key_exists($identifier, $this->attachments))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to update the attachment record: The identifier {$identifier} is unknown", MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }

        $attachment =& $this->attachments[$identifier];
        if ($title !== null)
        {
            $attachment->title = $title;
        }
        if ($mimetype !== null)
        {
            $attachment->mimetype = $mimetype;
        }

        $attachment->copy_from_handle($source);
        if ($autoclose)
        {
            fclose($source);
        }

        $attachment->title = $title;
        $attachment->name = $filename;
        if (! $attachment->update())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Failed to update the attachment record.', MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }

        // Add the following parameters for backup purposes only, so that the attachment
        // listing can be reconstructed when required. They are not required for regular
        // operation.
        $attachment->set_parameter('midcom.helper.datamanager2.type.blobs', 'fieldname', $this->name);
        $attachment->set_parameter('midcom.helper.datamanager2.type.blobs', 'identifier', $identifier);

        if ($tmpfile !== null)
        {
            $this->_set_image_size($identifier, $tmpfile);
        }
        else
        {
            // TODO: needs create temporary copy function.
            die ('TODO');
        }

        $this->_update_attachment_info($identifier);
        $this->_sort_attachments();

        if ($this->storage->object)
        {
            $document = new midcom_services_indexer_document_attachment($attachment, $this->storage->object);
            $indexer =& $_MIDCOM->get_service('indexer');
            $indexer->index($document);
        }

        return true;
    }

    /**
     * Deletes an existing attachment.
     *
     * @param string $identifier The identifier of the attachment that should be deleted.
     * @return boolean Indicating success.
     */
    function delete_attachment($identifier)
    {
        if (! array_key_exists($identifier, $this->attachments))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Failed to delete the attachment record: The identifier is unknown.', MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }

        $attachment = $this->attachments[$identifier];
        if (! $attachment->delete())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Failed to delete the attachment record: DBA delete call returned false.', MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }

        unset($this->attachments[$identifier]);
        unset($this->attachments_info[$identifier]);
        $this->_sort_attachments();
        $this->_save_attachment_listing();

        return true;
    }

    /**
     * This call will unconditionally delete all attachments currently contained by the type.
     *
     * @return boolean Indicating success.
     */
    function delete_all_attachments()
    {
        foreach ($this->attachments as $identifier => $attachment)
        {
            if (! $this->delete_attachment($identifier))
            {
                return false;
            }
        }
        return true;
    }

    function convert_from_csv ($source)
    {
        // TODO: Not yet supported
        return '';
    }

    function convert_to_raw()
    {
        return $this->convert_to_csv();
    }

    function convert_to_csv()
    {
        $results = array();
        if ($this->attachments_info)
        {
            foreach ($this->attachments_info as $info)
            {
                $results[] = $info['url'];
            }
        }

        if (empty($results))
        {
            return '';
        }
        else
        {
            return implode(',', $results);
        }
    }

    function convert_to_html()
    {
        $result = '';
        if ($this->attachments_info)
        {
            $result .= "<ul>\n";
            foreach($this->attachments_info as $identifier => $info)
            {
                if ($info['description'])
                {
                    $title = "{$info['filename']} - {$info['description']}";
                }
                else
                {
                    $title = $info['filename'];
                }
                $result .= "<li><a href='{$info['url']}'>{$title}</a></li>\n";
            }
            $result .= "</ul>\n";
        }
        return $result;
    }

    /**
     * Rewrite a filename to URL safe form
     *
     * @param string $filename file name to rewrite
     * @param boolean $force_single_extension force file to single extension (defaults to true)
     * @return string rewritten filename
     */
    function safe_filename($filename, $force_single_extension = true)
    {
        $filename = trim($filename);
        if ($force_single_extension)
        {
            $regex = '/^(.*)(\..*?)$/';
        }
        else
        {
            $regex = '/^(.*?)(\..*)$/';
        }
        if (preg_match($regex, $filename, $ext_matches))
        {
            $name = $ext_matches[1];
            $ext = $ext_matches[2];
        }
        else
        {
            $name = $filename;
            $ext = '';
        }
        return midcom_generate_urlname_from_string($name) . $ext;
    }

    /**
     * Creates a working copy to filesystem from given attachment object
     *
     * @param object $att the attachment object to copy
     * @return string tmp file name (or false on failure)
     */
    function create_tmp_copy($att)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $src = $att->open('r');
        if (!$src)
        {
            debug_add("Could not open attachment #{$att->id} for reading", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $tmpname = tempnam($GLOBALS['midcom_config']['midcom_tempdir'], 'midcom_helper_datamanager2_type_blobs_');
        $dst = fopen($tmpname, 'w+');
        if (!$dst)
        {
            debug_add("Could not open file '{$tmpname}' for writing", MIDCOM_LOG_ERROR);
            debug_pop();
            unlink($tmpname);
            return false;
        }
        while (! feof($src))
        {
            $buffer = fread($src, 131072); /* 128 kB */
            fwrite($dst, $buffer, 131072);
        }
        $att->close();
        fclose($dst);
        debug_pop();
        return $tmpname;
    }


    /**
     * Makes sanity checks on the uploaded file, used by add_attachment and update_attachment
     *
     * @see add_attachment
     * @see update_attachment
     * @param string $filepath path to file to check
     * @return boolean indicating sanity
     */
    function file_sanity_checks($filepath)
    {
        static $checked_files = array();
        static $checks = array
        (
            'sizenotzero',
            'avscan',
        );
        // Do not check same file twice
        if (isset($checked_files[$filepath]))
        {
            return $checked_files[$filepath];
        }
        foreach ($checks as $check)
        {
            $methodname = "file_sanity_checks_{$check}";
            /*
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Calling \$this->{$methodname}({$filepath})");
            debug_pop();
            */
            if (!$this->$methodname($filepath))
            {
                // the methods will log their own errors
                $checked_files[$filepath] = false;
                return false;
            }
        }
        $checked_files[$filepath] = true;
        return true;
    }

    /**
     * Make sure given file is larger than zero bytes
     *
     * @see file_sanity_checks
     * @return boolean indicating sanity
     */
    function file_sanity_checks_sizenotzero($filepath)
    {
        $size = @filesize($filepath);
        if ($size == 0)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("filesize('{$filepath}') returned {$size} which evaluated to zero", MIDCOM_LOG_ERROR);
            debug_pop();
            // TODO: UIMessage ?
            return false;
        }
        return true;
    }

    /**
     * Scans the file for virii
     *
     * @see file_sanity_checks
     * @return boolean indicating sanity
     */
    function file_sanity_checks_avscan($filepath)
    {
        $scan_template = $this->_config->get('type_blobs_avscan_command');
        if (empty($scan_template))
        {
            // silently ignore if scan command not configured
            return true;
        }
        $scan_command = escapeshellcmd(sprintf($scan_template, $filepath));
        $scan_output = array();
        exec($scan_command, $scan_output, $exit_code);
        if ($exit_code !== 0)
        {
            // Scan command returned error (likely infected file);
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("{$scan_command} returned {$exit_code}, likely file is infected", MIDCOM_LOG_ERROR);
            debug_print_r('scanner_output', $scan_output, MIDCOM_LOG_ERROR);
            debug_pop();
            $_MIDCOM->uimessages->add($this->_l10n_midcom->get('midcom.helper.datamanager2'), $this->_l10n->get('virus found in uploaded file'), 'error');
            return false;
        }
        return true;
    }
}

?>