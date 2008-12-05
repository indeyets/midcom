<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Toolbar helper
 *
 * @package midcom_core
 */
class midcom_core_helpers_toolbar
{
    /**
     * The CSS class that should be used for the toolbar.
     * Set to null if non should be used.
     *
     * @var string
     */
    public $css_class;

    /**
     * The items in the toolbar. 
     *
     * The array consists of Arrays outlined in the class introduction. 
     * You can modify existing items in this collection but you should use 
     * the class methods to add or delete existing items. Also note that 
     * relative URLs are processed upon the invocation of add_item(), if 
     * you change URL manually, you have to ensure a valid URL by yourself 
     * or use update_item_url, which is recommended.
     *
     * @var Array
     */
    public $items;
    
    public $holder_attributes;
    
    /**
     * Basic constructor, initializes the class and sets defaults for the
     * CSS style if omitted. 
     *
     * Note that the styles can be changed after construction by updating 
     * the css_class members.
     *
     * @param string $css_class The css class for the UL.
     */
    public function __construct($css_class = 'midcom_toolbar', $holder_attributes = '')
    {
        $this->css_class = $css_class;
        $this->holder_attributes = $holder_attributes;
        $this->items = array();
        
        $this->initialize();
    }
    
    protected function initialize() {}
    
    public function get_section_items($section=MIDCOM_TOOLBAR_NODE)
    {
        if (   !array_key_exists($section, $this->items)
            || !is_array($this->items[$section]))
        {
            return array();
        }
        
        return $this->items[$section];
    }
    
    /**
     * This function will add an Item to the toolbar. 
     *
     * Set before to the index of the element before which you want to insert 
     * the item or use -1 if you want to append an item. Alternatively, 
     * instead of specifying an index, you can specify a URL instead.
     *
     * This member will process the URL and append the anchor prefix in case
     * the URL is a relative one.
     *
     * Invalid positions will result in a MidCOM Error.
     *
     * @param Array $item The item to add.
     * @param mixed $before The index before which the item should be inserted.
     *     Use -1 for appending at the end, use a string to insert
     *     it before a URL, an integer will insert it before a
     *     given index.
     * @see midcom_helper_toolbar::get_index_from_url()
     * @see midcom_helper_toolbar::check_index()
     * @see midcom_helper_toolbar::clean_item()
     */
    public function add_item($section=MIDCOM_TOOLBAR_NODE, $item, $before = -1)
    {
        if (   !array_key_exists($section, $this->items)
            || !is_array($this->items[$section]))
        {
            $this->items[$section] = array();
        }
        
        if ($before != -1)
        {
            $before = $this->check_index($section, $before, false);
        }
        
        $item = $this->clean_item($item);

        if ($before == -1)
        {
            $this->items[$section][] = $item;
        }
        else if ($before == 0)
        {
            array_unshift($this->items[$section], $item);
        }
        else
        {
            $start = array_slice($this->items[$section], 0, $before - 1);
            $start[] = $item;
            $this->items[$section] = array_merge($start, array_slice($this->items[$section], $before));
        }
    }
    
    /**
     * Clean up an item that is added, making sure that the item has all the
     * needed options and indexes.
     * @param array the item to be cleaned
     * @return array the cleaned item.
     * @access public
     */
    public function clean_item($item)
    {
        static $used_access_keys = array();
        
        $item[MIDCOM_TOOLBAR__ORIGINAL_URL] = $item[MIDCOM_TOOLBAR_URL];
        if (! array_key_exists(MIDCOM_TOOLBAR_OPTIONS, $item))
        {
            $item[MIDCOM_TOOLBAR_OPTIONS] = array();
        }
        if (! array_key_exists(MIDCOM_TOOLBAR_HIDDEN, $item))
        {
            $item[MIDCOM_TOOLBAR_HIDDEN] = false;
        }
        if (! array_key_exists(MIDCOM_TOOLBAR_HELPTEXT, $item))
        {
            $item[MIDCOM_TOOLBAR_HELPTEXT] = '';
        }
        if (! array_key_exists(MIDCOM_TOOLBAR_ICON, $item))
        {
            $item[MIDCOM_TOOLBAR_ICON] = false;
        }
        else if($item[MIDCOM_TOOLBAR_ICON])
        {
            $item[MIDCOM_TOOLBAR_ICONURL] = MIDCOM_STATIC_URL . "/{$item[MIDCOM_TOOLBAR_ICON]}";
        }
        if (! array_key_exists(MIDCOM_TOOLBAR_ENABLED, $item))
        {
            $item[MIDCOM_TOOLBAR_ENABLED] = true;
        }

        if (! array_key_exists(MIDCOM_TOOLBAR_POST, $item))
        {
            $item[MIDCOM_TOOLBAR_POST] = false;
        }
        if (! array_key_exists(MIDCOM_TOOLBAR_POST_HIDDENARGS, $item))
        {
            $item[MIDCOM_TOOLBAR_POST_HIDDENARGS] = array();
        }

        // Check that access keys get registered only once
        if (   ! array_key_exists(MIDCOM_TOOLBAR_ACCESSKEY, $item)
            || array_key_exists($item[MIDCOM_TOOLBAR_ACCESSKEY], $used_access_keys))
        {
            $item[MIDCOM_TOOLBAR_ACCESSKEY] = null;
        }
        else
        {
            // We have valid access key, add it to help text
            if (strstr($_SERVER['HTTP_USER_AGENT'], 'Macintosh'))
            {
                // Mac users
                $hotkey = 'Ctrl-' . strtoupper($item[MIDCOM_TOOLBAR_ACCESSKEY]);
            }
            else
            {
                // Windows and Linux clients
                $hotkey = 'Alt-' . strtoupper($item[MIDCOM_TOOLBAR_ACCESSKEY]);
            }

            if ($item[MIDCOM_TOOLBAR_HELPTEXT] == '')
            {
                $item[MIDCOM_TOOLBAR_HELPTEXT] = $hotkey;
            }
            else
            {
                $item[MIDCOM_TOOLBAR_HELPTEXT] .= " ({$hotkey})";
            }
        }

        // Some items may want to keep their links unmutilated
        $direct_link = false;
        if (   array_key_exists(MIDCOM_TOOLBAR_OPTIONS, $item)
            && array_key_exists("rel", $item[MIDCOM_TOOLBAR_OPTIONS])
            && $item[MIDCOM_TOOLBAR_OPTIONS]["rel"] == "directlink")
        {
            $direct_link = true;
        }

        if (! $direct_link
            && substr($item[MIDCOM_TOOLBAR_URL], 0, 1) != '/'
            && ! preg_match('|^https?://|', $item[MIDCOM_TOOLBAR_URL]))
        {
            // $item[MIDCOM_TOOLBAR_URL] =
            //       $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
            //     . $item[MIDCOM_TOOLBAR_URL];
        }
        
        $item[MIDCOM_TOOLBAR_CLASSNAME] = 'disabled';
        if ($item[MIDCOM_TOOLBAR_ENABLED])
        {
            $item[MIDCOM_TOOLBAR_CLASSNAME] = 'enabled';            
        }
        
        if (! array_key_exists(MIDCOM_TOOLBAR_HTMLLABEL, $item))
        {
            $item[MIDCOM_TOOLBAR_HTMLLABEL] = $item[MIDCOM_TOOLBAR_LABEL];
        }
        
        return $item;
    }

    /**
     * This function will traverse all available items and return the first
     * element whose URL matches the value passed to the function.
     *
     * Note, that if two items point to the same URL, only the first one
     * will be reported.
     *
     * @param string $url The url to search in the list.
     * @return int The index of the item or null, if not found.
     */
    public function get_index_from_url($section, $url)
    {
        for ($i = 0; $i < count ($this->items[$section]); $i++)
        {
            if (   $this->items[$section][$i][MIDCOM_TOOLBAR_URL] == $url
                || $this->items[$section][$i][MIDCOM_TOOLBAR__ORIGINAL_URL] == $url)
            {
                return $i;
            }
        }
        
        return null;
    }
    
    public function render() {}
    
    /**
     * Private helper function which checks an index for validity.
     * Upon any error, an Exception is thrown.
     *
     * It will automatically convert a string-based URL into an
     * Index (if possible); if the URL can't be found, it will
     * also trigger an error. The translated URL is returned by the
     * function.
     *
     * @param mixed $index The integer index or URL to check
     * @param boolean $raise_error Whether we should raise an error on missing item
     * @return int $index The valid index (possibly translated from the URL) or null on missing index.
     */
    private function check_index($section=MIDCOM_TOOLBAR_NODE, $index, $raise_error = true)
    {
        if (is_string($index))
        {
            $url = $index;
            $index = $this->get_index_from_url($section, $url);
            if (is_null($index))
            {
                if ($raise_error)
                {
                    throw new Exception("midcom_core_helper_toolbar::check_index - Invalid URL '{$url}', URL not found.");
                    // This will exit.
                }
                else
                {
                    return null;
                }
            }
        }
        
        if ($index >= count($this->items[$section]))
        {
            throw new Exception("midcom_helper_toolbar::check_index - Invalid index {$index}, it is off-the-end.");
            // This will exit.
        }
        
        if ($index < 0)
        {
            throw new Exception("midcom_helper_toolbar::check_index - Invalid index {$index}, it is negative.");
            // This will exit.
        }
        
        return $index;
    }
}

?>