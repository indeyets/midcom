<?php
/**
 * @package midgard.admin.asgard
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: parameters.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Welcome interface
 *
 * @package midgard.admin.asgard
 */
class midgard_admin_asgard_handler_welcome extends midcom_baseclasses_components_handler
{
    /**
     * Reflectors
     *
     * @access private
     * @var Array
     */
    var $_reflectors = array();

    /**
     * Simple default constructor.
     */
    function midgard_admin_asgard_handler_welcome()
    {
        $this->_component = 'midgard.admin.asgard';
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Startup routines
     *
     * @access public
     */
    function _on_initialize()
    {
        // Ensure we get the correct styles
        $_MIDCOM->style->prepend_component_styledir('midgard.admin.asgard');
        $_MIDCOM->skip_page_style = true;

        $_MIDCOM->load_library('midcom.helper.datamanager2');
    }

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
    }

    function _list_revised($since)
    {
        $classes = array();
        $revised = array();
        $skip = $this->_config->get('skip_in_filter');

        // List installed MgdSchema types and convert to DBA classes
        foreach ($_MIDGARD['schema']['types'] as $schema_type => $dummy)
        {
            if (in_array($schema_type, $skip))
            {
                // Skip
                continue;
            }

            $mgdschema_class = midcom_helper_reflector::class_rewrite($schema_type);
            $dummy_object = new $mgdschema_class();
            $midcom_dba_classname = $_MIDCOM->dbclassloader->get_midcom_class_name_for_mgdschema_object($dummy_object);
            if (empty($midcom_dba_classname))
            {
                continue;
            }

            $classes[] = $midcom_dba_classname;
        }

        // List all revised objects
        foreach ($classes as $class)
        {
            if (!$_MIDCOM->dbclassloader->load_mgdschema_class_handler($class))
            {
                // Failed to load handling component, skip
                continue;
            }
            $qb_callback = array($class, 'new_query_builder');
            if (!is_callable($qb_callback))
            {
                continue;
            }
            $qb = call_user_func($qb_callback);
            $qb->add_constraint('metadata.revised', '>=', $since);
            $qb->add_order('metadata.revision', 'DESC');
            $objects = $qb->execute();

            if (count($objects) > 0)
            {
                if (!isset($this->_reflectors[$class]))
                {
                    $this->_reflectors[$class] = new midcom_helper_reflector($objects[0]);
                }
            }

            foreach ($objects as $object)
            {
                $revised["{$object->metadata->revised}_{$object->guid}_{$object->metadata->revision}"] = $object;
            }
        }

        krsort($revised);

        return $revised;
    }

    /**
     * Object editing view
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_welcome($handler_id, $args, &$data)
    {
        $this->_prepare_request_data();

        $data['view_title'] = $this->_l10n->get('asgard');
        $_MIDCOM->set_pagetitle($data['view_title']);

        $data['asgard_toolbar'] = new midcom_helper_toolbar();
        if (isset($_POST['execute_mass_action']))
        {
            if (   isset($_POST['selections'])
                && !empty($_POST['selections'])
                && isset($_POST['mass_action']))
            {
                $method_name = "_mass_{$_POST['mass_action']}";
                $this->$method_name($_POST['selections']);
            }
        }

        $data['revised'] = array();
        if (isset($_REQUEST['revised_after']))
        {
            $data['revised_after'] = date('Y-m-d H:i:s\Z', $_REQUEST['revised_after']);
            $data['revised'] = $this->_list_revised($data['revised_after']);
            $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/jQuery/jquery.tablesorter.js');
        	$_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/midgard.admin.asgard/jquery.batch_process.js');
            $_MIDCOM->add_link_head
            (
                array
                (
                    'rel' => 'stylesheet',
                    'type' => 'text/css',
                    'href' => MIDCOM_STATIC_URL . '/midgard.admin.asgard/tablewidget.css',
                )
            );
        }
        // else
        // {
        //     $data['revised_after'] = date('Y-m-d H:i:s\Z', mktime(0, 0, 0, date('m'), date('d') - 1, date('Y')));
        // }

        midgard_admin_asgard_plugin::get_common_toolbar($data);
        $data['asgard_toolbar']->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => '__mfa/asgard/preferences/',
                MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('user preferences', 'midgard.admin.asgard'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/configuration.png',
            )
        );

        $data['asgard_toolbar']->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => '__mfa/asgard/trash/',
                MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('trash', 'midgard.admin.asgard'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash-full.png',
            )
        );

        $data['asgard_toolbar']->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => '__mfa/asgard/components/',
                MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('components', 'midgard.admin.asgard'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/package.png',
            )
        );

        // Add link to site
        $data['asgard_toolbar']->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX),
                MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('back to site', 'midgard.admin.asgard'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/gohome.png',
            )
        );

        $data['asgard_toolbar']->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)."midcom-logout-",
                MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('logout','midcom'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/exit.png',
            )
        );

        return true;
    }

    function _mass_delete($guids)
    {
        foreach ($guids as $guid)
        {
            $object =& $_MIDCOM->dbfactory->get_object_by_guid($guid);
            if (   $object
                && $object->can_do('midgard:delete'))
            {
                //$label = $object->get_label();
                $label = $object->guid;
                if ($object->delete())
                {
                    $_MIDCOM->uimessages->add($this->_l10n->get('midgard.admin.asgard'), sprintf($this->_l10n->get('object %s removed'), $label));
                }
            }
        }
    }

    function _mass_approve($guids)
    {
        foreach ($guids as $guid)
        {
            $object =& $_MIDCOM->dbfactory->get_object_by_guid($guid);
            if (   $object
                && $object->can_do('midgard:update')
                && $object->can_do('midgard:approve'))
            {
                //$label = $object->get_label();
                $label = $object->guid;
                $metadata = $object->get_metadata();
                $metadata->approve();
                $_MIDCOM->uimessages->add($this->_l10n->get('midgard.admin.asgard'), sprintf($this->_l10n->get('object %s approved'), $label));
            }
        }
    }

    /**
     * Shows the loaded object in editor.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_welcome($handler_id, &$data)
    {
        $data['reflectors'] = $this->_reflectors;
        midcom_show_style('midgard_admin_asgard_header');
        midcom_show_style('midgard_admin_asgard_middle');
        midcom_show_style('midgard_admin_asgard_welcome');
        midcom_show_style('midgard_admin_asgard_footer');
    }
}
?>