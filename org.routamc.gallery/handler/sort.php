<?php
/**
 * @package org.routamc.gallery
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: configuration.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Gallery photo sorting
 *
 * @package org.routamc.gallery
 */
class org_routamc_gallery_handler_sort extends midcom_baseclasses_components_handler
{
    /**
     * Index of photos
     *
     * @access private
     * @var Array
     */
    var $_photos = array ();

    /**
     * Datamanager2 instance for a photo
     *
     * @access private
     * @var midcom_helper_datamanager2_datamanager $_datamanager
     */
    var $_datamanager;

    /**
     * Datamanager2 instance for AJAX editing of a photo
     *
     * @access private
     * @var midcom_helper_datamanager2_controller $_controller
     */
    var $_controller;

    /**
     * AJAX controller HTML storages
     *
     * @access private
     * @var Array $_html_data
     */
    var $_html_data = array();

    /**
     * Constructor, connect to the parent class
     *
     * @access public
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Load midcom_helper_datamanager2_datamanager for content and midcom_helper_datamanager2_controller
     * if required by configuration
     *
     * @access private
     * @return boolean Indicating success
     */
    function _load_datamanager()
    {
        // Depending on the configuration give either AJAX output or the simple HTML output
        if ($this->_config->get('enable_ajax_editing'))
        {
            $this->_controller = midcom_helper_datamanager2_controller::create('ajax');
            $this->_controller->schemadb =& $this->_request_data['schemadb'];

            foreach ($this->_photos as $link_id => $photo)
            {
                $this->_controller->set_storage($photo);
                $this->_controller->process_ajax();
                $this->_view_html[$link_id] = $this->_controller->get_content_html();
            }

            // Get the other galleries
            $qb = midcom_db_topic::new_query_builder();
            $qb->add_constraint('up', '=', $this->_topic->id);
            $qb->add_constraint('component', '=', 'org.routamc.gallery');

            $subgalleries = $qb->execute();

            // Get the subgallery photos
            foreach ($subgalleries as $gallery)
            {
                // Get the photos
                $this->organizer->node = $gallery->id;
                $photos = $this->organizer->get_sorted();

                foreach ($photos as $link_id => $photo)
                {
                    $this->_controller->set_storage($photo);
                    $this->_controller->process_ajax();
                    $this->_view_html[$link_id] = $this->_controller->get_content_html();
                }
            }
        }

        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_request_data['schemadb']);

        if (!$this->_datamanager)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to create a DM2 instance.');
            // This will exit.
        }

        return true;
    }

    /**
     * Handler method for sorting
     *
     * @access public
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success
     */
    function _handler_sort($handler_id, $args, &$data)
    {
        // ACL handling
        $this->_topic->require_do('midgard:update');
        $this->_topic->require_do('midgard:create');

        // Add JavaScript headers
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL.'/org.routamc.gallery/sorter.js');

        // Add style sheet
        $_MIDCOM->add_link_head
        (
            array
            (
                'type' => 'text/css',
                'rel' => 'stylesheet',
                'href' => MIDCOM_STATIC_URL . '/org.routamc.gallery/sorter.css',
            )
        );

        // Get the photos
        $this->organizer = new org_routamc_gallery_organizer('metadata.score');
        $this->organizer->node = $this->_topic->id;
        $this->_photos = $this->organizer->get_sorted();

        // Initialize DM2 instance
        $this->_load_datamanager();

        // Return the results of form processing as results for the handler
        return $this->_process_form();
    }

    /**
     * Process the form. If the form has been submitted this method will not return anything, but relocate straight
     * to the correct page.
     *
     * @access private
     * @return boolean indicating success
     */
    function _process_form()
    {
        if (isset($_POST['f_cancel']))
        {
            $_MIDCOM->relocate('');
            // This will exit
        }

        // Form processing ends if there is no form submitted for processing
        if (!isset($_POST['f_submit']))
        {
            return true;
        }

        // Initialize debugging only for changes
        debug_push_class(__CLASS__, __FUNCTION__);

        $count = count($_POST['sortable']);

        $n = 1;

        foreach ($_POST['sortable'] as $i => $value)
        {
            $score = $count - $i;

            if (!preg_match('/^([a-z]+)_(.+?)_(.+)$/', $value, $regs))
            {
                continue;
            }

            switch ($regs[1])
            {
                case 'gallery':
                case 'group':
                    if (ereg('^new:', $regs[2]))
                    {
                        debug_add('Create a new topic');
                        $create = true;
                        $topic = new midcom_db_topic();

                        // MidCOM 2.8 compatibility
                        if (isset($topic->component))
                        {
                            $topic->component = $this->_topic->component;
                        }

                        $topic->extra = $regs[3];
                        $topic->up = $this->_topic->id;
                        $topic->name = midcom_generate_urlname_from_string($regs[3]);
                    }
                    else
                    {
                        $create = false;
                        $topic = new midcom_db_topic((int) $regs[2]);

                        if (   !$topic
                            || !$topic->id)
                        {
                            debug_add("Failed to get topic with id {$regs[2]}. Last mgd_errstr() was " . mgd_errstr(), MIDCOM_LOG_ERROR);
                            debug_pop();

                            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to process the form data, see error level log for details');
                            // This will exit
                        }

                        if ($topic->extra !== $regs[3])
                        {
                            $topic->extra = $regs[3];
                        }
                    }

                    if ($topic->id !== $this->_topic->id)
                    {
                        $topic->score = $i;
                    }

                    if ($create)
                    {
                        if (!$topic->create())
                        {
                            debug_print_r("Failed to create a topic, last error was " . mgd_errstr(), $topic, MIDCOM_LOG_ERROR);
                            debug_pop();

                            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to process the form data, see error level log for details');
                            // This will exit
                        }

                        // Set the component information
                        $topic->component = $this->_topic->component;
                        foreach ($this->_topic->list_parameters('org.routamc.gallery') as $name => $value)
                        {
                            $topic->set_parameter('org.routamc.gallery', $name, $value);
                        }
                    }
                    else
                    {
                        if (!$topic->update())
                        {
                            debug_print_r("Failed to update the topic {$topic->id}, last error was " . mgd_errstr(), $topic, MIDCOM_LOG_ERROR);
                            debug_pop();

                            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to process the form data, see error level log for details');
                            // This will exit
                        }
                    }

                    if (   isset($_POST['approvals'])
                        && $_POST['approvals'])
                    {
                        // Get the original approval status
                        $metadata =& midcom_helper_metadata::retrieve($topic);
                        $metadata->approve();
                    }

                    break;

                case 'link':
                    $link = new org_routamc_gallery_photolink_dba((int) $regs[2]);

                    $qb = org_routamc_gallery_photolink_dba::new_query_builder();
                    $qb->add_constraint('id', '<>', (int) $regs[2]);
                    $qb->add_constraint('node', '=', $topic->id);
                    $qb->add_constraint('photo', '=', (int) $regs[3]);

                    if (   !$link
                        || !$link->id)
                    {
                        debug_add("Failed to get a link with id '{$regs[2]}'", MIDCOM_LOG_ERROR);
                        debug_pop();

                        $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to process the form data, see error level log for details');
                        // This will exit
                    }

                    if ($qb->count() !== 0)
                    {
                        $link->delete();
                        continue;
                    }

                    $link->node = $topic->id;
                    $link->score = $score;

                    if (   isset($link->metadata)
                        && isset($link->metadata->score))
                    {
                        $link->metadata->score = $score;
                    }

                    if (!$link->update())
                    {
                        debug_print_r("Failed to update the photo link {$link->id}, last error was " . mgd_errstr(), $link, MIDCOM_LOG_ERROR);
                        debug_pop();

                        $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to process the form data, see error level log for details');
                        // This will exit
                    }

                    if (   isset($_POST['approvals'])
                        && $_POST['approvals'])
                    {
                        // Get the original approval status
                        $metadata =& midcom_helper_metadata::retrieve($link);
                        $metadata->approve();
                    }
                    break;
            }
        }

        // Show UI message of a successful save
        $_MIDCOM->uimessages->add($this->_l10n->get('org.routamc.gallery'), $this->_l10n->get('order saved'));

        // Relocate to the gallery front page
        $_MIDCOM->relocate('');
    }

    /**
     * Show the sorting interface
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     * @access public
     */
    function _show_sort($handler_id, &$data)
    {
        midcom_show_style('gallery-sort-header');

        $data['gallery'] =& $this->_topic;
        $data['class'] = 'master';
        $data['datamanager'] =& $this->_datamanager;

        midcom_show_style('gallery-sort-subset-header');

        // Show each photo
        foreach ($this->_photos as $link_id => $photo)
        {
            $data['photo'] =& $photo;
            $data['link_id'] = $link_id;

            $this->_datamanager->autoset_storage($photo);

            // Depending on the configuration give either AJAX output or the simple HTML output
            if ($this->_config->get('enable_ajax_editing'))
            {
                $data['view_photo'] = $this->_view_html[$link_id];
            }
            else
            {
                $data['view_photo'] = $this->_datamanager->get_content_html();
            }

            midcom_show_style('gallery-sort-subset-item');
        }

        midcom_show_style('gallery-sort-subset-footer');

        // Get the other galleries
        $qb = midcom_db_topic::new_query_builder();
        $qb->add_constraint('up', '=', $this->_topic->id);
        $qb->add_constraint('component', '=', 'org.routamc.gallery');

        $subgalleries = $qb->execute();

        $data['class'] = 'group';

        // Print subset for each gallery
        foreach ($subgalleries as $topic)
        {
            $data['gallery'] =& $topic;

            midcom_show_style('gallery-sort-subset-header');

            $this->organizer->node = $topic->id;

            foreach ($this->organizer->get_sorted() as $link_id => $photo)
            {
                $data['link_id'] = $link_id;
                $data['photo'] =& $photo;

                $this->_datamanager->autoset_storage($photo);

                // Depending on the configuration give either AJAX output or the simple HTML output
                if ($this->_config->get('enable_ajax_editing'))
                {
                    $data['view_photo'] = $this->_view_html[$link_id];
                }
                else
                {
                    $data['view_photo'] = $this->_datamanager->get_content_html();
                }

                midcom_show_style('gallery-sort-subset-item');
            }

            midcom_show_style('gallery-sort-subset-footer');
        }

        midcom_show_style('gallery-sort-footer');
    }
}
?>