<?php
/**
 * @package net.nehmer.account
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/** We need the PEAR Date class. See http://pear.php.net/package/Date/docs/latest/ */
require_once('Date.php');

/**
 * Account Management handler class: View Account
 *
 * This class implements the regular account view modes, both a full fledged mode, and a
 * quick view which provides only the information which is always visible.
 *
 * For the URLs being handled here, see the main class' documentation.
 *
 * Summary of available request keys:
 *
 * - datamanager: A reference to the DM2 Instance.
 * - visible_fields: A plain list of all visible field names.
 * - visible_data: The rendered data associated with the visible fields.
 * - schema: A reference to the schema in use.
 * - account: A reference to the account in use.
 * - view_self: A bool indicating wether we display our own account, or not.
 * - profile_url: Only applicable in the quick-view mode, it contains the URL
 *   to the full profile record.
 * - edit_url: Only applicable if in view-self mode, it contains the URL to the
 *   edit record screen.
 *
 * This class listens to the handlers IDs 'self', 'self_quick', 'other' and 'other_quick',
 * invoking the appropriate view code. Unknown handler IDs will be rejected with generate_error.
 * It expects the following URL structures, relative to ANCHOR_PREFIX:
 *
 * - 'self': /
 * - 'self_quick': /quick.html
 * - 'other': /view/$guid.html
 * - 'other_quick': /view/quick/$guid.html
 *
 * @package net.nehmer.account
 */

class net_nehmer_account_handler_view extends midcom_baseclasses_components_handler
{
    function net_nehmer_account_handler_view()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * The user account we are managing. This is taken from the currently active user
     * if no account is specified in the URL, or from the GUID passed to the system.
     *
     * @var midcom_db_person
     * @access private
     */
    var $_account = null;

    /**
     * The Avatar image, if set.
     *
     * @var midcom_baseclasses_database_attachment
     * @access private
     */
    var $_avatar = null;

    /**
     * The Avatar thumbnail image, if set.
     *
     * @var midcom_baseclasses_database_attachment
     * @access private
     */
    var $_avatar_thumbnail = null;

    /**
     * The midcom_core_user object matching the loaded account. This is useful for
     * isonline checkes and the like.
     *
     * @var midcom_core_user
     * @access private
     */
    var $_user = null;

    /**
     * This flag is set to true if we are viewing the account of the currently registered
     * user. This influences the access control of the account display.
     *
     * @var bool
     * @access private
     */
    var $_view_self = false;

    /**
     * This is true if we are in the quick-view mode, which displays only the administrativly
     * assigned fields, along with a link to the full profile view. This makes live a bit
     * easier when including profiles in other components.
     *
     * @var bool
     * @access private
     */
    var $_view_quick = false;

    /**
     * The datamanager used to load the account-related information.
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_datamanager = null;

    /**
     * This is a list of visible field names of the current account. It is computed after
     * account loading. They are listed in the order they appear in the schema.
     *
     * @var Array
     * @access private
     */
    var $_visible_fields = Array();

    /**
     * This is an array extracted out of the parameter net.nehmer.account/visible_field_list,
     * which holds the names of all fields the user has marked visible. This is loaded once
     * when determining visibilities.
     *
     * @var Array
     * @access private
     */
    var $_visible_fields_user_selection = Array();

    /**
     * The view handler will load the account and set the appropriate flags for startup preparation
     * according to the handler name.
     */
    function _handler_view($handler_id, $args, &$data)
    {
        switch ($handler_id)
        {
            case 'self':
                if (   !$_MIDCOM->auth->user
                    && $this->_config->get('allow_register'))
                {
                    $_MIDCOM->relocate('register.html');
                }
                $_MIDCOM->auth->require_valid_user();
                $this->_account = $_MIDCOM->auth->user->get_storage();
                net_nehmer_account_viewer::verify_person_privileges($this->_account);
                $this->_view_self = true;
                $this->_view_quick = false;
                break;

            case 'self_quick':
                $_MIDCOM->auth->require_valid_user();
                $this->_account = $_MIDCOM->auth->user->get_storage();
                net_nehmer_account_viewer::verify_person_privileges($this->_account);
                $this->_view_self = true;
                $this->_view_quick = true;
                break;

            case 'other':
                $this->_account = new midcom_db_person($args[0]);
                $this->_view_self = false;
                $this->_view_quick = false;
                break;

            case 'other_quick':
                $this->_account = new midcom_db_person($args[0]);
                $this->_view_self = false;
                $this->_view_quick = true;
                break;

            default:
                $this->errstr = "Unknown handler ID {$handler_id} encountered.";
                $this->errcode = MIDCOM_ERRCRIT;
                return false;
        }

        if (! $this->_account)
        {
            $this->errcode = MIDCOM_ERRNOTFOUND;
            $this->errstring = 'The account was not found.';
            return false;
        }
        $this->_user =& $_MIDCOM->auth->get_user($this->_account);
        $this->_avatar = $this->_account->get_attachment('avatar');
        $this->_avatar_thumbnail = $this->_account->get_attachment('avatar_thumbnail');

        // This is temporary stuff until we get a preferences mechanism up and running.
        $data['communitymotto'] = $this->_account->get_parameter('midcom.helper.datamanager2', 'communitymotto');
        $data['communityactive'] = (bool) $this->_account->get_parameter('midcom.helper.datamanager2', 'communityactive');
        // End temporary Stuff

        $this->_prepare_datamanager();
        $this->_compute_visible_fields();
        $this->_prepare_request_data();
        $_MIDCOM->bind_view_to_object($this->_account, $this->_datamanager->schema->name);
        $_MIDCOM->set_26_request_metadata(time(), $this->_topic->guid);
        $_MIDCOM->set_pagetitle("{$this->_account->name} ({$this->_datamanager->schema->description})");

        return true;
    }

    /**
     * This function prepares the requestdata with all computed values.
     * A special case is the visible_data array, which maps field names
     * to prepared values, which can be used in display directly. The
     * information returned is already HTML escaped.
     *
     * @access private
     */
    function _prepare_request_data()
    {
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $att_prefix = $_MIDCOM->get_page_prefix();

        $visible_data = Array();
        foreach ($this->_visible_fields as $name)
        {
            $visible_data[$name] = $this->_render_field($name);
        }

        $revised = $this->_account->get_parameter('net.nehmer.account', 'revised');
        if (! $revised)
        {
            $revised = $this->_account->created;
        }
        $revised = new Date($revised);
        $published = $this->_account->get_parameter('net.nehmer.account', 'published');
        if ($published)
        {
            $published = new Date($published);
        }

        $this->_request_data['datamanager'] =& $this->_datamanager;
        $this->_request_data['visible_fields'] =& $this->_visible_fields;
        $this->_request_data['visible_data'] = $visible_data;
        $this->_request_data['schema'] =& $this->_datamanager->schema;
        $this->_request_data['account'] =& $this->_account;
        $this->_request_data['avatar'] =& $this->_avatar;
        $this->_request_data['avatar_thumbnail'] =& $this->_avatar_thumbnail;
        $this->_request_data['user'] =& $this->_user;
        $this->_request_data['revised'] = $revised;
        $this->_request_data['published'] = $published;
        $this->_request_data['view_self'] = $this->_view_self;

        if ($this->_view_quick)
        {
            if ($this->_view_self)
            {
                $this->_request_data['profile_url'] = $prefix;
            }
            else
            {
                $this->_request_data['profile_url'] = "{$prefix}view/{$this->_account->guid}.html";
            }
        }
        else
        {
            $this->_request_data['profile_url'] = null;
        }

        if ($this->_view_self)
        {
            $this->_request_data['edit_url'] = "{$prefix}edit.html";
        }
        else if ($_MIDCOM->auth->admin)
        {
            $this->_request_data['edit_url'] = "{$prefix}admin/edit/{$this->_account->guid}.html";
        }
        else
        {
            $this->_request_data['edit_url'] = null;
        }

        if ($this->_avatar)
        {
            $this->_request_data['avatar_url'] = "{$att_prefix}midcom-serveattachmentguid-{$this->_avatar->guid}/avatar";
        }
        else
        {
            $this->_request_data['avatar_url'] = null;
        }
        if ($this->_avatar_thumbnail)
        {
            $this->_request_data['avatar_thumbnail_url'] = "{$att_prefix}midcom-serveattachmentguid-{$this->_avatar_thumbnail->guid}/avatar_thumbnail";
        }
        else
        {
            $this->_request_data['avatar_thumbnail_url'] = null;
        }
    }

    /**
     * A little helper which extracts the view of the given type
     */
    function _render_field($name)
    {
        return $this->_datamanager->types[$name]->convert_to_html();
    }

    /**
     * Internal helper function, prepares a datamanager based on the current account.
     */
    function _prepare_datamanager()
    {
        $schemadb = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_account'));
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($schemadb);
        $this->_datamanager->autoset_storage($this->_account);
        foreach ($this->_datamanager->schema->field_order as $name)
        {
            if (! array_key_exists('visible_mode', $this->_datamanager->schema->fields[$name]['customdata']))
            {
                $this->_datamanager->schema->fields[$name]['customdata']['visible_mode'] = 'user';
            }
        }
    }

    /**
     * This function iterates over the field list in the schema and puts a list
     * of fields the user may see together.
     *
     * @see is_field_visisble()
     */
    function _compute_visible_fields()
    {
        if ($this->_view_quick)
        {
            // This will effectivly hide all user-defined fields.
            $this->_visible_fields_user_selection = Array();
        }
        else
        {
            $this->_visible_fields_user_selection = explode(',', $this->_account->get_parameter('net.nehmer.account', 'visible_field_list'));
        }
        $this->_visible_fields = Array();

        foreach ($this->_datamanager->schema->field_order as $name)
        {
            if ($this->_is_field_visible($name))
            {
                $this->_visible_fields[] = $name;
            }
        }
    }

    /**
     * This helper uses the 'visible_mode' customdata member to compute actual visibility
     * of a field. Possible settings:
     *
     * 'always' shows a field unconditionally, 'user' lets the user choose wether he
     * wants it shown, 'never' hides the field unconditionally and 'link' links it to the
     * visibility state of another field. In the last case you need to set the 'visible_link'
     * customdata to the name of another field to make this work.
     *
     * @return bool Indicating Visibility
     */
    function _is_field_visible($name)
    {
        if (   $_MIDCOM->auth->admin
            || (   $this->_view_self
                && ! $this->_view_quick))
        {
            return true;
        }

        switch ($this->_datamanager->schema->fields[$name]['customdata']['visible_mode'])
        {
            case 'always':
                return true;

            case 'never':
            case 'skip':
                return false;

            case 'link':
                $target = $this->_datamanager->schema->fields[$name]['customdata']['visible_link'];
                if ($target == $name)
                {
                    $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                        "Tried to link the visibility of {$name} to itself.");
                    // this will exit()
                }
                return $this->_is_field_visible($target);

            case 'user':
                return in_array($name, $this->_visible_fields_user_selection);

        }
        $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
            "Unknown Visibility declaration in {$name}: {$this->_datamanager->schema->fields[$name]['customdata']['visible_mode']}.");
        // This will exit()
    }

    /**
     * The rendering code consists of a standard init/loop/end construct.
     */
    function _show_view($handler_id, &$data)
    {
        if ($this->_view_quick)
        {
            midcom_show_style('show-quick-account');
        }
        else
        {
            midcom_show_style('show-full-account');
        }
    }

}

?>
