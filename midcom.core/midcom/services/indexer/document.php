<?php

/**
 * @package midcom.services
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:document.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This class encaspulates a single indexer document. It is used for both indexing
 * and retrieval.
 *
 * A document consists of a number of fields, each field has different properties
 * when handled by the indexer (exact bahvoir depends, as always, on the indexer
 * backend in use). On retrieval, this field information is lost, all fields being
 * of the same type (naturally). The core indexer backend supports these field
 * types:
 *
 * - <i>date</i> is a date-wrapped field suitable for use with the Date Filter.
 * - <i>keyword</i> is store and indexed, but not tokenized.
 * - <i>unindexed</i> is stored but neither indexed nor tokenized.
 * - <i>unstored</i> is not stored, but indexed and tokenized.
 * - <i>text</i> is stored, indexed and tokenized.
 *
 * This class should not be instantinated directly, a new instance of this class
 * can be obtained using the midcom_service_indexer class.
 *
 * A number of predefined fields are available using member fields. These fields
 * are all meta-fields. See their individual documentation for details. All fields
 * are mandatory unless mentioned otherwise explicitly and, as always, assumed to
 * be in the local charset.
 *
 * Remember, that both date and unstored fields are not available on retrieval.
 * For the core fields, all timestamps are stored twice therefore, once as searchable
 * field, and once as readable timestamp.
 *
 * The class will automatically pass all data to the i18n charset conversion functions,
 * thus you work using your site's charset like usual. UTF-8 conversion is done
 * implicitly.
 *
 * @package midcom.services
 * @see midcom_services_indexer
 * @todo The Type field is not yet handled properly.
 */

class midcom_services_indexer_document
{
    /**
     * An acciociative array containing all fields of the current document.
     *
     * Each field is indexed by its name (a string). The value is another
     * array containing the fields "name", type" and "content".
     *
     * @access private
     * @var Array
     */
    var $_fields = Array();

    /**
     * A reference to the i18n service, used for charset conversion.
     *
     * @access protected
     * @var midcom_service_i18n
     */
    var $_i18n = null;

    /**
     * This is the score of this document. Only populated on resultset documents,
     * of course.
     *
     * @var double
     */
    var $score = 0.0;

    /* ------ START OF DOCUMENT FIELDS --------- */

    /**
     * The Resource Identifier of this document. Must be UTF-8 on assignement
     * already.
     *
     * This field is mandatory.
     *
     * @var string
     */
    var $RI = '';

    /**
     * The GUID of the topic the document is assigned to. May be empty for
     * non-midgard resources.
     *
     * This field is mandatory.
     *
     * @var GUID
     */
    var $topic_guid = '';

    /**
     * The name of the component responsible for the document. May be empty for
     * non-midgard resources.
     *
     * This field is mandatory.
     *
     * @var string
     */
    var $component = '';

    /**
     * The fully qualified URL to the document, this should be a PermaLink.
     *
     * This field is mandatory.
     *
     * @var string
     */
    var $document_url = '';

    /**
     * The time of document creation, this is an UNIX timestamp.
     *
     * This field is mandatory.
     *
     * @var int
     */
    var $created = 0;

    /**
     * The time of the last document modification, this is an UNIX timestamp.
     *
     * This field is mandatory.
     *
     * @var int
     */
    var $edited = 0;

    /**
     * The timestamp of indexing.
     *
     * This field is added automatically and to be considered read-only.
     *
     * @var int
     */
    var $indexed = 0;

    /**
     * The MidgardPerson who created the object.
     *
     * This is optional.
     *
     * @var MidgardPerson
     */
    var $creator = null;

    /**
     * The MidgardPerson who modified the object the last time.
     *
     * This is optional.
     *
     * @var MidgardPerson
     */
    var $editor = null;

    /**
     * The title of the document
     *
     * This is mandatory.
     *
     * @var string
     */
    var $title = '';

    /**
     * The content of the document
     *
     * This is mandatory.
     *
     * This field is empty on documents retrieved from the index.
     *
     * @var string
     */
    var $content = '';

    /**
     * The abstract of the document
     *
     * This is optional.
     *
     * @var string
     */
    var $abstract = '';

    /**
     * The author of the document
     *
     * This is optional.
     *
     * @var string
     */
    var $author = '';

    /**
     * An additional tag indicating the source of the document for use by the
     * component doing the indexing. This value is not indexed and should not be
     * used by anybody except the component doing the indexing.
     *
     * This is optional.
     *
     * @var string
     */
    var $source = '';

    /**
     * The full path to the topic that houses the document. For external resources,
     * this should be either a MidCOM topic, to which this resource is accociated or
     * some "directory" after which you could filter. You may also leave
     * it empty prohibiting it to appear on any topic-specific search.
     *
     * The value should be fully qualified, as returned by MIDCOM_NAV_FULLURL, including
     * a trailing slahs, f.x. https://host/path/to/topic/
     *
     * This is optional.
     *
     * @var string.
     */
    var $topic_url = '';

    /**
     * The type of the document, set by subclasses and added to the index
     * automatically.
     *
     * The type *must* reflect the original type hierarchy. It is to be set
     * using the $this->_set_type call <i>after</i> initializing the base class.
     *
     * @see is_a()
     * @see _set_type
     * @access public
     * @var string
     */
    var $type = '';

    /**
     * Security mechainsm used to determine the availability of a search result.
     * Can be one of:
     *
     * - 'default': Use only built-in processing (topic and metadata visibility checks), this is, as you might have guessed, the default.
     * - 'component': Invoke the _on_check_document_visible component interface method of the component after doing default checks.
     *   This security class absolutely requires the document to contain a vaild topic GUID, otherwise access control will fail anyway.
     * - 'function:$function_name': Invoke the globally available function $function_name, its signature is <i>bool $function_name ($document, $topic)</i>,
     *   if you don't change the document during the check, you don't need to pass by-reference, so this is up to you. The topic passed is the
     *   Return true if the document is visible, false otherwise.
     * - 'class:$class_name': Like above, but using a class instead. The class must provide a statically callable <i>get_instance()</i> method, which
     *   returns a usable instance of the class (mostly, this should be a singelton, for performance reasons). The instance returned is assigned
     *   by-reference. On that object, the method check_document_permissions, whose signature must be identical to the function callback.
     *
     * @access public
     * @var string
     * @see midcom_baseclasses_components_interface::_on_check_document_permissions()
     */
    var $security = 'default';

    /* ------ END OF DOCUMENT FIELDS --------- */


    /**
     * Initialize the object, nothing fancy here.
     */
    function midcom_services_indexer_document()
    {
        $this->_i18n =& $GLOBALS['midcom']->get_service('i18n');
    }


    /**
     * Returns the contents of the field name or false on failure.
     *
     * @param string $name The name of the field.
     * @return mixed The content of the field or false on failure.
     */
    function get_field($name)
    {
        if (! array_key_exists($name, $this->_fields))
        {
            debug_push('midcom_service_indexer_document::get_field');
            debug_add("Field {$name} not found in the document.", MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }
        return $this->_i18n->convert_from_utf8($this->_fields[$name]['content']);
    }

    /**
     * Returns the complete internal field record, including type and UTF-8 encoded
     * content.
     *
     * This should normally not be used from the outside, it is geared towards the
     * indexer backends, which need the full field information on indexing.
     *
     * @param string $name The name of the field.
     * @return Array The full content record.
     */
    function get_field_record($name)
    {
        if (! array_key_exists($name, $this->_fields))
        {
            debug_push('midcom_service_indexer_document::get_field_record');
            debug_add("Field {$name} not found in the document.", MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }
        return $this->_fields[$name];
    }

    /**
     * Returns a list of all defined fields.
     *
     * @return Array Fieldname list.
     */
    function list_fields()
    {
        return array_keys($this->_fields);
    }

    /**
     * Remove a field from the list. Nonexistent fields are ignored silently.
     *
     * @param string $name The name of the field.
     */
    function remove_field($name)
    {
        unset($this->_fields[$name]);
    }

    /**
     * Add a date field. A timestamp is expected, which is automatically
     * converted to a suiteable ISO timestamp before storage.
     *
     * Direct specification of the ISO timestamp is not yet possible due
     * to lacking validation outside the timestamp range.
     *
     * If a field of the same name is already present, it is overwritten
     * silently.
     *
     * @param string $name The field's name.
     * @param int $timestamp The timestamp to store.
     */
    function add_date($name, $timestamp)
    {
        // This is always UTF-8 conformant.
        $this->_add_field($name, 'date', strftime('%Y-%m-%dT%H:%M:%SZ', $timestamp), true);
    }

    /**
     * This is a small helper which will create a normal date field and
     * a unindexed _TS-postfixed timestamp field at the same time.
     *
     * This is useful because the date fields are not in a readable format,
     * it can't even be determined that they were a date in the first place.
     * so the _TS field is quite useful if you need the orginal value for the
     * timestamp.
     *
     * @param string $name The field's name, "_TS" is appended for the plain-timestamp field.
     * @param int $timestamp The timestamp to store.
     */
    function add_date_pair($name, $timestamp)
    {
        $this->add_date($name, $timestamp);
        $this->add_unindexed("{$name}_TS", $timestamp);
    }

    /**
     * Add a keyword field.
     *
     * @param string $name The field's name.
     * @param string $content The field's content.
     */
    function add_keyword($name, $content)
    {
        $this->_add_field($name, 'keyword', $content);
    }

    /**
     * Add a unindexed field.
     *
     * @param string $name The field's name.
     * @param string $content The field's content.
     */
    function add_unindexed($name, $content)
    {
        $this->_add_field($name, 'unindexed', $content);
    }

    /**
     * Add a unstored field.
     *
     * @param string $name The field's name.
     * @param string $content The field's content.
     */
    function add_unstored($name, $content)
    {
        $this->_add_field($name, 'unstored', $content);
    }

    /**
     * Add a text field.
     *
     * @param string $name The field's name.
     * @param string $content The field's content.
     */
    function add_text($name, $content)
    {
        $this->_add_field($name, 'text', $content);
    }

    /**
     * Add a search result field, this should normally not be done
     * manually, the indexer will call this function when creating a
     * document out of a search result.
     *
     * @param string $name The field's name.
     * @param string $content The field's content, which is <b>assumed to be UTF-8 already</b>
     */
    function add_result($name, $content)
    {
        $this->_add_field($name, 'result', $content, true);
    }


    /**
     * This will translate all member variables into appropriate
     * field records, the backend should call this immediately before
     * indexing.
     *
     * This call will automatically populate indexed with time()
     * and author with the name of the creator (if set).
     */
    function members_to_fields()
    {
        // Complete fields
        $this->indexed = time();
        if ($this->author == '' && ! is_null($this->creator))
        {
            $this->author = $this->creator->name;
        }

        // __RI does not need to be populated, this is done by backends.
        $this->add_text('__TOPIC_GUID', $this->topic_guid);
        $this->add_text('__COMPONENT', $this->component);
        $this->add_unindexed('__DOCUMENT_URL', $this->document_url);
        $this->add_text('__TOPIC_URL', $this->topic_url);
        $this->add_date_pair('__CREATED', $this->created);
        $this->add_date_pair('__EDITED', $this->edited);
        $this->add_date_pair('__INDEXED', $this->indexed);
        $this->add_text('title', $this->title);
        $this->add_unstored('content', $this->content);

        $this->add_unindexed('__SOURCE', $this->source);
        if (! is_object($this->creator))
        {
            if (! is_null($this->creator))
            {
                debug_print_r("Warning, creator is not an object:", $this->creator, MIDCOM_LOG_INFO);
            }
            $this->add_text('__CREATOR', '');
        }
        else
        {
            $this->add_text('__CREATOR', $this->creator->guid());
        }
        if (! is_object($this->editor))
        {
            if (! is_null($this->editor))
            {
                debug_print_r("Warning, editor is not an object:", $this->creator, MIDCOM_LOG_INFO);
            }
            $this->add_text('__EDITOR', '');
        }
        else
        {
            $this->add_text('__EDITOR', $this->editor->guid());
        }
        $this->add_text('author', $this->author);
        $this->add_text('abstract', $this->abstract);
        $this->add_text('__TYPE', $this->type);
        $this->add_unindexed('__SECURITY', $this->security);
    }

    /**
     * This function should be called after retrieving a document from the
     * index. It will populate all relevant members with the according
     * values.
     */
    function fields_to_members()
    {
        $this->RI = $this->get_field('__RI');
        $this->topic_guid = $this->get_field('__TOPIC_GUID');
        $this->component = $this->get_field('__COMPONENT');
        $this->document_url = $this->get_field('__DOCUMENT_URL');
        $this->topic_url = $this->get_field('__TOPIC_URL');
        $this->created = $this->get_field('__CREATED_TS');
        $this->edited = $this->get_field('__EDITED_TS');
        $this->indexed = $this->get_field('__INDEXED_TS');
        $this->title = $this->get_field('title');

        $this->source = $this->get_field('__SOURCE');
        $this->creator = $this->get_field('__CREATOR');
        if ($this->creator != '')
        {
            $this->creator = mgd_get_object_by_guid($this->creator);
        }
        $this->editor = $this->get_field('__EDITOR');
        if ($this->editor != '')
        {
            $this->editor = mgd_get_object_by_guid($this->editor);
        }
        $this->author = $this->get_field('author');
        $this->abstract = $this->get_field('abstract');
        $this->type = $this->get_field('__TYPE');
        $this->security = $this->get_field('__SECURITY');
    }


    /**
     * Internal helper which actually stores a field.
     *
     * @param string $name The field's name.
     * @param string $type The field's type.
     * @param string $content The field's content.
     * @param bool $is_utf8 Set this to true explicitly, to override charset conversion and assume $content is UTF-8 already.
     * @access protected
     */
    function _add_field($name, $type, $content, $is_utf8 = false)
    {
        $this->_fields[$name] = Array
        (
            'name' => $name,
            'type' => $type,
            'content' => ($is_utf8 ? $content : $this->_i18n->convert_to_utf8($content))
        );
    }

    /**
     * Debugging helper, which will dump the documents contents to the
     * log file using the indicated log level. It will check the log-level
     * explicitly for performance reasons.
     *
     * Note: print_r'ing the entire document might not be an option, as subclasses
     * contain reference to non-dumpable object like the datamanager.
     *
     * @param string $message The log message for the dump
     * @param int $loglevel The log level
     */
    function dump($message, $loglevel = MIDCOM_LOG_DEBUG)
    {
        if ($GLOBALS["midcom_debugger"]->_loglevel < $loglevel)
        {
            return;
        }

        debug_add($message, $loglevel);

        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Author: {$this->author}", $loglevel);
        debug_add("Component: {$this->component}", $loglevel);
        debug_add("Created: " . strftime('%x %X', $this->created), $loglevel);
        debug_add("Creator: " . (! is_object($this->creator) ? 'none' : $this->creator->name), $loglevel);
        debug_add("Document URL: {$this->document_url}", $loglevel);
        debug_add("Edited: " . strftime('%x %X', $this->edited), $loglevel);
        debug_add("Editor: " . (! is_object($this->editor) ? 'none' : $this->editor->name), $loglevel);
        debug_add("RI: {$this->RI}", $loglevel);
        debug_add("Score: {$this->score}", $loglevel);
        debug_add("Source: {$this->source}", $loglevel);
        debug_add("Title: {$this->title}", $loglevel);
        debug_add("Topic GUID: {$this->topic_guid}", $loglevel);
        debug_add("Type: {$this->type}", $loglevel);
        debug_add("Security: {$this->security}", $loglevel);

        debug_print_r('Abstract:', $this->abstract, $loglevel);
        debug_print_r('Content:', $this->content, $loglevel);

        debug_print_r('Additional fields:', $this->_fields, $loglevel);

        debug_pop();
    }

    /**
     * This is a small helper that converts HTML to plain text (relativly simple):
     *
     * Basically, JavaScript blocks and
     * HTML Tags are stripped, and all HTML Entities
     * are converted to their native equivalents.
     *
     * Don't replace with an empty string but with a space, so that constructs like
     * &lt;li&gt;torben&lt;/li&gt;&lt;li&gt;nehmer&lt;/li&gt; are recognized correctly.
     * While this might result in double-spaces between words, this is better then
     * loosing the word boundaries entirely.
     *
     * @param string $text The text to convert to text
     * @return string The converted text.
     */
    function html2text($text)
    {
        $search = Array
        (
            "'<script[^>]*?>.*?</script>'si", // Strip out javascript
            "'<[\/\!]*?[^<>]*?>'si", // Strip out html tags
        );
        $replace = Array
        (
            ' ',
            ' ',
        );
        return $this->_i18n->html_entity_decode(preg_replace($search, $replace, $text));

    }

    /**
     * Returns a textual representation of the specified datamanager
     * field.
     *
     * Actual behavoir is dependent on the datatype. Text fields are
     * accessed directly, for other fields, the CSV representation is
     * used.
     *
     * Text fields run through the html2text converter of the document
     * base class.
     *
     * Attention: This function accesses originally private datamanager
     * members. It is the only possible way to access the CSV interface
     * of individual fields.
     *
     * @param midcom_helper_datamanager $datamanager A reference to the
     *     datamanager instance.
     * @param string $name The name of the field that should be queried
     * @return string The textual representation of the field.
     * @see midcom_services_indexer_document::html2text()
     */
    function datamanager_get_text_representation(&$datamanager, $name)
    {
        switch ($datamanager->_layout['fields'][$name]['datatype'])
        {
            case 'text':
                return $this->html2text($datamanager->data[$name]);

            // Types with no defined representation:
            case 'blob':
            case 'image':
            case 'collection':
            case 'mailtemplate':
            case 'account':
            case 'multiselect':
                return '';
        }

        // Default:
        return $datamanager->_datatypes[$name]->get_csv_data();
    }

    /**
     * Returns a textual representation of the specified datamanager2
     * field.
     *
     * Actual behavoir is dependent on the datatype. The system uses
     * the type's built-in html conversion callbacks
     *
     * Text fields run through the html2text converter of the document
     * base class.
     *
     * @param midcom_helper_datamanager2_datamanager $datamanager A
     *     reference to the datamanager2 instance.
     * @param string $name The name of the field that should be queried
     * @return string The textual representation of the field.
     * @see midcom_services_indexer_document::html2text()
     */
    function datamanager2_get_text_representation(&$datamanager, $name)
    {
        return $this->html2text($datamanager->types[$name]->convert_to_html());
    }

    /**
     * Checks wether the given document is an instance of given document type.
     *
     * This is equivalent to the is_a object hirarchy check, except that it
     * works with MidCOM documents.
     *
     * @see $type
     * @see _set_type()
     * @param string $document_type The base type to search for.
     * @return bool Indicationg relationship.
     */
    function is_a($document_type)
    {
        return (strpos($this->type, $document_type) === 0);
    }

    /**
     * Sets the type of the object, reflecting the inheritance hierarchy.
     *
     * @see $type
     * @see is_a()
     * @access protected
     * @param string $type The name of this document type
     */
    function _set_type($type)
    {
        if (strlen($this->type) == 0)
        {
            $this->type = $type;
        }
        else
        {
            $this->type .= "_{$type}";
        }
    }
}







?>
