<?php
/**
 * @package net.nemein.calendar
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Calendar event creator
 *
 * @package net.nemein.calendar
 */
class net_nemein_calendar_handler_create extends midcom_baseclasses_components_handler
{

    /**
     * The Controller of the article used for creating
     *
     * @var midcom_helper_datamanager2_controller_create
     * @access private
     */
    var $_controller = null;

    /**
     * Simple default constructor.
     */
    function net_nemein_calendar_handler_create()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Creates a root event if necessary.
     */
    function _handler_rootevent($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        
        if (array_key_exists('root_event', $this->_request_data))
        {
            // We have this already
            $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX));
        }
        
        $this->_request_data['root_event'] = new net_nemein_calendar_event();
        
        if (array_key_exists('master_event', $this->_request_data))
        {
            $this->_request_data['root_event']->up = $this->_request_data['master_event'];
        }
        else
        {
            $this->_request_data['root_event']->up = 0;
        }
        
        $this->_request_data['root_event']->title = sprintf('__%s root event', $this->_topic->guid);
        
        if ($this->_request_data['root_event']->create())
        {
            $this->_topic->parameter('net.nemein.calendar', 'root_event', $this->_request_data['root_event']->guid);
            
            $_MIDCOM->uimessages->add('net.nemein.calendar', "Root event {$this->_request_data['root_event']->guid} created", 'ok');
            
            $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)); 
            // This will exit;           
        }
        else
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create root event, reason ".mgd_errstr());
            // This will exit;
        }
    }

    /**
     * DM2 creation callback, binds to the current content topic.
     */
    function & dm2_create_callback (&$controller)
    {
        $this->_request_data['event'] = new net_nemein_calendar_event();
        $this->_request_data['event']->up = $this->_request_data['root_event']->id;

        if (! $this->_request_data['event']->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $this->_request_data['event']);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to create a new event, cannot continue. Last Midgard error was: '. mgd_errstr());
            // This will exit.
        }

        return $this->_request_data['event'];
    }

    /**
     * Internal helper, loads the controller for the current article. Any error triggers a 500.
     *
     * @access private
     */
    function _load_controller()
    {
        $this->_controller =& midcom_helper_datamanager2_controller::create('create');
        $this->_controller->schemadb =& $this->_request_data['schemadb'];
        $this->_controller->schemaname = $this->_request_data['schemadb_schema'];
        $this->_controller->defaults = $this->_request_data['schemadb_defaults'];
        $this->_controller->callback_object =& $this;
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 create controller.");
            // This will exit.
        }
    }

    /**
     * Displays an event creation view.
     */
    function _handler_create($handler_id, $args, &$data)
    {
        $this->_request_data['root_event']->require_do('midgard:create');

        $this->_request_data['schemadb_schema'] = $args[0];
        if (!array_key_exists($this->_request_data['schemadb_schema'], $this->_request_data['schemadb']))
        {
            return false;
        }
        
        $this->_request_data['schemadb_defaults'] = Array();
        
        $this->_load_controller();

        switch ($this->_controller->process_form())
        {
            case 'save':
                // Index the article
                $indexer =& $_MIDCOM->get_service('indexer');
                net_nemein_calendar_viewer::index($this->_controller->datamanager, $indexer, $this->_topic);
                
                // Generate URL name
                if ($this->_request_data['event']->extra == '')
                {
                    $this->_request_data['event']->extra = midcom_generate_urlname_from_string($this->_request_data['event']->title);
                    $tries = 0;
                    $maxtries = 999;
                    while(   !$this->_request_data['event']->update()
                          && $tries < $maxtries)
                    {
                        $this->_request_data['event']->extra = midcom_generate_urlname_from_string($this->_request_data['event']->title);
                        if ($tries > 0)
                        {
                            // Append an integer if articles with same name exist
                            $this->_request_data['event']->extra .= sprintf("-%03d", $tries);
                        }
                        $tries++;
                    }
                }

                $_MIDCOM->relocate("{$this->_request_data['event']->extra}.html");
                // This will exit.

            case 'cancel':
                $_MIDCOM->relocate('');
                // This will exit.
        }

        $title = sprintf($this->_l10n_midcom->get('create %s'), $this->_l10n->get($this->_request_data['schemadb'][$this->_request_data['schemadb_schema']]->description));
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$title}");

        // Set the breadcrumb
        $breadcrumb[] = array
        (
            MIDCOM_NAV_URL => "create/event.html",
            MIDCOM_NAV_NAME => sprintf($this->_l10n_midcom->get('create %s'), $this->_l10n->get($this->_request_data['schemadb'][$this->_request_data['schemadb_schema']]->description)),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $breadcrumb);
        
        return true;
    }


    /**
     * Shows the loaded article.
     */
    function _show_create ($handler_id, &$data)
    {
        $this->_request_data['controller'] =& $this->_controller;
        midcom_show_style('admin_create');
    }
}

?>
