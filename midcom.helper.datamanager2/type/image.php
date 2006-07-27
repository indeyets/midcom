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
 * Datamanger 2 Image type.
 *
 * This type encaspulates a sinlge uploaded image along with an optional number of
 * derived images like thumbnails. Both the main image and the derived thumbnails
 * will be ran through a defined filter chain. The originally uploaded file can be
 * kept optionally.
 *
 * The original image will be available under the "original" identifier unless
 * configured otherwise. The main image used for display is available as "main",
 * which will be ensured to be web-compatible. (This
 * distinction is important in case you upload TIFF or other non-web-compatible
 * images. All derived images will be available under the names defined in the schema
 * configuration.
 *
 * An optional "quick" thumbnail mode is available as well where you just specify the
 * maximum frame of a thumbnail to-be-generated. The auto-generated image will then be
 * available in the attachment identified as "thumbnail".
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
 * Be aware that the type holds <em>no</em> safety code to guard against duplicate image
 * identifiers (e.g. defining a "main" image in the derived images list). The results
 * of such a configuration is undefined.
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
 * @todo Implement thumbnail interface.
 * @todo Operation on file handles.
 *
 * @package midcom.helper.datamanager2
 */
class midcom_helper_datamanager2_type_image extends midcom_helper_datamanager2_type_blobs
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
     * The passed values will be given to the rescale funciton of the imagefilter.
     * This means that if you want to scale an image only by width, you still have
     * to set the height parameter to zero (auto_thumbnail => Array(100,0)).
     *
     * @var array.
     */
    var $auto_thumbnail = null;

    /**
     * The image title entered by the user. Stored in each attachments
     * title field.
     *
     * @var string
     */
    var $title = '';

    /**
     * The original filename of the uploaded file.
     *
     * @access private
     * @var string
     */
    var $_filename = null;

    /**
     * The name of the original temporary uploaded file (which will already be converted
     * to a Web-Aware format).
     *
     * @access private
     * @var string
     */
    var $_original_tmpname = null;

    /**
     * The current working file.
     *
     * @access private
     * @var string
     */
    var $_current_tmpname = null;

    /**
     * The image-filter instance to use.
     *
     * @access private
     * @var midcom_helper_imagefilter
     */
    var $_filter = null;

    /**
     * The target mimetype used after automatic conversion for all
     * generated images.
     *
     * @access private
     * @var string
     */
    var $_target_mimetype = null;

    /**
     * The original mimetype of the uploaded file.
     *
     * @access private
     * @var string
     */
    var $_original_mimetype = null;

    /**
     * This list is used when updating an existing attachment. It keeps track
     * of which attachments have been updated already when replacing an existing
     * image. All attachments still listed here after an set_image call will
     * be deleted. This keeps attachment GUIDs stable during updates but also
     * adds resilence against against changed type configuration.
     *
     * @access private
     * @var Array
     */
    var $_pending_attachments = null;


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
     * Adds the image to the type. Loads and processes the $tmpname file on disk.
     *
     * @param string $filename The name of the image attachment to be created.
     * @param string $tmpname The file to load.
     * @param string $title The title of the image.
     * @param bool $autodelete If this is true (the default), the temporary file will
     *     be deleted after postprocessing and attachment-creation.
     * @return bool Indicating success.
     */
    function set_image($filename, $tmpname, $title, $autodelete = true)
    {
        // First, ensure that the imagefilter helper is available.
        require_once(MIDCOM_ROOT . '/midcom/helper/imagefilter.php');

        $this->_pending_attachments = $this->attachments;

        // Prepare Internal Members
        $this->title = $title;
        $this->_filename = $filename;
        $this->_original_tmpname = $tmpname;
        $this->_original_mimetype = $this->_get_mimetype($this->_original_tmpname);
        $this->_filter = new midcom_helper_imagefilter();

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
            $this->delete_all_attachments();

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
            $this->delete_all_attachments();

            return false;
        }

        // Clear up all attachments no longer in use:
        foreach ($this->_pending_attachments as $identifier => $attachment)
        {
            $this->delete_attachment($identifier);
        }

        if ($autodelete)
        {
            unlink ($this->_original_tmpname);
        }

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
            foreach ($this->derived_images as $identifier => $filter_chain)
            {
                if (! $this->_create_working_copy())
                {
                    return false;
                }

                $result = $this->_save_derived_image($identifier);
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
     * @param string $identifier The derived image to construct.
     * @return bool Indicating success
     */
    function _save_derived_image($identifier)
    {
        if (! $this->_filter->process_chain($this->derived_images[$identifier]))
        {
            return false;
        }

        if (array_key_exists($identifier, $this->_pending_attachments))
        {
            unset($this->_pending_attachments[$identifier]);
            return $this->update_attachment($identifier,
                                            "{$identifier}_{$this->_filename}",
                                            $this->title,
                                            $this->_get_mimetype($this->_current_tmpname),
                                            $this->_current_tmpname,
                                            false);
        }
        else
        {
            return $this->add_attachment($identifier,
                                         "{$identifier}_{$this->_filename}",
                                         $this->title,
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
            if (array_key_exists('main', $this->_pending_attachments))
            {
                unset($this->_pending_attachments['main']);
                $result = $this->update_attachment('main',
                                                   $this->_filename,
                                                   $this->title,
                                                   $this->_target_mimetype,
                                                   $this->_current_tmpname,
                                                   false);
            }
            else
            {
                $result = $this->add_attachment('main',
                                                $this->_filename,
                                                $this->title,
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
            if (array_key_exists('original', $this->_pending_attachments))
            {
                unset($this->_pending_attachments['original']);
                return $this->update_attachment('original',
                                                "original_{$this->_filename}",
                                                $this->title,
                                                $this->_original_mimetype,
                                                $this->_original_tmpname,
                                                false);
            }
            else
            {
                return $this->add_attachment('original',
                                             "original_{$this->_filename}",
                                             $this->title,
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
     * Calls the base type to read all attachments, then extracts the title of the
     * main attachment, if applicable.
     */
    function convert_from_storage($source)
    {
        parent::convert_from_storage($source);
        
        if (array_key_exists('main', $this->attachments))
        {
            $this->title = $this->attachments['main']->title;
        }
    }

    /**
     * Updates the attachment titles.
     */
    function convert_to_storage()
    {
        foreach ($this->attachments as $identifier => $copy)
        {
            $this->update_attachment_title($identifier, $this->title);
        }

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
        if ($this->attachments_info)
        {
            $main = $this->attachments_info['main'];
            $title = ($main['description']) ? $main['description'] : $main['filename'];
            
            if (array_key_exists('thumbnail', $this->attachments_info))
            {
                $thumb = $this->attachments_info['thumbnail'];
                $result = "<a href='{$main['url']}'><img src='{$thumb['url']}' {$thumb['size_line']} alt='{$title}' title='{$title}' /></a>";
            }
            else
            {
                $result = "<img src='{$main['url']}' {$main['size_line']} alt='{$title}' title='{$title}' />";
            }
        }
        return $result;
    }

}