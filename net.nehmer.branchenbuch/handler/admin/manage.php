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

class net_nehmer_branchenbuch_handler_admin_manage extends midcom_baseclasses_components_handler
{
    /**
     * The category we're working with, if applicable.
     *
     * @var net_nehmer_branchenbuch_branche
     * @access private
     */
    var $_branche = null;

    /**
     * The DM2 controller instance we use for Editing etc.
     *
     * @var midcom_helper_datamanager2_controller
     * @access private
     */
    var $_controller = null;

    function net_nehmer_branchenbuch_handler_admin_manage()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * The listing handler provides you with a full list of all categories currently in the system
     * for maintenance. The preparation phase is minimal, as most code lies in the recursive
     * loading code in the style.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_list($handler_id, $args, &$data)
    {
        return true;
    }

    /**
     * Shows all loaded groups, it uses a set of recursively called functions for this.
     * The code is not styleable.
     */
    function _show_list($handler_id, &$data)
    {
        echo '<h2>' . $this->_l10n->get('category management') . "</h2>\n";
        echo "<div id='net_nehmer_branchenbuch_admin_category_list'>\n";
        $this->_show_list_sublist();
        echo "</div>\n";
    }

    /**
     * This shows the sublisting of a given category, if no guid is passed, the root categories
     * are listed. A separate function is called to display each category individually, which can
     * in turn invoke this function to render the sublisting.
     *
     * In the case that there are no subcategories for the given root, no calls are made.
     *
     * @param net_nehmer_branchenbuch_branche $root The category for which the subcategories
     *     should be listed. Omit this to get a root level listing.
     */
    function _show_list_sublist($root = null)
    {
        if ($root === null)
        {
            $categories = net_nehmer_branchenbuch_branche::list_root_categories();
        }
        else
        {
            $categories = $root->list_childs();
        }

        if (! $categories)
        {
            return;
        }

        echo "<ul>\n";
        foreach ($categories as $category)
        {
            $this->_show_list_category($category);
        }
        echo "</ul>\n";
    }

    /**
     * This function renders the actual category with all its required links.
     *
     * @param net_nehmer_branchenbuch_branche $category The category to render.
     */
    function _show_list_category($category)
    {
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $delete_url = "{$prefix}manage/delete/{$category->id}";
        $edit_url = "{$prefix}manage/edit/{$category->id}";
        $create_url = "{$prefix}manage/create/{$category->id}";

        // name(edit) edit/create/delete icons

        echo '<li>';
        echo "<form action='{$delete_url}' method='post'>\n";
        echo "<a href='{$edit_url}'>{$category->name}</a>\n"
            . "<a href='{$edit_url}'><img src='" . MIDCOM_STATIC_URL . "/stock-icons/16x16/edit.png'></a>\n"
            . "<a href='{$create_url}'><img src='" . MIDCOM_STATIC_URL . "/stock-icons/16x16/stock_new.png'></a>\n";
        echo "<input name='ok' value='ok' type='image' src='" . MIDCOM_STATIC_URL . "/stock-icons/16x16/trash.png'></a>\n";
        echo "</form>\n";

        $this->_show_list_sublist($category);
        echo "</li>\n";
    }

    /**
     * Handles category editing. Prepares a controller for usage.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_edit($handler_id, $args, &$data)
    {
        $this->_branche = new net_nehmer_branchenbuch_branche($args[0]);
        if (! $this->_branche)
        {
            $this->errcode = MIDCOM_ERRNOTFOUND;
            $this->errstr = "The category {$args[0]} was not found.";
            return false;
        }

        $_MIDCOM->auth->require_do('midgard:update', $this->_branche);

        $this->_controller = midcom_helper_datamanager2_controller::create('simple');
        $this->_controller->load_schemadb('file:/net/nehmer/branchenbuch/config/schemadb_internal.inc');
        $this->_controller->set_storage($this->_branche, 'editbranche');
        $this->_controller->initialize();

        // Process the form and update the owner if necessary
        switch ($this->_controller->process_form())
        {
            case 'save':
            case 'cancel':
                $_MIDCOM->relocate('manage/list.html');
                // This will exit.
        }

        return true;
    }

    /**
     * Shows the category editor.
     */
    function _show_edit($handler_id, &$data)
    {
        echo '<h2>' . $this->_l10n->get('edit category') . '</h2>';
        $this->_controller->display_form();
    }

    /**
     * Handles category creation. Prepares a controller for usage and creates the actual object.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_create($handler_id, $args, &$data)
    {
        $this->_branche = new net_nehmer_branchenbuch_branche($args[0]);
        if (! $this->_branche)
        {
            $this->errcode = MIDCOM_ERRNOTFOUND;
            $this->errstr = "The category {$args[0]} was not found.";
            return false;
        }

        $_MIDCOM->auth->require_do('midgard:create', $this->_branche);

        $this->_controller = midcom_helper_datamanager2_controller::create('nullstorage');
        $this->_controller->load_schemadb('file:/net/nehmer/branchenbuch/config/schemadb_internal.inc');
        $this->_controller->schemaname = 'createbranche';
        $this->_controller->initialize();

        // Process the form and update the owner if necessary
        switch ($this->_controller->process_form())
        {
            case 'save':
                $this->_create_branche($this->_controller->datamanager->types['name']->value);
                $_MIDCOM->relocate('manage/list.html');
                // This will exit.

            case 'next':
                $this->_create_branche($this->_controller->datamanager->types['name']->value);
                $_MIDCOM->relocate("manage/create/{$this->_branche->guid}.html");
                // This will exit.

            case 'cancel':
                $_MIDCOM->relocate('manage/list.html');
                // This will exit.
        }

        return true;
    }

    /**
     * Little helper which creates a new branche under the currently loaded one.
     *
     * @param string $name The name of the subcategory.
     */
    function _create_branche($name)
    {
        $branche = new net_nehmer_branchenbuch_branche();
        $branche->parent = $this->_branche->guid;
        $branche->type = $this->_branche->type;
        $branche->name = $name;
        if (! $branche->create())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to create the category: ' . mgd_errstr());
            // This will exit;
        }
    }

    /**
     * Shows the category editor.
     */
    function _show_create($handler_id, &$data)
    {
        echo '<h2>' . sprintf($this->_l10n->get('create subcategory of %s'), $this->_branche->name) . '</h2>';
        $this->_controller->display_form();
    }

    /**
     * Handles category deletion, no safety interlocks at this point.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_delete($handler_id, $args, &$data)
    {
        $this->_branche = new net_nehmer_branchenbuch_branche($args[0]);
        if (! $this->_branche)
        {
            $this->errcode = MIDCOM_ERRNOTFOUND;
            $this->errstr = "The category {$args[0]} was not found.";
            return false;
        }

        $_MIDCOM->auth->require_do('midgard:delete', $this->_branche);

        if (! $this->_branche->delete())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Failed to delete Branche {$args[0]}: " . mgd_errstr());
            // This will exit.
        }

        $_MIDCOM->relocate('manage/list.html');
    }
}

?>