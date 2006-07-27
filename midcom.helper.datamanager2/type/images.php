<?php
/**
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/** @ignore */
require_once('blobs.php');

/**
 * Datamanger 2 Images type.
 *
 * This type encaspulates a unlimited list of uploaded images each along with an optional
 * number of derived images like thumbnails. Both the main image and the derived thumbnails
 * will be ran through a defined filter chain. The originally uploaded files can be
 * kept optionally.
 *
 * Similar to the downloads widget, the individual images are distinguished using md5
 * based hashes (not their actual filenames).
 *
 * <b>Image identifier naming conventions</b>
 *
 * The various images are distinguished using these suffixes to their identifiers:
 *
 * The original image will be available with the "original" identifier prefix unless
 * configured otherwise. The main image used for display is available as "main",
 * which will be ensured to be web-compatible. (This
 * distinction is important in case you upload TIFF or other non-web-compatible
 * images. All derived images will be available under the prefixes defined in the schema
 * configuration.
 *
 * An optional "quick" thumbnail mode is available as well where you just specify the
 * maximum frame of a thumbnail to-be-generated. The auto-generated image will then be
 * available in the attachment identified as "thumbnail".
 *
 * <b>File type conversion and image filtering</b>
 *
 * The class uses the image filter system for the actual resizing and conversion work,
 * you need to specifiy all operations including resize operations in the filtering
 * chain declaration for the corresponding derived image.
 *
 * Regarding file type conversions: The original uploaded image will always be run through
 * an automatic type conversion as the very first step before any further processing is
 * done. From that point, no further type conversion is done unless the user specifies
 * another one in the filter chains for a derived type. A manual initial image conversion
 * is not yet supported by the type.
 *
 * All derived images will be computed from the initially converted, uploaded image,
 * which should minimize the losses of the subsequent conversions. This intermediate
 * image will not be kept an any place. The keep_original option will only save the
 * unmodified, uploaded file.
 *
 * The recreate_images call (not yet implemented) will recreate all derived images from
 * the original image. If that image is not available, the generated main image is used
 * instead.
 *
 * <b>Derived image naming warning</b>
 *
 * Be aware that the type holds <em>no</em> safety code to guard against duplicate image
 * identifiers (e.g. defining a "main" image in the derived images list). The results
 * of such a configuration is undefined.
 *
 * In addition, you should only use lowercase alphanumeric letters to identify images,
 * as other characters like underscores or spaces are reserved for type-internal usage.
 *
 * <b>Available configuration options:</b>
 *
 * - bool keep_original controls whether you want to keep the orginally uploaded
 *   file available. This option is disabled by default.
 * - string filter_chain The filter chain used to render the main image. This chain
 *   empty (null) by default.
 * - Array derived_images A list of derived images to construct from the main image.
 *   This option consists of a list of identfier/filter chain declarations. They will
 *   be constructed in order, each using a fresh copy of the (initially type-converted)
 *   original image. This options may be null (the default) indicating no derived
 *   images. Note, that the system will detect any explicit image type conversions
 *   you're doing in a filter chain, setting the attachments' Mime-Type property
 *   automagically.
 * - Array auto_thumbnail This array holds the maximum size of the thumbnail to create
 *   with automatic defaults. The array holds a maximum width/height pair as first and
 *   second element of the array, nothing else. The image will be available as
 *   "thumbnail". This image will be constructed after constructing all explicitly
 *   defined derived images. This option may be null (the default) indicating no
 *   thumbnail.
 *
 * <b>Implementation note:</b>
 *
 * Due to the fact that Imagemagick is used for most operations, this type is currently
 * only capable of operating based on actual files, not file handles.
 *
 *
 * @todo Implement thumbnail interface.
 * @todo Operation on file handles.
 * @todo Derived-images recreation.
 *
 * @package midcom.helper.datamanager2
 */
class midcom_helper_datamanager2_type_images extends midcom_helper_datamanager2_type_blobs
{
    /**
     * Set this to true to keep the original file available as "original".
     *
     * @var bool
     */
    var $keep_original = false;

    /**
     * The filter chain to use to create the "main" image.
     *
     * @var string
     * @see midcom_helper_imagefilter
     */
    var $filter_chain = null;

    /**
     * The list of derived images to construct.
     *
     * @var array
     */
    var $derived_images = null;

    /**
     * The maximum witdh/height (in this order) of the thumbnail to be auto-created.
     *
     * @var array.
     */
    var $auto_thumbnail = null;

    /**
     * The list of images. This is essentially a reordered version of the
     * $attachments_info member: It is multi-dimensional, mapping the image identifier
     * with all images available for that image. The actual images will only have the
     * derived image name (like "thumbnail" as array key.
     *
     * Example: Assume we have the images "original", "main" and "thumbnail"
     * available for each image. While the $attachments and $attachments_info lists will
     * just identify the images with their full handle,
     * $attachments_info["{$md5identifier}_main"], this member will list the attachment
     * as $images[$md5identifier]['main'] (as well as ...['original'] and
     * ['thumbnail']).
     *
     * The actual structures are references to the corresponding $_attachments_info
     * entries.
     *
     * @var Array
     * @see $attachments_info
     */
    var $images = Array();

    /**
     * This array maps attachment identifiers to image identifier/derived image name pairs.
     * The main purpose of this member is for easier mapping between blobs and images.
     *
     * The array is indexed by attachment identifier and contains Arrays containing image
     * identifier and derived image name consecutivly (without any special indexes).
     *
     * Updates are done on load of the types' data and on every attachment add/delete
     * operation.
     *
     * @var Array
     * @access protected
     */
    var $_attachment_map = Array();

    /**
     * The image title entered by the user. Stored in each attachments
     * title field.
     *
     * Used during processing.
     *
     * @var string
     * @access private
     */
    var $_title = null;

    /**
     * The original filename of the uploaded file.
     *
     * Used during processing.
     *
     * @access private
     * @var string
     */
    var $_filename = null;

    /**
     * The name of the original temporary uploaded file (which will already be converted
     * to a Web-Aware format).
     *
     * Used during processing.
     *
     * @access private
     * @var string
     */
    var $_original_tmpname = null;

    /**
     * The current working file.
     *
     * Used during processing.
     *
     * @access private
     * @var string
     */
    var $_current_tmpname = null;

    /**
     * The image-filter instance to use.
     *
     * Used during processing.
     *
     * @access private
     * @var midcom_helper_imagefilter
     */
    var $_filter = null;

    /**
     * The target mimetype used after automatic conversion for all
     * generated images.
     *
     * Used during processing.
     *
     * @access private
     * @var string
     */
    var $_target_mimetype = null;

    /**
     * The original mimetype of the uploaded file.
     *
     * Used during processing.
     *
     * @access private
     * @var string
     */
    var $_original_mimetype = null;

    /**
     * This list is used when updating an existing image. It keeps track
     * of which attachments have been updated already when replacing an existing
     * image. All attachments belonging to the image being updated still listed
     * here after an set_image call will be deleted. This keeps attachment GUIDs
     * stable during updates but also adds resilence against against changed type
     * configuration.
     *
     * Used during processing.
     *
     * @access private
     * @var Array
     */
    var $_pending_attachments = null;

    /**
     * The current image identifier to use when operating on images.
     *
     * @access private
     * @var string
     */
    var $_identifier = null;

    /**
     * Internal helper function, determines the mime-type of the specified file.
     *
     * The call uses the "file" utility which must be present for this type to work.
     *
     * @param string $filename The file to scan
     * @return string The autodetected mime-type
     */
    function _get_mimetype($filename)
    {
        return exec("{$GLOBALS['midcom_config']['utility_file']} -ib {$filename} 2>/dev/null");
    }

    /**
     * Adds a new image to the list.
     *
     * Unless specified, the function creates an unique image identifier based on the
     * hash of the current timestamp, the uploaded filename and the name of the temporary
     * upload file.
     *
     * @param string $filename The name of the image attachment to be created.
     * @param string $tmpname The file to load.
     * @param string $title The title of the image.
     * @param bool $autodelete If this is true, the temporary file will be deleted
     *     after postprocessing and attachment-creation.
     * @param string $identifier The identifier to use for the attaachment. This is usually
     *     auto-created, so you don't have to bother about this.
     * @return mixed Returns the identifier of the created image on success or false
     *     on failure.
     */
    function add_image($filename, $tmpname, $title, $autodelete = true, $identifier = null)
    {
        if ($identifier === null)
        {
            $identifier = md5(time() . $filename . $tmpname);
        }
        if (! $this->_set_image($identifier, $filename, $tmpname, $title, $autodelete))
        {
            return false;
        }

        return $identifier;
    }

    /**
     * Updates an existing image.
     *
     * @param string $identifier The image identifier to use.
     * @param string $filename The name of the image attachment to be created.
     * @param string $tmpname The file to load.
     * @param string $title The title of the image.
     * @param bool $autodelete If this is true, the temporary file will be deleted
     *     after postprocessing and attachment-creation.
     * @return bool Indicating success.
     */
    function update_image($identifier, $filename, $tmpname, $title, $autodelete = true)
    {
        if (! array_key_exists($identifier, $this->images))
        {
            return false;
        }

        return $this->_set_image($identifier, $filename, $tmpname, $title, $autodelete);
    }

    /**
     * Adds or updates an image to the type. Loads and processes the $tmpname
     * file on disk. The identifier is used to select the image in question.
     *
     * @param string $identifier The image identifier to use.
     * @param string $filename The name of the image attachment to be created.
     * @param string $tmpname The file to load.
     * @param string $title The title of the image.
     * @param bool $autodelete If this is true, the temporary file will be deleted
     *     after postprocessing and attachment-creation.
     * @return bool Indicating success.
     * @access protected
     */
    function _set_image($identifier, $filename, $tmpname, $title, $autodelete)
    {
        // First, ensure that the imagefilter helper is available.
        require_once(MIDCOM_ROOT . '/midcom/helper/imagefilter.php');

        // Prepare Internal Members
        $this->_identifier = $identifier;
        $this->_title = $title;
        $this->_filename = $filename;
        $this->_original_tmpname = $tmpname;
        $this->_original_mimetype = $this->_get_mimetype($this->_original_tmpname);
        $this->_filter = new midcom_helper_imagefilter();
        if (array_key_exists($this->_identifier, $this->images))
        {
            $this->_pending_attachments = $this->images[$this->_identifier];
        }
        else
        {
            $this->_pending_attachments = Array();
        }

        // 1st step: original image storage and auto-conversion..
        if (   ! $this->_save_original()
            || ! $this->_filter->set_file($this->_original_tmpname)
            || ! $this->_auto_convert_to_web_type())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to process the conversion batch 1 (save original & web conversion) for the uploaded file {$filename} in {$tmpname}, aborting type processing.",
                MIDCOM_LOG_ERROR);
            debug_pop();

            // Clean up
            $this->delete_image($this->_identifier);

            return false;
        }

        // Prepare all other images.
        if (   ! $this->_save_main_image()
            || ! $this->_add_thumbnail_to_derived_images()
            || ! $this->_save_derived_images())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to process the conversion batch 2 (derived images) for the uploaded file {$filename} in {$tmpname}, aborting type processing.",
                MIDCOM_LOG_ERROR);
            debug_pop();

            // Clean up
            $this->delete_image($this->_identifier);

            return false;
        }

        // Clear up all attachments no longer in use:
        foreach ($this->_pending_attachments as $name => $attachment_info)
        {
            $blob_identifier = "{$this->identifier}{$name}";
            unset($this->_attachment_map[$blob_identifier]);
            $this->delete_attachment($blob_identifier);
        }

        if ($autodelete)
        {
            unlink ($this->_original_tmpname);
        }

        $this->_save_image_listing();

        return true;
    }

    function update_image_title($identifier, $title)
    {
        if (! array_key_exists($identifier, $this->images))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to update the image title: The identifier {$identifier} is unknown", MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }

        foreach ($this->images[$identifier] as $name => $info)
        {
            if (! $this->update_attachment_title($info['identifier'], $title))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Failed to update the image title: Could not update attachment {$info['identifier']} bailing out.");
                debug_pop();
                return false;
            }
        }

        return true;
    }

    /**
     * Deletes the specified image.
     *
     * @param string $identifier The identifier of the image to delete.
     */
    function delete_image($identifier)
    {
        if (array_key_exists($identifier, $this->images))
        {
            foreach ($this->images[$identifier] as $name => $info)
            {
                $blob_identifier = "{$identifier}{$name}";
                unset($this->_attachment_map[$blob_identifier]);
                $this->delete_attachment($blob_identifier);
            }
            unset ($this->images[$identifier]);
        }

        $this->_save_image_listing();
        return true;
    }

    /**
     * Small internal helper function. It adds a derived 'thumbnail' image to the list
     * used if and only if the auto_thumbnail option is set. Any existing thumbnail
     * declaration will be silently overwritten!
     *
     * @return bool Indicating success.
     */
    function _add_thumbnail_to_derived_images()
    {
        if ($this->auto_thumbnail)
        {
            if (! $this->derived_images)
            {
                $this->derived_images = Array();
            }
            $this->derived_images['thumbnail'] = "resize({$this->auto_thumbnail[0]},{$this->auto_thumbnail[1]})";
        }

        return true;
    }

    /**
     * This loops over the defined derived images (if any) and constructs
     * each of them in turn.
     *
     * @return bool Indicating success.
     */
    function _save_derived_images()
    {
        if ($this->derived_images)
        {
            foreach ($this->derived_images as $name => $filter_chain)
            {
                if (! $this->_create_working_copy())
                {
                    return false;
                }

                $result = $this->_save_derived_image($name);
                @unlink($this->_current_tmpname);
                if (! $result)
                {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * This is the actual code which filters and saves a derived image.
     *
     * @param string $name The derived image to construct.
     * @return bool Indicating success
     */
    function _save_derived_image($name)
    {
        if (! $this->_filter->process_chain($this->derived_images[$name]))
        {
            return false;
        }

        $blob_identifier = "{$this->_identifier}{$name}";

        if (array_key_exists($name, $this->_pending_attachments))
        {
            unset($this->_pending_attachments[$name]);
            return $this->update_attachment($blob_identifier,
                                            "{$name}_{$this->_filename}",
                                            $this->_title,
                                            $this->_get_mimetype($this->_current_tmpname),
                                            $this->_current_tmpname,
                                            false);
        }
        else
        {
            $this->_attachment_map[$blob_identifier] = Array($this->_identifier, $name);
            return $this->add_attachment($blob_identifier,
                                         "{$name}_{$this->_filename}",
                                         $this->_title,
                                         $this->_get_mimetype($this->_current_tmpname),
                                         $this->_current_tmpname,
                                         false);
        }
    }

    /**
     * Saves the main image to the type, doing transformation work if configured to do so.
     *
     * @return bool Indicating success.
     */
    function _save_main_image()
    {
        if (! $this->_create_working_copy())
        {
            return false;
        }

        $result = true;

        // Filter if neccessary.
        if (   $this->filter_chain
            && ! $this->_filter->process_chain($this->filter_chain))
        {
            $result = false;
        }

        if ($result)
        {
            $blob_identifier = "{$this->_identifier}main";
            if (array_key_exists('main', $this->_pending_attachments))
            {
                unset($this->_pending_attachments['main']);
                $result = $this->update_attachment($blob_identifier,
                                                   $this->_filename,
                                                   $this->_title,
                                                   $this->_target_mimetype,
                                                   $this->_current_tmpname,
                                                   false);
            }
            else
            {
                $this->_attachment_map[$blob_identifier] = Array($this->_identifier, 'main');
                $result = $this->add_attachment($blob_identifier,
                                                $this->_filename,
                                                $this->_title,
                                                $this->_target_mimetype,
                                                $this->_current_tmpname,
                                                false);
            }
        }
        @unlink($this->_current_tmpname);

        return $result;
    }

    /**
     * This function creates a new working copy and stores the filename in _current_tmpname.
     * (Beware of consecutive uses with current_tmpname, which will be silently overwritten,
     * the old file must be unlinked by the callee.) The filter instance will automatically
     * be set to the new file.
     *
     * @access private
     * @return bool Indicating success.
     */
    function _create_working_copy()
    {
        $this->_current_tmpname = tempnam("/tmp", "midcom_helper_datamanager2_type_image");
        $src = fopen($this->_original_tmpname, 'r');
        $dst = fopen($this->_current_tmpname, 'w+');
        while (! feof($src))
        {
            $buffer = fread($src, 131072); /* 128 kB */
            fwrite($dst, $buffer, 131072);
        }
        fclose($src);
        fclose($dst);
        return $this->_filter->set_file($this->_current_tmpname);
    }

    /**
     * If we are configured to do so, we save the original image.
     *
     * @return bool Indicating success
     */
    function _save_original()
    {
        if ($this->keep_original)
        {
            $blob_identifier = "{$this->_identifier}original";
            if (array_key_exists('original', $this->_pending_attachments))
            {
                unset($this->_pending_attachments['original']);
                return $this->update_attachment($blob_identifier,
                                                "original_{$this->_filename}",
                                                $this->_title,
                                                $this->_original_mimetype,
                                                $this->_original_tmpname,
                                                false);
            }
            else
            {
                $this->_attachment_map[$blob_identifier] = Array($this->_identifier, 'original');
                return $this->add_attachment($blob_identifier,
                                             "original_{$this->_filename}",
                                             $this->_title,
                                             $this->_original_mimetype,
                                             $this->_original_tmpname,
                                             false);
            }
        }
        return true;
    }

    /**
     * Automatically convert the uploaded file to a web-compatible type. Uses
     * only the first image of multi-page uploads (like PDFs) and populates the
     * _target_mimetype member accordingly. The orignal_tmpname file is manipulated
     * directly.
     *
     * Uploaded GIF, PNG and JPEG files are left untouched.
     *
     * In case of any conversions being done, the new extension will be appended
     * to the uploaded file.
     *
     * @return bool Indicating success
     */
    function _auto_convert_to_web_type()
    {
        switch ($this->_original_mimetype)
        {
            case 'image/png':
            case 'image/gif':
            case 'image/jpeg':
                $this->_target_mimetype = $this->_original_mimetype;
                $conversion = null;
                break;

            case 'application/postscript':
            case 'application/pdf':
                $this->_target_mimetype = 'image/png';
                $conversion = 'png';
                break;

            default:
                $this->_target_mimetype = 'image/jpeg';
                $conversion = 'jpg';
                break;
        }

        if ($conversion)
        {
            $this->_filename .= ".{$conversion}";
            return $this->_filter->convert($conversion);
        }
        else
        {
            return true;
        }
    }

    /**
     * First, we load the attachment_map information so that we can collect all images
     * together. Then we call the base class.
     */
    function convert_from_storage($source)
    {
        $this->images = Array();
        $this->_attachment_map = Array();

        if ($this->storage->object === null)
        {
            // We don't have a storage object, skip the rest of the operations.
            parent::convert_from_storage($source);
            return;
        }

        $raw_list = $this->storage->object->get_parameter('midcom.helper.datamanger2.type.images', "attachment_map_{$this->name}");
        if (! $raw_list)
        {
            // No attachments found.
            parent::convert_from_storage($source);
            return;
        }

        $items = explode(',', $raw_list);

        foreach ($items as $item)
        {
            $info = explode(':', $item);
            $this->_attachment_map[$info[0]] = Array($info[1], $info[2]);
        }

        parent::convert_from_storage($source);

        /*
         * TODO: Title handling
        if (array_key_exists('main', $this->attachments))
        {
            $this->_title = $this->attachments['main']->title;
        }
        */
    }

    /**
     * Calls base class
     */
    function convert_to_storage()
    {
        /*
         * TODO: Title handling
        foreach ($this->attachments as $identifier => $copy)
        {
            $this->update_attachment_title($identifier, $this->_title);
        }
         */

        return parent::convert_to_storage();
    }


    /**
     * The HTML-Version of the image type can take two forms, depending on
     * type configuration:
     *
     * 1. If an 'thumbnail' image is present, it is shown and encaspulated in an
     *    anchor tag leading to the 'main' image.
     * 2. If no 'thumbnail' image is present, the 'main' image is shown
     *    directly, without any anchor.
     *
     * In case that there is no image uploaded, an empty string is returned.
     */
    function convert_to_html()
    {
        $result = '';
        if ($this->images)
        {
            $result .= "<ul class='midcom_helper_datamanager2_type_images_list'>";
            foreach ($this->images as $identifier => $images)
            {
                $result .= "<li>";
                $main = $images['main'];
                $title = ($main['description']) ? $main['description'] : $main['filename'];
                
                if (array_key_exists('thumbnail', $images))
                {
                    $thumb = $images['thumbnail'];
                    $result .= "<a href='{$main['url']}'><img src='{$thumb['url']}' {$thumb['size_line']} alt='{$title}' title='{$title}' /></a>";
                }
                else
                {
                    $result .= "<img src='{$main['url']}' {$main['size_line']} alt='{$title}' title='{$title}' />";
                }
                $result .= "</li>";
            }
            $result .= "</ul>";
        }
        return $result;
    }

    /**
     * This saves a map of attachment identifiers to image identifier/name pairs.
     * It is updated accordingly on all save operations and complements the base
     * types _save_attachment_listing. It is stored to have a safe way of loading
     * the images (heuristics could not be safe enough when subtypes create
     * non-md5 based identifiers).
     */
    function _save_image_listing()
    {
        $data = Array();
        foreach ($this->attachments_info as $identifier => $info)
        {
            $data[] = "{$identifier}:{$info['images_identifier']}:{$info['images_name']}";
        }

        // We need to be selective when saving, excluding one case: empty list
        // with empty storage object. In that case we store nothing. If we have
        // an object, we set the parameter unconditionally, to get all deletions.
        if ($this->storage->object)
        {
            $this->storage->object->set_parameter('midcom.helper.datamanger2.type.images', "attachment_map_{$this->name}", implode(',', $data));
        }
        else if ($data)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("We were told to store image infos, but no storage object was present. This should not happen, ignoring silently.",
                MIDCOM_LOG_WARN);
            debug_print_r("This data should have been stored:", $data);
            debug_pop();
        }
    }

    /**
     * Adds information specific to this type to the attachment info block (based on
     * the information in the $_attachment_map):
     *
     * - 'images_identifier' The identifier of the image set.
     * - 'images_name' contains the image name (like 'thumbnail', 'main' etc.) used
     *   when defining the conversion rules.
     *
     * In addition, this call will update the $images list as well.
     *
     * If the attachment is not present in the _attachment_map, it will be skipped
     * silently.
     */
    function _update_attachment_info($identifier)
    {
        parent::_update_attachment_info($identifier);

        if (! array_key_exists($identifier, $this->_attachment_map))
        {
            // Log and skip further processing.
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Could not find the attachment '{$identifier}' in the image map. Skipping it.",
                MIDCOM_LOG_INFO);
            debug_pop();
            return;
        }

        $images_identifier = $this->_attachment_map[$identifier][0];
        $images_name = $this->_attachment_map[$identifier][1];

        $this->attachments_info[$identifier]['images_identifier'] = $images_identifier;
        $this->attachments_info[$identifier]['images_name'] = $images_name;
        $this->images[$images_identifier][$images_name] =& $this->attachments_info[$identifier];
    }

    /**
     * This override adds a sorting call for the images member, which is sorted by main image
     * filename.
     */
    function _sort_attachments()
    {
        parent::_sort_attachments();
        uasort($this->images,
            Array('midcom_helper_datamanager2_type_images', '_sort_images_callback'));
    }

    /**
     * User-defined array sorting callback, used for sorting $images. See the
     * usort() documentation for further details.
     *
     * @access protected
     * @param Array $a The first image list.
     * @param Array $b The second image list.
     * @return int A value according to the rules from strcmp().
     */
    function _sort_images_callback($a, $b)
    {
        return strcasecmp($a['main']['filename'], $b['main']['filename']);
    }

}