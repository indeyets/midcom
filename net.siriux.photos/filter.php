<?php

/**
 * @package net.siriux.photos
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */


/**
 * Image Filter Class, used to post process all images, check the documentation
 * of the individual methods.
 *
 * @todo Document
 *
 * @deprecated This class is deprecated at this point, user midcom_helper_imagefilter instead.
 *
 * @author Eero af Heurlin, Nemein Oy <eero.afheurlin@nemein.com>
 * @author Nico Kaiser <nico@siriux.net>
 * @author Torben Nehmer <torben@nehmer.net>
 * @version 1.0 (2004-09-22)
 * @package net.siriux.photos
 */
class net_siriux_photos_imagefilter
{

    /**
     * The file currently being processed.
     *
     * @var string
     * @access private
     */
    var $_filename;

    /**
     * The quality to use for JPEG manipulation, this is not
     * yet settable from the outside. Stored as a valid imagemagick
     * option, defaults to '-quality 90' right now.
     *
     * @var string
     * @access private
     */
    var $_quality;

    /**
     * The constructor does a bit of variable initialization
     */
    function net_siriux_photos_imagefilter()
    {
        $this->_filename = null;
        $this->_quality = "-quality 90";
    }

    /**
     * Sets the filename of the image currently being edited.
     * This must be the full path to the file, the fill will be
     * replaced with the modified image.
     *
     * The process will check for write permission at this point,
     * A return value of false will indicate some problem, see the
     * MidCOM Debug Log for details.
     *
     * TODO: Use ImageMagick Identify to check for a valid image.
     *
     * @param string filename The file to be edited.
     * @returns true, if the file is valid, false otherwise.
     */
    function set_file($filename)
    {
        if (! is_writeable($filename))
        {
            debug_add("The File {$filename} is not writeable.", MIDCOM_LOG_INFO);
            return false;
        }
        $this->_filename = $filename;
        return true;
    }

    /**
     * This function will process a string-based filter chain. This
     * is a semicolon-separated list of commands, which will be evaluated
     * using process_command each.
     *
     * Processing stops as soon as one filter command fails.
     *
     * @param string chain The filter chain to be processed (filter1();filter2();...)
     * @returns true, if all filters have been successfully applied, false otherwise.
     */
    function process_chain($chain)
    {
        $filters = explode(";", $chain);

        foreach ($filters as $cmd)
        {
            if (!$this->process_command($cmd))
            {
                debug_add("Execution of {$cmd} failed, aborting now.");
                return false;
            }
        }

        return true;
    }

    /**
     * This is the main function evaluator of the filter class.
     * It will take a function style command like
     * rotate(90), arguments are separated by commas, there is no
     * quoting at the moment. All arguments are parsed as strings,
     * function names need to be [a-z_]
     *
     * Execution will relay to the corresponding filter function.
     *
     * All filters will use defaults for missing arguments (which can
     * result in a NULL operation) and will ignore excessive arguments.
     *
     * @param string cmd The command to be executed.
     * @returns true, if the filter executed successfully, false otherwise.
     */
    function process_command($cmd)
    {
        debug_add("Parsing command {$cmd}");
        $i = preg_match('/([a-z_]*)\(([^)]*)\)/', $cmd, $matches);
        if (! $i)
        {
            debug_add("Failed to parse command {$cmd}, aborting.", MIDCOM_LOG_INFO);
            return false;
        }
        $command = $matches[1];
        $args = explode(",", $matches[2]);

        debug_print_r("Have to execute {$command} with these arguments:", $args);

        switch ($command)
        {
            case "gamma":
                if (array_key_exists(0, $args) && $args[0] > 0)
                    $gamma = (double) $args[0];
                else
                    $gamma = 1.2;
                return $this->gamma($gamma);

            case "exifrotate":
                return $this->exifrotate();

            case "rotate":
                if (array_key_exists(0, $args) && $args[0] > 0 && $args[0] < 360)
                    $rotate = (double) $args[0];
                else
                    $rotate = 0;
                return $this->rotate($rotate);

            case "resize":
                if (count($args) == 0) {
                    debug_add("No arguments given, doing nothing.", MIDCOM_LOG_INFO);
                    return true;
                }
                $x = (int) $args[0];
                if (array_key_exists(1, $args) && $args[1] > 0)
                    $y = (int) $args[1];
                else
                    $y = 0;
                return $this->rescale($x, $y);

            case "none":
                return true;

            default:
                debug_add("This is no known command, we try to find a callback.");
                return $this->execute_user_callback($command, $args);
        }
    }

    /*********** INTERNAL HELPERS ******************/

    /**
     * Returns the name of a temporary file to be used to write
     * the transformed image to. Has to be managed by the callee.
     */
    function _get_tempfile()
    {
        return tempnam("/tmp", "net_siriux_photos");
    }

    /**
     * This will replace the original file with the processed copy
     * of $tmpfile, deleting the temporary file afterwards.
     */
    function _process_tempfile($tmpname)
    {
        $src = fopen($tmpname, "r");
        $dst = fopen($this->_filename, "w+");
        while (! feof($src))
        {
            $buffer = fread($src, 131072); /* 128 kB */
            fwrite($dst, $buffer, 131072);
        }
        fclose($src);
        fclose($dst);
        unlink($tmpname);
    }

    /*********** IMAGE MANIPULATION STUFF *****************/

    /**
     * Executes a custom image manipulation callback. The command
     * name is the function that is searched. The function must
     * use the following signature:
     *
     * bool callback (string $infile, $string outfile, array $parameters);
     *
     * infile is the file to be manipulated, outfile the name where
     * the result has to be stored in (a temporary file usually) and
     * the parameters passed to the command originally. It should return
     * true on a successful transformation, false otherwise.
     *
     * @param string $command  The name of the callback to execute
     * @param array $args      The arguments passed to the callback
     * @returns The return code of the callback.
     */
    function execute_user_callback($command, $args) {
        if (! function_exists($command))
        {
            debug_add("The function {$command} could not be found, aborting", MIDCOM_LOG_ERROR);
            return false;
        }
        $tmpfile = $this->_get_tempfile();

        if (! $command($this->_filename, $tmpfile, $args))
        {
            unlink($tmpfile);
            return false;
        }

        $this->_process_tempfile($tmpfile);
        return true;
    }

    /**
     * Gamma-Adjustment of the image.
     *
     * Filter Syntax: gamma($gamma)
     *
     * Where $gamma is a positive floating point number, e.g. 1.2
     *
     * @param $gamma Gamma adjustment value.
     * @returns true on success.
     */
    function gamma($gamma)
    {
        $cmd = "{$GLOBALS['midcom_config']['utility_imagemagick_base']}mogrify {$this->_quality} -gamma "
            . escapeshellarg($gamma) . " " . escapeshellarg($this->_filename);

        exec($cmd, $output, $exit_code);

        if ($exit_code === 0)
        {
            return true;
        }
        else
        {
            debug_add("ImageMagick failed to convert the image, it returned with {$exit_code}, see LOG_DEBUG for details.", MIDCOM_LOG_ERROR);
            debug_print_r('The generated output was:', $output);
            debug_add("Command was: [{$cmd}]");
            return false;
        }
    }


    /**
     * Automatic rotation for the image using EXIF tags.
     *
     * NOTE: This REQUIRES php_read_exif to be present. It will fail
     * if it is missing.
     *
     * Filter Syntax: exifrotate()
     *
     * @returns true on success.
     */
    function exifrotate()
    {
        if (! function_exists("read_exif_data"))
        {
            debug_add("read_exif_data required for exifrotate.", MIDCOM_LOG_ERROR);
            return false;
        }
        $exif = read_exif_data($this->_filename);
        if (! array_key_exists("Orientation", $exif))
        {
            debug_add("EXIF information misses the orientation tag. Skipping.", MIDCOM_LOG_INFO);
            return true;
        }
        if (! $exif || $exif["Orientation"] == 1)
        {
            debug_add("No rotation neccessary.");
            return true;
        }

        $do_unlink = false;
        $imagesize = getimagesize($this->_filename);

        if (   $imagesize[2] == 2
            && ! is_null($GLOBALS['midcom_config']['utility_jpegtran']))
        {
            /* jpegtran */
            switch ($exif["Orientation"])
            {
                case 2: $rotate = "-flip horizontal"; break;
                case 3: $rotate = "-rotate 180"; break;
                case 4: $rotate = "-flip vertical"; break;
                case 5: $rotate = "-transpose"; break;
                case 6: $rotate = "-rotate 90"; break;
                case 7: $rotate = "-transverse"; break;
                case 8: $rotate = "-rotate 270"; break;
                default:
                    debug_add("Unsupported EXIF-Rotation tag encountered, ingoring: " . $exif["Orientation"],
                        MIDCOM_LOG_INFO);
                    return true;
            }

            $tmpfile = $this->_get_tempfile();
            $do_unlink = true;
            $cmd = "{$GLOBALS['midcom_config']['utility_jpegtran']} -copy all {$rotate} -outfile {$tmpfile} " . escapeshellarg($this->_filename);
        }
        else
        {
            /* Mogrify */
            debug_add("jpegtran not found, falling back to mogrify.");

            switch ($exif["Orientation"])
            {
                case 2: $rotate = "-flip"; break;
                case 3: $rotate = "-rotate 180"; break;
                case 4: $rotate = "-flip"; break;
                case 5: $rotate = "-rotate 90 -flip"; break;
                case 6: $rotate = "-rotate 90"; break;
                case 7: $rotate = "-rotate 270 -flop"; break;
                case 8: $rotate = "-rotate 270"; break;
                default:
                    debug_add("Unsupported EXIF-Rotation tag encountered, ingoring: " . $exif["Orientation"],
                        MIDCOM_LOG_INFO);
                    return true;
            }

            $cmd = "{$GLOBALS['midcom_config']['utility_imagemagick_base']}mogrify {$this->_quality} {$rotate} "
                . escapeshellarg($this->_filename);
        }

        exec($cmd, $output, $exit_code);

        if ($exit_code !== 0)
        {
            debug_add("ImageMagick/jpegtran failed to convert the image, see LOG_DEBUG for details.", MIDCOM_LOG_ERROR);
            debug_print_r("Imagemagick/jpegtran returned with {$exit_code} and produced this output:", $output);
            debug_add("Command was: {$cmd}");
            if ($do_unlink)
            {
                unlink($tmpfile);
            }
            return false;
        }

        if ($do_unlink)
        {
            $this->_process_tempfile($tmpfile);
        }
        return true;
    }

    /**
     * Manual image rotation
     *
     * Filter Syntax: rotate($rotate)
     *
     * Where $gamma is a positive floating point number greater then 0
     * and less then 360; if omitted, a NULL operation is done.
     *
     * @param $rotate Degrees of rotation clockwise, negative amounts possible
     * @returns true on success.
     */
    function rotate($rotate) {
        // Do some normalizing on the argument
        while ($rotate < 0)
        {
            $rotate += 360;
        }
        while ($rotate > 360)
        {
            $rotate -= 360;
        }
        if ($rotate == 0 || $rotate == 360)
        {
            debug_add("Rotate is {$rotate}, we're happy as-is.");
            // We're happy as-is :)
            return true;
        }

        $do_unlink = false;
        $imagesize = getimagesize($this->_filename);

        // Try lossless jpegtran rotation if possible
        if (   $imagesize[2] == 2
            && ($rotate == 90 || $rotate == 180 || $rotate == 270)
            && ! is_null($GLOBALS['midcom_config']['utility_jpegtran']))
        {
            $tmpfile = $this->_get_tempfile();
            $do_unlink = true;
            $cmd = "{$GLOBALS['midcom_config']['utility_jpegtran']} -copy all -rotate {$rotate} -outfile {$tmpfile} " . escapeshellarg($this->_filename);
        }
        else
        {
            /* Mogrify */
            debug_add("jpegtran not found or rotation incompatible, falling back to mogrify.");

            $cmd = "{$GLOBALS['midcom_config']['utility_imagemagick_base']}mogrify {$this->_quality} -rotate {$rotate} " . escapeshellarg($this->_filename);
        }

        debug_add("We have to rotate clockwise by {$rotate} degrees, do_unlink: {$do_unlink}");
        exec($cmd, $output, $exit_code);

        if ($exit_code !== 0)
        {
            debug_add("ImageMagick/jpegtran failed to convert the image, see LOG_DEBUG for details.", MIDCOM_LOG_ERROR);
            debug_print_r("Imagemagick/jpegtran returned with {$exit_code} and produced this output:", $output);
            debug_add("Command was: {$cmd}");
            if ($do_unlink)
            {
                unlink($tmpfile);
            }
            return false;
        }

        if ($do_unlink)
        {
            $this->_process_tempfile($tmpfile);
        }
        return true;
    }

    /**
     * Image Resize: This will resize the orignal image.
     *
     * Filter Syntax: resize ($x, $y)
     *
     * The width of the image in pixels, it will be scaled in
     * proportion. One of the two parameters can be 0, meaning that
     * the width/height of the image is adjusted to the other, given
     * value.
     *
     * The resize will be done only if it would not increase the image
     * as this won't gain you any benifit.
     *
     * @param int $x Width
     * @param int $y Height
     * @returns true on success.
     */
    function rescale($x, $y)
    {
        if ($x == 0 && $y == 0)
        {
            debug_add("Both x and y are 0, skipping operation.", MIDCOM_LOG_INFO);
            return true;
        }

        if ($x == 0)
        {
            $geo = "x{$y}>";
        }
        else if ($y == 0)
        {
            $geo = "{$x}x>";
        }
        else
        {
            $geo = "{$x}x{$y}>";
        }
        $geo = "-geometry " . escapeshellarg($geo);

        $cmd = "{$GLOBALS['midcom_config']['utility_imagemagick_base']}mogrify {$this->_quality} {$geo} "
            . escapeshellarg($this->_filename);

        exec($cmd, $output, $exit_code);

        if ($exit_code !== 0)
        {
            debug_add("ImageMagick failed to convert the image, see LOG_DEBUG for details.", MIDCOM_LOG_ERROR);
            debug_print_r("Imagemagick returned with {$exit_code} and produced this output:", $output);
            debug_add("Command was: {$cmd}");
            return false;
        }

        return true;
    }



}

?>
