<?php
/**
 * @package net.nehmer.branchenbuch
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Branchenbuch custom search callback interface
 *
 * This is the base class defining the custom search interfaces for any types using
 * the 'customsearch' mode as a default view.
 *
 * You can use the various event handlers, as usual, to customize behavior:
 *
 * - _on_initialize(): Custom startup code should go here.
 *
 * The following calls are relevant for subclass operation:
 *
 * - prepare_query(): This is called whenever the class is in "query" mode,
 *   e.g. after the form has been evaluated and the component redirected to the
 *   result listing.
 * - get_total(): Returns the total number of hits matchiing the current query.
 *   Will not be called before prepare_query().
 *
 *
 * Basic search operation:
 *
 * - Search constraints have to be cached using PHP Sessions (and may thus be
 *   pre-processed).
 * - The constraints are thus only passed once to the component when the user clicks the
 *   search button.
 * - Depending on search complexity, it is recommended to also cache the result.
 * - The search form is rendered by the callback (midcom_show_style may be used of
 *   course), but the result is rendered normally using the entry list pager.
 *   Adaption of the search result can be done via substyles (set by type).
 *
 *
 * Development Notes:
 *
 * - two "cases", one reads the search data, the other queries the result
 *
 * @package net.nehmer.branchenbuch
 */

class net_nehmer_branchenbuch_callbacks_searchbase extends midcom_baseclasses_components_purecode
{
    /**
     * The type we are querying.
     *
     * @var net_nehmer_branchenbuch_branche
     * @access protected
     */
    var $_type = null;

    /**
     * Search handler configuration. Taken from the type_config, may be null if you don't use
     * it or the user doesn't set it explicitly. The type depends on the type set in the
     * type_config.
     *
     * @var mixed
     * @access protected
     */
    var $_config = null;

    /**
     * The handler class that is calling us.
     *
     * @var midcom_baseclasses_components_handler
     * @access protected
     */
    var $_handler = null;

    /**
     * Simple startup, initialize purecode baseclass.
     */
    function net_nehmer_branchenbuch_callbacks_searchbase()
    {
        $this->_component = 'net.nehmer.branchenbuch';
        parent::midcom_baseclasses_components_purecode();
    }

    /**
     * Initialize the class to the environment of the handler. Calls the _on_initialize
     * handler before returning.
     *
     * @param midcom_baseclasses_components_handler $handler The handler class loading us.
     * @param mixed $config The config set in the type_config.
     */
    function initialize(&$handler, $config)
    {
        $this->_handler =& $handler;
        $this->_type =& $this->_handler->_type;
        $this->_config = $config;

        $this->_on_initialize();
    }

    /**
     * Event handler, called after class startup. Errors should trigger generate_error.
     */
    function _on_initialize() {}

    /**
     * Called by the component to indicate that the componentent is in result mode.
     *
     * This call should prepare to run the query specified earlier, but not actually
     * execute it, as the page number is not yet known.
     *
     * generate_error should be called on failure.
     */
    function prepare_query() {}

    /**
     * This call returns the total number of hits matching the current query. It is
     * suggested that unchecked QB queries are run, as usual.
     *
     * - This method will not be called before prepare_query().
     * - This method must be overridden.
     * - generate_error should be called on failure.
     */
    function get_total()
    {
        die ('The method net_nehmer_branchenbuch_callbacks_searchbase::get_total() must be overridden.');
    }

    /**
     * Lists the entries specified by the page in the arguments.
     *
     * @param int $page The page number to show, one-indexed.
     * @param int $page_size The number of matches per page.
     * @return Array A simple list of net_nehmer_branchenbuch_entry records.
     */
    function list_entries($page, $page_size)
    {
        die ('The method net_nehmer_branchenbuch_callbacks_searchbase::list_entries() must be overridden.');
    }

    /**
     * This call should try to process the search form rendered by the class. Depending
     * on the return value the component decides whether to relocate to the entries list
     * or not.
     *
     * If you want to pass any error message during processing to the form you have to
     * do so yourself by using member variables. The form rendering is done by the same
     * class instance then this attempt to process any sent form data.
     *
     * @return bool True if the search form has been successfully processed, false otherwise.
     */
    function process_form()
    {
        die ('The method net_nehmer_branchenbuch_callbacks_searchbase::process_form() must be overridden.');
    }

    /**
     * Displays the serach form. Called only after process_form if and only if it returned
     * false.
     *
     * @param Array $data The request data.
     */
    function show(&$data)
    {
        die ('The method net_nehmer_branchenbuch_callbacks_searchbase::show() must be overridden.');
    }
    
    /**
     * Returns the next entry after the given one. Used for view-mode paging.
     * 
     * @return net_nehmer_branchenbuch_entry The next entry.
     */
    function get_next($guid)
    {
        die ('The method net_nehmer_branchenbuch_callbacks_searchbase::get_next() must be overridden.');
    }
    
    /**
     * Returns the previous entry after the given one. Used for view-mode paging.
     * 
     * @return net_nehmer_branchenbuch_entry The previous entry.
     */
    function get_previous($guid)
    {
        die ('The method net_nehmer_branchenbuch_callbacks_searchbase::get_previous() must be overridden.');
    }

    /**
     * Static helper method for search class instantiation. Called by the entries
     * and categories handlers.
     *
     * @param Array $config The customsearch handler configuration (the 'custom_search'
     *     part of the corresponding types' type_config.
     * @param midcom_baseclasses_components_handler The handler calling us.
     * @return net_nehmer_branchenbuch_callbacks_searchbase A reference to the newly created handler.
     */
    function & create_instance(&$handler, $config)
    {
        if (! class_exists($config['class']))
        {
            if (! $config['src'])
            {
                $_MIDCOM->generate_error(MIDCOM_LOG_ERROR,
                    "The custom search handler '{$config['class']}' was not found: Class does not exist and src is undefined.");
                // This will exit.
            }

            if (substr($config['src'], 0, 5) == 'file:')
            {
                require_once(MIDCOM_ROOT . substr($config['src'], 5));
            }
            else
            {
                mgd_include_snippet_php($config['src']);
            }

            if (! class_exists($config['class']))
            {
                $_MIDCOM->generate_error(MIDCOM_LOG_ERROR,
                    "The custom search handler '{$config['class']}' was not found: Autoload of '{$config['src']}' failed.");
                // This will exit.
            }
        }

        if (! array_key_exists('config', $config))
        {
            $config['config'] = null;
        }

        $customsearch = new $config['class']();
        $customsearch->initialize($handler, $config['config']);
        return $customsearch;
    }

}

?>