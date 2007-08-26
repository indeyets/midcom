<?php
/**
 * Created on 2006-Oct-Thu
 * @package pl.olga.windguru
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 *
 */
class pl_olga_windguru_handler_view extends midcom_baseclasses_components_handler
{
    /**
     * Simple default constructor.
     */
    function pl_olga_windguru_handler_list()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Resolve username or person GUID to a midcom_db_person object
     *
     * @param string $username Username or GUID
     * @return midcom_db_person Matching person or null
     */
    function _resolve_user($username)
    {
        $qb = midcom_db_person::new_query_builder();
        $qb->add_constraint('username', '=', $username);
        $users = $qb->execute();
        if (count($users) > 0)
        {
            return $users[0];
        }

        if (mgd_is_guid($username))
        {
            // Try resolving as GUID as well
            $user = new midcom_db_person($username);
            return $user;
        }

        return null;
    }

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['article'] =& $this->_article;
        $this->_request_data['datamanager'] =& $this->_datamanager;

        // Populate the toolbar
        if ($this->_article->can_do('midgard:update'))
        {
            $this->_view_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "edit/{$this->_article->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                MIDCOM_TOOLBAR_ACCESSKEY => 'e',
            ));
        }
        if ($this->_article->can_do('midgard:delete'))
        {
            $this->_view_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "delete/{$this->_article->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                MIDCOM_TOOLBAR_ACCESSKEY => 'd',
            ));
        }
    }

    /**
     * The handler for displaying a messagegrapher's statusmessage
     * @param mixed $handler_id the array key from the requestarray
     * @param array $args the arguments given to the handler
     */
    function _handler_index($handler_id, $args, &$data)
    {

        // List spots
        $qb = midcom_db_article::new_query_builder();
		$qb->add_constraint('topic','=',$this->_topic->id);
		$qb->add_order('metadata.score');
		$this->_list = $qb->execute();

        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: ".$this->_l10n->get('winguru forecasts'));
        $this->_update_breadcrumb_line($handler_id);

        return true;
    }

    function _show_index($handler_id, &$data)
    {
		if (count($this->_list))
		{
		}
		else
		{
			midcom_show_style("spot-empty");
		}

    }

    function _load_datamanager()
    {
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_request_data['schemadb']);

        if (   ! $this->_datamanager
            || ! $this->_datamanager->autoset_storage($this->_article))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for article {$this->_article->id}.");
            // This will exit.
        }
    }


    function _can_handle_view ($handler_id, $args, &$data)
    {

        $qb = midcom_baseclasses_database_article::new_query_builder();
        $qb->add_constraint('topic', '=', $this->_topic->id);
        $qb->add_constraint('name', '=', $args[0]);
        $qb->add_constraint('up', '=', 0);
        $result = $qb->execute();

        if ($result)
        {
            $this->_article = $result[0];
            return true;
        }

        return false;
    }

    function _handler_view ($handler_id, $args, &$data)
    {

        $this->_load_datamanager();

        $this->_request_data['controller'] =& midcom_helper_datamanager2_controller::create('ajax');
        $this->_request_data['controller']->schemadb =& $this->_request_data['schemadb'];
        $this->_request_data['controller']->set_storage($this->_article);
        $this->_request_data['controller']->process_ajax();
        

        $this->_prepare_request_data();
        $_MIDCOM->set_26_request_metadata($this->_article->metadata->revised, $this->_article->guid);
        $_MIDCOM->bind_view_to_object($this->_article, $this->_datamanager->schema->name);

        if (   $this->_config->get('indexinnav')
            || $this->_config->get('autoindex')
            || $this->_article->name != 'index')
        {
            $this->_component_data['active_leaf'] = $this->_article->id;
        }

        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_article->title}");

        return true;
    }

    function _show_view ($handler_id, &$data)
    {
		$this->_request_data['view_article'] = $this->_request_data['controller']->get_content_html();

        midcom_show_style('show-spot');
    }



    /**
     * Helper, updates the context so that we get a complete breadcrum line towards the current
     * location.
     *
     */
    function _update_breadcrumb_line($handler_id)
    {
        $tmp = Array();

        switch ($handler_id)
        {
            case 'index':
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "",
                    MIDCOM_NAV_NAME => $this->_l10n->get('winguru forecasts'),
                );
                break;
        }

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }
}
?>