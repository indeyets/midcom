<?php
/**
 * @package net.nemein.quickpoll
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a URL handler class for net.nemein.quickpoll
 *
 * The midcom_baseclasses_components_handler class defines a bunch of helper vars
 *
 * @see midcom_baseclasses_components_handler
 * @package net.nemein.quickpoll
 */
class net_nemein_quickpoll_handler_index  extends midcom_baseclasses_components_handler
{
    /**
     * The content topic to use
     *
     * @var midcom_db_topic
     * @access private
     */
    var $_content_topic = null;

    /**
     * The article to display
     *
     * @var midcom_db_article
     * @access private
     */
    var $_article = null;

    /**
     * The Datamanager of the article to display.
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
        $this->_request_data['article'] =& $this->_article;
        $this->_request_data['vote_count'] =& $this->_vote_count;
        $this->_request_data['manage'] =& $this->_manage;
        $this->_request_data['datamanager'] =& $this->_datamanager;

        // Populate the toolbar
        if ($this->_article->can_do('midgard:update'))
        {
            $this->_view_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "edit/{$this->_article->guid}/",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                MIDCOM_TOOLBAR_ACCESSKEY => 'e',
            ));
            if ($this->_manage)
            {
                $this->_view_toolbar->add_item(Array(
                    MIDCOM_TOOLBAR_URL => "{$this->_article->guid}/",
                    MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('close manage'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                    MIDCOM_TOOLBAR_ACCESSKEY => 'm',
                ));
            }
            else
            {
                $this->_view_toolbar->add_item(Array(
                    MIDCOM_TOOLBAR_URL => "manage/{$this->_article->guid}/",
                    MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('manage'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                    MIDCOM_TOOLBAR_ACCESSKEY => 'm',
                ));
            }
        }

        if ($this->_article->can_do('midgard:delete'))
        {
            $this->_view_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "delete/{$this->_article->guid}/",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                MIDCOM_TOOLBAR_ACCESSKEY => 'd',
            ));
        }
    }


    /**
     * Simple default constructor.
     */
    function net_nemein_quickpoll_handler_index()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * _on_initialize is called by midcom on creation of the handler.
     */
    function _on_initialize()
    {
         $this->_content_topic =& $this->_request_data['content_topic'];
    }

    /**
     * The handler for the index article.
     *
     * @param mixed $handler_id the array key from the request array
     * @param array $args the arguments given to the handler
     * @param Array &$data The local request data.
     * @return bool Indicating success.
     */
    function _handler_index ($handler_id, $args, &$data)
    {
        if ($handler_id == 'index-with-type')
        {
            $_MIDCOM->skip_page_style = true;
            $data['return_type'] = strtoupper($args[0]);
        }
        
        $options_data = array();
        $total_count = 0;

        $this->_manage = false;

        $qb = new org_openpsa_qbpager('midcom_db_article', 'net_nemein_quickpoll');
        $qb->add_constraint('topic', '=', $this->_content_topic->id);
        $qb->add_constraint('up', '=', 0);
        $qb->add_order('metadata.created', 'DESC');
        
        $qb->begin_group('OR');
            $qb->add_constraint('metadata.scheduleend', '=', '0000-00-00 00:00:00');
            $qb->add_constraint('metadata.scheduleend', '>', gmdate('Y-m-d h:i:s'));
        $qb->end_group();
        
        $qb->results_per_page = 1;
        $data['qb'] =& $qb;
        $index_poll = $qb->execute();
        if(array_key_exists(0, $index_poll))
        {
            $this->_article = $index_poll[0];
        }
        else
        {
            $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
            $_MIDCOM->relocate($prefix.'create/'.$this->_config->get('schema'));
        }

        $this->_load_datamanager();

        $poll_data = array(
            'id' => $this->_article->id,
            'guid' => $this->_article->guid,
            'title' => $this->_article->title,
            'abstract' => mgd_format($this->_article->abstract, 'h'),
        );

        $this->_request_data['name']  = "net.nemein.quickpoll";

        $qb_vote_count_total = net_nemein_quickpoll_vote_dba::new_query_builder();
        $qb_vote_count_total->add_constraint('article', '=', $this->_article->id);
        $this->_vote_count = $qb_vote_count_total->count();

        $qb_vote = net_nemein_quickpoll_vote_dba::new_query_builder();
        $qb_vote->add_constraint('article', '=', $this->_article->id);

        if ($this->_config->get('lock_ip_address'))
        {
            $qb_vote->add_constraint('ip', '=', $_SERVER['REMOTE_ADDR']);
        }
        else if ($_MIDGARD['user'])
        {
            $qb_vote->add_constraint('user', '=', $_MIDGARD['user']);
        }

        $vote_count = $qb_vote->count();
        
        if ($vote_count > 0)
        {
            $this->_request_data['voted'] =  true;
        }
        else
        {
            $this->_request_data['voted'] =  false;
        }

        if (   $this->_config->get('enable_anonymous')
            && !$this->_config->get('lock_ip_address')
            && !$_MIDGARD['user'])
        {
            $this->_request_data['voted'] =  false;
        }

        if (   !$this->_config->get('enable_anonymous')
            && (   !$_MIDCOM->auth->user
                && !$_MIDCOM->auth->admin))
        {
            $this->_request_data['voted'] =  true;
        }

        $qb_options = net_nemein_quickpoll_option_dba::new_query_builder();
        $qb_options->add_constraint('article', '=', $this->_article->id);
        $options = $qb_options->execute();

        foreach ($options as $option)
        {
            if (! isset($options_data[$option->id]))
            {
                $options_data[$option->id] = array(
                    'title' => $option->title,
                    'votes' => 0,
                );
            }
        }

        $qb_votes = net_nemein_quickpoll_vote_dba::new_query_builder();
        $qb_votes->add_constraint('article', '=', $this->_article->id);
        $votes = $qb_votes->execute();
        
        foreach ($votes as $vote)
        {
            if (! isset($options_data[$vote->selectedoption]))
            {
                continue;
            }
            
            if (! isset($options_data[$vote->selectedoption]['votes']))
            {
                $options_data[$vote->selectedoption]['votes'] = 1;
            }
            else
            {
                $options_data[$vote->selectedoption]['votes'] += 1;                
            }
            
            $total_count++;
        }
        
        $poll_results = array(
            'poll' => $poll_data,
            'options' => $options_data,
            'total' => $total_count,
        );
        
        $data['poll_results'] =& $poll_results;

        $this->_prepare_request_data();

        return true;
    }

    /**
     * Internal helper, loads the datamanager for the current article. Any error triggers a 500.
     *
     * @access private
     */
    function _load_datamanager()
    {
        if ($this->_manage)
        {
            $this->_request_data['controller'] =& midcom_helper_datamanager2_controller::create('ajax');
            $this->_request_data['controller']->schemadb =& $this->_request_data['schemadb'];
            $this->_request_data['controller']->set_storage($this->_article);
            $this->_request_data['controller']->process_ajax();
            $this->_datamanager =& $this->_request_data['controller'];
        }
        else
        {
            $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_request_data['schemadb']);

            if (   ! $this->_datamanager
                || ! $this->_datamanager->autoset_storage($this->_article))
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for article {$this->_article->id}.");
                // This will exit.
            }
        }
    }

    /**
     * This function does the output.
     *
     */
    function _show_index($handler_id, &$data)
    {
        if ($handler_id == 'index-with-type')
        {
            if ($data['return_type'] == 'XML')
            {
                $encoding = 'UTF-8';

                $_MIDCOM->cache->content->content_type('text/xml');
                $_MIDCOM->header('Content-type: text/xml; charset=' . $encoding);
                
                $voted = $data['voted'] ? 1 : 0;
                
                echo '<?xml version="1.0" encoding="' . $encoding . '" standalone="yes"?>' . "\n";
                echo "<quickpoll>\n";
                echo "<questions>\n";
                echo "  <question id='{$data['poll_results']['poll']['id']}' guid='{$data['poll_results']['poll']['guid']}' voted='{$voted}'>\n";
                echo "    <title><![CDATA[{$data['poll_results']['poll']['title']}]]></title>\n";
                echo "    <abstract><![CDATA[{$data['poll_results']['poll']['abstract']}]]></abstract>\n";
                echo "    <total_votes><![CDATA[{$data['poll_results']['total']}]]></total_votes>\n";
                echo "    <options>\n";

                foreach ($data['poll_results']['options'] as $id => $option)
                {
                    echo "       <option id='{$id}' votes='{$option['votes']}'><![CDATA[{$option['title']}]]></option>\n";
                }

                echo "    </options>\n";
                echo "  </question>\n";
                echo "</questions>\n";
                
                $data['qb']->show_pages_as_xml();
                
                echo "</quickpoll>\n";
                
                //$data['qb']
            }
        }
        else
        {
            $this->_request_data['view_article'] = $this->_datamanager->get_content_html();
            midcom_show_style('index');            
        }
    }

    /**
     * Can-Handle check against the article name. We have to do this explicitly
     * in can_handle already, otherwise we would hide all subtopics as the request switch
     * accepts all argument count matches unconditionally.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return bool True if the request can be handled, false otherwise.
     */
    function _can_handle_view ($handler_id, $args, &$data)
    {
        $qb = midcom_db_article::new_query_builder();
        $qb->add_constraint('topic', '=', $this->_content_topic->id);
        $qb->add_constraint('up', '=', 0);
        $qb->begin_group('OR');
            $qb->add_constraint('name', '=', $args[0]);
            $qb->add_constraint('guid', '=', $args[0]);
        $qb->end_group();
        $articles = $qb->execute();
        if (count($articles) > 0)
        {
            $this->_article = $articles[0];
        }

        if (!$this->_article)
        {
            return false;
            // This will 404
        }

        return true;
    }

    /**
     * The handler for the index article.
     *
     * @param mixed $handler_id the array key from the request array
     * @param array $args the arguments given to the handler
     * @param Array &$data The local request data.
     * @return bool Indicating success.
     */
    function _handler_view ($handler_id, $args, &$data)
    {
        $this->_manage = false;

        if ($handler_id == 'manage')
        {
            $this->_manage = true;

            // Enable creation of new options in the management mode
            foreach ($this->_request_data['schemadb'] as $schemaname => $schema)
            {
                $this->_request_data['schemadb'][$schemaname]->fields['options']['type_config']['enable_creation'] = true;
            }
        }

        $this->_load_datamanager();

        $this->_request_data['name']  = "net.nemein.quickpoll";

        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "{$this->_article->guid}/",
            MIDCOM_NAV_NAME => $this->_article->title,
        );
        if ($this->_manage)
        {
            $tmp[] = Array
            (
                MIDCOM_NAV_URL => "manage/{$this->_article->guid}/",
                MIDCOM_NAV_NAME => $this->_l10n->get('manage'),
            );
        }

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
        $_MIDCOM->set_pagetitle($this->_article->title);

        $qb_vote_count_total = net_nemein_quickpoll_vote_dba::new_query_builder();
        $qb_vote_count_total->add_constraint('article', '=', $this->_article->id);
        $this->_vote_count = $qb_vote_count_total->count();

        $this->_prepare_request_data();

        $qb_vote = net_nemein_quickpoll_vote_dba::new_query_builder();
        $qb_vote->add_constraint('article', '=', $this->_article->id);
        $qb_vote->begin_group('OR');
            $qb_vote->add_constraint('user', '=', $_MIDGARD['user']);
            $qb_vote->add_constraint('ip', '=', $_SERVER['REMOTE_ADDR']);
        $qb_vote->end_group();
        $vote_count = $qb_vote->count();

        if ($vote_count > 0)
        {
            $this->_request_data['voted'] =  true;
        }
        else
        {
            $this->_request_data['voted'] =  false;
        }

        if (   !$this->_config->get('enable_anonymous')
            && (   !$_MIDCOM->auth->user
                && !$_MIDCOM->auth->admin))
        {
            $this->_request_data['voted'] =  true;
        }

        return true;
    }

    /**
     * Can-Handle check against the article name. We have to do this explicitly
     * in can_handle already, otherwise we would hide all subtopics as the request switch
     * accepts all argument count matches unconditionally.
     */
    function _can_handle_polldata($handler_id, $args, &$data)
    {
        $qb = midcom_db_article::new_query_builder();
        $qb->add_constraint('topic', '=', $this->_content_topic->id);
        $qb->add_constraint('up', '=', 0);
        $qb->begin_group('OR');
            $qb->add_constraint('name', '=', $args[1]);
            $qb->add_constraint('guid', '=', $args[1]);
        $qb->end_group();
        $articles = $qb->execute();
        if (count($articles) > 0)
        {
            $this->_article = $articles[0];
        }

        if (!$this->_article)
        {
            return false;
            // This will 404
        }

        return true;
    }

    /**
     * The handler to return polls data. 
     * @param mixed $handler_id the array key from the requestarray
     * @param array $args the arguments given to the handler
     * 
     */
    function _handler_polldata($handler_id, $args, &$data)
    {
        $_MIDCOM->skip_page_style = true;
        
        $data['return_type'] = 'XML';
        if (isset($args[0]))
        {
            $data['return_type'] = strtoupper($args[0]);
        }
        
        $this->_manage = false;
        $this->_load_datamanager();
        
        $options_data = array();
        $total_count = 0;
        
        $poll_data = array(
            'id' => $this->_article->id,
            'guid' => $this->_article->guid,
            'title' => $this->_article->title,
            'abstract' => mgd_format($this->_article->abstract, 'h'),
        );
                
        $qb_options = net_nemein_quickpoll_option_dba::new_query_builder();
        $qb_options->add_constraint('article', '=', $this->_article->id);
        $options = $qb_options->execute();

        foreach ($options as $option)
        {
            if (! isset($options_data[$option->id]))
            {
                $options_data[$option->id] = array(
                    'title' => $option->title,
                    'votes' => 0,
                );
            }
        }

        $qb_votes = net_nemein_quickpoll_vote_dba::new_query_builder();
        $qb_votes->add_constraint('article', '=', $this->_article->id);
        $votes = $qb_votes->execute();
        
        foreach ($votes as $vote)
        {
            if (! isset($options_data[$vote->selectedoption]))
            {
                continue;
            }
            
            if (! isset($options_data[$vote->selectedoption]['votes']))
            {
                $options_data[$vote->selectedoption]['votes'] = 1;
            }
            else
            {
                $options_data[$vote->selectedoption]['votes'] += 1;                
            }
            
            $total_count++;
        }

        $qb_vote = net_nemein_quickpoll_vote_dba::new_query_builder();
        $qb_vote->add_constraint('article', '=', $this->_article->id);

        if ($this->_config->get('lock_ip_address'))
        {
            $qb_vote->add_constraint('ip', '=', $_SERVER['REMOTE_ADDR']);
        }
        else if ($_MIDGARD['user'])
        {
            $qb_vote->add_constraint('user', '=', $_MIDGARD['user']);
        }

        $vote_count = $qb_vote->count();
        
        if ($vote_count > 0)
        {
            $this->_request_data['voted'] =  true;
        }
        else
        {
            $this->_request_data['voted'] =  false;
        }

        if (   $this->_config->get('enable_anonymous')
            && !$this->_config->get('lock_ip_address')
            && !$_MIDGARD['user'])
        {
            $this->_request_data['voted'] =  false;
        }

        if (   !$this->_config->get('enable_anonymous')
            && (   !$_MIDCOM->auth->user
                && !$_MIDCOM->auth->admin))
        {
            $this->_request_data['voted'] =  true;
        }

        $poll_results = array(
            'poll' => $poll_data,
            'options' => $options_data,
            'total' => $total_count,
        );
        
        $data['poll_results'] =& $poll_results;
        
        if ($data['return_type'] == 'AJAX')
        {
            $this->_prepare_request_data();
        }
        
        return true;
    }
    
    /**
     * This function does the output.
     *
     */
    function _show_view($handler_id, &$data)
    {
        $this->_request_data['view_article'] = $this->_datamanager->get_content_html();
        midcom_show_style('index');
    }
    
    function _show_polldata($handler_id, &$data)
    {
        if ($data['return_type'] == 'XML')
        {
            $encoding = 'UTF-8';
            
            $_MIDCOM->cache->content->content_type('text/xml');
            $_MIDCOM->header('Content-type: text/xml; charset=' . $encoding);
            
            $voted = $data['voted'] ? 1 : 0;

            echo '<?xml version="1.0" encoding="' . $encoding . '" standalone="yes"?>' . "\n";
            echo "<data id='{$data['poll_results']['poll']['id']}' guid='{$data['poll_results']['poll']['guid']}' voted='{$voted}'>\n";
            echo "    <title><![CDATA[{$data['poll_results']['poll']['title']}]]></title>\n";
            echo "    <abstract><![CDATA[{$data['poll_results']['poll']['abstract']}]]></abstract>\n";
            echo "    <total_votes><![CDATA[{$data['poll_results']['total']}]]></total_votes>\n";
            echo "    <options>\n";

            foreach ($data['poll_results']['options'] as $id => $option)
            {
                echo "       <option id='{$id}' votes='{$option['votes']}'><![CDATA[{$option['title']}]]></option>\n";                
            }

            echo "    </options>\n";
            echo "</data>\n";
        }
        else
        {
            $this->_request_data['view_article'] = $this->_datamanager->get_content_html();
            midcom_show_style('index');
        }
    }
}
?>
