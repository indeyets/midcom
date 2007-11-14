<?php
/**
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/** @ignore */
require_once('image.php');

/**
 * Datamanager 2 Images type.
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
class midcom_helper_datamanager2_type_images extends midcom_helper_datamanager2_type_image
{

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
     * The current image identifier to use when operating on images.
     *
     * @access private
     * @var string
     */
    var $_identifier = null;

    /**
     * The current image title to use when operating on images.
     *
     * @access private
     * @var string
     */
    var $_title = null;

    var $output_mode = 'html';

    /**
     * Adds a new image to the list.
     *
     * Unless specified, the function creates a unique image identifier based on the
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
        if (! $this->set_image($identifier, $filename, $tmpname, $title, $autodelete))
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

        return $this->set_image($identifier, $filename, $tmpname, $title, $autodelete);
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
    function set_image($identifier, $filename, $tmpname, $title, $autodelete)
    {
        if (empty($identifier))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("identifier must not be empty", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $this->_identifier = $identifier;
        $this->_title = $title;
        if (array_key_exists($this->_identifier, $this->images))
        {
            // PHP5-TODO: Must be copy-by-value
            $force_pending_attachments = $this->images[$this->_identifier];
        }
        else
        {
            $force_pending_attachments = Array();
        }
        if (!$this->_set_image($filename, $tmpname, $title, $autodelete, $force_pending_attachments))
        {
            return false;
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

        $raw_list = $this->storage->object->get_parameter('midcom.helper.datamanager2.type.images', "attachment_map_{$this->name}");
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
            if (   !is_array($info)
                || !array_key_exists(0, $info)
                || !array_key_exists(1, $info)
                || !array_key_exists(2, $info)
                )
            {
                // Broken item
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("item '{$item}' is broken!", MIDCOM_LOG_ERROR);
                debug_pop();
                continue;
            }
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
        if (empty($this->images))
        {
            return $result;
        }
        switch ($this->output_mode)
        {
            case 'html':
                $result .= "<ul class='midcom_helper_datamanager2_type_images_list'>";
                foreach ($this->images as $identifier => $images)
                {
                    $result .= "<li>";
                    switch(true)
                    {
                        case (isset($images['main'])):
                            $main = $images['main'];
                            break;
                        case (isset($images['original'])):
                            $main = $images['original'];
                            break;
                        default:
                            // ugly fallback...
                            $images_copy = $images;
                            $main = array_shift($images_copy);
                            break;
                    }
                    if (   !isset($main['object'])
                        || !is_object($main['object'])
                        || !isset($main['object']->guid)
                        || empty($main['object']->guid))
                    {
                        //Panic, broken identifier
                        debug_push_class(__CLASS__, __FUNCTION__);
                        debug_add("Identifier '{$identifier}' does not have a valid object behind it",  MIDCOM_LOG_ERROR);
                        debug_pop();
                        continue;
                    }
                    
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
                break;

            // FIXME: wouldn't it be better to access $dm2->types->imagesfield->images ??
            case 'array':
                $result = array();
                // FIXME: this probably does not work as expected, look into this
                foreach ($this->images as $identifier => $images)
                {
                    $tmp = $images['main'];
                    if (array_key_exists('thumbnail', $images))
                    {
                        $tmp['thumbnail'] = $images['thumbnail'];
                    }
                    $result[] = $tmp;
                }
                break;
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
            if (empty($identifier))
            {
                // Identifier must not be empty
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("\$identifier is empty, this is not allowed.", MIDCOM_LOG_ERROR);
                debug_pop();
                continue;
            }
            if (!array_key_exists('images_identifier', $info))
            {
                // We have somehow broken data, try heuristics
                $info['images_identifier'] = substr($identifier, 0, 32);
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("\$info['images_identifier'] was not set, used heuristics to set it to '{$info['images_identifier']}' (from '{$identifier}')", MIDCOM_LOG_WARN);
                debug_pop();
            }
            if (!array_key_exists('images_name', $info))
            {
                // We have somehow broken data, try heuristics
                $info['images_name'] = substr($identifier, 32);
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("\$info['images_name'] was not set, used heuristics to set it to '{$info['images_name']}' (from '{$identifier}')", MIDCOM_LOG_WARN);
                debug_pop();
            }
            $data[] = "{$identifier}:{$info['images_identifier']}:{$info['images_name']}";
        }

        // We need to be selective when saving, excluding one case: empty list
        // with empty storage object. In that case we store nothing. If we have
        // an object, we set the parameter unconditionally, to get all deletions.
        if ($this->storage->object)
        {
            $this->storage->object->set_parameter('midcom.helper.datamanager2.type.images', "attachment_map_{$this->name}", implode(',', $data));
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
        if (! array_key_exists($identifier, $this->attachments_info))
        {
            // Log and skip further processing.
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Could not find the attachment '{$identifier}' in the \$this->attachments_info. Skipping it.",
                MIDCOM_LOG_ERROR);
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
     * @todo compare based on metadata->score and filename (or title ???)
     */
    function _sort_images_callback($a, $b)
    {
        // safety against broken images
        if (   isset($a['main'])
            && isset($a['main']['filename'])
            && isset($b['main'])
            && isset($b['main']['filename'])
            )
        {
            return strcasecmp($a['main']['filename'], $b['main']['filename']);
        }
        // Try to read the sort values from some other key 
        if (   is_array($a)
            && is_array($b))
        {
            foreach($a as $key => $data)
            {
                if (   isset($a[$key]['filename'])
                    && isset($b[$key])
                    && isset($b[$key]['filename']))
                {
                    return strcasecmp($a[$key]['filename'], $b[$key]['filename']);
                }
            }
        }
        // we could not determine any source for comparison, return equal value
        return 0;
    }

    /**
     * Recreates derived images
     */
    function recreate_derived_images($force_prepare = true)
    {
        // This need separate implementation for this type
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Method not implemented for this type yet", MIDCOM_LOG_ERROR);
        debug_pop();
        return false;
    }

    /**
     * Rotates applies a rotational filter to all images under given main identifier
     *
     * @param string $direction direction to rotate to
     * @return boolean indicating success/faiilure
     */
    function rotate($images_identifier, $direction)
    {
        $filter = $this->_rotate_get_filter($direction);
        if ($filter === false)
        {
            return false;
        }
        return $this->apply_filter_images($images_identifier, $filter);
    }

    /**
     * Applies a given filter to all images (except original) under main identifier
     *
     * @param string $images_identifier identifier for $this->images array
     * @param string $filter the midcom_helper_imagefilter filter chain to apply
     * @return boolean indicating success/failure
     */
    function apply_filter_images($images_identifier, $filter)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (!array_key_exists($images_identifier, $this->images))
        {
            debug_add("identifier '{$identifier}' not found in \$this->images", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        foreach($this->images[$images_identifier] as $sub_identifier => $info)
        {
            if ($sub_identifier === 'original')
            {
                continue;
            }
            $identifier = "{$images_identifier}{$sub_identifier}";
            if (!$this->apply_filter($identifier, $filter))
            {
                debug_add("Failed to apply filter '{$filter}' to image '{$identifier}', aborting", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
        }
        debug_pop();
        return true;
    }

    function _batch_handler_cleanup($tmp_dir, $new_name)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("called with: '{$tmp_dir}', '{$new_name}'");
        if (   empty($tmp_dir)
            || $tmp_dir === '/'
            /* TODO: better tmp dir matching */
            || !preg_match('|^/tmp/|', $tmp_dir)
            )
        {
            // Do somethign ? we cannot return as there's more work to do...
        }
        else
        {
            $cmd = "rm -rf {$tmp_dir}";
            debug_add("executing '{$cmd}'");
            exec($cmd, $output, $ret);
        }
        if (   empty($new_name)
            /* TODO: better tmp dir matching */
            || !preg_match('|^/tmp/|', $new_name)
            )
        {
            debug_pop();
            return;
        }
        $cmd = "rm -f {$new_name}";
        debug_add("executing '{$cmd}'");
        exec($cmd, $output, $ret);
        debug_pop();
    }

    function _batch_handler($extension, $file_data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $tmp_name =& $file_data['tmp_name'];
        $new_name = "{$tmp_name}.{$extension}";
        $mv_cmd = "mv -f {$tmp_name} {$new_name}";
        exec($mv_cmd, $output, $ret);
        if ($ret != 0)
        {
            // Move failed
            debug_add("failed to execute '{$mv_cmd}'", MIDCOM_LOG_ERROR);
            unlink($tmp_name);
            debug_pop();
            return false;
        }
        $tmp_dir = "{$tmp_name}_extracted";
        if (!mkdir($tmp_dir))
        {
            // Could not create temp dir
            debug_add("failed to create directory '{$tmp_dir}'", MIDCOM_LOG_ERROR);
            $this->_batch_handler_cleanup(false, $new_name);
            debug_pop();
            return false;
        }
        $zj = false;
        switch (strtolower($extension))
        {
            case 'zip':
                $extract_cmd = "unzip -q -b -L -o {$new_name} -d {$tmp_dir}";
                break;
            case 'tgz':
            case 'tar.gz':
                $zj = 'z';
            case 'tar.bz2':
                if (!$zj)
                {
                    $zj = 'j';
                }
            case 'tar':
                $extract_cmd = "tar -x{$zj} -C {$tmp_dir} -f {$new_name}";
                break;
            default:
                // Unknown extension (we should never hit this)
                debug_add("unusable extension '{$extension}'", MIDCOM_LOG_ERROR);
                $this->_batch_handler_cleanup($tmp_dir, $new_name);
                debug_pop();
                return false;
        }
        debug_add("executing '{$extract_cmd}'");
        exec($extract_cmd, $output, $ret);
        if ($ret != 0)
        {
            // extract failed
            debug_add("failed to execute '{$extract_cmd}'", MIDCOM_LOG_ERROR);
            $this->_batch_handler_cleanup($tmp_dir, $new_name);
            debug_pop();
            return false;
        }
        $files = array();
        // Handle archives with subdirectories correctly
        $this->_batch_handler_get_files_recursive($tmp_dir, $files);

        foreach ($files as $file)
        {
            // Set image
            $basename = basename($file);
			$this->add_image($basename, $file, $basename);
        }

        $this->_batch_handler_cleanup($tmp_dir, $new_name);
        debug_pop();
    }

    function _batch_handler_get_files_recursive($path, &$files)
    {
        $dp = @opendir($path);
        if (!$dp)
        {
            return;
        }
        while (($file = readdir($dp)) !== false)
        {
            if (preg_match('/(^\.)|(~$)/', $file))
            {
                // ignore dotfiles and backup files
                continue;
            }
            $filepath = "{$path}/{$file}";
            if (is_dir($filepath))
            {
                // It's a directory, recurse
                $this->_batch_handler_get_files_recursive($filepath, $files);
                continue;
            }
            if (is_link($filepath))
            {
                // Is a symlink, we can't do anything sensible with it
                continue;
            }
            if (!is_readable($filepath))
            {
                // for some weird reason the file *we* extracted is not readable by us...
                continue;
            }
            $files[] = $filepath;
        }
    }

}