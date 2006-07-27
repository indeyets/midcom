<?php
/**
 * Created on Mar 13, 2006
 * @author tarjei huse
 * @package midcom.helper.imagepopup
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * 
 */
 
class midcom_helper_imagepopup_attachment {

    var $url = null;
    var $object = null;
    
    
    function midcom_helper_imagepopup_attachment($attachment_id) 
    {
        $this->object = new midcom_baseclasses_database_attachment($attachment_id);
        $this->url = "{$GLOBALS['midcom_config']['midcom_site_url']}midcom-serveattachmentguid-{$this->object->guid}/{$this->object->name}";
        // if the title is missing, use the object's name as the title.
        if ($this->object->title == "" ) {
            $this->object->title =$this->object->name;
        }
    } 
    /**
     * generates a preview of the  
     */
    function get_preview() 
    {
        $icon = $this->get_mime_icon();
        $static_html = "<div class='file_description' ><img class='midcom_helper_imagepopup_image_file' src='{$icon}' /><br/>" .
            "<a href='{$this->url}' target='_new'>{$this->object->name}</a><br />" .
            " </div>";
        return $static_html;
        
    }
    function get_mimetype() {
        return $this->object->mimetype;
    }
    /**
     * Tries to generate an icon url
     */
    function get_mime_icon()
    {
        
        list($main,$sub) = split("/", $this->object->mimetype);
        $icon = "gnome-{$main}-{$sub}.png";
        
        if (file_exists(MIDCOM_STATIC_ROOT . "/stock-icons/mime/$icon")) 
        {
            return MIDCOM_STATIC_URL . "/stock-icons/mime/$icon";
        }
        return MIDCOM_STATIC_URL . "/stock-icons/mime/gnome-unknown.png";
        
    }
    
    /**
     *  gets the name of the attachment
     *  
     **/
    function get_name () 
    {
        return "<div class='midcom_helper_imagepopup_file'>Filename:" . $this->object->name . "<br/>Mimetype: " . $this->object->mimetype . "</div>";
    }
    
    /**
     * Returns basic information about the object as a javascript array with the
     * guid as the image index.
     * @return string javascript
     * @access public
     */
    function get_image_js_info() {
        return <<<EOT
imagepopup_images["{$this->object->guid}"] = new Array();
imagepopup_images["{$this->object->guid}"]['name'] = "{$this->object->name}";
imagepopup_images["{$this->object->guid}"]['alt'] = "{$this->object->title}";
imagepopup_images["{$this->object->guid}"]['type'] = 'attachment';
imagepopup_images["{$this->object->guid}"]['size'] = 0;
     
EOT;
    }
}

class midcom_helper_imagepopup_image extends midcom_helper_imagepopup_attachment  
{
    
    /**
     * Size in pixels
     */
    var $x = 0;
    var $y = 0;

    function midcom_helper_imagepopup_image (&$attachment)
    {
        parent::midcom_helper_imagepopup_attachment(&$attachment);
        $this->set_size();        
    }
    /**
     * Generates the HTML used for the preview part of the object.
     * @access public
     * @return datatype description
     */
    function get_preview() 
    {
        
        $x = $this->x;
        $y = $this->y;
        // Downscale Preview image to max 75px, rotect against broken images:
        if (   $x != 0
            && $y != 0)
        {
            $aspect = $x/$y;
            if ($x > 50)
            {
                $x = 50;
                $y = round($x / $aspect);
            }
            if ($y > 50)
            {
                $y = 50;
                $x = round($y * $aspect);
            }
        }

        $size = " width='{$x}' height='{$y}'";
        $static_html = "<div class='file_description' >" .
                "<a href='#' onclick='insertImage(\"{$this->object->guid}\")'>" .
                "<img id='IMG_{$this->object->guid}' class='midcom_helper_imagepopup_image_thumbnail' src='{$this->url}' {$size} /></a>" .
                "<br/>" .
            "<a href='{$this->url}' target='_new'>{$this->object->name}</a><br />" .
            "Size: {$this->x} x {$this->y} " .
            " </div>";
        return $static_html;
    }
    
    function set_size() 
    {
    
        $parameters = $this->object->list_parameters();
        
        if (array_key_exists ('midcom.helper.datamanager2.type.blobs', $parameters))
        {
            // dm2 object.
            $this->x = $parameters['midcom.helper.datamanager2.type.blobs']['size_x'];
            $this->y = $parameters['midcom.helper.datamanager2.type.blobs']['size_y'];
            return;
        }
    }
    /**
     * Returns basic information about the object as a javascript array with the
     * guid as the image index.
     * @return string javascript
     * @access public
     */
    function get_image_js_info() {
        $ret = parent::get_image_js_info();
        
        return <<<EOT
$ret
imagepopup_images["{$this->object->guid}"]['size_x'] = {$this->x};
imagepopup_images["{$this->object->guid}"]['size_y'] = {$this->y};
imagepopup_images["{$this->object->guid}"]['type'] = 'image';
imagepopup_images["{$this->object->guid}"]['has_thumbnail'] = 0;
     
EOT;
    }
}
?>