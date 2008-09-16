<?php
/**
 * @package org.routamc.photostream
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 */

/**
 * Importer for fetching photos from a filesystem
 *
 * @package org.routamc.photostream
 */
class org_routamc_photostream_importer_filesystem extends org_routamc_photostream_importer
{
    var $directory = null;


    /**
     * Initializes the class. The real startup is done by the initialize() call.
     */
    function __construct($photostream)
    {
        $folder = $_GET['import_folder'];
        parent::__construct($photostream);
        if(!is_dir($folder))
        {
            return false;
        }
        $this->folder = $folder;
    }

    function import_photos_directory()
    {
        // Reading all photos
        $files = scandir($this->folder);
        foreach($files as $file)
        {
            if(is_file($this->folder.'/'.$file))
            {
                if($this->import($this->folder.'/'.$file, $file))
                {
                    echo("$file OK <br />");
                }
                else
                {
                    echo("$file failed <br />");
                }
            }
            flush();
            ob_flush();
        }
    }

    /**
     * Imports a single photo
     * @param photo absolute filesystem path
     * @param file just the filename. 
     * @return boolean Indicating success.
     */
    function import($photo, $filename)
    {
        // Check for duplicates first
        // Check if the photo is already in database
        $qb = org_routamc_photostream_photo_dba::new_query_builder();
        $qb->add_constraint('title', '=', $filename);
        if ($qb->count() > 0)
        {
            // This photo is already in database, update only tags and such
            return true;
        }

        $photo_obj = new org_routamc_photostream_photo_dba();
        $photo_obj->node = $this->photostream;
        $photo_obj->title = $filename;

        if (!$photo_obj->create())
        {
            return false;
        }

        if (!$this->datamanager->autoset_storage($photo_obj))
        {
            $photo_obj->delete();
            return false;
        }
        // We don not want the script to try to delete the files that we import
        if (!$this->datamanager->types[$this->photo_field]->set_image($filename, $photo, $filename, false))
        {
            $photo_obj->delete();
            return false;
        }

        if (!$this->datamanager->save())
        {
            $photo_obj->delete();
            return false;
        }
        return true;
    }
}