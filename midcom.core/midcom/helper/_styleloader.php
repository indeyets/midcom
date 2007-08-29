<?php
/**
 * @package midcom
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:_styleloader.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This class is responsible for all style management and replaces
 * the old <[...]> syntax. It is instantiated by the MidCOM framework
 * and accessible through the $midcom->style object.
 *
 * The method show ($style) returns the style element $style for the current
 * component:
 *
 * It checks whether a style path is defined for the current component.
 *
 * - If there is a user defined style path, the element named $style in
 *   this path is returned,
 * - otherwise the element "$style" is taken from the default style of the
 *   current component (/path/to/component/_style/$path).
 *
 * (The default fallback is always the default style, e.g. if $style
 * is not in the user defined style path)
 *
 * To enable cross-style referencing and provide the opportunity to access
 * any style element in the current sitegroup (not only the style that is set
 * in the current page), "show" can be called with a full qualified style
 * path (like "/mystyle/element1", while the current page's style may be set
 * to "/yourstyle").
 *
 * Note: To make sure sub-styles and elements included in styles are handled
 * correctly, the old style tag <[...]> should not be used anymore,
 * but should be replaced by something like this:
 *
 * <code>
 * <?php midcom_show_style ("elementname"); ?>
 * </code>
 *
 * Styleinheritance
 *
 * The basic path the styleloader follows to find a styleelement is:
 * 1. Topic style -> if the current topic has a style set
 * 2. Inherited topic style -> if the topic inherits a style from another topic.
 * 3. Site-wide per-component default style -> if defined in MidCOM configuration key styleengine_default_styles
 * 4. Midgard style -> the style of the MidCOM component.
 * 5. The filestyle. This is usually the elements found in the components style directory.
 *
 * Regarding nr. 4:
 * It is possible to add extra filestyles if so is needed for example by a portalcomponent.
 * This is done either using the append/prepend component_style functions or by setting it
 * to another directory by calling (append|prepend)_styledir directly.
 *
 * NB: This cannot happen after the $_MIDCOM->content() stage in midcom is called,
 * i.e. you cannot change this in another styleelement or in a _show() function in a component.
 *
 * @todo Document Style Inheritance
 *
 * @package midcom
 */
class midcom_helper__styleloader {

    /**
     * @ignore
     */
    var $_debug_prefix;

    /**
     * Current style scope
     *
     * @var Array
     * @access private
     */
    var $_scope;

    /**
     * Current topic
     *
     * @var MidgardTopic
     * @access private
     */
    var $_topic;

    /**
     * Default style path
     *
     * @var string
     * @access private
     */
    var $_snippetdir;

    /**
     * Path to filestyles.
     * @var array
     */
    var $_filedirs = array();

    /**
     * Current context
     *
     * @var id
     * @access private
     */
    var $_context;

    /**
     * Style element cache
     *
     * @var Array
     * @access private
     */
    var $_styles;

    /**
     * Default style element cache
     *
     * @todo Is this still in use?
     * @var Array
     * @access private
     */
    var $_snippets;

    /**
     * List of styledirs to handle after componentstyle
     * @var Array
     * @access private
     */
    var $_styledirs_append = array();

    /**
     * List of styledirs to handle before componentstyle
     * @var Array
     * @access private
     */
    var $_styledirs_prepend = array();

    /**
     * The stack of directories to check for styles.
     */
    var $_styledirs = array();
    /**
     * Simple initialization
     */
    function midcom_helper__styleloader()
    {
        $this->_debug_prefix = "midcom_helper__styleloader::";

        $this->_context = array ();
        $this->_scope = array ();
        $this->_topic = false;
        $this->_styles = array ();
        $this->_snippets = array ();
    }

    /**
     * Returns the path of the style described by $id.
     *
     * @param int $id	Style id to look up path for
     * @return	string Style path
     * @access public
     */
    function get_style_path_from_id($id)
    {
        $path_parts = array();
        
        while (($style = new midcom_db_style($id)))
        {
            if (!$style->guid)
            {
                break;
            }
            
            $path_parts[] = $style->name; 
            $id = $style->up;
                        
            if ($style->up == 0)
            {
                break;
            }
        }
        
        $path_parts = array_reverse($path_parts);
        
        $path = '/' . implode('/', $path_parts);
        return $path;
    }

    /**
     * Returns the id of the style described by $path.
     *
     * Note: $path already includes the element name, so $path looks like
     * "/rootstyle/style/style/element".
     *
     * @todo complete documentation
     * @param string $path		The path to retrieve
     * @param int $rootstyle_id	???
     * @return	int ID of the matching style or FALSE
     * @access public
     */
    function get_style_id_from_path($path, $rootstyle = 0)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $path = preg_replace("/^\/(.*)/", "$1", $path); // leading "/"
        $path_array = explode('/', $path);

        $current_style = $rootstyle;

        if (count($path_array) == 0)
        {
            return false;
        }

        foreach ($path_array as $path_item)
        {
            if ($path_item == '')
            {
                // Skip
                continue;
            }

            //$mc = new midgard_collector('midgard_style', 'up', $current_style);
            //$mc->set_key_property('guid');
            $mc = midcom_db_style::new_collector('up', $current_style);
            $mc->add_value_property('id');
            $mc->add_constraint('name', '=', $path_item);
            $mc->execute();
            $styles = $mc->list_keys();

            if (!$styles)
            {
                //$mc->destroy();
                return false;
            }

            foreach ($styles as $style_guid => $value)
            {
                $current_style = $mc->get_subkey($style_guid, 'id');
            }
            //$mc->destroy();
        }
        if ($current_style != 0)
        {
            return $current_style;
        }

        return false;
    }
    
    function _get_nodes_inheriting_style($node)
    {
        $nodes = array();
        
        $child_qb = midcom_db_topic::new_query_builder();
        $child_qb->add_constraint('up', '=', $node->id);
        $child_qb->add_constraint('style', '=', '');
        $children = $child_qb->execute();
        
        foreach ($children as $child_node)
        {
            $nodes[] = $child_node;
            $subnodes = $this->_get_nodes_inheriting_style($child_node);
            $nodes = array_merge($nodes, $subnodes);
        }
        
        return $nodes;
    }
    
    /**
     * Get list of topics using a particular style
     *
     * @param string $style Style path
     * @return array List of folders
     */
    function get_nodes_using_style($style)
    {
        $style_nodes = array();
        // Get topics directly using the style
        $qb = midcom_db_topic::new_query_builder();
        $qb->add_constraint('style', '=', $style);
        $nodes = $qb->execute();
        
        foreach ($nodes as $node)
        {
            $style_nodes[] = $node;
            
            if ($node->styleInherit)
            {
                $child_nodes = $this->_get_nodes_inheriting_style($node);
                $style_nodes = array_merge($style_nodes, $child_nodes);
            }
        }
        
        return $style_nodes;
    }
    
    /**
     * List the default template elements shipped with a component
     * @param string $component Component to look elements for
     * @return array List of elements found indexed by the element name
     */
    function get_component_default_elements($component)
    {
        $elements = array();
        
        // Path to the file system
        $path = MIDCOM_ROOT . '/' . str_replace('.', '/', $component) . '/style';
        
        if (!is_dir($path))
        {
            debug_add("Directory {$path} not found.");
            return $elements;
        }
        
        $directory = dir($path);
        
        if (!$directory)
        {
            debug_add("Failed to read directory {$path}");
            return $elements;
        }
        
        while (($file = $directory->read()) !== false)
        {
            if (!ereg('\.php$', $file))
            {
                continue;
            }
        
            $elements[str_replace('.php', '', $file)] = "{$path}/{$file}";
        }
        
        $directory->close();

        return $elements;
    }

    /**
     * Returns a style element that matches $name and is in style $id.
     * Unlike mgd_get_element_by_name2 it also returns an element if it is not in
     * the given style, but in one of its parent styles.
     *
     * @param int $id		The style id to search in.
     * @param string $name	The element to locate.
     * @return string	Value of the found element, or false on failure.
     * @access private
     */
    function _get_element_in_styletree($id, $name)
    {
        $style_mc = midcom_db_style::new_collector('id', $id);
        $style_mc->add_value_property('up');
        $style_mc->execute();
        $styles = $style_mc->list_keys();
        foreach ($styles as $style_guid => $value)
        {
            $element_mc = midcom_db_element::new_collector('style', $id);
            $element_mc->add_value_property('value');
            $element_mc->add_constraint('name', '=', $name);
            $element_mc->execute();
            $elements = $element_mc->list_keys();
            if ($elements)
            {
                foreach ($elements as $element_guid => $value)
                {
                    //$style_mc->destroy();
                    $value = $element_mc->get_subkey($element_guid, 'value');
                    //$element_mc->destroy();
                    return $value;
                }
            }
            else
            {
                $up = $style_mc->get_subkey($style_guid, 'up');
                if (   $up
                    && $up != 0)
                {
                    //$style_mc->destroy();
                    //$element_mc->destroy();
                    return $this->_get_element_in_styletree($up, $name);
                }
            }
        }
        //$style_mc->destroy();
        return false;
    }
    
    function get_style_elements_and_nodes($style)
    {
        $results = array
        (
            'elements' => array(),
            'nodes' => array(),
        );
        
        $style_id = $this->get_style_id_from_path($style);
        if (!$style_id)
        {
            return $results;
        }
        
        $style_nodes = $_MIDCOM->style->get_nodes_using_style($style);
        
        foreach ($style_nodes as $node)
        {            
            if (!isset($results['nodes'][$node->component]))
            {
                $results['nodes'][$node->component] = array();
            }
            
            $results['nodes'][$node->component][] = $node;
        }
        
        foreach ($results['nodes'] as $component => $nodes)
        {
            // Get the list of style elements for the component
            $results['elements'][$component] = $_MIDCOM->style->get_component_default_elements($component);

            // Arrange elements in alphabetical order
            ksort($results['elements'][$component]);
        }
        
        $results['elements']['midcom'] = array
        (
            'style-init' => '',
            'style-finish' => '',
        );
        
        if ($style_id == $_MIDGARD['style'])
        {
            // We're in site main style, append elements from there to the list of "common elements"
            $qb = midcom_db_element::new_query_builder();
            $qb->add_constraint('style', '=', $_MIDGARD['style']);
            $style_elements = $qb->execute();
            foreach ($style_elements as $element)
            {
                $results['elements']['midcom'][$element->name] = '';
            }
        }

        return $results;
    }

    /**
     * Looks for a style element matching $path (either in a user defined style
     * or the default style snippetdir) and displays/evaluates it.
     *
     * @param string $path	The style element to show.
     * @return bool			True on success, false otherwise.
     */
    function show($path)
    {

        if ($this->_context === array ())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("trying to show '$path' but there is no context set!", MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }

        if (count($this->_scope) > 0)
        {
            debug_add("style scope is " . $this->_scope[0]);
        }

        $_element = $path;

        // we have full qualified path to element

        if (preg_match("|(.*)/(.*)|", $path, $matches))
        {
            $_stylepath = $matches[1];
            $_element = $matches[2];
        }

        if (   isset ($_stylepath)
            && $_styleid = $this->get_style_id_from_path($_stylepath))
        {
            array_unshift($this->_scope, $_styleid);
        }

        $_style = null;

        // try to find element in current / given scope
        if (count($this->_scope) > 0)
        {
            $src = "{$this->_scope[0]}/{$_element}";
            if (array_key_exists($src, $this->_styles))
            {
                $_style = $this->_styles[$src];
            }
            else if ($this->_scope[0] != '')
            {
                if ($_result = $this->_get_element_in_styletree($this->_scope[0], $_element))
                {
                    $this->_styles[$src] = $_result;
                    $_style = $this->_styles[$src];
                }
            }
        }

        // fallback: try to get element from default style snippet
        if (! isset($_style))
        {
            $src = "{$this->_snippetdir}/{$_element}";
            if (array_key_exists($src, $this->_snippets))
            {
                $_style = $this->_snippets[$src];
            }
            else
            {
                for ($i = 0; ! isset($_style) && $i < $this->_styledirs_count; $i++)
                {
                    $filename = MIDCOM_ROOT . $this->_styledirs[$i] .  "/{$_element}.php";
                    if (file_exists($filename))
                    {
                        $_style = file_get_contents($filename);
                        $src = $filename;
                        $this->_snippets[$src] = $_style;
                    }
                }
            }
        }

        if (isset($_style))
        {
            // This is a bit of a hack to allow &(); tags
            $data =& $_MIDCOM->get_custom_context_data('request_data');
            $result = eval('?>' . mgd_preparse($_style));
            if ($result === false)
            {
                // Note that src detection will be semi-reliable, as it depends on all errors beeing
                // found before caching kicks in.
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    "Failed to parse style element '{$path}', content was loaded from '{$src}', see above for PHP errors.");
                // This will exit.
            }
        }
        else
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("The element {$path} could not be found.", MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }

        if (isset($_stylepath))
        {
            array_shift($this->_scope);
        }

        return true;
    }

    /**
     * Gets the component style.
     *
     * @todo Document
     *
     * @param midcom_db_topic $topic	Current topic
     * @return int Database ID if the style to use in current view or FALSE
     */
    function _getComponentStyle($topic)
    {
        /* this global is set by the urlparser.
         */
        global $midcom_style_inherited;
        debug_push_class(__CLASS__, __FUNCTION__);

        // get user defined style for component

        // style inheritance
        // should this be cached somehow?
        if ($topic->style)
        {
            $_st = $this->get_style_id_from_path($topic->style);
            debug_add( "topic->style:" . $topic->style . "( $_st )" );
        }
        else if (   isset($midcom_style_inherited)
                 && ($midcom_style_inherited))
        {
            // get user defined style inherited from topic tree
            $_st = $this->get_style_id_from_path($midcom_style_inherited);
            debug_add( 'Inherited styleid:' . $midcom_style_inherited );
        }
        else
        {
            // Get style from sitewide defaults.
            $component = $topic->component;
            if (array_key_exists($component, $GLOBALS['midcom_config']['styleengine_default_styles']))
            {
                $_st = $this->get_style_id_from_path($GLOBALS['midcom_config']['styleengine_default_styles'][$component]);
                debug_add( 'Component_default styles: ' . $GLOBALS['midcom_config']['styleengine_default_styles'][$component]);
            }
        }

        if (isset($_st))
        {
            debug_add("Current component has user defined style: $_st", MIDCOM_LOG_INFO);
            $substyle = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_SUBSTYLE);
            debug_add( 'Component substyle:' . $substyle );

            if (isset($substyle)) 
            {
                $chain = explode('/', $substyle);
                //debug_print_r("substyles (from $substyle):", $chain);
                foreach ($chain as $stylename)
                {
                    $_subst_id = $this->get_style_id_from_path($stylename, $_st);
                    if ($_subst_id)
                    {
                        $_st = $_subst_id;
                        debug_add("Found substyle '$substyle', overriding component's user defined style.", MIDCOM_LOG_INFO);
                    }
                    else
                    {
                        debug_add("Substyle '$substyle' not found under {$_st}.", MIDCOM_LOG_INFO);
                    }
                }
            }
        }

        debug_pop();
        if (isset($_st)) {
            return $_st;
        } else {
            return false;
        }
    }


    /**
     * Gets the component styledir assosiated with the topics
     * component.
     *
     * @param MidgardTopic $topic the current componenttopic.
     * @returns mixed the path to the components styledirectory.
     */
    function _getComponentSnippetdir($topic)
    {
        // get component's snippetdir (for default styles)
        $loader =& $_MIDCOM->get_component_loader();
        return $loader->path_to_snippetpath($topic->component) . "/style";
    }

    /**
     * Function append styledir
     *
     * Adds an extra styledirectory to check for stylelements at
     * the end of the styledir queue.
     *
     * @param dirname path of styledirectory within midcom.
     * @return boolean true if directory appended
     * @throws midcom exception if directory does not exist.
     */
    function append_styledir ($dirname)
    {
        if (!file_exists(MIDCOM_ROOT . $dirname)) {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,"Styledirectory $dirname does not exist!");
        }
        $this->_styledirs_append[] = $dirname;
        return true;
    }

    /**
     * append the styledir of a component to the queue of styledirs.
     *
     * @param string componentname
     * @return void
     * @throws midcom exception if directory does not exist.
     */
    function append_component_styledir ($component)
    {
        $loader =& $_MIDCOM->get_component_loader();
        $path = $loader->path_to_snippetpath($component ) . "/style";
        $this->append_styledir($path);
        return;
    }
    /**
     * prepend the styledir of a component
     * @param string componentname
     * @return void
     * @throws midcom exception if directory does not exist.
     */
    function prepend_component_styledir ($component) {
        $loader =& $_MIDCOM->get_component_loader();
        $path = $loader->path_to_snippetpath($component ) . "/style";
        $this->prepend_styledir($path);
        return;
    }

    /**
     * Function prepend styledir
     * @param dirname path of styledirectory within midcom.
     * @return boolean true if directory appended
     * @throws midcom exception if directory does not exist.
     */
    function prepend_styledir ($dirname)
    {
        if (!file_exists(MIDCOM_ROOT . $dirname)) {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,"Styledirectory $dirname does not exist!");
        }
        $this->_styledirs_prepend[] = $dirname;
        return true;
    }

    /**
     * This function merges the prepend and append styles with the
     * componentstyle. This happens when the enter_context function is called.
     * You cannot change the style call stack after that (unless you call enter_context again of course).
     * @param string component style
     * @return void
     */
    function _merge_styledirs ($component_style)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        /* first the prepend styles */
        $this->_styledirs = $this->_styledirs_prepend;
        /* then the contextstyle */
        $this->_styledirs[count($this->_styledirs)] = $component_style;

        $this->_styledirs =  array_merge($this->_styledirs, $this->_styledirs_append);
        $this->_styledirs_count = count($this->_styledirs);
        debug_pop();
    }

    /**
     * Switches the context (see dynamic load). Private variables $_context, $_topic
     * and $_snippetdir are adjusted.
     *
     * @todo check documentation
     * @param int $context	The context to enter
     * @return bool			True on success, false on failure.
     */
    function enter_context($context)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        // set new context and topic
        array_unshift($this->_context, $context); // push into context stack
        debug_add("entering context $context", MIDCOM_LOG_DEBUG);

        $this->_topic = $_MIDCOM->get_content_topic();

        $_st = $this->_getComponentStyle($this->_topic);
        if (isset($_st)) {
            array_unshift($this->_scope, $_st);
        }

        $this->_snippetdir = $this->_getComponentSnippetdir($this->_topic);

        $this->_merge_styledirs($this->_snippetdir);

        debug_pop();
        return true;
    }

    /**
     * Switches the context (see dynamic load). Private variables $_context, $_topic
     * and $_snippetdir are adjusted.
     *
     * @todo check documentation
     * @return bool			True on success, false on failure.
     */
    function leave_context()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        debug_add("leaving context " . $this->_context[0], MIDCOM_LOG_DEBUG);
        /* does this cause an extra, not needed call to ->parameter ? */
        $_st = $this->_getComponentStyle($this->_topic);
        if (isset($_st)) {
            array_shift($this->_scope);
        }

        array_shift($this->_context);

        // get our topic again
        // FIXME: does this have to be above _getComponentStyle($this->_topic) ??
        $this->_topic = $_MIDCOM->get_content_topic();

        $this->_snippetdir = $this->_getComponentSnippetdir($this->_topic);

        debug_pop();
        return true;
    }

}

/**
 * Global shortcut.
 *
 * @see midcom_helper__styleloader::show()
 */
function midcom_show_style($param) {
    return $_MIDCOM->style->show($param);
}

?>
