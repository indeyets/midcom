<?php
/**
 * @package net.fernmark.pedigree
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: view.php 5856 2007-05-04 12:13:52Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Shows the dog object view page
 *
 * @package net.fernmark.pedigree
 */
class net_fernmark_pedigree_handler_dog_view extends midcom_baseclasses_components_handler
{
    /**
     * The dog to display
     *
     * @var midcom_db_dog
     * @access private
     */
    var $_dog = null;

    /**
     * The Datamanager of the dog to display.
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_datamanager = null;

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['dog'] =& $this->_dog;
        $this->_request_data['l10n'] =& $this->_l10n;
        $this->_request_data['l10n_midcom'] =& $this->_l10n_midcom;
        $this->_request_data['datamanager'] =& $this->_datamanager;

        // Populate the toolbar
        $this->_view_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "dog/edit/{$this->_dog->guid}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
            MIDCOM_TOOLBAR_ACCESSKEY => 'e',
            MIDCOM_TOOLBAR_ENABLED => $this->_dog->can_do('midgard:update'),
        ));
        $this->_view_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "create/result/{$this->_dog->guid}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('new result'),
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-text.png',
            MIDCOM_TOOLBAR_ACCESSKEY => 'r',
            MIDCOM_TOOLBAR_ENABLED => $this->_dog->can_do('midgard:create'),
        ));
        $this->_view_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "create/dog/{$this->_dog->guid}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('new offspring'),
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-text.png',
            MIDCOM_TOOLBAR_ACCESSKEY => 'n',
            MIDCOM_TOOLBAR_ENABLED => $this->_topic->can_do('midgard:create'),
        ));

        $this->_view_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "dog/delete/{$this->_dog->guid}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
            MIDCOM_TOOLBAR_ACCESSKEY => 'd',
            MIDCOM_TOOLBAR_ENABLED => $this->_dog->can_do('midgard:delete'),
        ));
    }


    /**
     * Simple default constructor.
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Maps the content topic from the request data to local member variables.
     */
    function _on_initialize()
    {
    }

    /**
     * Handle actual dog display
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_view ($handler_id, $args, &$data)
    {
        $this->_dog = new net_fernmark_pedigree_dog_dba($args[0]);
        if (! $this->_dog)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The dog '{$args[0]}' was not found.");
            // This will exit.
        }
        $this->_load_datamanager();

        /*
        if ($this->_config->get('enable_ajax_editing'))
        {
            $this->_request_data['controller'] =& midcom_helper_datamanager2_controller::create('ajax');
            $this->_request_data['controller']->schemadb =& $this->_request_data['schemadb'];
            $this->_request_data['controller']->set_storage($this->_dog);
            $this->_request_data['controller']->process_ajax();
        }
        */

        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "dog/{$this->_dog->guid}",
            MIDCOM_NAV_NAME => $this->_dog->name_with_kennel,
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        $this->_prepare_request_data();

        $_MIDCOM->bind_view_to_object($this->_dog, $this->_datamanager->schema->name);
        $_MIDCOM->set_26_request_metadata($this->_dog->metadata->revised, $this->_dog->guid);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_dog->name_with_kennel}");

        return true;
    }

    /**
     * Internal helper, loads the datamanager for the current dog. Any error triggers a 500.
     *
     * @access private
     */
    function _load_datamanager()
    {
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_request_data['schemadb']);

        if (   ! $this->_datamanager
            || ! $this->_datamanager->autoset_storage($this->_dog))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for dog {$this->_dog->id}.");
            // This will exit.
        }
    }

    /**
     * Shows the loaded dog.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_view ($handler_id, &$data)
    {
        /*
        if ($this->_config->get('enable_ajax_editing'))
        {
            // For AJAX handling it is the controller that renders everything
            $this->_request_data['view_dog'] = $this->_request_data['controller']->get_content_html();
        }
        else
        {
            $this->_request_data['view_dog'] = $this->_datamanager->get_content_html();
        }
        */
        $this->_request_data['view_dog'] = $this->_datamanager->get_content_html();

        midcom_show_style('view-dog');
    }
}

?>