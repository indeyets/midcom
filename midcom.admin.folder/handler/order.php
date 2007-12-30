<?php
/**
 * @package midcom.admin.folder
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: configuration.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Sort navigation order.
 *
 * This handler enables drag'n'drop sorting of navigation
 *
 * @package midcom.admin.folder
 */
class midcom_admin_folder_handler_order extends midcom_baseclasses_components_handler
{
    /**
     * Constructor metdot
     *
     * @access public
     */
    public function midcom_admin_folder_handler_order ()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * This function will set the score.
     *
     * @access private
     * @return boolean Indicating success
     */
    function _process_order_form()
    {
        if (isset($_POST['f_navorder']))
        {
            $this->_topic->set_parameter('midcom.helper.nav', 'nav_order', $_POST['f_navorder']);
        }

        // Form has been handled if cancel has been pressed
        if (isset($_POST['f_cancel']))
        {
            $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('midcom.admin.folder'), $_MIDCOM->i18n->get_string('cancelled'));
            $_MIDCOM->relocate($_MIDCOM->permalinks->create_permalink($this->_topic->guid));
            exit;
            // This will exit
        }

        // If the actual score list hasn't been posted, return false
        if (!isset($_POST['f_submit']))
        {
            return false;
        }

        // Success tells whether the update was successful or not. On default everything goes fine,
        // but when any errors are encountered, there will be a uimessage that will be shown.
        $success = true;

        // Loop through the sortables and store the new score
        foreach ($_POST['sortable'] as $key => $array)
        {
            // Total number of the entries
            $count = count($array);

            foreach ($array as $guid => $i)
            {
                // Set the score reversed: the higher the value, the higher the rank
                $score = $count - $i;

                // Use the DB Factory to resolve the class and to get the object
                $object = $_MIDCOM->dbfactory->get_object_by_guid($guid);

                // Skip the pages that cannot be ordered
                if (   !$object
                    || !isset($object->guid)
                    || $object->guid !== $guid)
                {
                    continue;
                }

                // Get the original approval status
                $metadata =& midcom_helper_metadata::retrieve($guid);
                $approval_status = false;

                // Get the approval status if metadata object is available
                if (   is_object($metadata)
                    && $metadata->is_approved())
                {
                    $approval_status = true;
                }

                // Store the old-fashioned score as well
                if (isset($object->score))
                {
                    $object->score = $score;
                }

                $object->metadata->score = $score;

                // Show an error message on an update failure
                if (!$object->update())
                {
                    // Some heuristics for the update logging
                    if (   isset($object->title)
                        && $object->title)
                    {
                        $title = $object->title;
                    }
                    elseif (isset($object->extra)
                        && $object->extra)
                    {
                        $title = $object->extra;
                    }
                    elseif (isset($object->name)
                        && $object->name)
                    {
                        $title = $object->name;
                    }
                    else
                    {
                        $title = sprintf("{$object->guid} %s", get_class($object));
                    }

                    $_MIDCOM->uimessages->add($this->_l10n->get('midcom.admin.folder'), sprintf($this->_l10n->get('failed to update %s due to: %s'), $title, mgd_errstr()), 'error');
                    $success = false;
                    continue;
                }

                // Approve if possible
                if (   $approval_status
                    && $object->can_do('midgard:approve'))
                {
                    $metadata =& midcom_helper_metadata::retrieve($guid);
                    $metadata->approve();
                }
            }
        }

        if ($success)
        {
            $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('midcom.admin.folder'), $_MIDCOM->i18n->get_string('order saved'));
            $_MIDCOM->relocate($_MIDCOM->permalinks->create_permalink($this->_topic->guid));
            exit;
            // This will exit
        }
    }

    /**
     * Handler for setting the sort order
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return bool Indicating success.
     */
    function _handler_order($handler_id, $args, &$data)
    {
        // Include Scriptaculous JavaScript libraries to headers
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL.'/jQuery/jquery.dimensions-1.1.2.js');
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL.'/jQuery/jquery.form-1.0.3.js');
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL.'/jQuery/ui/ui.mouse.js');
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL.'/jQuery/ui/ui.draggable.js');
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL.'/jQuery/ui/ui.droppable.js');
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL.'/jQuery/ui/ui.sortable.js');
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL.'/midcom.admin.folder/jquery-postfix.js');

        // These pages need no caching
        $_MIDCOM->cache->content->no_cache();

        // Custom styles
        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL.'/midcom.admin.folder/midcom-admin-order.css',
            )
        );

        $this->_topic->require_do('midgard:update');

        // Process the form
        $this->_process_order_form();

        // Add the view to breadcrumb trail
        $tmp = array();

        $tmp[] = array
        (
            MIDCOM_NAV_URL => '__ais/folder/order.html',
            MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('order navigation', 'midcom.admin.folder'),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        // Hide the button in toolbar
        $this->_node_toolbar->hide_item('__ais/folder/order.html');

        // Set page title
        $data['folder'] = $this->_topic;
        $data['title'] = sprintf($_MIDCOM->i18n->get_string('order navigation in folder %s', 'midcom.admin.folder'), $data['folder']->extra);
        $_MIDCOM->set_pagetitle($data['title']);

        // Set the help object in the toolbar
        $this->_view_toolbar->add_help_item('navigation_order', 'midcom.admin.folder');

        // Ensure we get the correct styles
        $_MIDCOM->style->prepend_component_styledir('midcom.admin.folder');

        // Skip the page style on AJAX form handling
        if (isset($_GET['ajax']))
        {
            $_MIDCOM->skip_page_style = true;
        }

        return true;
    }

    /**
     * Show the sorting
     *
     * @access private
     */
    function _show_order($handler_id, &$data)
    {
        $data['navigation'] = array();
        $data['navorder'] = $this->_topic->get_parameter('midcom.helper.nav', 'nav_order');

        // Navorder list for the selection
        $data['navorder_list'] = array
        (
            MIDCOM_NAVORDER_DEFAULT => $_MIDCOM->i18n->get_string('default sort order', 'midcom.admin.folder'),
            MIDCOM_NAVORDER_TOPICSFIRST => $_MIDCOM->i18n->get_string('folders first', 'midcom.admin.folder'),
            MIDCOM_NAVORDER_ARTICLESFIRST => $_MIDCOM->i18n->get_string('pages first', 'midcom.admin.folder'),
            MIDCOM_NAVORDER_SCORE => $_MIDCOM->i18n->get_string('by score', 'midcom.admin.folder'),
        );

        if (!isset($_GET['ajax']))
        {
            midcom_show_style('midcom-admin-folder-order-start');
        }

        // Initialize the midcom_helper_nav or navigation access point
        $nap = new midcom_helper_nav();

        switch ((int) $this->_topic->get_parameter('midcom.helper.nav', 'nav_order'))
        {
            case MIDCOM_NAVORDER_DEFAULT:
                $data['navigation']['nodes'] = array();
                $nodes = $nap->list_nodes($this->_topic->id);

                foreach ($nodes as $id => $node_id)
                {
                    $node = $nap->get_node($node_id);
                    $node[MIDCOM_NAV_TYPE] = 'node';
                    $data['navigation']['nodes'][$id] = $node;
                }
                break;

            case MIDCOM_NAVORDER_TOPICSFIRST:
                // Sort the array to have the nodes first
                $data['navigation'] = array
                (
                    'nodes' => array(),
                    'leaves' => array(),
                );
                // Fall through

            case MIDCOM_NAVORDER_ARTICLESFIRST:
                // Sort the array to have the leaves first

                if (!isset($data['navigation']['leaves']))
                {
                    $data['navigation'] = array
                    (
                        'leaves' => array(),
                        'nodes' => array(),
                    );
                }

                // Get the nodes
                $nodes = $nap->list_nodes($this->_topic->id);

                foreach ($nodes as $id => $node_id)
                {
                    $node = $nap->get_node($node_id);
                    $node[MIDCOM_NAV_TYPE] = 'node';
                    $data['navigation']['nodes'][$id] = $node;
                }

                // Get the leafs
                $leaves = $nap->list_leaves($this->_topic->id);

                foreach ($leaves as $id => $leaf_id)
                {
                    $leaf = $nap->get_leaf($leaf_id);
                    $leaf[MIDCOM_NAV_TYPE] = 'leaf';
                    $data['navigation']['leaves'][$id] = $leaf;
                }
                break;

            case MIDCOM_NAVORDER_SCORE:
            default:
                $data['navigation']['mixed'] = array();

                // Get the navigation items
                $items = $nap->list_child_elements($this->_topic->id);

                foreach ($items as $id => $item)
                {
                    if ($item[MIDCOM_NAV_TYPE] === 'node')
                    {
                        $element = $nap->get_node($item[MIDCOM_NAV_ID]);
                    }
                    else
                    {
                        $element = $nap->get_leaf($item[MIDCOM_NAV_ID]);
                    }

                    // Store the type information
                    $element[MIDCOM_NAV_TYPE] = $item[MIDCOM_NAV_TYPE];

                    $data['navigation']['mixed'][] = $element;
                }
                break;
        }

        // Loop through each navigation type (node, leaf and mixed)
        foreach ($data['navigation'] as $key => $array)
        {
            $data['navigation_type'] = $key;
            $data['navigation_items'] = $array;
            midcom_show_style('midcom-admin-folder-order-type');
        }

        if (!isset($_GET['ajax']))
        {
            midcom_show_style('midcom-admin-folder-order-end');
        }
    }

    /**
     * Fill a given integer with zeros for alphabetic ordering
     *
     * @access private
     * @param int $int    Integer
     * @return string     String filled with leading zeros
     */
    private function _get_score($int)
    {
        $string = (string) $int;

        while (strlen($string) < 5)
        {
            $string = "0{$string}";
        }

        return $string;
    }
}
?>