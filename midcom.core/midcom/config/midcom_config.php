<?php
/**
 * Core configuration defaults.
 * @package midcom
 *
 * The global variable <i>midcom_config</i> will hold the current values of all these
 * options.
 *
 * As always, you must not change this file. Instead, you have two levels of customization,
 * merged in the order listed here:
 *
 * <b>Site-specific configuration:</b>
 *
 * MidCOM will include the file <i>/etc/midgard/midcom.conf</i> which must be a regular
 * PHP file. You may populate the global array $midcom_config_site in this file. It should
 * list all options that apply to all MidCOM installations (like the Cache backend selection
 * or the indexer host).
 *
 * Example:
 *
 * <code>
 * <?php
 * $GLOBALS['midcom_config_site']['cache_module_content_backend'] =
 *     Array ('directory' => 'content/', 'driver' => 'dba');
 * ?>
 * </code>
 *
 * <b>Instance-specifc configuration:</b>
 *
 * After including the site itself, MidCOM also merges the contents of the global array
 * $midcom_config_local, which may hold configuration data for the website itself.
 *
 * These settings must be set for all sites:
 * - midcom_root_topic_guid
 *
 * You will usually include these lines somewhere before actually including MidCOM.
 *
 * <code>
 * <?php
 * $GLOBALS['midcom_config_local']['midcom_root_topic_guid'] = '123456789...';
 * ?>
 * </code>
 *
 * <b>Configuration setting overview:</b>
 *
 * The following configuration options are available for the MidCOM core along
 * with their defaults, shown in alphabetical order:
 *
 * <b>Authentication configuration</b>
 *
 * - <b>bool allow_sudo:</b> Set this to true (the default) to allow components to
 *   request super user privileges for certain operations. This is mainly used to allow
 *   anonymous access to the system without having to store a user account everywhere.
 * - <b>int auth_backend:</b> The authentication backend to use, the "simple"
 *   backend is used as a default.
 * - <b>bool auth_check_client_ip:</b> Controlw whether to check the client IP address
 *   on each subsequent request when authentication a user. This is enabled by default
 *   as it will make session hijacking much harder. You should not turn it off unless
 *   you have very good reasons to do.
 * - <b>int auth_login_session_timeout:</b> The login session timeout to use, this
 *   defaults to 3600 seconds (1 hour).
 * - <b>int auth_frontend:</b> The authentication frontend to use, the "form" frontend
 *   is used by default.
 * - <b>string auth_sitegroup_mode:</b> This parameter determines in which sitegroup
 *   context the MidCOM authentication should work in. If set to 'sitegrouped', the
 *   system automatically works within the current sitegroup, appending the corresponding
 *   suffix. If set to 'not-sitegrouped', no processing is done, which means the user
 *   has to specify the correct sitegroup always. The setting 'auto', which is the default,
 *   uses sitegrouped if the current host is in a sitegroup (that is, $_MIDGARD['sitegroup']
 *   is nonzero) or non-sitegrouped mode if we are in SG0.
 * - <b>int auth_login_form_httpcode</b>: HTTP return code used in MidCOM login screens,
 *   either 403 (403 Forbidden) or 200 (200 OK), defaulting to 403.
 * - <b>bool auth_openid_enable:</b> Whether to enable OpenID authentication handled with
 *   the net.nemein.openid library
 *
 * <b>Authentication Backend configuration: "simple"</b>
 *
 * - <b>auth_backend_simple_cookie_id:</b> The ID appended to the cookie prefix, separating
 *   auth cookies for different sites. Defaults to the GUID of the current host.
 * - <b>auth_backend_simple_cookie_path:</b> Controls the valid path of the cookie,
 *   defaults to $_MIDGARD['self'].
 * - <b>auth_backend_simple_cookie_domain:</b> Controls the valid domain of the cookie.
 *   If it is set to null (the default), no domain is specified in the cookie, making
 *   it a traditional site-specific session cookie. If it is set, the domain parameter
 *   of the cookie will be set accordingly.
 *
 * <b>Cache configuration</b>
 *
 * - <b>array cache_autoload_queue:</b> The cache module loading queue during startup, you
 *   should normally have no need to change this (unless you want to add your own caching modules,
 *   in which case you have to ensure that the loading queue of MidCOM itself (as seen in this
 *   file) is not changed.
 * - <b>string cache_base_directory:</b> The directory where to place cache files for MidCOM.
 * 	 This defaults to /tmp/ (note the trailing slash) as this is writable everywhere.
 *
 * - <b>Array cache_module_acl:</b> If this is non-null and an array, MidCOM will create a memcached
 *   caching instance to buffer ACL reads from the DB. You set this parameter to the configuration
 *   array to use (use an empty array for the defaults). See the memcached backend for details.
 *   <b>THIS PARAMETER (cache_module_acl) IS NOT IN USE</b>
 * - <b>Array cache_module_content_backend:</b> The configuration of the content cache backend.
 *   Check the documentation of midcom_services_cache_backend of what options are available here.
 *   In general, you should use this only to change the backend driver.
 *   In all other cases you should leave this option untouched. The defaults are to store all
 *   cache databases into the 'content/' subdirectory of the cache base directory, using the
 *   dba driver.
 * - <b>string cache_module_content_name:</b> The identifier, the content cache should use for
 *   naming the files/directories it creates. This defaults to a string constructed out of the
 *   host's name, prot and prefix. You should only change this if you run multiple MidCOM
 *   sites on the same host.
 * - <b>bool cache_module_content_multilang:</b> Set this to true if you need a cache that honors the current
 *   client language. Set this to false, if you don't have a multilingual site and the language
 *   is fixed through a static $i18n->set_language call before code-init. This will improve
 *   the cache's performance.
 * - <b>bool cache_module_content_uncached:</b> Set this to true if you want the site to run in an uncached
 * 	 mode. This is different from cache_disable in that the regular header preprocessing is
 *   done anyway, allowing for browser side caching. Essentially, the computing order is the
 *   same (no_cache for example is considered like usual), but the cache file is not stored.
 *   This defaults to false.
 * - <b>string cache_module_content_headers_strategy:</b> Valid values are<br/>
 *   'no-cache' activates no-cache mode that actively tries to circumvent all caching<br/>
 *   'revalidate' is the default which sets 'must-revalidate' and presses the issue by setting Expires to current time<br/>
 *   'public' and 'private' enable caching with the cache-control header of the same name, default expiry timestamps are generated using the cache_module_content_default_lifetime
 * - <b>int cache_module_content_default_lifetime:</b> How many seconds from now to set the default Expires header to, defaults to one minute
 * - <b>Array cache_module_nap_backend:</b> The configuration of the nap/metadata cache backend.
 *   Check the documentation of midcom_services_cache_backend of what options are available here.
 *   In general, you should use this only to change the backend driver.
 *   In all other cases you should leave this option untouched. The defaults are to store all
 *   cache databases into the 'nap/' subdirectory of the cache base directory, using the
 *   dba driver. The databases are named after the root topic guid, which should be sufficient
 *   in all cases. If you really want to separate things here, use different directories for
 *   the backends.
 * - <b>int cache_module_nap_metadata_cachesize:</b> The number of Metadata objects that may be kept
 *   in memory simultaneously. Caching strategy is undefined at this time, it removes one randomly
 *   chosen element. Defaults to 75.
 * - <b>string cache_module_memcache_backend:</b> The cache backend to use for the memcache caching
 *   module. The default is null, which disables the module entirely. This is the default. If you
 *   have both memcached and the memcache PHP extension installed, set this to 'memcached', to enable
 *   the cache.
 * - <b>Array cache_module_memcache_backend_config:</b> The backend configuration to use if a backend
 *   was specified. See the individual backend documentations for more information about the allowed
 *   option set. This defaults to an empty array.
 * - <b>Array cache_module_memcache_data_groups:</b> The data groups avaialable for the memcache module.
 *   You should normally not have to touch this, see the memcache module documentation for details.
 *   This defaults to Array('ACL', 'PARENT').
 * - <b>string cache_module_phpscripts_directory:</b> The directory used for systemwide caching
 *   of PHP scripts (for example for the DBA intermediate classes or the component manifests).
 *
 * See also midcom_services_cache, the midcom_services_cache_backend class hierarchy and
 * the midcom_services_cache_module class hierarchy.
 *
 * <b>Indexer configuration</b>
 *
 * - <b>string indexer_index_name:</b> The default Index to use when indexing the website. This defaults to
 *   a string constructed out of the host's name, prot and prefix. You should only change this if you run
 *   the same MidCOM site across multiple hosts.
 * - <b>string indexer_backend:</b> The default indexer backend to use. This defaults to the false,
 *   indicating that <i>no</i> indexing should be done. Right now, the xmltcp backend in combination
 *   with the Lucene Indexer daemon is recommended.
 * - <b>int indexer_reindex_memorylimit:</b> The memory limit to use when reindexing the entire site.
 *   This is required, as reindexing can require quite some amount of memory, as the complete NAP
 *   cache has to be loaded for example and binary indexing can take some memory too. Defaults to 250 MB.
 * - <b>indexer_reindex_allowed_ips:</b> Array of IPs that don't need to basic authenticate themselves
 *   to run MidCOM reindexing or cron.
 *
 * <b>Indexer backend configuration: XMLShell module</b>
 *
 * - <b>string indexer_xmlshell_executable:</b> The executable that is used to interface with the
 *   Indexer. This must be a full path and currently has no default and must be set..
 * - <b>string indexer_xmlshell_working_directory:</b> If set, this is the working directory to which
 *   a chdir is made before execution of the script file. This has no default and must be set.
 *
 * <b>Indexer backend configuration: XMLTCP module</b>
 *
 * - <b>string indexer_xmltcp_host:</b> The host name or IP address where the indexer daemon is running.
 *   This defaults to "tcp://127.0.0.1", which is the default bind address of the daemon.
 * - <b>int indexer_xmltcp_port:</b> The port to which to connect. This defaults to 2222, which is the
 * 	 default port of the daemon.
 *
 * <b>Logging configuration</b>
 *
 * - <b>string log_filename:</b> The filename to dump logging messages to, this
 * 	 defaults to /tmp/debug.log.
 * - <b>int log_level:</b> The logging level to use when starting up the logger, set to
 *   MIDCOM_LOG_ERROR by default. You cannot use the MIDCOM* constants when setting
 *   micdom_config_local, as they are not defined at that point. Use 0 for CRITICAL,
 *   1 for ERROR, 2 for WARING, 3 for INFO and 4 for DEBUG level logging.
 * - <b>bool log_tailurl_enable:</b> Set this to true to enable the on-site interface to
 *   the logging system. See the URL method log of midcom_application for details. Turned off
 *   by default for security reasons.
 *
 * <b>MidCOM Core configuration</b>
 *
 * - <b>string midcom_ais_url:</b> The fully qualified URL to the AIS system. This is used
 *   for building AIS links on-site. A trailing slash is required. It defaults to the
 *   absolute local URL '/midcom-admin/ais/'. If an absolute local URL is
 *   given to this
 *   value, the full URL of the current host is prefixed to its value, so that this
 *   configuration key can be used for Location headers. You must not use a relative
 *   URL. This key will be completed by the MidCOM Application constructor, before
 *   that, it might contain an URL which is not suitable for relcoations.
 * - <b>string midcom_prefix:</b> Any prefix you might have on your site.
 *   Defaults to none.
 * - <b>GUID midcom_root_topic_guid:</b> This is the GUID of the topic we should handle.
 *   This must be set on a per-site basis, otherwise MidCOM won't start up.
 * - <b>string midcom_sgconfig_basedir:</b> The base snippetdir where the current
 *   sites' configuration is stored. This defaults to "/sitegroup-config" which will
 *   result in the original default shared sitegroupwide configuration.
 * - <b>string midcom_site_url:</b> The fully qualified URL to the Website, used in AIS to
 *   build on-site links. A trailing slash is required. It defaults to '/'.
 *   If an absolute local URL is given to this
 *   value, the full URL of the current host is prefixed to its value, so that this
 *   configuration key can be used for Location headers. You must not use a relative
 *   URL. This key will be completed by the MidCOM Application constructor, before
 *   that, it might contain an URL which is not suitable for relcoations.
 * - <b>string midcom_tempdir:</b> A temporary directory that can be used when components
 *   need to write out files. Defaluts to '/tmp'.
 * - <b>int midcom_temporary_resource_timeout:</b> Temporary resources will be deleted
 *   after the amount of seconds set in this options. It defaults to 86400 = 1 day.
 *   The corresponding cron-job is run on hourly.
 *
 * <b>RCS system</b>
 *
 *  <b>string midcom_services_rcs_bin_dir</b>: the prefix for the rcs utilities (default: /usr/bin)
 *  <b>string midcom_services_rcs_root </b>: the directory where the rcs files get placed. (default: must be set!)
 *  <b>boolean midcom_services_rcs_enable</b>:  if set, midcom will fail hard if the rcs service is not operational. (default: false)
 *  See also: http://www.midgard-project.org/documentation/midcom-services-rcs/
 *
 * <b>Style Engine</b>
 *
 * - <b>Array styleengine_default_styles:</b> Use this array to set site-wide default
 *   styles to be used for the components. This is an array indexing component name to
 *   style path. Components not present in this array use the default style delivered
 *   with the component. Any style set directly on a topic or inherited to it will
 *   override these settings. This defaults to an empty Array.
 *
 * <b>Toolbars System</b>
 *
 * The CSS classes and IDs used by the toolbars service can be configured using these
 * options:
 *
 * - <b>string toolbars_host_style_class:</b> defaults to "midcom_toolbar host_toolbar"
 * - <b>string toolbars_host_style_id:</b> defaults to ""
 * - <b>string toolbars_node_style_class:</b> defaults to "midcom_toolbar node_toolbar"
 * - <b>string toolbars_node_style_id:</b> defaults to ""
 * - <b>string toolbars_view_style_class:</b> defaults to midcom_toolbar view_toolbar
 * - <b>string toolbars_view_style_id:</b> defaults to ""
 * - <b>string toolbars_object_style_class:</b> defaults to midcom_toolbar object_toolbar
 * - <b>string toolbars_css_path:</b> this defaults to MIDCOM_ROOT_URL/midcom.services.toolbars/toolbar.css
 *   and is used to set the css for the toolbars used with onsite editing.
 * - <b>boolean toolbars_enable_centralized:</b> defaults to true, whether to enable the centralized,
 *   javascript-floating MidCOM toolbar that users can display with $_MIDCOM->toolbars->show();
 *
 * <b>Utility Programs</b>
 *
 * The various paths set here lead to the utility programs required by MidCOM, both
 * mandatory and optional applications are listed here. To indicate that a certain
 * application is unavailable, set it to null. It is recommended to set this in the
 * /etc/midgard/midcom.conf file. The defaults assume that the files are within the
 * $PATH of the Apache user and should be sufficient in most cases. Package mainatainers
 * are encouraged to make the paths explicit.
 *
 * - <b>string utility_imagemagick_base:</b> The base path of the ImageMagick executables,
 *   the tools <i>mogrify</i>, <i>identify</i> and <i>convert</i> are needed for almost
 *   all kinds of image operations in MidCOM and have to be present therefore. The path
 *   entered here requires a trailing slash.
 * - <b>string utility_jpegtran:</b> JPEGTran is used to do lossless rotation of JPEG
 *   images for automatic EXIF rotation in n.s.photos for example. If unavailable,
 *   there is an automatic fallback to imagemagick.
 * - <b>string utility_unzip:</b> The unzip utility, used for bulk uploads.
 * - <b>string utility_gzip:</b> The gzip utility, used for bulk uploads.
 * - <b>string utility_tar:</b> The tar utility, used for bulk uploads.
 * - <b>string utility_jhead:</b> The jhead utility, used as a fallback to read EXIF
 *   information if the PHP implementation (pre 4.3) is buggy.
 * - <b>string utility_find:</b> The Find utility is used for bulk upload preprocessing
 *   and the like.
 * - <b>string utility_file:</b> Utility to identify all kinds of uploaded files.
 * - <b>string utility_catdoc:</b> Transforms Word Documents into text for indexing.
 * - <b>string utility_pdftotext:</b> Transforms PDF Documents into text for indexing.
 * - <b>string utility_unrtf:</b> Transforms RTF Documents into text files for indexing.
 * - <b>string utility_diff:</b> The diff utility. Used to create diffs.
 * - <b>string utility_rcs:</b> The rcs revision controlsystem is needed for versioning.
 *
 * <b>Multilingual content settings (NAP & DBA)</b>
 *
 * These options manage how multilingual content is displayed in the MidCOM environment.
 *
 * - <b>bool show_untranslated_content:</b> This flag indicates whether content not available
 * in current content language should be shown on the site. The flag is ignored on sites that
 * are set to use the default language (lang0)
 *
 * <b>Visibility settings (NAP and DBA)</b>
 *
 * Note: It is not recommended to activate these two options at this time, as the metadata
 * framework is not yet rewritten to a more efficient MidgardSchema driven solution. With
 * larger sites, having Metadata active can lead to serious performance impacts.
 *
 * - <b>bool show_hidden_objects:</b> This flag indicates whether objects that are
 *   invisible either by explicit hiding or by their scheduling should be shown anyway.
 *   This defaults to true at this time (due to Metadata performance problems).
 * - <b>bool show_unapproved_objects:</b> This flag indicates whether objects should be
 *   shown even if they are not approved. This defaults to true.
 *
 * <b>Geopositioning settings</b>
 *
 * - <b>bool positioning_enable:</b> This flag indicates wether components should start
 * tracking and displaying the geographical position where they were created.
 *
 * @package midcom
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:midcom_config.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM core configuration option defaults.
 *
 * @global Array $GLOBALS['midcom_config_default']
 */
$GLOBALS['midcom_config_default'] = Array();

// Initialize Helpers
$phpversion = phpversion();
$host = new midgard_host();
$host->get_by_id($_MIDGARD['host']);
$unique_host_name = "{$host->name}_{$host->port}_" . str_replace('/', '_', $host->prefix);

// Authentication configuration
$GLOBALS['midcom_config_default']['auth_backend'] = 'simple';
$GLOBALS['midcom_config_default']['auth_login_session_timeout'] = 3600;
$GLOBALS['midcom_config_default']['auth_frontend'] = 'form';
$GLOBALS['midcom_config_default']['auth_sitegroup_mode'] = 'auto';
$GLOBALS['midcom_config_default']['auth_check_client_ip'] = true;
$GLOBALS['midcom_config_default']['auth_allow_sudo'] = true;
$GLOBALS['midcom_config_default']['auth_login_form_httpcode'] = 403;
$GLOBALS['midcom_config_default']['auth_openid_enable'] = false;

$GLOBALS['midcom_config_default']['auth_backend_simple_cookie_id'] = $host->guid;
$GLOBALS['midcom_config_default']['auth_backend_simple_cookie_path'] = $_MIDGARD['self'];
$GLOBALS['midcom_config_default']['auth_backend_simple_cookie_domain'] = null;

// Where to redirect the user after a successful login
$GLOBALS['midcom_config_default']['login_redirect_url'] = $_MIDGARD['self'];

// Cache configuration
$GLOBALS['midcom_config_default']['cache_base_directory'] = '/tmp/';
$GLOBALS['midcom_config_default']['cache_autoload_queue'] = Array('content', /*'nap', */ 'phpscripts', 'memcache');

// Content Cache
$GLOBALS['midcom_config_default']['cache_module_content_name'] = $unique_host_name;
$GLOBALS['midcom_config_default']['cache_module_content_backend'] = Array('driver' => 'sqlite');
// Defaults:
// $GLOBALS['midcom_config_default']['cache_module_content_backend'] = Array ('directory' => 'content/', 'driver' => 'dba');
// $GLOBALS['midcom_config_default']['cache_module_content_multilang'] = true;
// $GLOBALS['midcom_config_default']['cache_module_content_uncached'] = false;
$GLOBALS['midcom_config_default']['cache_module_content_uncached'] = false; /* Temporary until the cache module is back working correctly */
$GLOBALS['midcom_config_default']['cache_module_content_headers_strategy'] = 'revalidate';
$GLOBALS['midcom_config_default']['cache_module_content_default_lifetime'] = 60; // Seconds, added to gmdate() for expiry timestamp (in case no other expiry is set)

// NAP / Metadata Cache
$GLOBALS['midcom_config_default']['cache_module_nap_backend'] = Array(); /* Auto-Detect */
$GLOBALS['midcom_config_default']['cache_module_nap_metadata_cachesize'] = 75;
// Defaults:
// $GLOBALS['midcom_config_default']['cache_module_nap_backend'] =  Array ('directory' => 'nap/', 'driver' => 'dba');

//Memory Caching Daemon
$GLOBALS['midcom_config_default']['cache_module_memcache_backend'] = 'sqlite';
$GLOBALS['midcom_config_default']['cache_module_memcache_backend_config'] = Array();
$GLOBALS['midcom_config_default']['cache_module_memcache_data_groups'] = Array('ACL', 'PARENT', 'jscss_merged');

// Generated class cache directory
$GLOBALS['midcom_config_default']['cache_module_phpscripts_directory'] = 'phpscripts/';


// CRON Service configuration
$GLOBALS['midcom_config_default']['cron_day_hours'] = 0;
$GLOBALS['midcom_config_default']['cron_day_minutes'] = 0;
$GLOBALS['midcom_config_default']['cron_hour_minutes'] = 30;


// I18n Subsystem configuration
$GLOBALS['midcom_config_default']['i18n_available_languages'] = null;
$GLOBALS['midcom_config_default']['i18n_fallback_language'] = 'en';

// Indexer Configuration
$GLOBALS['midcom_config_default']['indexer_backend'] = false;
$GLOBALS['midcom_config_default']['indexer_index_name'] = $unique_host_name;
$GLOBALS['midcom_config_default']['indexer_reindex_memorylimit'] = 250;
$GLOBALS['midcom_config_default']['indexer_reindex_allowed_ips'] = Array();

// XMLTCP indexer backend (THE RECOMMENDED ONE)
$GLOBALS['midcom_config_default']['indexer_xmltcp_host'] = "127.0.0.1";
$GLOBALS['midcom_config_default']['indexer_xmltcp_port'] = 2222;

// XMLShell indexer backend configuration
$GLOBALS['midcom_config_default']['indexer_xmlshell_executable'] = '';
$GLOBALS['midcom_config_default']['indexer_xmlshell_working_directory'] = '';


// Logging Configuration
$GLOBALS['midcom_config_default']['log_filename'] = '/tmp/midcom.log';
$GLOBALS['midcom_config_default']['log_level'] = MIDCOM_LOG_ERROR;
$GLOBALS['midcom_config_default']['log_tailurl_enable'] = false;

// Core configuration
$GLOBALS['midcom_config_default']['midcom_ais_url'] = '/midcom-admin/ais/';
$GLOBALS['midcom_config_default']['midcom_prefix'] = '';
$GLOBALS['midcom_config_default']['midcom_root_topic_guid'] = '';
$GLOBALS['midcom_config_default']['midcom_sgconfig_basedir'] = '/sitegroup-config';
$GLOBALS['midcom_config_default']['midcom_site_url'] = '/';
$GLOBALS['midcom_config_default']['midcom_tempdir'] = '/tmp';
$GLOBALS['midcom_config_default']['midcom_temporary_resource_timeout'] = 86400;

// MultiLang system
$GLOBALS['midcom_config_default']['show_untranslated_content'] = false;

// Visibility settings (NAP)
$GLOBALS['midcom_config_default']['show_hidden_objects'] = true;
$GLOBALS['midcom_config_default']['show_unapproved_objects'] = true;
$GLOBALS['midcom_config_default']['i18n_multilang_strict'] = false;

// Style Engine defaults
$GLOBALS['midcom_config_default']['styleengine_default_styles'] = Array();

// Toolbars service
$GLOBALS['midcom_config_default']['toolbars_host_style_class'] = 'midcom_toolbar host_toolbar';
$GLOBALS['midcom_config_default']['toolbars_host_style_id'] = null;
$GLOBALS['midcom_config_default']['toolbars_node_style_class'] = 'midcom_toolbar node_toolbar';
$GLOBALS['midcom_config_default']['toolbars_node_style_id'] = null;
$GLOBALS['midcom_config_default']['toolbars_view_style_class'] = 'midcom_toolbar view_toolbar';
$GLOBALS['midcom_config_default']['toolbars_view_style_id'] = null;
$GLOBALS['midcom_config_default']['toolbars_help_style_class'] = 'midcom_toolbar help_toolbar';
$GLOBALS['midcom_config_default']['toolbars_help_style_id'] = null;
$GLOBALS['midcom_config_default']['toolbars_object_style_class'] = 'midcom_toolbar object_toolbar';
$GLOBALS['midcom_config_default']['toolbars_css_path'] = MIDCOM_STATIC_URL . "/Javascript_protoToolkit/styles/protoToolbar.css";
$GLOBALS['midcom_config_default']['toolbars_simple_css_path'] = MIDCOM_STATIC_URL . "/midcom.services.toolbars/simple.css";
$GLOBALS['midcom_config_default']['toolbars_enable_centralized'] = true;

// Service implementation defaults
$GLOBALS['midcom_config_default']['service_midcom_core_service_urlparser'] = 'midcom_core_service_implementation_urlparsertopic';
$GLOBALS['midcom_config_default']['service_midcom_core_service_urlgenerator'] = 'midcom_core_service_implementation_urlgeneratori18n';

// Utilities
$GLOBALS['midcom_config_default']['utility_imagemagick_base'] = '';
$GLOBALS['midcom_config_default']['utility_jpegtran'] = 'jpegtran';
$GLOBALS['midcom_config_default']['utility_unzip'] = 'unzip';
$GLOBALS['midcom_config_default']['utility_gzip'] = 'gzip';
$GLOBALS['midcom_config_default']['utility_tar'] = 'tar';
$GLOBALS['midcom_config_default']['utility_jhead'] = 'jhead';
$GLOBALS['midcom_config_default']['utility_find'] = 'find';
$GLOBALS['midcom_config_default']['utility_file'] = 'file';
$GLOBALS['midcom_config_default']['utility_catdoc'] = 'catdoc';
$GLOBALS['midcom_config_default']['utility_pdftotext'] = 'pdftotext';
$GLOBALS['midcom_config_default']['utility_unrtf'] = 'unrtf';
$GLOBALS['midcom_config_default']['utility_diff'] = 'diff';
$GLOBALS['midcom_config_default']['utility_rcs'] = 'rcs';

$GLOBALS['midcom_config_default']['midcom_services_rcs_bin_dir'] = '/usr/bin';

// TODO: Would be good to include DB name into the path
if (   $_MIDGARD['config']['prefix'] == '/usr' )
{
    $GLOBALS['midcom_config_default']['midcom_services_rcs_root'] = '/var/lib/midgard/rcs';
}
else if ( $_MIDGARD['config']['prefix'] == '/usr/local')
{
    $GLOBALS['midcom_config_default']['midcom_services_rcs_root'] = '/var/local/lib/midgard/rcs';
}
else
{
    $GLOBALS['midcom_config_default']['midcom_services_rcs_root'] =  "{$_MIDGARD['config']['prefix']}/var/lib/midgard/rcs";
}

$GLOBALS['midcom_config_default']['midcom_services_rcs_enable'] = true;

// Metadata system
// Be aware that these options are INTERMEDIATE until the actual rewrite to the
// 1.8 Metadata system commences. Option names might change at that point (as might
// some of the functionality. These options are unofficial which is why they are not
// PHPDoc'ed yet.

// Enables approval/scheduling controls (does not influence visibility checks using
// show_unapproved_objects). Disabled by default. Unsafe to Link Prefetching!
$GLOBALS['midcom_config_default']['metadata_approval'] = false;
$GLOBALS['midcom_config_default']['metadata_scheduling'] = false;
$GLOBALS['midcom_config_default']['staging2live_staging'] = false;

// Set the DM2 schema used by the Metadata Service
$GLOBALS['midcom_config_default']['metadata_schema'] = 'file:/midcom/config/metadata_default.inc';

// Component system
// Show only these components when creating or editing
$GLOBALS['midcom_config_default']['component_listing_allowed'] = null;
$GLOBALS['midcom_config_default']['component_listing_excluded'] = null;

// Positioning system
// If this argument is set to true, various components will start gathering
// and displaying geolocation information.
$GLOBALS['midcom_config_default']['positioning_enable'] = false;

// Page class (body class)
// If this argument is set to true, sanitized name of the component is added to the page class string.
$GLOBALS['midcom_config_default']['page_class_include_component'] = true;

// If this argument is set to true, All midcom_show_style calls wrap the style with HTML comments defining the style path
$GLOBALS['midcom_config_default']['wrap_style_show_with_name'] = false;

// Related to JavaScript libraries
$GLOBALS['midcom_config_default']['enable_prototype'] = true;
$GLOBALS['midcom_config_default']['enable_scriptaculous'] = true;
$GLOBALS['midcom_config_default']['jquery_no_conflict'] = true;

$GLOBALS['midcom_config_default']['auto_formatter'] = array();


/* ----- Include the site config ----- */
/* This should be replaced by $_MIDGARD constructs */
if (file_exists(MIDCOM_CONFIG_FILE))
{
    include(MIDCOM_CONFIG_FILE);
}

/* ----- MERGE THE CONFIGURATION ----- */
if (! array_key_exists('midcom_config_site', $GLOBALS))
{
    /**
     * MidCOM site specific configuration, read from /etc/midgard/midcom.conf.
     *
     * @global Array $GLOBALS['midcom_config_site']
     */
    $GLOBALS['midcom_config_site'] = Array();
}
if (! array_key_exists('midcom_config_local', $GLOBALS))
{
    /**
     * Local MidCOM configuration options, specific to this Instance.
     *
     * @global Array $GLOBALS['midcom_config_local']
     */
    $GLOBALS['midcom_config_local'] = Array();
}

/**
 * Current MidCOM configuration
 *
 * @global Array $GLOBALS['midcom_config']
 */
$GLOBALS['midcom_config'] = array_merge
(
    $GLOBALS['midcom_config_default'],
    $GLOBALS['midcom_config_site'],
    $GLOBALS['midcom_config_local']
);
?>