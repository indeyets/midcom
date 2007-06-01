<?php
/**
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: text.php 3858 2006-08-23 16:18:26Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/** @ignore */
require_once('text.php');

/**
 * Datamanger 2 tag datatype. The text value encapsulated by this type is
 * passed to the net.nemein.tag library and corresponding tag objects and
 * relations will be handled there.
 *
 * @package midcom.helper.datamanager2
 */
class midcom_helper_datamanager2_type_tags extends midcom_helper_datamanager2_type_text
{
    /**
     * This event handler is called after construction, so passing references to $this to the
     * outside is safe at this point.
     *
     * @return bool Indicating success, false will abort the type construction sequence.
     * @access protected
     */
    function _on_initialize()
    {
        return $_MIDCOM->load_library('net.nemein.tag');
    }

    function convert_from_storage($source)
    {
        if (! $this->storage->object)
        {
            // That's all folks, no storage object, thus we cannot continue.
            return;
        }
        
        $tags = net_nemein_tag_handler::get_object_tags($this->storage->object);
        
        foreach ($tags as $tag => $url)
        {
            if (strpos($tag, ' '))
            {
                // This tag contains whitespace, surround with quotes
                $tag = "\"{$tag}\"";
            }
            
            // Simply place the tags into a string
            $this->value .= "{$tag} ";
        }
        
        $this->value = trim($this->value);
    }

    function convert_to_storage()
    {
        // TODO: Move this parser to net_nemein_tag_handler
        $tag_array = array();
        // Clean all whitespace sequences to single space
        $tags_string = preg_replace('/\s+/', ' ', $this->value);
        // Parse the tags string byte by byte
        $tags = array();
        $current_tag = '';
        $quote_open = false;
        for ($i = 0; $i < (strlen($tags_string)+1); $i++)
        {
            $char = substr($tags_string, $i, 1);
            $hex = strtoupper(dechex(ord($char)));
            //echo "DEBUG: iteration={$i}, char={$char} (\x{$hex})\n";
            if (   (   $char == ' '
                    && !$quote_open)
                || $i == strlen($tags_string))
            {
                $tags[] = $current_tag;
                $current_tag = '';
                continue;
            }
            if ($char === $quote_open)
            {
                $quote_open = false;
                continue;
            }
            if (   $char === '"'
                || $char === "'")
            {
                $quote_open = $char;
                continue;
            }
            $current_tag .= $char;
        }
        foreach ($tags as $tag)
        {
            // Just to be sure there is not extra whitespace in beginning or end of tag
            $tag = trim($tag);
            if (empty($tag))
            {
                continue;
            }
            $tag_array[$tag] = '';
        }
        
        $status = net_nemein_tag_handler::tag_object($this->storage->object, $tag_array);
        if (!$status)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Tried to save the tags \"{$this->value}\" for field {$this->name}, but failed. Ignoring silently.",
                MIDCOM_LOG_WARN);
            debug_pop();
        }
    
        return null;
    }
}

?>