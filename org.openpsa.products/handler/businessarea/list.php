<?php
/**
 * Created on 2006-08-09
 * @author Henri Bergius
 * @package org.openpsa.products
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 *
 */

class org_openpsa_products_handler_businessarea_list  extends midcom_baseclasses_components_handler
{
    /*
     * The midcom_baseclasses_components_handler class defines a bunch of helper vars
     * See: http://www.midgard-project.org/api-docs/midcom/dev/midcom.baseclasses/midcom_baseclasses_components_handler.html
     */

    /**
     * Simple default constructor.
     */
    function org_openpsa_products_handler_businessarea_list()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Can-Handle check against the current businessarea GUID. We have to do this explicitly
     * in can_handle already, otherwise we would hide all subtopics as the request switch
     * accepts all argument count matches unconditionally.
     */
    function _can_handle_list($handler_id, $args, &$data)
    {
        if ($handler_id == 'index_businessarea')
        {
            // We're in root-level product index
            $data['businessarea'] = null;
            $data['parent_businessarea'] = $this->_config->get('root_businessarea');
            $data['view_title'] = $this->_l10n->get('business areas');
            $data['acl_object'] = $this->_topic;
        }
        else
        {
            // We're in some level of businessareas
            $qb = org_openpsa_products_businessarea_dba::new_query_builder();
            $qb->add_constraint('code', '=', $args[0]);
            $results = $qb->execute();
            if (count($results) == 0)
            {
                if (!mgd_is_guid($args[0]))
                {
                    return false;
                }

                $data['businessarea'] = new org_openpsa_products_businessarea_dba($args[0]);
                if (   !$data['businessarea']
                    || !$data['businessarea']->guid)
                {
                    return false;
                }
            }
            else
            {
                $data['businessarea'] = $results[0];
            }

            $data['parent_businessarea'] = $data['businessarea']->id;
            $data['view_title'] = "{$data['businessarea']->code} {$data['businessarea']->title}";
            $data['acl_object'] = $data['businessarea'];

            $_MIDCOM->bind_view_to_object($data['businessarea']);
        }

        return true;
    }

    /**
     * The handler for the businessarea_list article.
     *
     * @param mixed $handler_id the array key from the request array
     * @param array $args the arguments given to the handler
     * @param Array $data The local request data.
     * @return bool Indicating success.
     */
    function _handler_list($handler_id, $args, &$data)
    {
        // Query for sub-objects
        $businessarea_qb = org_openpsa_products_businessarea_dba::new_query_builder();
        $businessarea_qb->add_constraint('up', '=', $data['parent_businessarea']);
        $businessarea_qb->add_order('code');
        $businessarea_qb->add_order('title');
        $data['businessareas'] = $businessarea_qb->execute();

        $data['groups'] = array();
        $group_qb = org_openpsa_products_businessarea_member_dba::new_query_builder();
        $group_qb->add_constraint('businessarea', '=', $data['parent_businessarea']);

        if (version_compare(mgd_version(), '1.8.0alpha1', '>'))
        {
            $group_qb->add_order('grp.code');
            $group_qb->add_order('grp.title');
        }

        $groups = $group_qb->execute();
        foreach ($groups as $member)
        {
            $group = new org_openpsa_products_product_group_dba($member->grp);
            if ($group)
            {
                $data['groups'][] = $group;
            }
        }

        // Prepare datamanager
        $data['datamanager_businessarea'] = new midcom_helper_datamanager2_datamanager($data['schemadb_businessarea']);
        $data['datamanager_group'] = new midcom_helper_datamanager2_datamanager($data['schemadb_group']);

        // Populate toolbar
        if ($data['acl_object']->can_do('midgard:create'))
        {
            foreach (array_keys($this->_request_data['schemadb_businessarea']) as $name)
            {
                $this->_view_toolbar->add_item
                (
                    array
                    (
                        MIDCOM_TOOLBAR_URL => "businessarea/create/{$data['parent_businessarea']}/{$name}.html",
                        MIDCOM_TOOLBAR_LABEL => sprintf
                        (
                            $this->_l10n_midcom->get('create %s'),
                            $this->_l10n->get($this->_request_data['schemadb_businessarea'][$name]->description)
                        ),
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-dir.png',
                    )
                );
            }
        }

        if ($this->_request_data['businessarea'])
        {
            $this->_request_data['controller'] =& midcom_helper_datamanager2_controller::create('ajax');
            $this->_request_data['controller']->schemadb =& $this->_request_data['schemadb_businessarea'];
            $this->_request_data['controller']->set_storage($this->_request_data['businessarea']);
            $this->_request_data['controller']->process_ajax();
        }

        /***
         * Set the breadcrumb text
         */
        $this->_update_breadcrumb_line();

        /**
         * change the pagetitle. (must be supported in the style)
         */
        $_MIDCOM->set_pagetitle($this->_request_data['view_title']);
        return true;
    }

    /**
     * This function does the output.
     *
     */
    function _show_list($handler_id, &$data)
    {
        if ($this->_request_data['businessarea'])
        {
            $this->_request_data['view_businessarea'] = $this->_request_data['controller']->get_content_html();
        }

        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

        midcom_show_style('businessarea_header');

        if (count($data['businessareas']) > 0)
        {
            midcom_show_style('businessarea_areas_header');

            foreach ($data['businessareas'] as $businessarea)
            {
                $data['businessarea'] = $businessarea;
                if (! $data['datamanager_businessarea']->autoset_storage($businessarea))
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("The datamanager for businessarea #{$businessarea->id} could not be initialized, skipping it.");
                    debug_print_r('Object was:', $businessarea);
                    debug_pop();
                    continue;
                }
                $data['view_businessarea'] = $data['datamanager_businessarea']->get_content_html();

                if ($businessarea->code)
                {
                    $data['view_businessarea_url'] = "{$prefix}businessarea/{$businessarea->code}/";
                }
                else
                {
                    $data['view_businessarea_url'] = "{$prefix}businessarea/{$businessarea->guid}/";
                }

                midcom_show_style('businessarea_areas_item');
            }

            midcom_show_style('businessarea_areas_footer');
        }

        if (count($data['groups']) > 0)
        {
            midcom_show_style('businessarea_groups_header');

            foreach ($data['groups'] as $group)
            {
                $data['group'] = $group;
                if (! $data['datamanager_group']->autoset_storage($group))
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("The datamanager for group #{$group->id} could not be initialized, skipping it.");
                    debug_print_r('Object was:', $group);
                    debug_pop();
                    continue;
                }
                $data['view_group'] = $data['datamanager_group']->get_content_html();

                if ($group->code)
                {
                    $data['view_group_url'] = "{$prefix}{$group->code}/";
                }
                else
                {
                    $data['view_group_url'] = "{$prefix}{$group->guid}/";
                }

                midcom_show_style('businessarea_groups_item');
            }

            midcom_show_style('businessarea_groups_footer');
        }

        midcom_show_style('businessarea_footer');
    }

    /**
     * Helper, updates the context so that we get a complete breadcrumb line towards the current
     * location.
     *
     */
    function _update_breadcrumb_line()
    {
        $tmp = Array();

        $businessarea = $this->_request_data['businessarea'];
        $root_businessarea = $this->_config->get('root_businessarea');

        if (!$businessarea)
        {
            $tmp[] = Array
            (
                MIDCOM_NAV_URL => "businessarea/",
                MIDCOM_NAV_NAME => $this->_l10n->get('business areas'),
            );

            $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

            return false;
        }

        $parent = $businessarea;
        while ($parent)
        {
            $businessarea = $parent;

            if ($businessarea->guid === $root_businessarea)
            {
                break;
            }

            if ($businessarea->code)
            {
                $url = "businessarea/{$businessarea->code}/";
            }
            else
            {
                $url = "businessarea/{$businessarea->guid}/";
            }

            $tmp[] = Array
            (
                MIDCOM_NAV_URL => $url,
                MIDCOM_NAV_NAME => $businessarea->title,
            );
            $parent = $businessarea->get_parent();
        }

        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "businessarea/",
            MIDCOM_NAV_NAME => $this->_l10n->get('business areas'),
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', array_reverse($tmp));
    }
}
?>