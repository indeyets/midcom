<?php
/**
 * @package de.linkm.taviewer
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * TAViewer site interface class
 * 
 * This is a complete rewrite of the topic-article viewer the has been made for MidCOM 2.6.
 * It incorporates all of the goodies current MidCOM has to offer and can serve as an 
 * example component therefore.
 * 
 * @package de.linkm.taviewer
 */

class de_linkm_taviewer_viewer extends midcom_baseclasses_components_request
{
    /**
     * The topic in which to look for articles. This defaults to the current content topic
     * unless overridden by the symlink topic feature.
     * 
     * @var midcom_db_topic
     * @access private
     */
    var $_content_topic = null;

    function de_linkm_taviewer_viewer($topic, $config) 
    {
        parent::midcom_baseclasses_components_request($topic, $config);
    }

    /**
     * Initialize the request switch and the content topic.
     * 
     * @access protected
     */
    function _on_initialize()
    {
        $this->_determine_content_topic();
        
        // Configure request switch here.
        
        // Index Handler, configure depending on autoindex mode.
        if ($this->_config->get('autoindex'))
        {
            $this->_request_switch[] = Array
	        (
	            'handler' => 'autoindex',
	            // No further arguments, we have neither fixed nor variable arguments.
	        );
        }
        else
        {
            $this->_request_switch[] = Array
	        (
	            'handler' => 'index',
	            // No further arguments, we have neither fixed nor variable arguments.
	        );
        }
        
        $this->_request_switch[] = Array
        (
            'handler' => 'show_article',
            'variable_args' => 1,
        );
        
    }
    
    /**
     * Set the content topic to use. This will check against the configuration setting 'symlink_topic'.
     * 
     * @access protected
     */
    function _determine_content_topic() 
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $guid = $this->_config->get('symlink_topic');
        if (is_null($guid)) 
        {
            // No symlink topic
            // Workaround, we should talk to an DBA object automatically here in fact. 
            $this->_content_topic = new midcom_db_topic($this->_topic->id);
            debug_pop();
            return;
        }
        
        $this->_content_topic = new midcom_db_topic($guid);

        // Validate topic.
                
        if (! $this->_content_topic) 
        {
            debug_add('Failed to open symlink content topic, (might also be an invalid object) last Midgard Error: ' 
                . mgd_errstr(), MIDCOM_LOG_ERROR);
            $_MIDCOM->generate_error('Failed to open symlink content topic.');
            // This will exit.
        }
        
        if ($this->_content_topic->get_parameter('midcom', 'component') != 'de.linkm.taviewer')
        {
            debug_print_r('Retrieved topic was:', $this->_content_topic);
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Symlink content topic is invalid, see the debug level log for details.');
            // This will exit.
        }
        
        debug_pop();
    }
    
    /**
     * Can-Handle check against the current article name. We have to do this explicitly
     * in can_handle already, otherwise we would hide all subtopics as the request switch
     * accepts all argument count matches unconditionally.
     */
    function _can_handle_show_article ($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $can_handle = false;
        $name = $args[0];
        
        $qb = midcom_baseclasses_database_article::new_query_builder();
        $qb->add_constraint('topic', '=', $this->_content_topic->id);
        $qb->add_constraint('name', '=', $name);
        $result = $qb->execute();
        
        if (count($result) == 1)
        {
            $data['article'] = $result[0];
            $can_handle = true;
        } 
        
        debug_pop();
        return $can_handle;
    }
    
    /**
     * The actual request handler for article showing.
     */
    function _handler_show_article ($handler_id, $args, &$data)
    {
        return $this->_prepare_article_for_show();
    }
    
    /**
     * Looks up the index article in the topic. If not found, a FORBIDDEN error is triggered,
     * as we are not in the autoindex mode.
     */
    function _handler_index ($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $qb = midcom_baseclasses_database_article::new_query_builder();
        $qb->add_constraint('topic', '=', $this->_content_topic->id);
        $qb->add_constraint('name', '=', 'index');
        $result = $qb->execute();
        
        if (count($result) != 1)
        {
            debug_add('Resultset was:', $result);
            $this->errcode = MIDCOM_ERRFORBIDDEN;
            $this->errstr = 'Directory index forbidden';
            return false;
        }
        
        $data['article'] = $result[0];
        
        debug_pop();
        return $this->_prepare_article_for_show();
    }
    
    /**
     * Shows the autoindex list. Nothing to do in the handle phase except setting last modified
     * dates.
     */
    function _handler_autoindex ($handler_id, $args, &$data)
    {
        // Modify last modified timestamp
        $qb = midcom_db_article::new_query_builder();
        $qb->add_constraint('topic', '=', $this->_topic->id);
        $qb->add_order('revised', 'DESC');
		$qb->set_limit(1);
        $result = $qb->execute();
        if ($result)
        {
            $article_time = $result[0]->revised;
        }
        else
        {
            $article_time = 0;
        }
        $topic_time = $this->_topic->revised;
        $_MIDCOM->set_26_request_metadata(max($article_time, $topic_time), null);
        return true;
    }
    
    /**
     * Internal helper, takes a database article and prepares it for display.
     * 
     * The main operation here is the creation of a datamanager instance for the article to be
     * shown and an according substyle switch (unless it is the 'default' schema.
     * 
     * It will work of the request data key 'article' and will create the a DM instance as 'datamanager'.
     * 
     * This handler still contains compatibility code for the old Aegir Symlink Article feature,
     * controlled by the corresponding configuration directive. Be aware, that this feature
     * is deprecated and will be dropped in after MidCOM 2.6.
     * 
     * @deprecate The enable_aegir_symlink_article option is deprecated and will be removed after MidCOM 2.6.
     *
     * @return bool Indicating success
     */
    function _prepare_article_for_show()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
 
        // Check for the Aegir Symlink Article
		if ($this->_config->get('enable_aegir_symlink_article')) 
        {
            $real_guid = $this->_request_data['article']->parameter('midcom.symlink','GUID');
		    if ($real_guid) 
            {
                $real_article = new midcom_baseclasses_database_article($real_guid);
				if ($real_article) 
	            {
				    debug_add("Symlink article found with guid: {$real_guid}");
				    $this->_request_data['article']->abstract = $real_article->abstract;
				    $this->_request_data['article']->content = $real_article->content;
				} 
	            else 
	            {
				    debug_add("Symlink article not found with guid: {$real_guid}, skipping silently.", MIDCOM_LOG_WARNING);
				}
		    }
		}
        
        $this->_request_data['datamanager'] = new midcom_helper_datamanager($this->_config->get('schemadb'));
        if (! $this->_request_data['datamanager'])
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Datamanager class could not be instantinated, see the debug level log for details.');
            // This will exit.
        }

        if (! $this->_request_data['datamanager']->init($this->_request_data['article']))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Datamanager class could not be initialized, see the debug level log for details.');
            // This will exit.
        }
        
        // Set the currently active leaf and the MidCOM page title accordingly.
        $this->_component_data['active_leaf'] = $this->_request_data['article']->id;
        $_MIDCOM->set_pagetitle($this->_request_data['article']->title);
        
        // Descend into the substyle according to the schema name
        $schema = $this->_request_data['datamanager']->get_schema_name();
        debug_add("Appending Substyle {$schema}", MIDCOM_LOG_INFO);
        $_MIDCOM->substyle_append($schema); 
        
        debug_pop();
        return true;
    }
    
    /**
     * Shows the article on screen.
     */
    function _show_show_article($handler_id, &$data)
    {
        $this->_show_article();
    }
    
    /**
     * Shows the index article on screen.
     */
    function _show_index($handler_id, &$data)
    {
        $this->_show_article();
    }
    
    /**
     * Internal helper function. Displays an article.
     * 
     * For compatibility reasons, the original globals 'view' and 'view_datamanager'
     * are still populated with the data array and the datamanager instance respectivly,
     * but they too will be deprectated after MidCOM 2.6.
     * 
     * @deprecate The globals 'view' and 'view_datamanager' will be deprecated after MidCOM 2.6.
     */
    function _show_article()
    {
        // Set the compatibility Values:
        $GLOBALS['view'] = $this->_request_data['datamanager']->get_array();
        $GLOBALS['view_datamanager'] =& $this->_request_data['datamanager'];
        
        midcom_show_style('show-article');
    }
    
    /**
     * Displays the autoindex of the Taviewer. This is a list of all articles and attachments on
     * the current topic.
     * 
     * The globals view_title, view_l10n and view_l10n_midcom are populated for compatibility reasons 
     * only, they have been superseeded
     * by the corresponding request data key. The global will be dropped after MidCOM 2.6.
     * 
     * @deprecate The globals view_title, view_l10n and view_l10n_midcom will be deprecated after MidCOM 2.6.
     */
    function _show_autoindex($handler_id, &$data) 
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        // Compatibility values
        $GLOBALS['view_title'] = $this->_topic->extra;
        $GLOBALS['view_l10n'] = $_MIDCOM->i18n->get_l10n('de.linkm.taviewer');
        $GLOBALS['view_l10n_midcom'] = $_MIDCOM->i18n->get_l10n('midcom');

        $data['title'] = $this->_topic->extra;
        
        midcom_show_style('autoindex-start');

        $view = $this->_load_autoindex_data();
                
        if (count ($view) > 0) 
        {
            foreach ($view as $filename => $data) 
            {
                $GLOBALS['view_filename'] = $filename;
                $GLOBALS['view'] = $data;
                midcom_show_style('autoindex-item');
            }
        } 
        else 
        {
            midcom_show_style('autoindex-directory-empty');
        }
                
        midcom_show_style('autoindex-end');
        
        debug_pop();
        return true;
    }
    
    /**
     * This helper function goes over the topic and loads all available objects for displaying
     * in the autoindex.
     * 
     * TODO: Array result description
     * 
     * @return Array Autoindex objectes as outlined above
     */
    function _load_autoindex_data()
    {
        $view = Array();
        $datamanager = new midcom_helper_datamanager($this->_config->get('schemadb'));
        
        $qb = midcom_baseclasses_database_article::new_query_builder();
        $qb->add_constraint('topic', '=', $this->_content_topic->id);
        $result = $qb->execute();

        foreach ($result as $article)
        {
            if (! $datamanager->init($article)) 
            {
                debug_print_r('The datamanager for this article could not be initialized, skipping it:', $article);
                continue;
            }
            
            $data = $datamanager->get_array();
            $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
            $filename = "{$article->name}.html";
            $view[$filename]['name'] = $filename;
            $view[$filename]['url'] = $prefix . $filename;
            $view[$filename]['size'] = '?';
            if (array_key_exists('title', $data))
            {
                $view[$filename]['desc'] = $data['title'];
            }
            else
            {
                $view[$filename]['desc'] = $article->title;
            }
            $view[$filename]['type'] = 'text/html';
            $view[$filename]['lastmod'] = strftime('%x %X', $article->revised);
            foreach ($data as $key => $value) 
            {
                if (   is_array($value)
                    && array_key_exists('filename', $value)
                    && array_key_exists('mimetype', $value)
                    && array_key_exists('lastmod', $value)
                    && array_key_exists('formattedsize', $value)
                    && array_key_exists('url', $value)
                    && array_key_exists('description', $value)) 
                {
                    // This is a Blob (or a derived type)
                    $view[$value['filename']]['name'] = $value['filename'];
                    $view[$value['filename']]['url'] = $value['url'];

                    $view[$value['filename']]['size'] = $value['formattedsize'];
                    $view[$value['filename']]['desc'] = $value['description'];
                    $view[$value['filename']]['type'] = $value['mimetype'];
                    $view[$value['filename']]['lastmod'] = strftime('%x %X', $value['lastmod']);
                } 
                else if (is_array($value)) 
                {
                    // This could be a collection.
                    foreach ($value as $subkey => $subvalue) 
                    {
                        if (   is_array($subvalue)
                            && array_key_exists('filename', $subvalue)
                            && array_key_exists('mimetype', $subvalue)
                            && array_key_exists('lastmod', $subvalue)
                            && array_key_exists('formattedsize', $subvalue)
                            && array_key_exists('url', $subvalue)
                            && array_key_exists('description', $subvalue)) 
                        {
                            // This is a Blob (or a derived type)
                            $view[$subvalue['filename']]['name'] = $subvalue['filename'];
                            $view[$subvalue['filename']]['url'] = $subvalue['url'];
                            $view[$subvalue['filename']]['size'] = $subvalue['formattedsize'];
                            $view[$subvalue['filename']]['desc'] = $subvalue['description'];
                            $view[$subvalue['filename']]['type'] = $subvalue['mimetype'];
                            $view[$subvalue['filename']]['lastmod'] = strftime('%x %X', $subvalue['lastmod']);
                        }                            
                    }
                }
            }
        }
        ksort ($view);
        debug_pop();
        return $view;
    }
    

    function _on_get_metadata()
    {
        if (array_key_exists('article', $this->_request_data))
        {
            return midcom_helper_metadata::retrieve($this->_request_data['article']);
        }
        else
        {
            return parent::_on_get_metadata();
        }
    }

}

?>
