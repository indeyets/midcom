<?php
/**
 * @package midcom.services
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:content.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the Output Caching Engine of MidCOM. It will intercept page output,
 * map it using the currently used URL and use the cached output on subsequent
 * requests.
 *
 * <b>Important note for application developers</b>
 *
 * Please read the documentation of the following functions thoroughly:
 *
 * - midcom_services_cache_module_content::no_cache();
 * - midcom_services_cache_module_content::uncached();
 * - midcom_services_cache_module_content::expires();
 * - midcom_services_cache_module_content::invalidate_all();
 * - midcom_services_cache_module_content::content_type();
 * - midcom_services_cache_module_content::enable_live_mode();
 *
 * You have to use these functions everywhere where it is applicable or the cache
 * will not work reliably.
 *
 * <b>Caching strategy</b>
 *
 * The cache takes three parameters into account when storing in or retrieving from
 * the cache: The current User ID, the current language and the request's URL.
 *
 * Only on a complete match a cached page is displayed, which should take care of any
 * permission check done on the page. When you change the permissions of users, you
 * need to manually invalidate the cache though, as MidCOM currently cannot detect
 * changes like this (of course, this is true if and only if you are not using a
 * MidCOM to change permissions).
 *
 * Special care is taken when HTTP POST request data is present. In that case, the
 * caching engine will automatically and transparently go into no_cache mode for
 * that request only, allowing your application to process form data. This feature
 * does neither invalidate the cache or drop the page that would have been delivered
 * normally from the cache. If you change the content, you need to do that yourself.
 *
 * HTTP 304 Not Modified support is built into this module, and it will kill the
 * output buffer and send a 304 reply if applicable.
 *
 * <b>Internal notes</b>
 *
 * This module is the first cache module which is initialized, and it will be the
 * last one in the shutdown sequence. Its startup code will exit with exit() in case of
 * a cache hit, and it will enclose the entire request using PHPs output buffering.
 *
 * <b>Module configuration (see also midcom_config.php)</b>
 *
 * - <i>string cache_module_content_name</i>: The name of the cache database to use. This should usually be tied to the actual
 *   MidCOM site to have exactly one cache per site. This is mandatory (and populated by a sensible default
 *   by midcom_config.php, see there for details).
 * - <i>boolean cache_module_content_multilang</i>: Set this to true (the default) if you want to have a cache which
 *   distinguishes between languages on each request.
 * - <i>boolean cache_module_content_uncached</i>: Set this to true to prevent the saving of cached pages. This is useful
 *   for development work, as all other headers (like E-Tag or Last-Modified) are generated
 *   normally. See the uncached() and _uncached members.
 *
 * @package midcom.services
 */
class midcom_services_cache_module_content extends midcom_services_cache_module
{
    /**#@+
     * Internal runtime state variable.
     *
     * @access private
     */

    /**
     * Flag, indicating whether the current page may be cached. If
     * false, the usual no-cache headers will be generated.
     *
     * @var boolean
     */
    var $_no_cache = false;

    /**
     * Page expiration in seconds. If NULL (unset), the page does
     * not expire.
     *
     * @var int
     */
    var $_expires = null;

    /**
     * The time of the last modification, set during auto-header-completion.
     *
     * @var int
     */
    var $_last_modified = 0;

    /**
     * An array storing all HTTP headers registered through register_sent_header().
     * They will be sent when a cached page is delivered.
     *
     * @var array
     */
    var $_sent_headers = Array();

    /**
     * The MIME content-type of the current request. It defaults to text/html, but
     * must be set correctly, so that the client gets the correct type delivered
     * upon cache deliveries.
     *
     * @var string
     */
    var $_content_type = 'text/html';

    /**
     * Internal flag indicating whether the output buffering is active.
     *
     * @var boolean
     */
    var $_obrunning = false;

    /**
     * This flag is true if the live mode has been activated. This prevents the
     * cache processing at the end of the request.
     *
     * @var boolean
     */
    var $_live_mode = false;

    /**#@-*/

    /**#@+
     * Module configuration variable.
     *
     * @access private
     */

    /**
     * True, if the cache should honor the language settings.
     *
     * @var boolean
     */
    var $_multilang = true;

    /**
     * Set this to true, if you want to inhibit storage of the generated pages in
     * the cache database. All other headers will be created as usual though, so
     * 304 processing will kick in for example.
     *
     * @var boolean
     */
    var $_uncached = false;

    /**
     * controls cache headers strategy
     * 'no-cache' activates no-cache mode that actively tries to circumvent all caching
     * 'revalidate' is the default which sets must-revalidate and expiry to current time
     * 'public' and 'private' enable caching with the cache-control header of the same name, default expiry timestamps are generated using the default_lifetime
     *
     * @var string
     */
    var $_headers_strategy = 'revalidate';

    /**
     * controls cache headers strategy for authenticated users, needed because some proxies store cookies too
     * making a horrible mess when used by mix of authenticated and non-authenticated users
     *
     * @see $_headers_strategy
     * @var string
     */
    var $_headers_strategy_authenticated = 'private';

    /**
     * Default lifetime of page for public/private headers strategy
     * When generating default expires header this is added to time().
     *
     * @var int
     */
    var $_default_lifetime = 0;

    /**
     * Default lifetime of page for public/private headers strategy for authenticated users
     *
     * @see $_default_lifetime
     * @var int
     */
    var $_default_lifetime_authenticated = 0;

    /**#@-*/

    /**
     * Cache backend instance.
     *
     * @var midcom_services_cache_backend
     */
    var $_meta_cache = null;

    /**
     * A cache backend used to store the actual cached pages.
     *
     * @var midcom_services_cache_backend
     */
    var $_data_cache = null;

    /**
     * GUIDs loaded per context in this request
     */
    var $context_guids = array();


    /**
     * Module constructor, relay to base class.
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Generate a valid cache identifier for a context of the current request
     */
    function generate_request_identifier($context, $customdata = null)
    {
        $identifier_source = 'CACHE:' . $GLOBALS['midcom_config']['cache_module_content_name'];

        if ($this->_multilang)
        {
            if (   !isset($_MIDCOM)
                || !is_object($_MIDCOM)
                || !isset($_MIDCOM->i18n)
                || !is_a($_MIDCOM->i18n, 'midcom_services_i18n'))
            {
                $i18n = new midcom_services_i18n();
            }
            else
            {
                $i18n =& $_MIDCOM->i18n;
            }
            $identifier_source .= ';LANG=' . $i18n->get_current_language();
        }
        else
        {
            $identifier_source .= ';LANG=ALL';
        }

        if (!isset($customdata['cache_module_content_caching_strategy']))
        {
            $cache_strategy = $GLOBALS['midcom_config']['cache_module_content_caching_strategy'];
        }
        else
        {
            $cache_strategy = $customdata['cache_module_content_caching_strategy'];
        }

        switch ($cache_strategy)
        {
            case 'memberships':
                if (empty($_MIDGARD['user']))
                {
                    $identifier_source .= ';USER=ANONYMOUS';
                    break;
                }
                $mc = new midgard_collector('midgard_member', 'uid', $_MIDGARD['user']);
                $mc->set_key_property('gid');
                $mc->execute();
                $gids = $mc->list_keys();
                unset($mc);
                $identifier_source .= ';GROUPS=' . implode(',', array_keys($gids));
                unset($gids);
                break;
            case 'public':
                $identifier_source .= ';USER=EVERYONE';
                break;
            case 'user':
            default:
                $identifier_source .= ';USER=' . $_MIDGARD['user'];
                break;            
        }

        if (isset($_MIDCOM))
        {
            $identifier_source .= ';URL=' . $_MIDCOM->get_context_data(MIDCOM_CONTEXT_URI);
        }
        else
        {
            $identifier_source .= ';URL=' . $_SERVER['REQUEST_URI'];
        }
        // check_dl_hit needs to take config changes into account...
        if (is_null($customdata))
        {
            $identifier_source .= ';' . serialize($customdata);
        }

        // TODO: Add browser capability data (mobile, desktop browser etc) from WURFL here

        /**
         * This can leak data usefull for attacker, OTOH it's very handy for debugging 
         *
        if ($context === 0)
        {
            header("X-MidCOM-request-id-source: {$identifier_source}");
        }
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Generating context {$context} request-identifier from: {$identifier_source}");
        debug_print_r('$customdata was: ', $customdata);
        debug_pop();
        /* */
        return 'R-' . md5($identifier_source);
    }

    /**
     * Generate a valid cache identifier for a context of the current content (all loaded objects).
     */
    function generate_content_identifier($context)
    {
        if (   !isset($this->context_guids[$context])
            || empty($this->context_guids[$context]))
        {
            // Error pages and such have no GUIDs in some cases
            $identifier_source = $this->generate_request_identifier($context);
        }
        else
        {
            // FIXME: These guids should be registered by language...
            $identifier_source = implode(',', $this->context_guids[$context]);
        }
        return 'C-' . md5($identifier_source);
    }

    /**
     * This function is responsible for initializing the cache.
     *
     * The first step is to initialize the cache backends. The names of the
     * cache backends used for meta and data storage are derived from the name
     * defined for this module (see the 'name' configuration parameter above).
     * The name is used directly for the meta data cache, while the actual data
     * is stored in a backend postfixed with '_data'.
     *
     * After core initialization, the module checks for a cache hit (which might
     * trigger the delivery of the cached page and exit) and start the output buffer
     * afterwards.
     */
    function _on_initialize()
    {
        $backend_config = $GLOBALS['midcom_config']['cache_module_content_backend'];
        if (! array_key_exists('directory', $backend_config))
        {
            $backend_config['directory'] = 'content/';
        }
        if (! array_key_exists('driver', $backend_config))
        {
            $backend_config['driver'] = 'null';
        }

        //$name = $GLOBALS['midcom_config']['cache_module_content_name'];
        $name = 'content';
        $meta_backend_name = "{$name}_meta";
        $data_backend_name = "{$name}_data";

        $backend_config['auto_serialize'] = true;
        $this->_meta_cache =& $this->_create_backend($meta_backend_name, $backend_config);
        $backend_config['auto_serialize'] = false;
        $this->_data_cache =& $this->_create_backend($data_backend_name, $backend_config);

        if (array_key_exists('cache_module_content_multilang', $GLOBALS['midcom_config']))
        {
            $this->_multilang = $GLOBALS['midcom_config']['cache_module_content_multilang'];
        }
        if (array_key_exists('cache_module_content_uncached', $GLOBALS['midcom_config']))
        {
            $this->_uncached = $GLOBALS['midcom_config']['cache_module_content_uncached'];
        }

        if (array_key_exists('cache_module_content_headers_strategy', $GLOBALS['midcom_config']))
        {
            $this->_headers_strategy = strtolower($GLOBALS['midcom_config']['cache_module_content_headers_strategy']);
        }
        if (array_key_exists('cache_module_content_headers_strategy_authenticated', $GLOBALS['midcom_config']))
        {
            $this->_headers_strategy_authenticated = strtolower($GLOBALS['midcom_config']['cache_module_content_headers_strategy_authenticated']);
        }
        if (array_key_exists('cache_module_content_default_lifetime', $GLOBALS['midcom_config']))
        {
            $this->_default_lifetime = (int)$GLOBALS['midcom_config']['cache_module_content_default_lifetime'];
        }
        if (array_key_exists('cache_module_content_default_lifetime_authenticated', $GLOBALS['midcom_config']))
        {
            $this->_default_lifetime_authenticated = (int)$GLOBALS['midcom_config']['cache_module_content_default_lifetime_authenticated'];
        }
        switch ($this->_headers_strategy)
        {
            case 'no-cache':
                $this->no_cache();
                break;
            case 'revalidate':
            case 'public':
            case 'private':
                break;
            default:
                $message = "Cache headers strategy '{$this->_headers_strategy}' is not valid, try 'no-cache', 'revalidate', 'public' or 'private'";
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add($message, MIDCOM_LOG_ERROR);
                debug_pop();
                $this->no_cache();
                /* Copied from midcom_application::generate_error, because we do not yet have midcom fully loaded */
                $title = "Server Error";
                $code = 500;
                header('HTTP/1.0 500 Server Error');
                header ('Content-Type: text/html');
                echo '<?'.'xml version="1.0" encoding="ISO-8859-1"?'.">\n";
                ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
    <head>
        <title><?echo $title; ?></title>
        <style type="text/css">
            body { color: #000000; background-color: #FFFFFF; }
            a:link { color: #0000CC; }
            p, address {margin-left: 3em;}
            address {font-size: smaller;}
        </style>
    </head>

    <body>
        <h1><?echo $title; ?></h1>

        <p>
        <?echo $message; ?>
        </p>

        <h2>Error <?echo $code; ?></h2>
        <address>
          <a href="/"><?php echo $_SERVER["SERVER_NAME"]; ?></a><br />
          <?php echo date("r"); ?><br />
          <?php echo $_SERVER["SERVER_SOFTWARE"]; ?>
        </address>
    </body>
</html>
                <?php
                exit();
                break;
        }

        // Init complete, now check for a cache hit and start up caching.
        // Note, that check_hit might exit().
        $this->_check_hit();
        $this->_start_caching();
    }

    /**
     * The shutdown event handler will finish the caching sequence by storing the cached data,
     * if required.
     */
    function _on_shutdown()
    {
        $this->_finish_caching();
    }

    /**
     * This function holds the cache hit check mechanism. It searches the requested
     * URL in the cache database. If found, it checks, whether the cache page has
     * expired. If not, the cached page is delivered to the client and processing
     * ends. In all other cases this method simply returns.
     *
     * The midcom-cache URL methods are handled before checking for a cache hit.
     *
     * Also, any HTTP POST request will automatically circumvent the cache so that
     * any component can process the request. It will set no_cache automatically
     * to avoid any cache pages being overwritten by, for example, search results.
     *
     * Note, that HTTP GET is <b>not</b> checked this way, as GET requests can be
     * safely distinguished by their URL.
     *
     * @access private
     */
    function _check_hit()
    {
        foreach ($GLOBALS['argv'] as $arg)
        {
            switch ($arg)
            {
                case "midcom-cache-invalidate":
                case "midcom-cache-nocache":
                case "midcom-cache-stats":
                    // Don't cache these.
                    header("X-MidCOM-cache: midcom-xxx uncached");
                    debug_pop();
                    return;
            }
        }

        // Check for POST variables, if any is found, go for no_cache.
        if (count($_POST) > 0)
        {
            header("X-MidCOM-cache: POST uncached");
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('POST variables have been found, setting no_cache and not checking for a hit.');
            $this->no_cache();
            debug_pop();
            return;
        }

        // Check for uncached operation
        if ($this->_uncached)
        {
            header("X-MidCOM-cache: uncached mode");
            return;
        }

        // Check that we have cache for the identifier
        $this->_meta_cache->open();

        $request_id = $this->generate_request_identifier(0);
        if (!$this->_meta_cache->exists($request_id))
        {
            header("X-MidCOM-meta-cache: MISS {$request_id}");
            // We have no information about content cached for this request
            $this->_meta_cache->close();
            return;
        }
        header("X-MidCOM-meta-cache: HIT {$request_id}");

        // Load metadata for the content identifier connected to current request
        $content_id = $this->_meta_cache->get($request_id);

        if (!$this->_meta_cache->exists($content_id))
        {
            header("X-MidCOM-meta-cache: MISS {$content_id}", false);
            // Content cache data is missing
            $this->_meta_cache->close();
            return;
        }

        $data = $this->_meta_cache->get($content_id);

        if (!is_null($data['expires']))
        {
            if ($data['expires'] < time())
            {
                header("X-MidCOM-meta-cache: EXPIRED {$content_id}", false);
                $this->_meta_cache->close();            
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add('Current page is in cache, but has expired on ' . gmdate('c', $data['expires']), MIDCOM_LOG_INFO);
                debug_pop();
                return;
            }
        }

        $this->_meta_cache->close();
        header("X-MidCOM-meta-cache: HIT {$content_id}", false);

        // Check If-Modified-Since and If-None-Match, do content output only if
        // we have a not modified match.
        if (! $this->_check_not_modified($data['last_modified'], $data['etag']))
        {
            $this->_data_cache->open();
            if (! $this->_data_cache->exists($content_id))
            {
                header("X-MidCOM-data-cache: MISS {$content_id}");
                $this->_data_cache->close();
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Current page is in not in the data cache, possible ghost read.", MIDCOM_LOG_WARN);
                debug_pop();
                return;
            }

            header("X-MidCOM-data-cache: HIT {$content_id}");
            $content = $this->_data_cache->get($content_id);
            $this->_data_cache->close();

            foreach ($data['sent_headers'] as $header)
            {
                header($header);
            }

            // Echo the content to the client.
            echo $content;
        }

        exit();
    }

    /**
     * This function will start the output cache. Call this before any output
     * is made. MidCOM's startup sequence will automatically do this.
     */
    function _start_caching()
    {
        ob_implicit_flush(false);
        ob_start();
        $this->_obrunning = true;
    }

    /**
     * Call this, if the currently processed output must not be cached for any
     * reason. Dynamic pages with sensitive content are a candidate for this
     * function.
     *
     * Note, that this will prevent <i>any</i> content invalidation related headers
     * like E-Tag to be generated automatically, and that the appropriate
     * no-store/no-cache headers from HTTP 1.1 and HTTP 1.0 will be sent automatically.
     * This means that there will also be no 304 processing.
     *
     * You should use this only for sensitive content. For simple dynamic output,
     * you are strongly encouraged to use the less strict uncached() function.
     *
     * @see uncached()
     */
    function no_cache()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $GLOBALS['midcom_debugger']->print_function_stack('no_cache called from');
        debug_pop();
        if ($this->_no_cache)
        {
            return;
        }

        $this->_no_cache = true;

        if (headers_sent())
        {
            // Whatever is wrong here, we return.
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Warning, we should move to no_cache but headers have already been sent, skipping header transmission.', MIDCOM_LOG_ERROR);
            debug_pop();
            return;
        }

        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);
        if (   isset ($_SERVER['HTTPS'])
            && preg_match('/MSIE/', $_SERVER['HTTP_USER_AGENT']))
        {
            //Suppress "Pragma: no-cache" header, because otherwise file downloads don't work in IE with https.
        }
        else
        {
            header('Pragma: no-cache');
        }
        // PONDER:, send expires header (set to long time in past) as well ??
    }

    /**
     * Call this, if the currently processed output must not be cached for any
     * reason. Dynamic pages or form processing results are the usual candidates
     * for this mode.
     *
     * Note, that this will still keep the caching engine active so that it can
     * add the usual headers (ETag, Expires ...) in respect to the no_cache flag.
     * As well, at the end of the processing, the usual 304 checks are done, so if
     * your page doesn't change in respect of E-Tag and Last-Modified, only a 304
     * Not Modified reaches the client.
     *
     * Essentially, no_cache behaves the same way as if the uncached configuration
     * directive is set to true, it is just limited to a single request.
     *
     * If you need a higher level of client side security, to avoid storage of sensitive
     * information on the client side, you should use no_cache instead.
     *
     * @see no_cache()
     */
    function uncached()
    {
        if ($this->_uncached)
        {
            return;
        }
        $this->_uncached = true;
    }

    /**
     * Sets the expiration time of the current page (Unix (GMT) Timestamp).
     *
     * <b>Note:</B> This generate error call will add browser-side cache control
     * headers as well to force a browser to revalidate a page after the set
     * expiry.
     *
     * You should call this at all places where you have timed content in your
     * output, so that the page will be regenerated once a certain article has
     * expired.
     *
     * Multiple calls to expires will only save the
     * "youngest" timestamp, so you can safely call expires where appropriate
     * without respect to other values.
     *
     * The cache's default (null) will disable the expires header. Note, that once
     * an expiry time on a page has been set, it is not possible, to reset it again,
     * this is for dynamic_load situation, where one component might depend on a
     * set expiry.
     *
     * @param int $timestamp The UNIX timestamp from which the cached page should be invalidated.
     */
    function expires($timestamp)
    {
        if (   is_null($this->_expires)
            || $this->_expires > $timestamp)
        {
            $this->_expires = $timestamp;
        }
    }

    /**
     * Sets the content type for the current page. The required HTTP Headers for
     * are automatically generated, so, to the contrary of expires, you just have
     * to set this header accordingly.
     *
     * This is usually set automatically by MidCOM for all regular HTML output and
     * for all attachment deliveries. You have to adapt it only for things like RSS
     * output.
     *
     * @param string $type    The content type to use.
     */
    function content_type($type)
    {
        $this->_content_type = $type;

        // Send header (don't register yet to avoid duplicates, this is done during finish
        // caching).
        $header = "Content-type: " . $this->_content_type;
        header($header);
    }

    /**
     * Use this function to put the cache into a "live mode". This will disable the
     * cache during runtime, correctly flushing the output buffer and sending cache
     * control headers. You will not be able to send any additional headers after
     * executing this call therefore you should adjust the headers in advance.
     *
     * The midcom-exec URL handler of the core will automatically enable live mode.
     *
     * @see midcom_application::_exec_file()
     */
    function enable_live_mode()
    {
        if ($this->_live_mode)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Cannot enter live mode twice, ignoring request.', MIDCOM_LOG_WARN);
            debug_pop();
            return;
        }

        $this->_live_mode = true;
        $this->_no_cache = true;
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        if ($this->_obrunning)
        {
            // Flush any remaining output buffer.
            // Ignore errors in case _obrunning is wrong, we are in the right state then anyway.
            // We do this only if there is actually content in the output buffer. If not, we won't
            // send anything, so that you can still send HTTP Headers after enabling the live mode.
            // Check is for nonzero and non-false
            if (ob_get_length())
            {
                @ob_end_flush();
            }
            else
            {
                @ob_end_clean();
            }
            $this->_obrunning = false;
        }
    }

    /**
     * This method stores a sent header into the cache database, so that it will
     * be resent when the cache page is delivered. midcom_application::header()
     * will automatically call this function, you need to do this only if you use
     * the PHP header function.
     *
     * @param string $header The header that was sent.
     */
    function register_sent_header($header)
    {
        $this->_sent_headers[] = $header;
    }

    /**
     * Looks for list of content and request identifiers paired with the given guid
     * and removes all of those from the caches.
     */
    function invalidate($guid)
    {
        $this->_meta_cache->open();

        if (!$this->_meta_cache->exists($guid))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("No entry for {$guid} in meta cache, ignoring invalidation request.");
            debug_pop();
            return;
        }

        $guidmap = $this->_meta_cache->get($guid);
        $this->_data_cache->open();
        foreach ($guidmap as $content_id)
        {
            if ($this->_meta_cache->exists($content_id))
            {
                //debug_push_class(__CLASS__, __FUNCTION__);
                //debug_add("Removing key {$content_id} from meta cache");
                //debug_pop()
                $this->_meta_cache->remove($content_id);
            }

            if ($this->_data_cache->exists($content_id))
            {
                //debug_push_class(__CLASS__, __FUNCTION__);
                //debug_add("Removing key {$content_id} from data cache");
                //debug_pop();
                $this->_data_cache->remove($content_id);
            }
        }
    }

    /**
     * All objects loaded within a request are stored into a list for cache invalidation purposes
     */
    function register($guid)
    {
        $context = $_MIDCOM->get_current_context();
        if ($context != 0)
        {
            // We're in a dynamic_load, register it for that as well
            if (!isset($this->context_guids[$context]))
            {
                $this->context_guids[$context] = array();
            }
            $this->context_guids[$context][] = $guid;
        }

        // Register all GUIDs also to the root context
        if (!isset($this->context_guids[0]))
        {
            $this->context_guids[0] = array();
        }
        $this->context_guids[0][] = $guid;
    }

    /**
     * Checks, whether the browser supplied if-modified-since or if-none-match headers
     * match the passed etag/last modified timestamp. If yes, a 304 not modified header
     * is emitted and true is returned. Otherwise the function will return false
     * without modifications to the current runtime state.
     *
     * If the headers have already been sent, something is definitely wrong, so we
     * ignore the request silently returning false.
     *
     * Note, that if both If-Modified-Since and If-None-Match are present, both must
     * actually match the given stamps to allow for a 304 Header to be emitted.
     *
     * @param int $last_modified The last modified timestamp of the current document. This timestamp
     *     is assumed to be in <i>local time</i>, and will be implicitly converted to a GMT time for
     *     correct HTTP header comparisons.
     * @param string $etag The etag header associated with the current document.
     * @return boolean True, if an 304 match was detected and the appropriate headers were sent.
     */
    function _check_not_modified($last_modified, $etag)
    {
        if (headers_sent())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("The headers have already been sent, cannot do a not modified check.", MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }

        // These variables are set to true if the corresponding header indicates a 403 is
        // possible.
        $if_modified_since = false;
        $if_none_match = false;
        if (array_key_exists('HTTP_IF_NONE_MATCH', $_SERVER))
        {
            if ($_SERVER['HTTP_IF_NONE_MATCH'] != $etag)
            {
                // The E-Tag is different, so we cannot 304 here.
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("The HTTP supplied E-Tag requirement does not match: {$_SERVER['HTTP_IF_NONE_MATCH']} (!= {$etag})");
                debug_pop();
                return false;
            }
            $if_none_match = true;
        }
        if (array_key_exists('HTTP_IF_MODIFIED_SINCE', $_SERVER))
        {
            $tmp = $_SERVER['HTTP_IF_MODIFIED_SINCE'];
            if (strpos($tmp, 'GMT') === false)
            {
                $tmp .= ' GMT';
            }
            $modified_since = strtotime($tmp);
            if ($modified_since < $last_modified)
            {
                // Last Modified does not match, so we cannot 304 here.
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("The supplied HTTP Last Modified requirement does not match: {$_SERVER['HTTP_IF_MODIFIED_SINCE']}.");
                debug_add("If-Modified-Since: ({$modified_since}) " . gmdate("D, d M Y H:i:s", $modified_since) . ' GMT');
                debug_add("Last-Modified: ({$last_modified})" . gmdate("D, d M Y H:i:s", $last_modified) . ' GMT');
                debug_pop();
                return false;
            }
            $if_modified_since = true;
        }

        if (! $if_modified_since && ! $if_none_match)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('No If-Header was detected, we cannot 304 therefore.');
            debug_pop();
            return false;
        }

        if ($this->_obrunning)
        {
            // Drop the output buffer, if any.
            ob_end_clean();
        }

        // Emit the 304 header, then exit.
        header('HTTP/1.0 304 Not Modified');
        return true;
    }
    
    /**
     * This helper will be called during module shutdown, it completes the output caching,
     * post-processes it and updates the cache databases accordingly.
     *
     * The first step is to check against _no_cache pages, which will be delivered immediately
     * without any further post processing. Afterwards, the system will complete the sent
     * headers by adding all missing headers. Note, that E-Tag will be generated always
     * automatically, you must not set this in your component.
     *
     * If the midcom configuration option cache_uncached is set or the corresponding runtime function
     * has been called, the cache file will not be written, but the header stuff will be added like
     * usual to allow for browser-side caching.
     */
    function _finish_caching($etag=null)
    {
        if (   $this->_no_cache
            || $this->_live_mode)
        {
            if ($this->_obrunning)
            {
                ob_end_flush();
            }
            return;
        }

        $cache_data = ob_get_contents();
        /**
         * WARNING: 
         *   From here on anything added to content is not included in cached
         *   data, so make sure nothing content-wise is added after this 
         */

        // Generate E-Tag header.
        if (strlen($cache_data) == 0)
        {
            $etag = md5(serialize($this->_sent_headers));
        }
        else
        {
            $etag = md5($cache_data);
        }

        $etag_header = "ETag: {$etag}";
        header($etag_header);
        $this->register_sent_header($etag_header);

        // Register additional Headers around the current output request
        // It has been sent already during calls to content_type
        $header = "Content-type: " . $this->_content_type;
        $this->register_sent_header($header);
        $this->_complete_sent_headers($cache_data);

        // If-Modified-Since / If-None-Match checks, if no match, flush the output.
        if (! $this->_check_not_modified($this->_last_modified, $etag))
        {
            ob_end_flush();
            $this->_obrunning = false;
        }

        /**
         * WARNING: 
         *   Stuff below here is executed *after* we have flushed output,
         *   so here we should only write out our caches but do nothing else
         */

        if ($this->_uncached)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Not writing cache file, we are in uncached operation mode.');
            debug_pop();
        }
        else
        {
            /**
             * See the FIXME in generate_content_identifier on why we use the content hash
            $content_id = $this->generate_content_identifier($context);
             */
            $content_id = 'C-' . $etag;
            $this->write_meta_cache($content_id, $etag);
            $this->_data_cache->put($content_id, $cache_data);
        }
    }

    /**
     * Writes meta-cache entry from context data using given content id
     * Used to be part of _finish_caching, but needed by serve-attachment method in midcom_application as well
     */
    function write_meta_cache($content_id, $etag)
    {
        // Construct cache identifiers
        $context = $_MIDCOM->get_current_context();
        $request_id = $this->generate_request_identifier($context);
        
        //debug_push_class(__CLASS__, __FUNCTION__);
        //debug_add("Creating cache entry for {$content_id} as {$request_id}", MIDCOM_LOG_INFO);
        //debug_pop();

        if (!is_null($this->_expires))
        {
            $entry_data['expires'] = $this->_expires;
        }
        else
        {
            // Use default expiry for cache entry, most components don't bother calling expires() properly
            /*
            debug_push_class(__CLASS__, __FUNCTION__);        
            debug_add("explicit expires is not set, using \$this->_default_lifetime: {$this->_default_lifetime}");
            debug_pop();
            */
            $entry_data['expires'] = time() + $this->_default_lifetime;
        }
        $entry_data['etag'] = $etag;
        $entry_data['last_modified'] = $this->_last_modified;
        $entry_data['sent_headers'] = $this->_sent_headers;

        /**
         * Remove comment to debug cache
        debug_push_class(__CLASS__, __FUNCTION__);        
        debug_print_r("Writing meta-cache entry {$content_id}", $entry_data);
        debug_pop();
        */

        $this->_meta_cache->open(true);
        $this->_meta_cache->put($content_id, $entry_data);
        $this->_meta_cache->put($request_id, $content_id);
        $this->_meta_cache->close();

        // Cache where the object have been
        $this->store_context_guid_map($context, $content_id, $request_id);
    }


    function store_context_guid_map($context, $content_id, $request_id)
    {
        $this->_meta_cache->open(true);
        foreach ($this->context_guids[$context] as $guid)
        {
            // This needs to be array as GUIDs often appear in multiple requests
            if ($this->_meta_cache->exists($guid))
            {
                $guidmap = $this->_meta_cache->get($guid);
                if (!is_array($guidmap))
                {
                    $guidmap = array();
                }
            }
            else
            {
                $guidmap = array();
            }

            if (!in_array($content_id, $guidmap))
            {
                $guidmap[] = $content_id;
            }
            if (!in_array($request_id, $guidmap))
            {
                $guidmap[] = $request_id;
            }
            $this->_meta_cache->put($guid, $guidmap);
        }
        $this->_meta_cache->close();
    }

    function check_dl_hit(&$context, &$dl_config)
    {
        //debug_push_class(__CLASS__, __FUNCTION__);
        if (   $this->_no_cache
            || $this->_live_mode)
        {
            return false;
        }
        $dl_request_id = 'DL' . $this->generate_request_identifier($context, $dl_config);
        //debug_add("Checking if we have '{$dl_request_id}' in \$this->_meta_cache");
        $this->_meta_cache->open();
        if ($this->_meta_cache->exists($dl_request_id))
        {
            $dl_content_id = $this->_meta_cache->get($dl_request_id);
            $this->_meta_cache->close();
            $this->_data_cache->open();
            //debug_add("Checking if we have '{$dl_content_id}' in \$this->_data_cache");
            if ($this->_data_cache->exists($dl_content_id))
            {
                //debug_add('Cached content found, serving it');
                echo $this->_data_cache->get($dl_content_id);
                $this->_data_cache->close();
                debug_pop();
                return true;
            }
            //debug_add("We received content_id ({$dl_content_id}), but did not find corresponding data in cache", MIDCOM_LOG_INFO);
            $this->_data_cache->close();
        }
        else
        {
            $this->_meta_cache->close();
        }
        //debug_pop();
        return false;
    }

    function store_dl_content(&$context, &$dl_config, &$dl_cache_data)
    {
        //debug_push_class(__CLASS__, __FUNCTION__);
        if (   $this->_no_cache
            || $this->_live_mode)
        {
            return;
        }
        if ($this->_uncached)
        {
            return;
        }
        $dl_request_id = 'DL' . $this->generate_request_identifier($context, $dl_config);
        /**
         * See the FIXME in generate_content_identifier on why we use the content hash
        $dl_content_id = $this->generate_content_identifier($context);
         */
        $dl_content_id = 'DLC-' . md5($dl_cache_data);
        $this->_meta_cache->open(true);
        $this->_data_cache->open(true);
        $this->_meta_cache->put($dl_request_id, $dl_content_id);
        //debug_add("Writing cache entry for '{$dl_content_id}' in request '{$dl_request_id}'");
        $this->_data_cache->put($dl_content_id, $dl_cache_data);
        // Cache where the object have been
        $this->store_context_guid_map($context, $dl_content_id, $dl_request_id);
        unset($guid, $guidmap);
        $this->_meta_cache->close();
        $this->_data_cache->close();
        unset($dl_cache_data, $dl_content_id, $dl_request_id);
        //debug_pop();
    }

    /**
     * This little helper ensures that the headers Accept-Ranges, Content-Length
     * and Last-Modified are present. The lastmod timestamp is taken out of the
     * component context information if it is populated correctly there; if not, the
     * system time is used instead.
     *
     * To force browsers to revalidate the page on every request (login changes would
     * go unnoticed otherwise), the Cache-Control header max-age=0 is added automatically.
     *
     * @param Array &$cache_data The current cache data that will be written to the database.
     * @access private
     */
    function _complete_sent_headers(& $cache_data)
    {
        // Detected headers flags
        $ranges = false;
        $size = false;
        $lastmod = false;

        foreach ($this->_sent_headers as $header)
        {
            if (strncasecmp($header, 'Accept-Ranges', 13) == 0)
            {
                $ranges = true;
            }
            else if (strncasecmp($header, 'Content-Length', 14) == 0)
            {
                $size = true;
            }
            else if (strncasecmp($header, 'Last-Modified', 13) == 0)
            {
                $lastmod = true;
                // Populate last modified timestamp (force GMT):
                $tmp = substr($header, 15);
                if (strpos($tmp, 'GMT') === false)
                {
                    $tmp .= ' GMT';
                }
                $this->_last_modified = strtotime($tmp);
                if ($this->_last_modified == -1)
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("Failed to extract the timecode from the last modified header '{$header}', defaulting to the current time.", MIDCOM_LOG_WARN);
                    debug_pop();
                    $this->_last_modified = time();
                }
            }
        }

        if (! $ranges)
        {
            $header = "Accept-Ranges: none";
            header ($header);
            $this->_sent_headers[] = $header;
        }
        if (! $size)
        {
            /* TODO: Doublecheck the way this is handled, it seems it's one byte too short
               which causes issues with Squid for example (could be that we output extra
               whitespace somewhere or something), now we just don't send it if headers_strategy
               implies caching */
            switch($this->_headers_strategy)
            {
                case 'public':
                case 'private':
                    break;
                default:
                    $header = "Content-Length: " . ob_get_length();
                    header ($header);
                    $this->_sent_headers[] = $header;
                    break;
            }
        }
        if (! $lastmod)
        {
            /* Determine Last-Modified using MidCOM's component context,
             * Fallback to time() if this fails.
             */
            $time = 0;
            foreach ($_MIDCOM->_context as $id => $context)
            {
                $meta = $_MIDCOM->get_26_request_metadata($id);
                if ($meta['lastmodified'] > $time)
                {
                    $time = $meta['lastmodified'];
                }
            }
            if (   $time == 0
                || !is_numeric($time))
            {
                $time = time();
            }

            //debug_push_class(__CLASS__, __FUNCTION__);
            //debug_add("Setting last modified to " . gmdate('c', $time));
            //debug_pop();

            $header = "Last-Modified: " . gmdate('D, d M Y H:i:s', $time) . ' GMT';
            header ($header);
            $this->_sent_headers[] = $header;
            $this->_last_modified = $time;
        }

        $this->cache_control_headers();
    }

    function _use_auth_headers()
    {
    }

    function cache_control_headers()
    {
        //debug_push_class(__CLASS__, __FUNCTION__);
        // Add Expiration and Cache Control headers
        $cache_control = false;
        $pragma = false;
        $expires = false;
        // Just to be sure not to mess the headers sent by no_cache in case it was called
        if (!$this->_no_cache)
        {
            // Typecast to make copy in stead of reference
            $strategy = (string)$this->_headers_strategy;
            $default_lifetime = (int)$this->_default_lifetime;
            if (   (   isset($_MIDCOM->auth)
                    && is_a($_MIDCOM->auth, 'midcom_services_auth')
                    && $_MIDCOM->auth->is_valid_user())
                || !empty($_MIDGARD['user'])
                )
            {
                // Typecast to make copy in stead of reference
                $strategy = (string)$this->_headers_strategy_authenticated;
                $default_lifetime = (int)$this->_default_lifetime_authenticated;
            }
            switch($strategy)
            {
                // included in case _headers_strategy_authenticated sets this
                case 'no-cache':
                    $this->no_cache();
                    break;
                case 'revalidate':
                    // Currently, we *force* a cache client to revalidate the copy every time.
                    // I hope that this fixes most of the problems outlined in #297 for the time being.
                    // The timeout of a content cache entry is not affected by this.
                    $cache_control = 'max-age=0 must-revalidate';
                    $expires = time();
                    break;
                case 'private':
                    // Fall-strough intentional
                case 'public':
                    if (!is_null($this->_expires))
                    {
                        $expires = $this->_expires;
                        $max_age = $this->_expires - time();
                    }
                    else
                    {
                        $expires = time() + $default_lifetime;
                        $max_age = $default_lifetime;
                    }
                    $cache_control = "{$strategy} max-age={$max_age}";
                    if ($max_age == 0)
                    {
                        $cache_control .= ' must-revalidate';
                    }
                    $pragma =& $strategy;
                    break;
            }
        }
        if ($cache_control !== false)
        {
            $header = "Cache-Control: {$cache_control}";
            header ($header);
            $this->_sent_headers[] = $header;
            //debug_add("Added Header '{$header}'");
        }
        if ($pragma !== false)
        {
            $header = "Pragma: {$pragma}";
            header ($header);
            $this->_sent_headers[] = $header;
            //debug_add("Added Header '{$header}'");
        }
        if ($expires !== false)
        {
            $header = "Expires: " . gmdate("D, d M Y H:i:s", $expires) . " GMT";
            header ($header);
            $this->_sent_headers[] = $header;
            //debug_add("Added Header '{$header}'");
        }
        //debug_pop();
    }
}

?>