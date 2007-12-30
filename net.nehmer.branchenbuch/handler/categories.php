<?php
/**
 * @package net.nehmer.branchenbuch
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Branchenbuch Category Management class.
 *
 * @package net.nehmer.branchenbuch
 */

class net_nehmer_branchenbuch_handler_categories extends midcom_baseclasses_components_handler
{
    /**
     * The category record encaspulating the root (type) category.
     *
     * @var net_nehmer_branchenbuch_branche
     * @access private
     */
    var $_type = null;

    /**
     * This is an array holding the computed category list.
     *
     * The elements are indexed by category GUID and contain the following keys:
     *
     * - string localname
     * - string fullname
     * - net_nehmer_branchenbuch_branche category
     * - int entrycount
     * - string guid
     * - string listurl
     * - int depth
     *
     * @var Array
     * @access private
     */
    var $_category_list = null;

    /**
     * The string filter used to narrow down the top-level category listing, usually
     * used for alphabetical queries. It is used in the form of a SQL LIKE query.
     *
     * This is null in case there was no filter applied.
     *
     * @var string
     * @access private
     */
    var $_filter = null;

    /**
     * The handler class responsible for the custom search forms.
     *
     * @var net_nehmer_branchenbuch_callbacks_searchbase
     * @access protected
     */
    var $_customsearch = null;

    function net_nehmer_branchenbuch_handler_categories()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['category_list'] =& $this->_category_list;
        $this->_request_data['type'] =& $this->_type;
        $this->_request_data['filter'] =& $this->_filter;
    }

    /**
     * Welcome page handler, redirects to a root category along the following priorities:
     *
     * 1. If the configuration key 'default_root_category' is set, that category is displayed
     *    as default root category.
     * 2. If 1 doesn't apply, the system tries to display the root category matching the account
     *    type of the currently authenticated user.
     * 3. If neither 1 or 2 applies, the first known category is displayed.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array $data The local request data.
     * @return bool Indicating success.
     */
    function _handler_welcome($handler_id, $args, &$data)
    {
        $schemamgr = new net_nehmer_branchenbuch_schemamgr($this->_topic);
        $default = $this->_config->get('default_root_category');
        if ($default)
        {
            $category = new net_nehmer_branchenbuch_branche($default);
            $_MIDCOM->relocate($schemamgr->get_root_category_url($category->type, $category->guid));
            // this will exit.
        }

        if ($_MIDCOM->auth->user !== null)
        {
            $schemamgr = new net_nehmer_branchenbuch_schemamgr($this->_topic);
            $remote =& $schemamgr->remote;
            $type = net_nehmer_branchenbuch_branche::get_root_category_by_type($remote->get_account_type());
            if ($type)
            {
                $_MIDCOM->relocate($schemamgr->get_root_category_url($type->type, $type->guid));
                // this will exit.
            }
            else
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add('The currently active account is not associated with one of the known account types. Proceeding to use default type.',
                    MIDCOM_LOG_INFO);
                debug_pop();
            }
        }

        $result = net_nehmer_branchenbuch_branche::list_root_categories();
        if (   $result
            && count($result) > 0)
        {
            $_MIDCOM->relocate($schemamgr->get_root_category_url($result[0]->type, $result[0]->guid));
            // this will exit.
        }

        $this->errstr = 'No categories are defined, cannot continue.';
        $this->errcode = MIDCOM_ERRNOTFOUND;
        return false;
    }

    /**
     * This is the basic list handler which provides you with a flat, full listing of all
     * categories. As outlined in the components' main interface class, this code is optimized
     * for a two level hierarchy below the root category both to ease implementation and to keep
     * up the performance.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array $data The local request data.
     * @return bool Indicating success.
     */
    function _handler_list($handler_id, $args, &$data)
    {
        $this->_type = new net_nehmer_branchenbuch_branche($args[0]);
        if (   ! $this->_type
            || $this->_type->parent != '')
        {
            $this->errcode = MIDCOM_ERRNOTFOUND;
            $this->errstr = "The root category specified is invalid.";
            return false;
        }

        // Go over the top level categories
        $this->_category_list = Array();

        $categories = $this->_type->list_childs();
        foreach ($categories as $category)
        {
            $childs = $category->list_childs();
            if ($childs)
            {
                foreach($childs as $child_category)
                {
                    $this->_add_category_to_list($child_category, false, "{$category->name}: ");
                }
            }
            else
            {
                $this->_add_category_to_list($category, false);
            }
        }

        $this->_prepare_request_data();
        $_MIDCOM->substyle_append($this->_type->type);
        $_MIDCOM->set_26_request_metadata(time(), $this->_topic->guid);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_type->name}");
        $this->_component_data['active_leaf'] = $this->_type->guid;

        return true;
    }

    /**
     * This is a helper which adds the specfied catgory to the _category_list. It computes
     * all members that could be helpful for display.
     *
     * @param net_nehmer_branchenbuch_branche $category The category to add.
     * @param bool $listalpha Set this to true if you are in alphabetic category listing mode.
     *     It will define whether the entry listings will set the return_url to the letter
     *     filtered, alphabetic listing code or to the full category listing.
     * @param string $parent_prefix The string to use as prefix in front of the name to generate
     *     the full category name. This is faster then using the get_full_name function of the
     *     branchen class. If you need any separators like ': ', you nedd to add them yourself.
     */
    function _add_category_to_list($category, $listalpha, $parent_prefix = '')
    {
        $urlprefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        if ($listalpha)
        {
            $urlprefix .= 'entry/list/alpha';
        }
        else
        {
            $urlprefix .= 'entry/list';
        }
        $this->_category_list[$category->guid] = Array
        (
            'localname' => $category->name,
            'fullname' => "{$parent_prefix}{$category->name}",
            'guid' => $category->guid,
            'category' => $category,
            'entrycount' => $category->itemcount,
            'listurl' => "{$urlprefix}/{$category->guid}.html",
            'depth' => ($parent_prefix == '') ? 0 : 1,
        );
    }

    /**
     * Shows all loaded groups.
     *
     * The request data keys <i>category_list</i> and <i>type</i> are populated all the time,
     * while iterating over the categories to show, a reference to the category_list entry that
     * should be shown is added to the <i>category</i> entry.
     */
    function _show_list($handler_id, &$data)
    {
        midcom_show_style('categories-list-begin');
        foreach ($this->_category_list as $guid => $category)
        {
            $data['category'] =& $this->_category_list[$guid];
            midcom_show_style('categories-list-item');
        }
        midcom_show_style('categories-list-end');
    }

    /**
     * This is the basic list handler which provides you with a flat, full listing of all
     * categories filtered by a letter from the alphabet.
     *
     * As outlined in the components' main interface class, this code is optimized
     * for a two level hierarchy below the root category both to ease implementation and to keep
     * up the performance.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array $data The local request data.
     * @return bool Indicating success.
     */
    function _handler_list_alpha($handler_id, $args, &$data)
    {
        $this->_type = new net_nehmer_branchenbuch_branche($args[0]);
        if (   ! $this->_type
            || $this->_type->parent != '')
        {
            $this->errcode = MIDCOM_ERRNOTFOUND;
            $this->errstr = "The root category specified is invalid.";
            return false;
        }
        $this->_filter = $args[1];

        // Go over the top level categories
        $this->_category_list = Array();

        $qb = $this->_type->get_list_childs_qb();
        $qb->add_constraint('name', 'LIKE', "{$this->_filter}%");
        $categories = $qb->execute();
        foreach ($categories as $category)
        {
            $childs = $category->list_childs();
            if ($childs)
            {
                foreach($childs as $child_category)
                {
                    $this->_add_category_to_list($child_category, true, "{$category->name}: ");
                }
            }
            else
            {
                $this->_add_category_to_list($category, true);
            }
        }

        $this->_prepare_request_data();
        $this->_request_data['return_url'] = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
            . "category/list/{$this->_type->guid}.html";
        $_MIDCOM->substyle_append($this->_type->type);
        $_MIDCOM->set_26_request_metadata(time(), $this->_topic->guid);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_type->name}");
        $this->_component_data['active_leaf'] = $this->_type->guid;

        return true;
    }

    /**
     * Shows all loaded groups.
     *
     * The request data keys <i>category_list</i> and <i>type</i> are populated all the time,
     * while iterating over the categories to show, a reference to the category_list entry that
     * should be shown is added to the <i>category</i> entry.
     */
    function _show_list_alpha($handler_id, &$data)
    {
        midcom_show_style('categories-list-alpha-begin');
        foreach ($this->_category_list as $guid => $category)
        {
            $data['category'] =& $this->_category_list[$guid];
            midcom_show_style('categories-list-alpha-item');
        }
        midcom_show_style('categories-list-alpha-end');
    }

    /**
     * Loads the custom search plugin for the selected type. It either displays the
     * search form or processes the search form (which relocates to the entries list).
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array $data The local request data.
     * @return bool Indicating success.
     */
    function _handler_customsearch($handler_id, $args, &$data)
    {
        $this->_type = new net_nehmer_branchenbuch_branche($args[0]);
        if (   ! $this->_type
            || $this->_type->parent != '')
        {
            $this->errcode = MIDCOM_ERRNOTFOUND;
            $this->errstr = "The root category specified is invalid.";
            return false;
        }

        // Load the search handler, try form_processing, relocate on success, show the
        // search form on failure.
        $this->_load_searchhandler();

        if ($this->_customsearch->process_form())
        {
            $_MIDCOM->relocate("entry/list/customsearch/{$this->_type->guid}");
            // This will exit()
        }

        $this->_prepare_request_data();
        $_MIDCOM->substyle_append($this->_type->type);
        $_MIDCOM->set_26_request_metadata(time(), $this->_topic->guid);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_type->name}");
        $this->_component_data['active_leaf'] = $this->_type->guid;

        return true;
    }

    /**
     * Displays the custom search form.
     */
    function _show_customsearch($handler_id, &$data)
    {
        $this->_customsearch->show(&$data);
    }

    /**
     * Creates and returns an instance of the custom search handler.
     *
     * Any error calls generate_error.
     */
    function _load_searchhandler()
    {
        // Ensure that the base class is there (for the static callback)
        require_once(MIDCOM_ROOT . '/net/nehmer/branchenbuch/callbacks/searchbase.php');

        $type_config = $this->_config->get('type_config');
        $config = $type_config[$this->_type->type]['customsearch'];
        $this->_customsearch =&
            net_nehmer_branchenbuch_callbacks_searchbase::create_instance($this, $config);
    }


}

?>
