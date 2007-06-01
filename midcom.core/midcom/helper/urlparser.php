<?php
/**
 * @package midcom
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:urlparser.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a generic topic/article/attachment based URL parser. It
 * gives you the basic matching algorithms you need for every more
 * complex parser. Most of the time you will use this class as a
 * part of the MidCOM parser system.
 *
 * midcom_helper_urlparser allows you to parse an argc/argv pair
 * step by step by trying to retrieve different type of objects
 * based on the unevaluated argv elements.
 *
 * The argc/argv pair is tracked independently from the parser and
 * available through the member variables $argc and $argv of the
 * class. These two always contain the currently _unevaluated_
 * elements. This allows you for example to evaluate the "first"
 * element, then give the remaining elements to another part of
 * your application for evaluation. (MidCOM works this way when it
 * supplies a component's can_handle() hooks with its arguments.)
 *
 * Internally, midcom_helper_urlparser will keep track of the
 * current object and can distinguish between Topics, Articles and
 * Attachments there. It will also enforce some sanity rules based
 * on the object class in the fetch_*() methods. See their function
 * reference for a more detailed description.
 *
 * A very MidCOM oriented feature is the method fetch_variable().
 * It allows you to encode variables into an URL using the syntax
 * <prefix>-<key>-<value>. Specifying the prefix, which is usually
 * a MidCOM Namespace you can extract the key-value pair from the
 * URL. As an example, the Advanced Style Engine (see Section 9,
 * MidCOM specification) uses it to activate a substyle in the
 * selected Style. This mechanism allows you to encode parameters
 * into the _beginning_ of the URL, instead appending it at the
 * end, which is a far safer way of independantly adding an
 * option. About naming: prefix has to be a valid MidCOM Path
 * using "." as a separator, key has to be an alphanumeric string
 * (RegEx [a-zA-Z0-9]*) and value may ba anything not containing
 * a "/".
 *
 * The parser gives you the ability to move back "upward" in the
 * argv's. This enables you to "move back and forth" in the URL
 * tree as you need it.
 *
 * @package midcom
 */
class midcom_helper_urlparser {

    /**
     * The currently remaining argument count.
     *
     * @var int
     */
    var $argc;

    /**
     * The currently remaining argument list.
     *
     * @var Array
     */
    var $argv;

    /**
     * Stores the Topic from which the parsing has started.
     *
     * @var MidgardTopic
     * @access private
     */
    var $_roottopic;

    /**
     * Stores the type of $_curobject, represented by one of the
     * MIDCOM_HELPER_URLPARSER_... constants.
     *
     * @var int
     * @access private
     */
    var $_curtype;

    /**
     * Stores a copy of the last parsed object. Type is stored in
     * $_curtype.
     *
     * @var MidgardObject
     * @access private
     */
    var $_curobject;

    /**
     * This is the URL to the currently requested object in the form
     * of /page-prefix/topic/topic/, /page-prefix/topic/topic/article/
     * or /page-prefix/topic/topic/article/attachment-name.  Note,
     * that even URL-Parameters (midcom-style-xyz) will get added to
     * $_curURL.
     *
     * @param string
     * @access private
     */
    var $_curURL;

    /**
     * The argc value that has been used to initialize the URL parser.
     *
     * @var int
     * @access private
     */
    var $_origArgc;

    /**
     * The argv value that has been used to initialize the URL parser.
     *
     * @var Array
     * @access private
     */
    var $_origArgv;

    /**
     * Initialize the classe
     *
     * Initializes the object using the topic with the ID $topicid as
     * the starting point for the parser. If this topic cannot be found
     * in the topic tree, the object will be set to "FALSE" and
     * $midcom_errstr will contain an appropriate error message
     * including the Midgard error message.
     *
     * If the topic can be loaded successfully, the rest of the object
     * will be initialized as follows:
     *
     * If $myargv is undefinied, $this->argc and $this->argv will
     * be set to the global $argc and $argv, otherwise they are constructed from
     * the parameter.
     *
     * Finally, the current object is set to the root topic
     * and the current object type will be set to
     * MIDCOM_HELPER_URLPARSER_TOPIC.
     *
     * If set, the URL string $prefix will be used as an initializer for
     * the current URL (_curURL). This is useful, where the default
     * ($midgard->self) just isn't enough, for example in the AIS system.
     *
     * @param int $topicid		The id of the root topic (required).
     * @param Array $myargv		Custom argv that replaces the global argc/v (set -1 to use the globals).
     * @param string $prefix	Set an explicit anchor prefix.
     */
    function midcom_helper_urlparser($topicid = NULL, $myargv = -1, $prefix = null) {
        global $midcom_errstr;
        global $argc;
        global $argv;

        debug_push("URL Parser");

        if ($topicid == NULL) {
            $midcom_errstr = "Constructed without root Topic Reference";
            debug_add ($midcom_errstr);
            $x =& $this;
            $x = 0;
            debug_pop();
            return false;
        }

        $this->_roottopic = new midcom_db_topic($topicid);
        if (! $this->_roottopic) {
            $midcom_errstr = "Could not load root topic: " . mgd_errstr();
            debug_add($midcom_errstr);
            $x =& $this;
            $x = 0;
            debug_pop();
            return false;
        }

        // style inheritance
        $tmp_style_inherit = $this->_roottopic->styleInherit;
        if ($tmp_style_inherit) {
            $tmp_style = $this->_roottopic->style;
            if ($tmp_style) {
                global $midcom_style_inherited;
                $midcom_style_inherited = $tmp_style;
                $midcom_errstr .= "\nTopic style ($tmp_style) is marked inheritable";
            }
        }

        if ($myargv != -1 && is_array($myargv)) {
            $this->argc = count ($myargv);
            $this->argv = $myargv;
        } else {
            $this->argc = $argc;
            $this->argv = $argv;
        }
        $this->_curtype = MIDCOM_HELPER_URLPARSER_TOPIC;
        $this->_curobject = $this->_roottopic;

        if (is_null($prefix))
        {
            $this->_curURL = $_MIDCOM->get_page_prefix();
        }
        else
        {
            $this->_curURL = $prefix;
        }

        $this->_origArgc = $this->argc;
        $this->_origArgv = $this->argv;

        debug_pop();
    }

    /**
     * Returns a copy of the currently active object that has been
     * successfully parsed so far.
     *
     * @return MidgardObject The currently active object.
     */
    function fetch_object() {
        return $this->_curobject;
    }

    /**
     * Try to fetch a topic.
     *
     * This can only be called if the current object also is of the
     * type MidgardTopic. It will try to fetch a subtopic with a name
     * according to the next, unevaluated URL Part (represented by
     * $this->argv[0]).
     *
     * On success, it returns the found topic, drops $this->argv[0]
     * from the array and reduces $this->argc by one.
     *
     * On failure, it will return an error message in $midcom_errstr
     * including the error from the Midgard core.
     *
     * @return	MidgardTopic	The found topic, or false on failure.
     */
    function fetch_topic() {
        global $midcom_errstr;

        debug_push("URL Parser");

        if ($this->argc == 0) {
            $midcom_errstr = "No more elements in \$argv.";
            debug_add($midcom_errstr);
            debug_pop();
            return false;
        }

        if ($this->_curtype != MIDCOM_HELPER_URLPARSER_TOPIC) {
            $midcom_errstr = "Last parsed element was no topic.";
            debug_add($midcom_errstr);
            debug_pop();
            return false;
        }

        debug_add("Trying to fetch topic " . $this->argv[0]);

        $qb = midcom_db_topic::new_query_builder();
        $qb->add_constraint('name', '=', $this->argv[0]);
        $qb->add_constraint('up', '=', $this->_curobject->id);
        $result = $qb->execute();

        if ($qb->denied > 0)
        {
            // Enforce error code for access denied cases
            mgd_set_errno(MGD_ERR_ACCESS_DENIED);
        }

        if (! $result)
        {
            $midcom_errstr = "Failed to fetch topic " . $this->argv[0] . ": " . mgd_errstr();
            debug_add($midcom_errstr);
            debug_pop();
            return false;
        }

        $this->_curobject = $result[0];
        $this->argc -= 1;
        $this->_curURL .= $this->_curobject->name . "/";
        array_shift ($this->argv);

        // style inheritance
        $tmp_style_inherit = $this->_curobject->styleInherit;
        if ($tmp_style_inherit) {
            $tmp_style = $this->_curobject->style;
            if ($tmp_style) {
                global $midcom_style_inherited;
                $midcom_style_inherited = $tmp_style;
                $midcom_errstr .= "\nTopic style ($tmp_style) is marked inheritable";
            }
        }

        debug_pop();
        return $this->_curobject;
    }

    /**
     * Try to fetch an article.
     *
     * This can only be called if the current object is of the type
     * MidgardTopic. It will try to fetch an article with a name
     * according to the next, unevaluated URL Part (represented by
     * $this->argv[0]) attached to the last evaluated topic.
     *
     * On success, it returns the found article, drops $this->argv[0]
     * from the array and reduces $this->argc by one.
     *
     * On failure, it will return an error message in $midcom_errstr
     * including the error from the Midgard core.
     *
     * @return	MidgardArticle	The found article, or false on failure.
     */
    function fetch_article() {
        global $midcom_errstr;

        debug_push("URL Parser");

        if ($this->argc == 0) {
            $midcom_errstr = "No more elements in \$argv.";
            debug_add($midcom_errstr);
            debug_pop();
            return false;
        }

        if ($this->_curtype != MIDCOM_HELPER_URLPARSER_TOPIC) {
            $midcom_errstr = "Last parsed element was no topic.";
            debug_add($midcom_errstr);
            debug_pop();
            return false;
        }

        debug_add("Trying to fetch article " . $this->argv[0]);

        $qb = midcom_db_topic::new_query_builder();
        $qb->add_constraint('name', '=', $this->argv[0]);
        $qb->add_constraint('topic', '=', $this->_curobject->id);
        $result = $qb->execute();

        if (! $result) {
            $midcom_errstr = "Failed to fetch article " . $this->argv[0] . ": " . mgd_errstr();
            debug_add($midcom_errstr);
            debug_pop();
            return false;
        }

        $this->_curtype = MIDGARD_HELPER_URLPARSER_ARTICLE;
        $this->_curobject = $result[0];
        $this->_curURL .= $this->_curobject->name . "/";
        $this->argc -= 1;
        array_shift($this->argv);
        debug_pop();
        return $this->_curobject;
    }

    /**
     * Try to fetch an attachment.
     *
     * This can only be called if the current object is not of the type
     * MidgardAttachment. It will try if this object has an attachment
     * with a name according to the next, unevaluated URL Part
     * (represented by $this->argv[0]).
     *
     * On success, it returns the found Attachment, drops
     * $this->argv[0] from the array and reduces $this->argc by one.
     *
     * On failure, it will return an error message in $midcom_errstr
     * including the error from the Midgard core.
     *
     * @return MidgardAttachment	The found attachment, or false on failure.
     */
    function fetch_attachment() {
        global $midcom_errstr;

        debug_push("URL Parser");

        if ($this->argc == 0) {
            $midcom_errstr = "No more elements in \$argv.";
            debug_add($midcom_errstr);
            debug_pop();
            return false;
        }

        if ($this->_curtype == MIDCOM_HELPER_URLPARSER_ATTACHMENT) {
            $midcom_errstr = "Last parsed element was an attachment.";
            debug_add($midcom_errstr);
            debug_pop();
            return false;
        }

        debug_add("Trying to fetch Attachment " . $this->argv[0]);

        $tmp = $this->_curobject->getattachment ($this->argv[0]);

        if (! $tmp) {
            $midcom_errstr = "Failed to fetch attachment " . $this->argv[0] . ": " . mgd_errstr();
            debug_add($midcom_errstr);
            debug_pop();
            return false;
        }

        $this->_curtype = MIDCOM_HELPER_URLPARSER_ATTACHMENT;
        $this->_curobject = $tmp;
        $this->_curURL .= $this->_curobject->name;
        $this->argc -= 1;
        array_shift($this->argv);

        debug_pop();

        return $tmp;
    }

    /**
     * Try to fetch an URL variable.
     *
     * Try to decode an <prefix>-<key>-<value> pair at the current URL
     * position. Prefix must be a valid MidCOM Path, Key must mach the RegEx
     * [a-zA-Z0-9]* and value must not contain a "/".
     *
     * On success it returns an acciocative array containing two rows,
     * indexed with MIDGARD_HELPER_URLPARSER_KEY and _VALUE which hold
     * the elements that have been parsed. $this->argv[0] will be dropped
     * and $this->argc will be reduced by one.
     *
     * On failure it returns FALSE with an error message in $midcom_errstr.
     *
     * @param string $prefix	The prefix for which to search a variable
     * @return Array			The key and value of the URL parameter, or false on failure.
     */
    function fetch_variable($prefix) {
        global $midcom_errstr;

        debug_push("URL Parser");

        if ($this->argc == 0) {
            $midcom_errstr = "No more elements in \$argv.";
            debug_add($midcom_errstr);
            debug_pop();
            return false;
        }

        debug_add ("Trying to fetch variable with prefix $prefix");

        if (strpos ($this->argv[0], $prefix . "-") !== 0) {
            $midcom_errstr = "\$this->argv[0] (" . $this->argv[0] . ") does not start with prefix >$prefix<";
            debug_add($midcom_errstr);
            debug_pop();
            return false;
        }

        $tmp = substr($this->argv[0], strlen($prefix)+1);

        debug_add ("Remaining string: $tmp");

        $value = substr(strstr($tmp,"-"),1);
        $key = substr($tmp,0,strpos($tmp,"-"));

        debug_add ("Extracted: $key => $value");

        $this->_curURL .=  array_shift ($this->argv) . "/";
        $this->argc -= 1;

        debug_pop();

        return array (
          MIDCOM_HELPER_URLPARSER_KEY => $key,
          MIDCOM_HELPER_URLPARSER_VALUE => $value);

    }

    /**
     * Returns the URL to the currently selected object including the Midgard
     * Page Prefix.
     *
     * See $_curURL for further details.
     *
     * @return string	Currently successfully parsed URL.
     */
    function fetch_URL() {
        return $this->_curURL;
    }

    /**
     * Undo the last fetch operation.
     *
     * Undoes the last fetch operation effectivly restoring the previous state
     * of URL Parsing. Returns the type of the object that is now the current
     * object of the URL Parser or false if there are no more elements. The
     * new element can be retrieved with fetch_object.
     *
     * Be aware that this function currently is unable to unfetch URL encoded
     * variables. The function will return fals if you try this.
     *
     * @return int	The new type of the current object, or false on failure.
     */
    function unfetch() {
        global $midcom_errstr;

        debug_push("URL Parser");

        if ($this->argc >= $this->_origArgc) {
            $midcom_errstr = "No more elements to unfetch.";
            debug_add($midcom_errstr);
            debug_pop();
            return false;
        }

        $argument_index = $this->_origArgc - $this->argc;
        $argument = $this->_origArgv[$argument_index];

        switch ($this->_curtype) {
        case MIDCOM_HELPER_URLPARSER_TOPIC:
            debug_add("We are at Topic $this->_curobject->id, "
              . "searching for Topic $this->_curobject->up");
            $parent = new midcom_db_topic($this->_curobject->up);
            if (!$parent) {
                $midcom_errstr = "Could not load Topic"
                  . $this->_currentobject->up . " : " . mgd_errstr();
                debug_add($midcom_errstr, MIDCOM_LOG_ERROR);
                debug_print_r("Topic Object Dump", $this->_curobject);
                debug_pop();
                return false;
            }

            $parent_type = MIDCOM_HELPER_URLPARSER_TOPIC;
            break;

        case MIDCOM_HELPER_URLPARSER_ARTICLE:
            debug_add("We are at Article $this->_curobject->id, "
              . "searching for Topic $this->_curobject->topic");
            $parent = new midcom_db_topic($this->_curobject->topic);
            if (!$parent) {
                $midcom_errstr = "Could not load Topic"
                  . $this->_currentobject->topic . " : " . mgd_errstr();
                debug_add($midcom_errstr, MIDCOM_LOG_ERROR);
                debug_print_r("Article Object Dump", $this->_curobject);
                debug_pop();
                return false;
            }

            $parent_type = MIDCOM_HELPER_URLPARSER_TOPIC;

            break;

        case MIDCOM_HELPER_URLPARSER_ATTACHMENT:
            debug_add("We are at Attachment $this->_curobject->id, "
              . "searching for parent object in realm "
              . $this->_curobject->ptable . " with ID "
              . $this->_curobject->pid . ".");

            if ($this->_curobject->ptable = "topic") {
                // topic attachment
                $parent = new midcom_db_topic($this->_curobject->pid);
                if (!$parent) {
                    $midcom_errstr = "Could not load Topic" . $this->_currentobject->pid . " : " . mgd_errstr();
                    debug_add($midcom_errstr, MIDCOM_LOG_ERROR);
                    debug_print_r("Attachment Object Dump", $this->_curobject);
                    debug_pop();
                    return false;
                }

                $parent_type = MIDCOM_HELPER_URLPARSER_TOPIC;

            } else if ($this->_curobject->ptable = "article") {
                // article attachment

                $parent = new midcom_db_article($this->_curobject->pid);
                if (!$parent) {
                    $midcom_errstr = "Could not load Article" . $this->_currentobject->pid . " : " . mgd_errstr();
                    debug_add($midcom_errstr, MIDCOM_LOG_ERROR);
                    debug_print_r("Attachment Object Dump", $this->_curobject);
                    debug_pop();
                    return false;
                }

                $parent_type = MIDCOM_HELPER_URLPARSER_ARTICLE;

            } else {
                // unkown attachment
                $midcom_errstr = "Attachment of unkown type is active. " . "This should not happen, aborting unfetch.".
                debug_add($midcom_errstr, MIDCOM_LOG_ERROR);
                debug_print_r("Attachment Object Dump", $this->_curobject);
                debug_pop();
                return false;
            }
            break; // ATTACHMENT

        } // SWITCH (type)

        if ($parent->name != $argument) {
            $midcom_errstr = "The name of the parent element ($parent->name) "
              . "is different from the URL Argument to be unshifted "
              . "($argument) aborting. (Could be a fetched variable.)";
            debug_add($midcom_errstr, MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }

        $this->argc++;
        array_unshift($this->argv, $argument);
        $this->_curobject = $parent;
        $this->_curtype = $parent_type;

        // unfetch URL, look out for trailing slashes...

        if (substr($this->_curURL, -1) == "/")
            $this->_curURL = substr($this->_curURL, 0, (-1)*(2+strlen($argument)) );
        else
            $this->_curURL = substr($this->_curURL, 0, (-1)*(1+strlen($argument)) );

        return $parent_type;

        debug_pop();
    }

    /**
     * @ignore
     */
    function _dump() {
        debug_push("URL Parser Dump");
        debug_add("argc = $this->argc");
        foreach ($this->argv as $arg)
            debug_add ("argv[] = $arg");
        debug_add("_roottopic = $this->_roottopic->name ($this->_roottopic->id)");
        debug_add("_curtype = $this->_curtype");
        debug_pop();
    }

}

?>