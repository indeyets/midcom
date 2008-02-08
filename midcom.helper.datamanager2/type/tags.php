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
 * Datamanager 2 tag datatype. The text value encapsulated by this type is
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
     * @return boolean Indicating success, false will abort the type construction sequence.
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
        $this->value = net_nemein_tag_handler::tag_array2string($tags);
    }

    function convert_to_storage()
    {
        $tag_array = net_nemein_tag_handler::string2tag_array($this->value);
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

    function convert_to_raw()
    {
        return net_nemein_tag_handler::string2tag_array($this->value);
    }
}

?>