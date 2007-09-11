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
        
        if (array_key_exists('root_event', $data))
        {
            // We have this already
            $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX));
        }
        
        $data['root_event'] = new net_nemein_calendar_event();
        
        if (array_key_exists('master_event', $data))
        {
            $data['root_event']->up = $data['master_event'];
        }
        else
        {
            $data['root_event']->up = 0;
        }
        
        $data['root_event']->title = sprintf('__%s root event', $this->_topic->guid);
        
        if ($data['root_event']->create())
        {
            $this->_topic->parameter('net.nemein.calendar', 'root_event', $data['root_event']->guid);
            
            $_MIDCOM->uimessages->add('net.nemein.calendar', "Root event {$data['root_event']->guid} created", 'ok');
            
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
        $this->_controller->schemaname = $this->_request_data['schema'];
        $this->_controller->defaults = $this->_request_data['defaults'];
        $this->_controller->callback_object =& $this;
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 create controller.");
            // This will exit.
        }
    }

    /**
     * Displays an event creation view.
     *
     * The form can be manipulated using query strings like the following:
     *
     * ?defaults[title]=Kaljakellunta&defaults[start]=20070911T123001&defaults[categories]=|foo|
     */
    function _handler_create($handler_id, $args, &$data)
    {
        $data['root_event']->require_do('midgard:create');

        $data['schema'] = $args[0];
        if (!array_key_exists($data['schema'], $data['schemadb']))
        {
            return false;
        }

        $data['defaults'] = Array();
        
        // Allow setting defaults from query string, useful for things like "create event for today" and chooser        
        if (isset($_GET['defaults'])
            && is_array($_GET['defaults']))
        {
            foreach ($_GET['defaults'] as $key => $value)
            {
                if (!isset($data['schemadb'][$data['schema']]->fields[$key]))
                {
                    // No such field in schema
                    continue;
                }
                
                $data['defaults'][$key] = $value;
            }
        }
        
        $this->_load_controller();

        switch ($this->_controller->process_form())
        {
            case 'save':
                // Index the article
                $indexer =& $_MIDCOM->get_service('indexer');
                net_nemein_calendar_viewer::index($this->_controller->datamanager, $indexer, $this->_topic);
                
                // Generate URL name
                if ($data['event']->extra == '')
                {
                    $data['event']->extra = midcom_generate_urlname_from_string($data['event']->title);
                    $tries = 0;
                    $maxtries = 999;
                    while(   !$data['event']->update()
                          && $tries < $maxtries)
                    {
                        $data['event']->extra = midcom_generate_urlname_from_string($data['event']->title);
                        if ($tries > 0)
                        {
                            // Append an integer if articles with same name exist
                            $data['event']->extra .= sprintf("-%03d", $tries);
                        }
                        $tries++;
                    }
                }

                $_MIDCOM->relocate("{$data['event']->extra}/");
                // This will exit.

            case 'cancel':
                $_MIDCOM->relocate('');
                // This will exit.
        }

        $title = sprintf($this->_l10n_midcom->get('create %s'), $this->_l10n->get($this->_request_data['schemadb'][$this->_request_data['schema']]->description));
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$title}");
        
        if ($handler_id == 'create_chooser')
        {
            $_MIDCOM->skip_page_style = true;
        }

        // Set the breadcrumb
        $breadcrumb[] = array
        (
            MIDCOM_NAV_URL => "create/{$data['schema']}/",
            MIDCOM_NAV_NAME => sprintf($this->_l10n_midcom->get('create %s'), $this->_l10n->get($data['schemadb'][$data['schema']]->description)),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $breadcrumb);
        
        return true;
    }


    /**
     * Shows the loaded article.
     */
    function _show_create ($handler_id, &$data)
    {
        if ($handler_id == 'create_chooser')
        {
            midcom_show_style('popup_header');
        }    
        
        $data['controller'] =& $this->_controller;
        midcom_show_style('admin_create');
        
        if ($handler_id == 'create_chooser')
        {
            midcom_show_style('popup_footer');
        }    

    }
}

?>
