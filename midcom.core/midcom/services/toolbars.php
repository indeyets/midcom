<?php
/**
 * @package midcom.services
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:toolbars.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * On-site toolbar service.
 *
 * This service manages the toolbars used for the on-site administration system.
 * For each context, it provides the following set of toolbars:
 *
 * 1. The <em>Node</em> toolbar is applicable to the current
 *    node, which is usually a topic. MidCOM places the topic management operations
 *    into this toolbar, where applicable.
 *
 * 2. The <em>View</em> toolbar is applicable to the specific view ("url"). Usually
 *    this maps to a single displayed object (see also the bind_to_object() member
 *    function). MidCOM places the object-specific management operations (like
 *    Metadata controls) into this toolbar, if it is bound to an object. Otherwise,
 *    this toolbar is not touched by MidCOM.
 *
 * It is important to understand, that these default toolbars made available through this
 * service are completly specific to a given request context. If you have a dynamic_load
 * running on a given site, it will have its own set of toolbars for each instance.
 *
 * In addition, components my retrieve a third kind of toolbars, which are not under
 * the general control of MidCOM, the <em>Object</em> toolbars. They apply to a single
 * database object (like a bound <em>View</em> toolbar). The usage of this kind of
 * toolbars is completly component-specific: It is recommended to use them only for
 * cases where multiple objects are displayed simultaneously. For example, the
 * index page of a Newsticker or Image Gallery might provide them.
 *
 *
 *
 * <strong>Implementation notes</strong>
 *
 * It has yet to prove if the toolbar system is yet needed for a dynamic_load environments.
 * The main reason for this is that dl'ed stuff is often quite tight in space and thus cannot
 * display any toolbars in a sane way. Usually, the administrative tasks will be bound to the
 * main request.
 *
 * This could be different for portal applications, which display several components on the
 * welcome page, each with its own management options.
 *
 * <strong>Configuration</strong>
 * See midcom_config.php for configuration options.
 *
 * @package midcom.services
 */
class midcom_services_toolbars extends midcom_baseclasses_core_object
{
    /**
     * The toolbars currently available. This array is indexed by context id; each
     * value consists of an flat array of two toolbars, the first object being the
     * Node toolbar, the second View toolbar. The toolbars are created on-demand.
     *
     * @var Array
     * @access private
     */
    var $_toolbars = Array();

    /**
     * midcom.services.toolbars has two modes, it can either display one centralized toolbar
     * for authenticated users, or the node and view toolbars separately for others. This
     * property controls whether centralized mode is enabled.
     *
     * @var boolean
     * @access private
     */
    var $_enable_centralized = false;

    /**
     * Whether we're in centralized mode, i.e. centralized toolbar has been shown
     *
     * @var boolean
     * @access private
     */
    var $_centralized_mode = false;

    /**
     * Simple constructor, calls base class.
     */
    function midcom_services_toolbars()
    {
        parent::midcom_baseclasses_core_object();

        $this->_initialize_centralized_toolbar();
    }

    /**
     * Initialize centralized toolbar if required
     */
    function _initialize_centralized_toolbar()
    {
        if (!$_MIDCOM->auth->user)
        {
            // Centralized toolbar is only for registered users
            return;
        }

        if (!$GLOBALS['midcom_config']['toolbars_enable_centralized'])
        {
            return;
        }

        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/Pearified/JavaScript/Prototype/prototype.js');
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/Pearified/JavaScript/Scriptaculous/scriptaculous.js');
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/Javascript_protoToolkit/protoToolkit.js');
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/Javascript_protoToolkit/protoMemory.js');
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/Javascript_protoToolkit/protoToolbar.js');        

        $_MIDCOM->add_link_head(
            array
            (
                'rel'   => 'stylesheet',
                'type'  => 'text/css',
                'media' => 'screen',
                'href'  => $GLOBALS['midcom_config']['toolbars_css_path'],
            )
        );

        $this->type = 'palette';

        // Compute the final script:
        $script = "
            function protoToolbarOnload() {
            	protoToolbar = new protoToolbar({
            	   type: '{$this->type}'
                });
            }
        ";

        $_MIDCOM->add_jscript($script);
        $_MIDCOM->add_jsonload('protoToolbarOnload()');

        // We've included CSS and JS, path is clear for centralized mode
        $this->_enable_centralized = true;
    }

    /**
     * Returns a reference to the node toolbar of the specified context. The toolbars
     * will be created if this is the first request.
     *
     * @param int $context_id The context to retrieve the node toolbar for, this
     *     defaults to the current context.
     */
    function & get_node_toolbar ($context_id = null)
    {
        if ($context_id === null)
        {
            $context_id = $_MIDCOM->get_current_context();
        }

        if (! array_key_exists($context_id, $this->_toolbars))
        {
            $this->_create_toolbars($context_id);
        }

        return $this->_toolbars[$context_id][MIDCOM_TOOLBAR_NODE];
    }

    /**
     * Returns a reference to the view toolbar of the specified context. The toolbars
     * will be created if this is the first request.
     *
     * @param int $context_id The context to retrieve the view toolbar for, this
     *     defaults to the current context.
     */
    function & get_view_toolbar ($context_id = null)
    {
        if ($context_id === null)
        {
            $context_id = $_MIDCOM->get_current_context();
        }

        if (! array_key_exists($context_id, $this->_toolbars))
        {
            $this->_create_toolbars($context_id);
        }

        return $this->_toolbars[$context_id][MIDCOM_TOOLBAR_VIEW];
    }

    /**
     * Creates the node and view toolbars for a given context ID.
     *
     * @param int $context_id The context ID for whicht the toolbars should be created.
     */
    function _create_toolbars ($context_id)
    {
        $this->_toolbars[$context_id][MIDCOM_TOOLBAR_NODE] =
            new midcom_helper_toolbar
            (
                $GLOBALS['midcom_config']['toolbars_node_style_class'],
                $GLOBALS['midcom_config']['toolbars_node_style_id']
            );
        $this->_toolbars[$context_id][MIDCOM_TOOLBAR_VIEW] =
            new midcom_helper_toolbar
            (
                $GLOBALS['midcom_config']['toolbars_view_style_class'],
                $GLOBALS['midcom_config']['toolbars_view_style_id']
            );
            
        $this->_toolbars[$context_id][MIDCOM_TOOLBAR_HOST] =
            new midcom_helper_toolbar
            (
                $GLOBALS['midcom_config']['toolbars_host_style_class'],
                $GLOBALS['midcom_config']['toolbars_host_style_id']
            );
        $this->add_topic_management_commands($this->_toolbars[$context_id][MIDCOM_TOOLBAR_NODE], $context_id);
        $this->add_host_management_commands($this->_toolbars[$context_id][MIDCOM_TOOLBAR_HOST], $context_id);
    }

    /**
     * Adds the topic management commands to the specified toolbar.
     *
     * Repeated calls to the same toolbar are intercepted accordingly.
     *
     * @todo This is an intermediate implementation to link to the current proof-of-concept
     *     Folder management code. This needs adaption to Aegir2!
     * @todo Better privilege checks
     * @todo Localize
     *
     * @param midcom_helper_toolbar $toolbar A reference to the toolbar to use.
     * @param int $context_id The context to use (the topic is drawn from there). This defaults
     *     to the currently active context.
     */
    function add_topic_management_commands(&$toolbar, $context_id = null)
    {
        if (array_key_exists('midcom_service_toolbars_bound_to_topic', $toolbar->customdata))
        {
            // We already processed this toolbar, skipping further adds.
            return;
        }
        else
        {
            $toolbar->customdata['midcom_service_toolbars_bound_to_topic'] = true;
        }

        if ($context_id === null)
        {
            $topic = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
        }
        else
        {
            $topic = $_MIDCOM->get_context_data($context_id, MIDCOM_CONTEXT_CONTENTTOPIC);
        }
        
        if (!$topic)
        {
            return false;
        }

        if (! is_a($topic, 'midcom_baseclasses_database_topic'))
        {
            // Force-Cast to DBA object
            $topic = new midcom_db_topic($topic->id);
        }

        if (   $topic->can_do('midgard:update')
            && $topic->can_do('midcom.admin.folder:topic_management'))
        {
            $toolbar->add_item(
                array
                (
                    MIDCOM_TOOLBAR_URL => "__ais/folder/edit.html",
                    MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('edit folder', 'midcom.admin.folder'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                    MIDCOM_TOOLBAR_ACCESSKEY => 'g',
                )
            );

            // TEMPORARY CODE: This links the old DM1 Metadata editor into the site.
            $toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "__ais/folder/metadata/{$topic->guid}.html",
                    MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('edit folder metadata', 'midcom.admin.folder'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/properties.png',
                )
            );
            // TEMPORARY CODE END

            $toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "__ais/folder/move/{$topic->guid}.html",
                    MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('move', 'midcom.admin.folder'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/topic-score.png',
                )
            );
            
            $toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "__ais/folder/order.html",
                    MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('order navigation', 'midcom.admin.folder'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/topic-score.png',
                    MIDCOM_TOOLBAR_ACCESSKEY => 'o',
                )
            );
        }

        // TEMPORARY CODE: This links the old midcom approval helpers into the site
        // if we are configured to do so. This will be replaced once we revampt the
        // Metadata system of MidCOM to use 1.8
        if (   $GLOBALS['midcom_config']['metadata_approval']
            && $topic->can_do('midcom:approve'))
        {
            $metadata =& midcom_helper_metadata::retrieve($topic);
            if ($metadata->is_approved())
            {
                $toolbar->add_item
                (
                    array
                    (
                        MIDCOM_TOOLBAR_URL => "__ais/folder/unapprove.html",
                        MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('unapprove topic', 'midcom'),
                        MIDCOM_TOOLBAR_HELPTEXT => $_MIDCOM->i18n->get_string('approved', 'midcom'),
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/approved.png',
                        MIDCOM_TOOLBAR_POST => true,
                        MIDCOM_TOOLBAR_POST_HIDDENARGS => Array
                        (
                            'guid' => $topic->guid,
                            'return_to' => $_SERVER['REQUEST_URI'],
                        ),
                    )
                );
            }
            else
            {
                $toolbar->add_item
                (
                    array
                    (
                        MIDCOM_TOOLBAR_URL => "__ais/folder/approve.html",
                        MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('approve topic', 'midcom'),
                        MIDCOM_TOOLBAR_HELPTEXT => $_MIDCOM->i18n->get_string('unapproved', 'midcom'),
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/not_approved.png',
                        MIDCOM_TOOLBAR_POST => true,
                        MIDCOM_TOOLBAR_POST_HIDDENARGS => Array
                        (
                            'guid' => $topic->guid,
                            'return_to' => $_SERVER['REQUEST_URI'],
                        ),
                    )
                );
            }
        }
        
        if (   array_key_exists('midcom.helper.replicator', $_MIDCOM->componentloader->manifests)
            && $_MIDGARD['admin'] == true)
        {
            $toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "__mfa/replication/object/{$topic->guid}.html",
                    MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('replication information', 'midcom.helper.replicator'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/repair.png',
                )
            );
        }
        
        if ($topic->can_do('midcom.admin.folder:template_management'))
        {
            if ($topic->style != '')
            {
                $enabled = true;
            }
            else
            {
                $enabled = false;
            }
            
            $toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "__mfa/styleeditor/",
                    MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('edit layout template', 'midcom.admin.styleeditor'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/text-x-generic-template.png',
                    MIDCOM_TOOLBAR_ACCESSKEY => 't',
                    MIDCOM_TOOLBAR_ENABLED => $enabled,
                )
            );            
        }
        
        // TEMPORARY METADATA CODE END
        if (   $topic->can_do('midgard:create')
            && $topic->can_do('midcom.admin.folder:topic_management'))
        {
            $toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "__ais/folder/create.html",
                    MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('create subfolder', 'midcom.admin.folder'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-dir.png',
                    MIDCOM_TOOLBAR_ACCESSKEY => 'f',
                )
            );
        }
        if (   $topic->guid != $GLOBALS['midcom_config']['midcom_root_topic_guid']
            && $topic->can_do('midgard:delete')
            && $topic->can_do('midcom.admin.folder:topic_management'))
        {
            $toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "__ais/folder/delete.html",
                MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('delete folder', 'midcom.admin.folder'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                // for terminate d is used by everyone to go to the location bar
            ));
        }
        if ($topic->can_do('midgard:privileges'))
        {
            $toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "__ais/acl/edit/{$topic->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('topic privileges', 'midgard.admin.acl'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/properties.png',
                MIDCOM_TOOLBAR_ACCESSKEY => 'p',
            ));
        }
        
        
    }
    
    /**
     * Adds the Host management commands to the specified toolbar.
     *
     * Repeated calls to the same toolbar are intercepted accordingly.
     *
     * @todo This is an intermediate implementation to link to the current proof-of-concept
     *     Folder management code. This needs adaption to Aegir2!
     * @todo Better privilege checks
     * @todo Localize
     *
     * @param midcom_helper_toolbar $toolbar A reference to the toolbar to use.
     * @param int $context_id The context to use (the topic is drawn from there). This defaults
     *     to the currently active context.
     */
    function add_host_management_commands(&$toolbar, $context_id = null)
    {
        if (array_key_exists('midcom_service_toolbars_bound_to_host', $toolbar->customdata))
        {
            // We already processed this toolbar, skipping further adds.
            return;
        }
        else
        {
            $toolbar->customdata['midcom_service_toolbars_bound_to_host'] = true;
        }

        if ($_MIDCOM->auth->user)
        {
            $toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "{$_MIDGARD['self']}midcom-logout-",
                    MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('logout', 'midcom'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/exit.png',
                    MIDCOM_TOOLBAR_ACCESSKEY => 'l',
                )
            );
        }
        
        if ($_MIDGARD['admin'] == true)
        {
            // Safety
            if (!isset($GLOBALS['midcom_config']['staging2live_staging']))
            {
                $GLOBALS['midcom_config']['staging2live_staging'] = false;
            }
            $toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "{$_MIDGARD['self']}midcom-exec-midcom/runreplication.php",
                    MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('run replication', 'midcom'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_jump-to.png',
                    MIDCOM_TOOLBAR_ENABLED => $GLOBALS['midcom_config']['staging2live_staging'],
                    MIDCOM_TOOLBAR_POST => true,
                    MIDCOM_TOOLBAR_POST_HIDDENARGS => Array
                    (
                        'return_to' => $_SERVER['REQUEST_URI'],
                    ),
                )
            );
            
            $toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "{$_MIDGARD['self']}__ais/midcom-settings/edit.html",
                    MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('host configuration', 'midcom.admin.settings'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
                )
            );
            
            $toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "{$_MIDGARD['self']}midcom-cache-invalidate",
                    MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('invalidate cache', 'midcom'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_refresh.png',
                )
            );
            
            if (array_key_exists('midcom.helper.replicator', $_MIDCOM->componentloader->manifests))
            {
                $toolbar->add_item
                (
                    array
                    (
                        MIDCOM_TOOLBAR_URL => "{$_MIDGARD['self']}__mfa/replication/",
                        MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('manage replication', 'midcom.helper.replicator'),
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
                    )
                );
            }
        }

    	if ($_MIDGARD['admin'] == true)
    	{
            $toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "{$_MIDGARD['self']}__ais/l10n/",
                    MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('midcom.admin.babel', 'midcom.admin.babel'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
                    MIDCOM_TOOLBAR_ACCESSKEY => 'z',
                )
            );
    	}

        if ($_MIDGARD['admin'] == true)
        {
            $toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "{$_MIDGARD['self']}midcom-exec-midcom/config-test.php",
                    MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('test settings', 'midcom'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/repair.png',
                )
            );
        }
        
        
    }

    /**
     * Binds the a toolbar to a DBA object. This will append a number of globally available
     * toolbar options. For example, expect Metadata- and Version Control-related options
     * to be added.
     *
     * This call is available through convenience functions throughout the framework: The
     * toolbar main class has a mapping for it (midcom_helper_toolbar::bind_to($object))
     * and object toolbars created by this service will automatically be bound to the
     * specified object.
     *
     * Repeated bind calls are intercepted, you can only bind a toolbar to a single object.
     *
     * @see midcom_helper_toolbar::bind_to()
     * @see create_object_toolbar()
     * @var $toolbar
     *
     * @todo This is a stub implementation only, no hooks are added yet. For testing purposes
     *     however the permalink of the bound object is added to the toolbar for all users.
     */
    function bind_toolbar_to_object (&$toolbar, &$object)
    {
        if (array_key_exists('midcom_service_toolbars_bound_to_object', $toolbar->customdata))
        {
            // We already processed this toolbar, skipping further adds.
            return;
        }
        else
        {
            $toolbar->customdata['midcom_service_toolbars_bound_to_object'] = true;
        }
        
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        if (!$prefix)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Toolbar for object {$object->guid} was called before topic prefix was available, skipping global items.", MIDCOM_LOG_WARN);
            debug_pop();
            return;
        }

        // TEMPORARY CODE: This links the old midcom metadata helpers into the site
        // if we are configured to do so. This will be replaced once we revampt the
        // Metadata system of MidCOM to use 1.8
        if (   $GLOBALS['midcom_config']['metadata_approval']
            && $object->can_do('midcom:approve'))
        {
            $metadata =& midcom_helper_metadata::retrieve($object);
            if (   $metadata
                && $metadata->is_approved())
            {
                $toolbar->add_item(Array(
                    MIDCOM_TOOLBAR_URL => "{$prefix}__ais/folder/unapprove.html",
                    MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('unapprove', 'midcom'),
                    MIDCOM_TOOLBAR_HELPTEXT => $_MIDCOM->i18n->get_string('approved', 'midcom'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/approved.png',
                    MIDCOM_TOOLBAR_POST => true,
                    MIDCOM_TOOLBAR_POST_HIDDENARGS => Array
                    (
                        'guid' => $object->guid,
                        'return_to' => $_SERVER['REQUEST_URI'],
                    ),
                    MIDCOM_TOOLBAR_ACCESSKEY => 'u',
                ));
            }
            else
            {
                $toolbar->add_item(Array(
                    MIDCOM_TOOLBAR_URL => "{$prefix}__ais/folder/approve.html",
                    MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('approve', 'midcom'),
                    MIDCOM_TOOLBAR_HELPTEXT => $_MIDCOM->i18n->get_string('unapproved', 'midcom'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/not_approved.png',
                    MIDCOM_TOOLBAR_POST => true,
                    MIDCOM_TOOLBAR_POST_HIDDENARGS => Array
                    (
                        'guid' => $object->guid,
                        'return_to' => $_SERVER['REQUEST_URI'],
                    ),
                    MIDCOM_TOOLBAR_ACCESSKEY => 'a',
                ));
            }
        }

        if (   array_key_exists('midcom.helper.replicator', $_MIDCOM->componentloader->manifests)
            && $_MIDGARD['admin'] == true)
        {
            $toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "{$prefix}__mfa/replication/object/{$object->guid}.html",
                    MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('replication information', 'midcom.helper.replicator'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/repair.png',
                )
            );
        }

        if ($object->can_do('midgard:update'))
        {
            $toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "{$prefix}__ais/folder/metadata/{$object->guid}.html",
                    MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('edit metadata', 'midcom.admin.folder'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/properties.png',
                    MIDCOM_TOOLBAR_ACCESSKEY => 'm',
                )
            );
        }
        // TEMPORARY METADATA CODE END
        
        if ($object->can_do('midgard:update'))
        {
            $toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "{$prefix}__ais/folder/move/{$object->guid}.html",
                    MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('move', 'midcom.admin.folder'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/topic-score.png',
                )
            );
        }

        if ($object->can_do('midgard:privileges'))
        {
            $toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "{$prefix}__ais/acl/edit/{$object->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('privileges', 'midgard.admin.acl'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/properties.png',
            ));
        }
        
        if ($object->can_do('midgard:update'))
        {
            $toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "{$prefix}__ais/rcs/{$object->guid}/",
                    MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('show history', 'no.bergfald.rcs'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/properties.png',
                    MIDCOM_TOOLBAR_ACCESSKEY => 'v',
                )
            );
        }
    }

    /**
     * Renders the specified toolbar for the indicated context. If the toolbar is undefined,
     * an empty string is returned. If you want to show the toolbar directly, look for
     * the _show_toolbar method.
     *
     * @param int $toolbar_identifier The toolbar identifier constant (one of
     *     MIDCOM_TOOLBAR_NODE or MIDCOM_TOOLBAR_VIEW etc.)
     * @param int $context_id The context to retrieve the node toolbar for, this
     *     defaults to the current context.
     * @return string The rendered toolbar
     * @see midcom_helper_toolbar::render()
     */
    function _render_toolbar($toolbar_identifier, $context_id = null)
    {
        if ($context_id === null)
        {
            $context_id = $_MIDCOM->get_current_context();
        }

        if (! array_key_exists($context_id, $this->_toolbars))
        {
            return '';
        }

        /**
         * These have been moved to _create_toolbars() so that the appropriate actions can hide their own buttons
         * if ($toolbar_identifier == MIDCOM_TOOLBAR_NODE)
         * {
         *    //$this->add_topic_management_commands($this->_toolbars[$context_id][MIDCOM_TOOLBAR_NODE], $context_id);
         * }
         *
         * if ($toolbar_identifier == MIDCOM_TOOLBAR_HOST)
         * {
         *     $this->add_host_management_commands($this->_toolbars[$context_id][MIDCOM_TOOLBAR_HOST], $context_id);
         * }
         */

        return $this->_toolbars[$context_id][$toolbar_identifier]->render();
    }

    /**
     * Renders the node toolbar for the indicated context. If the toolbar is undefined,
     * an empty string is returned. If you want to show the toolbar directly, look for
     * the show_xxx_toolbar methods.
     *
     * @param int $context_id The context to retrieve the node toolbar for, this
     *     defaults to the current context.
     * @return string The rendered toolbar
     * @see midcom_helper_toolbar::render()
     */
    function render_node_toolbar($context_id = null)
    {
        return $this->_render_toolbar(MIDCOM_TOOLBAR_NODE, $context_id);
    }

    /**
     * Renders the view toolbar for the indicated context. If the toolbar is undefined,
     * an empty string is returned. If you want to show the toolbar directly, look for
     * the show_xxx_toolbar methods.
     *
     * @param int $context_id The context to retrieve the node toolbar for, this
     *     defaults to the current context.
     * @return string The rendered toolbar
     * @see midcom_helper_toolbar::render()
     */
    function render_view_toolbar($context_id = null)
    {
        return $this->_render_toolbar(MIDCOM_TOOLBAR_VIEW, $context_id);
    }
    
    /**
     * Renders the host toolbar for the indicated context. If the toolbar is undefined,
     * an empty string is returned. If you want to show the toolbar directly, look for
     * the show_xxx_toolbar methods.
     *
     * @param int $context_id The context to retrieve the node toolbar for, this
     *     defaults to the current context.
     * @return string The rendered toolbar
     * @see midcom_helper_toolbar::render()
     */
    function render_host_toolbar($context_id = null)
    {
        return $this->_render_toolbar(MIDCOM_TOOLBAR_HOST, $context_id);
    }

    /**
     * Displays the node toolbar for the indicated context. If the toolbar is undefined,
     * an empty string is returned.
     *
     * @param int $context_id The context to retrieve the node toolbar for, this
     *     defaults to the current context.
     * @see midcom_helper_toolbar::render()
     */
    function show_node_toolbar($context_id = null)
    {
        if ($this->_centralized_mode)
        {
            return;
        }
        echo $this->render_node_toolbar();
    }
    
    /**
     * Displays the host toolbar for the indicated context. If the toolbar is undefined,
     * an empty string is returned.
     *
     * @param int $context_id The context to retrieve the node toolbar for, this
     *     defaults to the current context.
     * @see midcom_helper_toolbar::render()
     */
    function show_host_toolbar($context_id = null)
    {
        if ($this->_centralized_mode)
        {
            return;
        }
        echo $this->render_host_toolbar();
    }

    /**
     * Displays the view toolbar for the indicated context. If the toolbar is undefined,
     * an empty string is returned.
     *
     * @param int $context_id The context to retrieve the node toolbar for, this
     *     defaults to the current context.
     * @see midcom_helper_toolbar::render()
     */
    function show_view_toolbar($context_id = null)
    {
        if ($this->_centralized_mode)
        {
            return;
        }
        echo $this->render_view_toolbar();
    }

    /**
     * Displays the combined MidCOM toolbar system
     *
     * @param int $context_id The context to retrieve the node toolbar for, this
     *     defaults to the current context.
     * @see midcom_helper_toolbar::render()
     */
    function show($context_id = null)
    {
        if (!$this->_enable_centralized)
        {
            return;
        }

        $this->_centralized_mode = true;

        echo "<div id=\"protoToolbar-{$this->type}\" style=\"display: none;\">\n";
        echo "    <div id=\"protoToolbar-{$this->type}-logos\">\n";
        echo "        <a href=\"" . $_MIDCOM->get_page_prefix() . "midcom-exec-midcom/about.php\">\n";
        echo "            <img src=\"" . MIDCOM_STATIC_URL . "/Javascript_protoToolkit/images/midgard-logo.png\" width=\"16\" height=\"16\" alt=\"Midgard\" />\n";
        echo "        </a>\n";
        echo "    </div>\n";
        echo "    <div id=\"protoToolbar-{$this->type}-content\">\n";
        echo "        <div id=\"item-page\" class=\"item\">\n";
        echo "            <span class=\"toolbar_list_class page\">". $_MIDCOM->i18n->get_string('page', 'midcom') . "</span>\n";
        echo $this->render_view_toolbar();
        echo "        </div>\n";
        echo "        <div id=\"item-folder\" class=\"item\">\n";
        echo "            <span class=\"toolbar_list_class folder\">". $_MIDCOM->i18n->get_string('folder', 'midcom') . "</span>\n";
        echo $this->render_node_toolbar();
        echo "        </div>\n";
        echo "        <div id=\"item-host\" class=\"item\">\n";
        echo "            <span class=\"toolbar_list_class host\">". $_MIDCOM->i18n->get_string('host', 'midcom') . "</span>\n";
        echo $this->render_host_toolbar();
        echo "        </div>\n";
        echo "    </div>\n";
        echo "     <div class=\"dragbar\"></div>\n";
        echo "</div>\n";
    }
}

?>
