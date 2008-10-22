<?php
/**
 * @package midcom
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:application.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * URL name parser that uses the MidCOM 2.8+ topic structure
 *
 * @package midcom
 */
class midcom_core_service_implementation_urlparsertopic implements midcom_core_service_urlparser
{
    public $argc = 0;
    public $argv = array();
    private $argv_original = array();

    private $root_topic = null;
    private $current_object = null;

    private $url = '';

    public function __construct()
    {
        $this->root_topic = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ROOTTOPIC);
        $this->current_object = $this->root_topic;

        // TODO: Remove
        $this->check_style_inheritance($this->root_topic);
    }

    public function tokenize($url)
    {
        static $tokenized = array();
        $original_url = $url;
        if (isset($tokenized[$original_url]))
        {
            return $tokenized[$original_url];
        }

        if (strlen($_MIDGARD['prefix']) > 1)
        {
            $url = str_replace($_MIDGARD['prefix'], '', $url);
        }
        if (   $url == ''
            || $url == '/')
        {
            $tokenized[$original_url] = array();
            return $tokenized[$original_url];
        }
        else
        {
            if (strpos($url, '/') === 0)
            {
                $url = substr($url, 1);
            }
            if (substr($url,-1) == '/')
            {
                $url = substr($url, 0, -1);
            }

            $argv_tmp = explode('/', $url);
        }

        $argv = array();
        foreach ($argv_tmp as $arg)
        {
            if (empty($arg))
            {
                continue;
            }

            $argv[] = $arg;
        }

        $tokenized[$original_url] = $argv;
        return $tokenized[$original_url];
    }

    /**
     * Check topic style inheritance rules for style loader
     *
     * @todo refactor style loader so this isn't needed
     */
    private function check_style_inheritance($topic)
    {
        // style inheritance
        if (!$topic->styleInherit)
        {
            return;
        }

        if (!$topic->style)
        {
            return;
        }

        $GLOBALS['midcom_style_inherited'] = $topic->style;
    }

    /**
     * Set the URL path to be parsed
     */
    public function parse($argv)
    {
        // Use straight Midgard data instead of tokenizing the URL
        $this->argc = count($argv);
        $this->argv = $argv;
        $this->argv_original = $argv;

        $this->current_object = $this->root_topic;
        $this->url = '';
    }

    /**
     * Return current object pointed to by the parse URL
     */
    public function get_current_object()
    {
        return $this->current_object;
    }

    /**
     * Return next object in URL path
     */
    public function get_object()
    {
        if ($this->argc == 0)
        {
            // No arguments left
            return false;
        }

        // Run-time cache of objects by URL
        static $objects = array();
        $object_url = "{$this->url}/{$this->argv[0]}/";
        if (array_key_exists($object_url, $objects))
        {
            // Remove this component from path
            $this->argc -= 1;
            array_shift($this->argv);
            
            // Set as current object
            $this->current_object = $objects[$object_url];
            return $objects[$object_url];
        }

        $qb = midcom_db_topic::new_query_builder();
        $qb->add_constraint('name', '=', $this->argv[0]);
        $qb->add_constraint('up', '=', $this->current_object->id);
        //$qb->add_constraint('component', '<>', '');

        if ($qb->count() == 0)
        {
            //last load returned ACCESS DENIED, no sense to dig deeper
            if (mgd_errno() == MGD_ERR_ACCESS_DENIED)
            {
                return false;
            }
            // No topics matching path, check for attachments
            $att_qb =  midcom_baseclasses_database_attachment::new_query_builder();
            $att_qb->add_constraint('name', '=', $this->argv[0]);
            $att_qb->add_constraint('parentguid', '=', $this->current_object->guid);
            if ($att_qb->count() == 0)
            {
                // allow for handler switches to work
                return false;
            }

            $atts = $att_qb->execute();

            // Remove this component from path
            $this->argc -= 1;
            array_shift($this->argv);

            // Set as current object
            $this->current_object = $atts[0];
            $objects[$object_url] = $this->current_object;
            return $objects[$object_url];
        }

        $topics = $qb->execute();

        // Set to current topic
        $this->current_object = $topics[0];
        $objects[$object_url] = $this->current_object;

        // TODO: Remove
        $this->check_style_inheritance($this->current_object);

        // Remove this component from path
        $this->argc -= 1;
        array_shift($this->argv);

        $this->url .= $this->current_object->name . '/';
        return $objects[$object_url];
    }

    /**
     * Try to fetch a URL variable.
     *
     * Try to decode an <namespace>-<key>-<value> pair at the current URL
     * position. Namespace must be a valid MidCOM Path, Key must match the RegEx
     * [a-zA-Z0-9]* and value must not contain a "/".
     *
     * On success it returns an associative array containing two rows,
     * indexed with MIDGARD_HELPER_URLPARSER_KEY and _VALUE which hold
     * the elements that have been parsed. $this->argv[0] will be dropped
     * and $this->argc will be reduced by one.
     *
     * On failure it returns FALSE with an error message in $midcom_errstr.
     *
     * @param string $namespace The namespace for which to search a variable
     * @return Array            The key and value pair of the URL parameter, or false on failure.
     */
    public function get_variable($namespace)
    {
        if ($this->argc == 0)
        {
            return false;
        }

        if (strpos($this->argv[0], $namespace . '-') !== 0)
        {
            return false;
        }

        $tmp = substr($this->argv[0], strlen($namespace) + 1);

        $value = substr(strstr($tmp,"-"),1);
        $key = substr($tmp,0,strpos($tmp,"-"));

        // Remove this component from path
        array_shift($this->argv);
        array_shift($this->argv_original);
        $this->argc -= 1;

        return array
        (
            $key => $value,
        );
    }

    /**
     * Return full URL that was given to the parser
     */
    public function get_url()
    {
        return "{$_MIDGARD['self']}{$this->url}";
    }
}
?>