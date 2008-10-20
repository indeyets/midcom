<?php
/**
 * Constants for the MidCOM System
 * 
 * @package midcom
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:application.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**#@+
 * MidCOM Property Keys
 */
define ('MIDCOM_PROP_DLOAD', 0); /* DEPRECATED ??? */
define ('MIDCOM_PROP_VERSION', 1);
define ('MIDCOM_PROP_PURECODE', 2);
define ('MIDCOM_PROP_NAME', 3);
define ('MIDCOM_PROP_ACL_PRIVILEGES', 4);
define ('MIDCOM_PROP_VGROUPS', 5);
/**#@-*/

/**#@+
 *MidCOM Default Error Codes (-> HTTP)
 */
define ('MIDCOM_ERROK',200);
define ('MIDCOM_ERRNOTFOUND',404);
define ('MIDCOM_ERRFORBIDDEN',403);
define ('MIDCOM_ERRAUTH',401);
define ('MIDCOM_ERRCRIT',500);
/**#@-*/

// MidCOM NAP URL Information Constants

/**#@+
 * MidCOM Meta Data Constants
 */
define ('MIDCOM_NAV_URL',0);
define ('MIDCOM_NAV_NAME',1);
define ('MIDCOM_NAV_NODEID',2);
define ('MIDCOM_NAV_INTERNAL',3); /* CURRENTLY DEPRECATED !!! */
define ('MIDCOM_NAV_VISIBLE',4); /* DEPRECATED AS OF 2.4.0 */
define ('MIDCOM_NAV_ID',5);
define ('MIDCOM_NAV_TYPE',6);
define ('MIDCOM_NAV_ADMIN',7);
define ('MIDCOM_NAV_SITE',8);
define ('MIDCOM_NAV_SCORE',9);
define ('MIDCOM_NAV_GUID',10);
define ('MIDCOM_NAV_TOOLBAR',11);
define ('MIDCOM_NAV_COMPONENT',12);
define ('MIDCOM_NAV_FULLURL',13);
define ('MIDCOM_NAV_PERMALINK', 14);
define ('MIDCOM_NAV_NOENTRY', 15);
define ('MIDCOM_NAV_OBJECT', 16);
define ('MIDCOM_NAV_RELATIVEURL', 17);
define ('MIDCOM_NAV_ABSOLUTEURL', 18);
//define ('MIDCOM_NAV_SUBNODES', 19); /* Yet unused. */
//define ('MIDCOM_NAV_LEAVES', 20);
//define ('MIDCOM_NAV_VIEWERGROUPS', 21); // Deprecated, using ACL instead
define ('MIDCOM_NAV_ACL', 22);
define ('MIDCOM_NAV_ICON', 23);
define ('MIDCOM_NAV_CONFIGURATION', 24);
define ('MIDCOM_NAV_LEAFID', 25);
define ('MIDCOM_NAV_SORTABLE', 26);

define ('MIDCOM_META_CREATOR',100); /* DEPRECATED AS OF 2.4.0 */
define ('MIDCOM_META_CREATED',101); /* DEPRECATED AS OF 2.4.0 */
define ('MIDCOM_META_EDITOR',102); /* DEPRECATED AS OF 2.4.0 */
define ('MIDCOM_META_EDITED',103); /* DEPRECATED AS OF 2.4.0 */
/**#@-*/

/**#@+
 * MidCOM Component Context Keys
 */
define ('MIDCOM_CONTEXT_ANCHORPREFIX',0);
define ('MIDCOM_CONTEXT_SUBSTYLE',1);
define ('MIDCOM_CONTEXT_REQUESTTYPE',2);
define ('MIDCOM_CONTEXT_ROOTTOPIC',3);
define ('MIDCOM_CONTEXT_CONTENTTOPIC',4);
define ('MIDCOM_CONTEXT_COMPONENT',6);
define ('MIDCOM_CONTEXT_OUTPUT',7);
define ('MIDCOM_CONTEXT_NAP',8);
define ('MIDCOM_CONTEXT_PAGETITLE',9);
define ('MIDCOM_CONTEXT_LASTMODIFIED', 10);
define ('MIDCOM_CONTEXT_PERMALINKGUID', 11);
define ('MIDCOM_CONTEXT_URI', 12);
define ('MIDCOM_CONTEXT_HANDLERID', 13);

/**#@-*/

/**
 * INTERNAL Context Keys, not accessible from outside midcom_application.
 */
define ('MIDCOM_CONTEXT_CUSTOMDATA', 1000);

/**#@+
 * URL PARSER Object Types
 */
define ('MIDCOM_HELPER_URLPARSER_TOPIC',0);
define ('MIDCOM_HELPER_URLPARSER_ARTICLE',1);
define ('MIDCOM_HELPER_URLPARSER_ATTACHMENT',2);
define ('MIDCOM_HELPER_URLPARSER_KEY',3);
define ('MIDCOM_HELPER_URLPARSER_VALUE',4);
/**#@-*/

/**#@+
 * Debugger
 */
define ('MIDCOM_LOG_DEBUG', 4);
define ('MIDCOM_LOG_INFO', 3);
define ('MIDCOM_LOG_WARN', 2);
define ('MIDCOM_LOG_ERROR', 1);
define ('MIDCOM_LOG_CRIT', 0);
/**#@-*/

/**#@+
 * Client Status Array Keys
 */
define ('MIDCOM_CLIENT_MOZILLA',0);
define ('MIDCOM_CLIENT_IE',1);
define ('MIDCOM_CLIENT_OPERA',2);
define ('MIDCOM_CLIENT_NETSCAPE',3);
define ('MIDCOM_CLIENT_UNIX',10);
define ('MIDCOM_CLIENT_MAC',11);
define ('MIDCOM_CLIENT_WIN',12);
/**#@-*/

/**#@+
 * Request Types
 */
define ('MIDCOM_REQUEST_CONTENT',0);
define ('MIDCOM_REQUEST_CONTENTADM',1);
define ('MIDCOM_REQUEST_COMPONENTADM',2);
/**#@-*/

/**#@+
 * Data Manager
 */
define ('MIDCOM_DATAMGR_EDITING',0);
define ('MIDCOM_DATAMGR_SAVED',1);
define ('MIDCOM_DATAMGR_FAILED',2);
define ('MIDCOM_DATAMGR_CANCELLED',3);
define ('MIDCOM_DATAMGR_CANCELLED_NONECREATED',4);
define ('MIDCOM_DATAMGR_CREATING',5);
define ('MIDCOM_DATAMGR_CREATEFAILED',6);
define ('MIDCOM_DATAMGR_SAVE_DELAYED',7);
/**#@-*/

/**#@+
 * MidCOM Core Status Codes
 */
define ('MIDCOM_STATUS_PREPARE',0);
define ('MIDCOM_STATUS_CANHANDLE',1);
define ('MIDCOM_STATUS_HANDLE',2);
define ('MIDCOM_STATUS_CONTENT',3);
define ('MIDCOM_STATUS_CLEANUP',4);
define ('MIDCOM_STATUS_ABORT',5);
/**#@-*/

/**#@+
 * MidCOM NAP Sorting Modes
 */
define ('MIDCOM_NAVORDER_DEFAULT', 0);
define ('MIDCOM_NAVORDER_ARTICLESFIRST', 1);
define ('MIDCOM_NAVORDER_TOPICSFIRST', 2);
define ('MIDCOM_NAVORDER_SCORE', 3);
/**#@-*/

/**#@+
 * MidCOM Toolbar Service
 */

/**
 * Element URL
 *
 * @see midcom_helper_toolbar
 */
define ('MIDCOM_TOOLBAR_URL', 0);
/**
 * Element Label
 *
 * @see midcom_helper_toolbar
 */
define ('MIDCOM_TOOLBAR_LABEL', 1);
/**
 * Element Helptext
 *
 * @see midcom_helper_toolbar
 */
define ('MIDCOM_TOOLBAR_HELPTEXT', 2);
/**
 * Element Icon (Relative URL to MIDCOM_STATIC_URL root),
 * e.g. 'stock-icons/16x16/attach.png'.
 *
 * @see midcom_helper_toolbar
 */
define ('MIDCOM_TOOLBAR_ICON', 3);
/**
 * Element Enabled state
 *
 * @see midcom_helper_toolbar
 */
define ('MIDCOM_TOOLBAR_ENABLED', 4);
/**
 * Original element URL as defined by the callee.
 *
 * @see midcom_helper_toolbar
 */
define ('MIDCOM_TOOLBAR__ORIGINAL_URL', 5);
/**
 * Options array.
 *
 * @see midcom_helper_toolbar
 */
define ('MIDCOM_TOOLBAR_OPTIONS', 6);
/**
 * Set this to true if you just want to hide this element
 * from the output.
 *
 * @see midcom_helper_toolbar
 */
define ('MIDCOM_TOOLBAR_HIDDEN', 7);

/**
 * Add a subobject here if you want to have nested menus.
 *
 * @see midcom_helper_toolbar
 */
define ('MIDCOM_TOOLBAR_SUBMENU', 8);

/**
 * Use an HTTP POST form request if this is true. The default is not to do so.
 *
 * @see midcom_helper_toolbar
 */
define ('MIDCOM_TOOLBAR_POST', 9);

/**
 * Optional arguments for a POST request.
 *
 * @see midcom_helper_toolbar
 */
define ('MIDCOM_TOOLBAR_POST_HIDDENARGS', 10);

/**
 * Identifier for a node toolbar for a request context.
 *
 * @see midcom_services_toolbars
 */
define ('MIDCOM_TOOLBAR_NODE', 100);

/**
 * Identifier for a view toolbar for a request context.
 *
 * @see midcom_services_toolbars
 */
define ('MIDCOM_TOOLBAR_VIEW', 101);

/**
 * Identifier for a host toolbar for a request context.
 *
 * @see midcom_services_toolbars
 */
define ('MIDCOM_TOOLBAR_HOST', 104);

/**
 * Identifier for a help toolbar for a request context.
 *
 * @see midcom_services_toolbars
 */
define ('MIDCOM_TOOLBAR_HELP', 105);

/**
 * Identifier for a custom object toolbar.
 *
 * @see midcom_services_toolbars
 */
define ('MIDCOM_TOOLBAR_OBJECT', 102);
/**
 * The accesskey for this button
 *
 * @see midcom_services_toolbars
 */
define ('MIDCOM_TOOLBAR_ACCESSKEY', 103);


/**
 * Identifier for a node metadata for a request context.
 *
 * @see midcom_services_metadata
 */
define ('MIDCOM_METADATA_NODE', 100);

/**
 * Identifier for a view metadata for a request context.
 *
 * @see midcom_services_metadata
 */
define ('MIDCOM_METADATA_VIEW', 101);

/**#@-*/


/**#@+
 * MidCOM Privilege System
 */

/**
 * Allow the privilege.
 */
define ('MIDCOM_PRIVILEGE_ALLOW', 1);
/**
 * Deny the privilege.
 */
define ('MIDCOM_PRIVILEGE_DENY', 2);
/**
 * Inherit the privilege from the parent.
 */
define ('MIDCOM_PRIVILEGE_INHERIT', 3);

/**
 * Privilege array name entry
 */
define ('MIDCOM_PRIVILEGE_NAME', 100);
/**
 * Privilege array assignee entry
 */
define ('MIDCOM_PRIVILEGE_ASSIGNEE', 101);
/**
 * Privilege array value entry
 */
define ('MIDCOM_PRIVILEGE_VALUE', 102);

/**
 * Magic scope value for privileges assigned to EVERYONE
 */
define ('MIDCOM_PRIVILEGE_SCOPE_EVERYONE', 0);
/**
 * Magic scope value for privileges assigned to all unauthenticated users
 */
define ('MIDCOM_PRIVILEGE_SCOPE_ANONYMOUS', 10);
/**
 * Magic scope value for privileges assigned to all authenticated users
 */
define ('MIDCOM_PRIVILEGE_SCOPE_USERS', 10);
/**
 * Starting scope value for root groups
 */
define ('MIDCOM_PRIVILEGE_SCOPE_ROOTGROUP', 100);
/**
 * Default scope value for virtual groups.
 */
define ('MIDCOM_PRIVILEGE_SCOPE_VGROUPS', 65000);
/**
 * Magic scope value for owner privileges.
 */
define ('MIDCOM_PRIVILEGE_SCOPE_OWNER', 65050);
/**
 * Magic scope value for user privileges.
 */
define ('MIDCOM_PRIVILEGE_SCOPE_USER', 65100);

/**#@-*/

/**#@+
 * MidCOM Operation Bitfield constant, used for the definition of watch operations
 * in component manifests.
 *
 * @see midcom_core_manifest
 */

/**
 * Matches all known operations.
 */
define ('MIDCOM_OPERATION_ALL', 0xFFFFFFFF);

/**
 * DBA object creation. This excludes parameter operations.
 */
define ('MIDCOM_OPERATION_DBA_CREATE', 0x1);

/**
 * DBA object update, this includes all attachment and parameter operations.
 */
define ('MIDCOM_OPERATION_DBA_UPDATE', 0x2);

/**
 * DBA object deletion. This excludes parameter operations.
 */
define ('MIDCOM_OPERATION_DBA_DELETE', 0x4);

/**
 * DBA object import. This includes parameters & attachments.
 */
define ('MIDCOM_OPERATION_DBA_IMPORT', 0x8);

/**
 * All known DBA operations.
 */
define ('MIDCOM_OPERATION_DBA_ALL', 0xF);

/**#@-*/

/**#@+
 * MidCOM Cron constants
 *
 * @see midcom_services_cron
 */

/**
 * Execute once every minute.
 */
define ('MIDCOM_CRON_MINUTE', 10);

/**
 * Execute once every hour.
 */
define ('MIDCOM_CRON_HOUR', 20);

/**
 * Execute once every day.
 */
define ('MIDCOM_CRON_DAY', 30);

/**
 * The last non-critical MidCOM error message. This is slowly superseded
 * by having all errors call generate_error.
 *
 * @global string $GLOBALS['midcom_errstr']
 */
$GLOBALS['midcom_errstr'] = '';

/**
 * The main MidCOM application class.
 *
 * @global midcom_application $GLOBALS['midcom']
 */
$GLOBALS['midcom'] = null;

/**
 * The MidCOM Logging interface. Note, that even though this variable
 * is called "debugger", it is actually a full-blown logging solution
 * which should not be turned off any longer. Instead, step up the
 * error reporting level to something above MIDCOM_LOG_DEBUG.
 *
 * @global midcom_debug $GLOBALS['midcom_debugger']
 */
$GLOBALS['midcom_debugger'] = null;

/**
 * This is a component specific global data storage area, which should
 * be used for stuff like default configurations etc. thus avoiding the
 * pollution of the global namespace. Each component has its own array
 * in the global one, allowing storage of arbitrary data indexed by arbitrary
 * keys in there. The component-specific arrays are indexed by their
 * name.
 *
 * Note, that this facility is quite a different thing to the component
 * context from midcom_application, even if it has many similar applications.
 * The component context is only available and valid for components, which
 * are actually handling a request. This data storage area is static to the
 * complete component and shared over all subrequests and therefore suitable
 * to hold default configurations, -schemas and the like.
 *
 * @global Array $GLOBALS['midcom_component_data']
 */
$GLOBALS['midcom_component_data'] = Array();

/**
 * Global instance of the Caching service. This is also available as $midcom->cache.
 *
 * @global midcom_services_cache $GLOBALS['midcom_cache']
 */
$GLOBALS['midcom_cache'] = null;

/**#@-*/
?>