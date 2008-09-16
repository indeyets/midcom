<?php
/**
 * @package net.nemein.alphabeticalindex
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is an index handler class for net.nemein.alphabeticalindex
 *
 * @package net.nemein.alphabeticalindex
 */
class net_nemein_alphabeticalindex_handler_index extends midcom_baseclasses_components_handler
{
    /**
     * List of alphabets (value is true if current alphabet has content)
     */
    var $_alphabets = array();

    /**
     * Rendered html list of alphabet navigation
     */
    var $_alphabets_nav = null;

    var $_all_items = null;
    var $_items = null;

    /**
     * Simple default constructor.
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * _on_initialize is called by midcom on creation of the handler.
     */
    function _on_initialize()
    {
        $tmp_letters = 'A B C D E F G H I J K L M N O P Q R S T U V W X Y Z Å Ä Ö';
        $tmp_letters = explode(' ', $tmp_letters);
        foreach ($tmp_letters as $letter)
        {
            $this->_alphabets[$letter] = false;
        }
    }

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data($handler_id)
    {
        $this->_request_data['controller'] =& $this->_controller;
        $this->_request_data['alphabets'] =& $this->_alphabets;
        $this->_request_data['alphabets_nav'] =& $this->_alphabets_nav;
        $this->_request_data['all_items'] =& $this->_all_items;
    }

    /**
     * The handler for the index article.
     *
     * @param mixed $handler_id the array key from the request array
     * @param array $args the arguments given to the handler
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_index ($handler_id, $args, &$data)
    {
        $this->_request_data['name']  = "net.nemein.alphabeticalindex";

        $qb = net_nemein_alphabeticalindex_item::new_query_builder();
        $qb->begin_group('AND');
        $qb->add_constraint('title', '<>', '');
        $qb->add_constraint('url', '<>', '');
        $qb->add_constraint('node', '=', $this->_topic->id);
        $qb->end_group();
        $qb->add_order('title', 'ASC');

        $this->_all_items = $qb->execute();
        $this->_items = $this->_all_items;

        foreach ($this->_alphabets as $letter => $value)
        {
            $this->_alphabets[$letter] = $this->_get_items_that_start_with($letter);
        }

        $this->_render_nav();

        $this->_prepare_request_data($handler_id);

        /**
         * change the pagetitle. (must be supported in the style)
         */
        $_MIDCOM->set_pagetitle($this->_topic->extra);

        return true;
    }

    function _get_items_that_start_with($letter)
    {
        $items = array();

        foreach ($this->_items as $k => $item)
        {
            if (strpos(strtolower($item->title), strtolower($letter)) === 0)
            {
                $items[] = $item;
                unset($this->_items[$k]);
            }
        }

        if (!empty($items))
        {
            return $items;
        }
        return false;
    }

    function _render_nav()
    {
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $html = '';

        $html .= "<div class=\"net_nemein_alphabeticalindex navigation\" id=\"net_nemein_alphabeticalindex_navigation\">\n";
        $html .= "    <ul>\n";

        $i = 0;
        $letter_count = count($this->_alphabets);
        foreach ($this->_alphabets as $letter => $value)
        {
            ++$i;

            $html_letter = $letter;//htmlentities($letter);
            $letter = urlencode($letter);

            $enabled = false;
            if (   is_array($value)
                && !empty($value))
            {
                $enabled = true;
            }

            $classname = '';
            if ($i == 1)
            {
                $classname .= 'first ';
            }
            else if ($i == $letter_count)
            {
                $classname .= 'last ';
            }
            if (!$enabled)
            {
                $classname .= 'disabled ';
            }

            $html .= "        ";
            $html .= "<li class=\"{$classname}\">";
            if ($enabled)
            {
                $html .= "<a href=\"{$prefix}#{$letter}\">{$html_letter}</a>";
            }
            else
            {
                $html .= "{$html_letter}";
            }
            $html .= "</li>\n";

            if ($i < $letter_count)
            {
                $html .= "        <li class=\"separator\"></li>\n";
            }
        }

        $html .= "    </ul>\n";
        $html .= "</div>\n";

        $this->_alphabets_nav = $html;
    }

    /**
     * This function does the output.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_index($handler_id, &$data)
    {
        // hint: look in the style/index.php file to see what happens here.
        midcom_show_style('index');
    }
}
?>