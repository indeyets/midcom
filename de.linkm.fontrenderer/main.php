<?php
/**
 * @package de.linkm.fontrenderer
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * @package de.linkm.fontrenderer
 */
class de_linkm_fontrenderer {

    var $_font;
    var $bbox_left;
    var $bbox_above;
    var $bbox_below;
    var $fontsize;
    var $lineheight;
    var $left_indent;
    var $first_indent;
    var $_max_width;
    var $_text;
    var $_lines;
    var $linecount;
    var $_hard_warp;
    var $bbox;
    var $_render_code;
    var $_cache_dir;
    var $_cache_prefix;
    var $_serial;

    function de_linkm_fontrenderer ($config = null) {
        debug_push("fontrenderer::constructor");

        if (is_null($config) ) {
            debug_add("Default Constructor is not allowed. Aborting", MIDCOM_LOG_ERROR);
            debug_pop();
            $x =& $this;
            $x = false;
            return false;
        }

        $this->_font = imagepsloadfont ($config["font-file"]);
        if ($this->_font === false) {
            debug_add("Could not load font. Aborting", MIDCOM_LOG_ERROR);
            debug_pop();
            $x =& $this;
            $x = false;
            return false;
        }

        if (! is_null($config["encoding-file"]))
            imagepsencodefont ($this->_font, $config["encoding-file"]);

        $this->fontsize = intval($config["font-size"]);

        // Transform the font metrics to real pixel values
        $this->bbox_left = intval(round($config["bbox-left"] * $this->fontsize / 1000));
        $this->bbox_above = intval(round($config["bbox-above"] * $this->fontsize / 1000));
        $this->bbox_below = intval(round($config["bbox-below"] * $this->fontsize / 1000));
        $this->lineheight = abs($this->bbox_above) + abs($this->bbox_below);

        $this->first_indent = intval($config["first-indent"]);
        $this->left_indent = intval($config["left-indent"]);
        $this->_max_width = intval($config["max-width"]);

        $this->_hard_wrap = $config["hard-wrap"];

        if (! mgd_snippet_exists ("/sitegroup-config/de.linkm.fontrenderer/" . $config["render-code"])) {
            debug_add("Render Code Snippet \"/sitegroup-config/de.linkm.fontrenderer/{$config['render-code']}\" "
                      . "does not exists. Aborting",
                      MIDCOM_LOG_ERROR);
            debug_pop();
            $x =& $this;
            $x = false;
            return false;
        }

        $tmp = mgd_get_snippet_by_path("/sitegroup-config/de.linkm.fontrenderer/" . $config["render-code"]);
        $this->_render_code = $tmp->code;

        debug_print_r("Render Code loaded:", $this->_render_code, MIDCOM_LOG_DEBUG);

        if (! array_key_exists("cache-dir", $config)) {
            debug_add("Setting Cache to default", MIDCOM_LOG_DEBUG);
            $this->_cache_dir = $_SERVER["DOCUMENT_ROOT"] . "/de.linkm.fontrenderer/" . $config["render-code"] . "/";
            $this->_cache_prefix = "/de.linkm.fontrenderer/" . $config["render-code"] . "/";
        } else if (array_key_exist("cache-prefix", $config)) {
            debug_add("Custom Cache Init", MIDCOM_LOG_DEBUG);
            $this->_cache_dir = $config["cache-dir"];
            if (substr($this->_cache_dir,-1) != "/")
                $this->_cache_dir .= "/";
            $this->_cache_prefix = $config["cache-prefix"];
            if (substr($this->_cache_prefix,-1) != "/")
                $this->_cache_prefix .= "/";
        } else {
            debug_add("A cache-dir directive without a cache-prefix directive was present. This is illegal, aborting.", MIDCOM_LOG_ERROR);
            debug_pop();
            $x =& $this;
            $x = false;
            return false;
        }

        debug_add("Executing: " . 'mkdir -p ' . escapeshellarg($this->_cache_dir), MIDCOM_LOG_DEBUG);
        exec('mkdir -p ' . escapeshellarg($this->_cache_dir));

        $this->_serial = strtotime($config["serial"]);
        if ($this->_serial == -1) {
            debug_add("Could not parse Serial, aborting.", MIDCOM_LOG_ERROR);
            debug_pop();
            $x =& $this;
            $x = false;
            return false;
        }

        $this->_text = null;
        $this->_lines = null;
        $this->bbox = null;
        debug_pop();
    }

    function _set_text ($text) {
        debug_push("fontrenderer::set_text");

        $this->_lines = Array();
        $this->bbox = null;
        $this->_text = trim($text);

        debug_add("We have to render \"" . $this->_text . "\"", MIDCOM_LOG_DEBUG);

        // Here we should wrap the text using the given rules
        // and return a bounding box in like imagepsbbox does.
        // Internally we store something like this:
        //   x/y delta coordinate for each line where the text rendering starts
        //   the string to paint there
        //   other data like its width and the original boundingbox

        $remaining = $text;
        $line = 0;

        while ($remaining != "") {
            debug_add("We are now looking at line $line", MIDCOM_LOG_DEBUG);

            $available = $this->_max_width - $this->left_indent;
            if ($line == 0)
                $available -= $this->first_indent;

            debug_add("Available space = $available", MIDCOM_LOG_DEBUG);

            $current = $remaining;
            $newremaining = "";
            $linecomplete = false;

            do {
                // Calculate width of the string, note that the "+2" is due to possible
                // off-by-one values of imagepsbbox.

                $box = imagepsbbox($current, $this->_font, $this->fontsize, 0,0,0);
                $width = $box[2] - $box[0] + 2;
                debug_add("The remaining string would be $width pixels long", MIDCOM_LOG_DEBUG);

                if ($width < $available) {
                    debug_add("Which means that it fits into the available width", MIDCOM_LOG_DEBUG);

                    $this->_lines[$line]["string"] = $current;
                    $this->_lines[$line]["dx"] = $this->left_indent;
                    $this->_lines[$line]["dy"] = $line * $this->lineheight;
                    $this->_lines[$line]["bbox"] = $box;
                    $this->_lines[$line]["width"] = $width;

                    if ($line == 0)
                        $this->_lines[$line]["dx"] += $this->first_indent;

                    $remaining = trim($newremaining);
                    $line++;
                    $linecomplete = true;

                } else {
                    debug_add("Which is too wide, so we'll have to compute a possible linebreak.", MIDCOM_LOG_DEBUG);

                    $space = strrpos ($current, " ");
                    $minus = strrpos ($current, "-");

                    debug_add("RAW indexes: space = $space -- minus = $minus", MIDCOM_LOG_DEBUG);

                    // Ignore Matches to the last character of the string!
                    // The following logic will move one character to the left so
                    // setting it to false should be safe.

                    if ($space == (strlen($current) - 1))
                        $space = false;
                    if ($minus == (strlen($current) - 1))
                        $minus = false;

                    debug_add("cleaned indexes: space = $space -- minus = $minus", MIDCOM_LOG_DEBUG);

                    if ($space === false and $minus === false) {
                        if (! $this->_hard_wrap) {
                            debug_add("Hard Word Wrapping has been disabled and we didn't find any space or minus. Aborting!", MIDCOM_LOG_ERROR);
                            debug_pop();
                            return null;
                        }
                        $break = strlen($current) - 2;  // Found no space or minus, so we do chop of a single char.
                    } elseif ($minus === false)
                        $break = $space-1; // We found a space at $space but no minus
                    elseif ($space === false)
                        $break = $minus; // We found a minus at $minus but no space
                    else if ($space > $minus)
                        $break = $space-1; // There was a space at $space and a minus at $minus, decided for $break
                    else
                        $break = $minus; // There was a space at $space and a minus at $minus, decided for $break

                    // Now $break should point to the last character that is part of
                    // the current line, everything behind it is the new line.
                    // So we build these strings

                    $newremaining = substr($current, $break+1) . $newremaining;
                    $current = substr($current, 0, $break+1);

                    // current string we test is now $current, new remaining string stored is $newremaining

                } // if - else (width > available)

            } while (! $linecomplete);


        } // while remaining != ""

        // Now we make a litte sanity check:
        if (count($this->_lines) == 0) {
            debug_add("this->_lines does not contain any elements, there is something really wrong.", MIDCOM_LOG_ERROR);
            debug_pop();
            return null;
        }

        ksort($this->_lines, SORT_NUMERIC);
        $this->linecount = count($this->_lines);

        debug_print_r("We ended up with these " . $this->linecount . " lines:",$this->_lines, MIDCOM_LOG_DEBUG);

        // The last step we have to do is to compute the real bounding box around
        // the (hypothetical) text base location. This will take any indents into
        // account. Note, that the base location is to the _left_ side of all
        // indents, i.e. they are included in the bounding box.
        //
        // We start with the extent of the text out to the left of the starting point
        // This value is taken out of all included bounding boxes, so that the real
        // string size is taken into account here (including indents)
        //
        // We do the same thing for the right side

        $x1_corr = min($this->left_indent, 0);

        $x1 = $this->_lines[0]["bbox"][0] + min(($this->first_indent + $this->left_indent), 0);
        $x2 = $this->_lines[0]["bbox"][2] + $this->_lines[0]["dx"];

        debug_add("Scanning for bounding box, x1_corr = $x1_corr, initial x1, x2 = $x1, $x2", MIDCOM_LOG_DEBUG);

        foreach ($this->_lines as $line => $data) {
            if ($line == 0)
                continue;

            if (($data["bbox"][0] + $x1_corr) < $x1)
                $x1 = $data["bbox"][0] + $x1_corr;

            if (($data["bbox"][2] + $data["dx"]) > $x2)
                $x2 = $data["bbox"][2] + $data["dx"];

        }

        // The lower and upper boundaries are easier:

        $lastline = $this->_lines[count($this->_lines)-1];
        $y1 = (-1 * $lastline["dy"]) + $lastline["bbox"][1];
        $y2 = $this->_lines[0]["bbox"][3];

        $this->bbox = Array($x1,$y1,$x2,$y2);

        debug_add("Now we are finished: x1/y1 = $x1/$y1, x2/$y2 = $x2/$y2", MIDCOM_LOG_DEBUG);
        debug_pop();
        return $this->bbox;
    }

    function draw_text ($image, $x, $y, $col_fg, $col_bg) {
        foreach ($this->_lines as $line => $data)
            imagepstext($image, $data["string"], $this->_font, $this->fontsize, $col_fg, $col_bg, intval($x+$data["dx"]), intval($y+$data["dy"]), 0, 0, 0, 16);
    }

    function _create_image () {
        debug_push("fontrenderer::_create_image");
        $image = null;

        debug_push("fontrenderer::_create_image::RENDER_CODE");
        eval($this->_render_code);
        debug_pop();

        if (is_null($image)) {
            debug_add("The Render-Code did not populate $image. Aborting!", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        $filename = $this->_cache_dir . md5($this->_text) . ".png";
        debug_add("Filename: $filename");

        if (file_exists($filename))
            if (!unlink($filename)) {
                debug_add("This file does already exist and we could not delete it. Aborting", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }

        debug_add("Writing image", MIDCOM_LOG_DEBUG);
        imagepng ($image, $filename);

        if (!file_exists($filename)) {
            debug_add("Something (look for PHP messages) went wrong when writing the image. Aborting", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        debug_pop();
        return true;
    }

    function get_image ($string) {
        debug_push("fontrenderer::get_image");

        $file = md5(trim($string)) . ".png";
        $filename = $this->_cache_dir . $file;

        debug_add("We should retrieve an image for the string \"" . trim($string) . "\" whose filename should be \"$filename\"", MIDCOM_LOG_INFO);

        $generate = false;

        if (!file_exists($filename)) {
            $generate = true;
            debug_add("This file was not found.", MIDCOM_LOG_DEBUG);
        } else {
            $stat = stat($filename);
            if ($stat[10] < $this->_serial) {
                $generate = true;
                debug_print_r("This file seems too old, here are its stats, serial is " . $this->_serial . ":", $stat, MIDCOM_LOG_DEBUG);
            }
        }

        if ($generate) {
            if (is_null($this->_set_text($string))) {
                debug_add("Could not format the text.", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
            if (!$this->_create_image()) {
                debug_add("Could not create image.", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
            debug_add("File created successfully", MIDCOM_LOG_INFO);
        }

        $info = getimagesize($filename);

        if ($info === false || $info[2] != 3) {
            debug_add("This does not look like a PNG Image, getimagesize returned this:", $info, MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        $result = Array();
        $result["url"] = $this->_cache_prefix . $file;
        $result["size_x"] = $info[0];
        $result["size_y"] = $info[1];
        $result["size_line"] = $info[3];

        debug_print_r("Cache Hit. Returning this:", $result, MIDCOM_LOG_DEBUG);

        debug_pop();
        return $result;
    }

}


?>