<?php

/**
 * @package midcom.baseclasses
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:request_admin.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Base class to encaspulate a admin request to the component, instantinated by the MidCOM
 * component interface.
 *
 * This is largly equivalent to the regular request base class midcom_baseclasses_components_request
 * with the exception, that helper handlers exist for configuration management.
 *
 * Three additional member variables are defined during startup, making the topic, leaf and metadata AIS
 * toolbars available to the user.
 *
 * <b>Custom Handler: Datamanger driven component configuration</b>
 *
 * It defines a handler that can be used for Datamanager driven configuration. It requires
 * a few configuration directives: 'schemadb' must hold a valid path to a
 * schema database containing the actual configuration schema. The key 'schema'
 * may contain the name of the schema to use, it defaults to 'config' if omitted.
 * Finally, 'disable_return_to_topic', if set to true, will hide the automatically
 * added "Return to topic" toolbar item. This is useful for components which have only
 * an configuration interface but nothing more, it defaults to false.
 *
 * It uses the MidCOM l10n string 'return to topic' as title for the leaf-toolbar-item added
 * during the handle phase.
 *
 * The handler will load the datamanager library during the handle phase, just in case you
 * have not yet loaded it.
 *
 * <i>Important Note:</i> The schema you supply for configuration is always treated as having
 * a zero lock timeout, this parameter is enforced after initializing the datamanager. It is
 * important, as the config handler stays in the edit loop indefinitly.
 *
 * Full configuration example:
 *
 * <code>
 * <?php
 *  $this->_request_switch[] = Array
 *  (
 *  	'handler' => 'config_dm',
 *      'fixed_args' => Array('config'),
 *      'schemadb' => 'file:/de/linkm/taviewer/config/schemadb_config.inc',
 *      'schema' => 'config',
 *      'disable_return_to_topic' => false
 *  );
 * ?>
 * </code>
 *
 *
 * @package midcom.baseclasses
 * @deprecated This class is deprecated since AIS will be dropped in favor of on-site
 *     administration. If you need a component configuration screen (like with the config_dm
 *     handler), check out the class midcom_core_handler_configdm.
 */

class midcom_baseclasses_components_request_admin extends midcom_baseclasses_components_request
{
    /**#@+
     * AIS Toolbar reference.
     *
     * @access protected
     * @var midcom_admin_content_toolbar
     */

    /**
     * The toolbar local to the current request
     */
    var $_local_toolbar = null;

    /**
     * The toolbar of the topic.
     */
    var $_topic_toolbar = null;

    /**
     * The Metadata toolbar. You should normally have no need to modify it.
     */
    var $_meta_toolbar = null;

    /**#@-*/

    /**
     * Initialize the toolbar references after constructing the base class.
     */
    function midcom_baseclasses_components_request_admin ($topic, $config)
    {
        parent::midcom_baseclasses_components_request($topic, $config);

        $toolbars = & midcom_helper_toolbars::get_instance();
        // Get the toolbars
        $this->_local_toolbar = & $toolbars->bottom;
        $this->_topic_toolbar = & $toolbars->top;
        $this->_meta_toolbar  = & $toolbars->meta;

        /*
        $this->_local_toolbar =& $GLOBALS['midcom_admin_content_toolbar_component'];
        $this->_topic_toolbar =& $GLOBALS['midcom_admin_content_toolbar_main'];
        $this->_meta_toolbar =& $GLOBALS['midcom_admin_content_toolbar_meta'];
        */
    }

    /**
     * Override initialization to add a default handler in case no admin handlers are
     * defined. This is a transitional measure to have on-site-components not block
     * AIS usage.
     */
    function initialize($component)
    {
        parent::initialize($component);

        if (! $this->_request_switch)
        {
            // Generic and personal welcom pages
            $this->_request_switch['welcome'] = Array
            (
                'handler' => 'welcome_deprecated',
            );

        }
    }

    /**
     * Fallback handler, shown in case no handler has been defined anymore by
     * deprecated AIS classes. Displays a simple welcome page.
     */
    function _handler_welcome_deprecated($handler_id, $args, &$data)
    {
        return true;
    }

    /**
     * Fallback handler, shown in case no handler has been defined anymore by
     * deprecated AIS classes. Displays a simple welcome page noting that everything
     * is done directly on-site now.
     */
    function _show_welcome_deprecated($handler_id, &$data)
    {
        echo '<h2>' . $_MIDCOM->i18n->get_string('ais-deprecation-heading', 'midcom') .
            "</h2>\n\n";
        echo '<p>' . $_MIDCOM->i18n->get_string('ais-deprecation-text', 'midcom') .
            "</p>\n\n";
    }

    /**
     * Helper function that prepares a datamanager instance for the config_dm handler.
     * You can override this function to influence the way the datamanger is initialized,
     * for example to modify the schema after it has been loaded.
     *
     * Normally, you should always call the base class implementation, and modify the
     * datamanager in $data['datamanager'] afterwards.
     *
     * @access protected
     * @param mixed $handler_id The ID of the handler.
     * @param mixed $data The local request data (note the reference when inheriting).
     */
    function _handler_config_dm_prepare($handler_id, &$data)
    {
        // Load the datamanager, then create an instance.
        $_MIDCOM->load_library('midcom.helper.datamanager');
        $data['datamanager'] = new midcom_helper_datamanager($this->_handler['schemadb']);

        if ($data['datamanager'] == false)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to instantinate configuration datamanager.');
            // This will exit.
        }

        // Call the dm prepared event handler.
        $this->_on_handler_config_dm_prepared($data['datamanager']);

        if (! $data['datamanager']->init($this->_topic, $this->_handler['schema']))
        {
            debug_add('Failed to initialize the datamanager.', MIDCOM_LOG_CRIT);
            debug_print_r('Topic object we tried was:', $this->_config_topic);
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to initialize configuration datamanager.');
            // This will exit.
        }

        // Turn off locking.
        $data['datamanager']->_layoutdb[$this->_handler['schema']]['locktimeout'] = 0;
    }

    /**
     * Event handler, called after the configuration datamanager instance has been created but not yet
     * initialized. Use this hook to modify the schema where neccessary.
     *
     * @param midcom_helper_datamanager $datamanager A reference(!) to the datamanager handling the request.
     * @access protected
     */
    function _on_handler_config_dm_prepared(&$datamanager) { }

    /**
     * Datamanager configuration handler interface.
     *
     * Displays the back to index toolbar item unless surpressed by the configuration and
     * processes the DM form data, staying in the edit loop.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param mixed $data The local request data.
     * @return bool Indicating success.
     * @access protected
     */
    function _handler_config_dm($handler_id, $args, &$data)
    {
        // Auto-Complete the config
        if (! array_key_exists('schema', $this->_handler))
        {
            $this->_handler['schema'] = 'config';
        }
        if (! array_key_exists('disable_return_to_topic', $this->_handler))
        {
            $this->_handler['disable_return_to_topic'] = false;
        }

        // Verify permissions
        $_MIDCOM->auth->require_do('midgard:update', $this->_topic);
        $_MIDCOM->auth->require_do('midcom:component_config', $this->_topic);

        // Call the pre-preparation event handler.
        $this->_on_handler_config_dm_preparing();

        $this->_handler_config_dm_prepare($handler_id, $data);

        if (! $this->_handler['disable_return_to_topic'])
        {
            /* Add the toolbar items, if neccessary */
            $this->_local_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => '',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('back to index'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/folder.png',
                MIDCOM_TOOLBAR_ENABLED => true
            ));
        }

        switch ($data['datamanager']->process_form()) {
            case MIDCOM_DATAMGR_SAVED:
                // Call the event handler
                $this->_on_handler_config_dm_saved();
                break;

            case MIDCOM_DATAMGR_EDITING:
            case MIDCOM_DATAMGR_CANCELLED:
                // Do nothing here, the datamanager will invalidate the cache.
                // Apart from that, let the user edit the configuration as long
                // as he likes.
                break;

            case MIDCOM_DATAMGR_FAILED:
                $this->errstr = "Datamanager: " . $GLOBALS["midcom_errstr"];
                $this->errcode = MIDCOM_ERRCRIT;
                debug_pop();
                return false;
        }

        return true;
    }

    /**
     * Event handler, called before the configuration datamanger is created. Use this to
     * prepare anything that is required to start up the Datamanager.
     *
     * @access protected
     */
    function _on_handler_config_dm_preparing() { }

    /**
     * Event handler, called when the configuration system has successfully stored
     * new configuration settings.
     *
     * @access protected
     */
    function _on_handler_config_dm_saved() { }


    /**
     * Simple display handler for the config_dm handler, it uses the MidCOM L10n string
     * 'component configuration' as heading, and immediately displays the form afterwards.
     *
     * If you need any styling, you should override this.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed $data The local request data.
     * @access protected
     */
    function _show_config_dm($handler_id, &$data)
    {
        echo '<h2>' . $this->_l10n_midcom->get('component configuration') . "</h2>\n";
        $data['datamanager']->display_form();
    }
}
?>