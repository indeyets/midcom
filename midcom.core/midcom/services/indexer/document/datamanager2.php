<?php

/**
 * @package midcom.services
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:datamanager2.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This class is geared to ease indexing of datamanager2 driven documents. The
 * user invoking the indexing must have full read permissions to the object
 * otherwise the NAP or Metadata objects can probably not be loaded successfully.
 *
 * <b>Basic indexing operation</b>
 *
 * This class uses a number of conventions, see below, to merge an existing,
 * datamanager2 driven document into an indexing capable document. It requires
 * the callee to instantiate the datamanager2, as this class would have no
 * idea where to take the schema database from.
 *
 * Additional information is taken out of the Metadata record and the NAP
 * record, both of which have to be available to the indexer.
 *
 * The RI (the GUID) from the base class is left untouched.
 *
 * <b>Indexing field defaults:</b>
 *
 * Unless you specify anything else explicitly in the schema,
 * the class will merge all text based fields together to form the <i>content</i>
 * field of the index record, to allow for easy searching of the document.
 * This will *not* include any metadata like keywords or summaries.
 *
 * If the schema contains a field <i>abstract,</i> it will also be used as
 * abstract field for the indexing process. In the same way, fields named
 * <i>title</i> or <i>author</i> will be used for the index document's title
 * or author respectively. The contents of abstract, title and author will also
 * be appended to the content field at the end of the object construction,
 * easing searching over this fields.
 *
 * If no abstract field is present, the first 200 characters of the content
 * area are used instead.
 *
 * Not all types can be indexed, check the various types in question about their
 * indexing capabilities. In general, if the system should index any non-text
 * field, it will use the CSV representation for implicit conversion.
 *
 * Metadata processing is done by the base class.
 *
 * <b>Document title:</b>
 *
 * In contrast to earlier implementations of the Datamanager 1 document type,
 * there is no NAP interaction anymore to determine the document title.
 *
 * Therefore, you should either have an auto-indexed title field, or an assortment
 * of other fields manually assigned to index to the title field.
 *
 * <b>Configurability using the Datamanager schema:</b>
 *
 * You can decorate datamanager fields with various directives influencing
 * the indexing. See the Datamanager's schema documentation for details.
 * Basically, you can choose from the following indexing methods using the
 * key 'index_method' for each field:
 *
 * - The default <i>auto</i> mode will use the above guidelines to determine
 *   the indexing destination automatically, adding data to the content, abstract,
 *   title and author fields respectively.
 * - You can specify <i>abstract,</i> <i>content,</i> <i>title</i> or
 *   <i>author</i> to indicate that the field should be used for the indicated
 *   document fields. The content selector may be specified more then once,
 *   indicating that the content of the relevant fields should be merged.
 * - Any date field can be indexed into its own, range-filterable field using
 *   the <i>date</i> method. In this case, two document fields will be created
 *   actually. One containing the filterable timestamp named directly after
 *   the schema field, and a second one, having the _TS postfix which is set as
 *   noindex containing the plain timestamp.
 * - Finally, you can explicitly index a field as a separate document field
 *   using one of the five field types <i>keyword,</i> <i>unindexed,</i>
 *   <i>unstored</i> or <i>text.</i>  You can further control if the content
 *   of these fields is also added to the main content field. This is useful
 *   if you want to have fields searchable both by explicit field specification
 *   and the default field for simpler searches. This is controlled by setting
 *   the bolean key 'index_merge_with_content' in the field, which defaults
 *   to true.
 * - <i>noindex</i> will prevent indexing of this field.
 *
 * The documents type is "midcom_datamanager2".
 *
 * Be aware that this class is designed to work on *datamanager2* instances, not
 * formmanagers, controllers or storage backends. It is also only targeted for the
 * actual database storage backend, so the nullstorage backend will not work.
 *
 * @package midcom.services
 * @see midcom_services_indexer
 * @see midcom_helper_datamanager2_datamanager
 */

class midcom_services_indexer_document_datamanager2 extends midcom_services_indexer_document_midcom
{
    /**
     * The datamanager instance of the document we need to index.
     * This is passed by reference through the constructor.
     *
     * @access private
     * @var midcom_helper_datamanager2_datamanager
     */
    var $_datamanager = null;

    /**
     * The schema in use. This is referenced into the datamanager2 instance.
     *
     * @access private
     * @var midcom_helper_datamanager2_schema
     */
    var $_schema = null;

    /**
     * The constructor initializes the member variables and invokes
     * _process_datamanager, which will read and process the information
     * out of that instance.
     *
     * The document is ready for indexing after construction. On any
     * critical error, generate_error is triggered.
     *
     * @param midcom_helper_datamanager2_datamanager &$datamanager The fully initialized datamanager2 instance to use
     */
    function midcom_services_indexer_document_datamanager2(&$datamanager)
    {
        parent::midcom_services_indexer_document_midcom($datamanager->storage->object);

        $this->_set_type('datamanager2');

        $this->_datamanager =& $datamanager;
        $this->_schema =& $this->_datamanager->schema;

        $this->_process_datamanager();
        $this->_complete_fields();
    }

    /**
     * Completes all fields which are not yet complete:
     *
     * content is completed with author, title and, if necessary, abstract.
     *
     * The title is set to the documents' URL in case that no title is set yet. The title
     * is not added to the content field in that case.
     *
     * @access private
     */
    function _complete_fields()
    {
        $this->content .= "{$this->author}\n{$this->title}\n";

        // Add the abstract only if we haven't done so already.
        if (strstr($this->abstract, $this->content) === false)
        {
            $this->content .= "{$this->abstract}\n";
        }

        if (! $this->title)
        {
            $this->title = $this->document_url;
        }
    }


    /**
     * Processes the information contained in the datamanager instance.
     *
     * The function iterates over the fields in the schema, and processes them
     * according to the rules given in the introduction.
     */
    function _process_datamanager()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Processing datamanager schema {$this->_schema->name}");

        foreach ($this->_schema->fields as $name => $field)
        {
            debug_print_r("Processing field {$name}:", $field);

            switch ($field['index_method'])
            {
                case 'auto':
                    $this->_process_auto_field($name);
                    break;

                case 'abstract':
                    debug_add("Adding field {$name} as abstract");
                    $this->abstract = $this->datamanager2_get_text_representation($this->_datamanager, $name);
                    break;

                case 'content':
                    debug_add("Adding field {$name} to content");
                    $this->content .= $this->datamanager2_get_text_representation($this->_datamanager, $name) . "\n";
                    break;

                case 'title':
                    debug_add("Adding field {$name} as title");
                    $this->title = $this->datamanager2_get_text_representation($this->_datamanager, $name);
                    break;

                case 'author':
                    debug_add("Adding field {$name} as author");
                    $this->author = $this->datamanager2_get_text_representation($this->_datamanager, $name);
                    break;

                case 'date':
                    debug_add("Adding field {$name} as date");
                    $this->_add_as_date_field($name);
                    break;

                case 'unstored':
                case 'unindexed':
                case 'text':
                case 'keyword':
                    debug_add("Adding field {$name} as {$field['index_method']}");
                    $data = $this->datamanager2_get_text_representation($this->_datamanager, $name);
                    $this->_add_field($name, $field['index_method'], $data);
                    if ($field['index_merge_with_content'])
                    {
                        $this->content .= $data . "\n";
                    }
                    break;

                case 'noindex':
                    debug_add("Not indexing field {$name}");
                    break;

                default:
                    debug_pop();
                    $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                        "Datamanager2 Indexer Document: Unknown indexing method {$field['index_method']} for field {$name} discovered, aborting.");
                    // This will exit.
            }
        }

        if ($this->abstract == '')
        {
            if (strlen($this->content) > 200)
            {
                $this->abstract = substr($this->content, 0, 200) . ' ...';
            }
            else
            {
                $this->abstract = $this->content;
            }
        }

        debug_pop();
        return false;
    }

    /**
     * This function tries to convert the field $name into a date
     * representation. Unixdate fields are used directly (localtime is used,
     * not GMT), other fields will be parsed with strtodate.
     *
     * Invalid strings which are not parseable using strtotime will be
     * stored as a "0" timestamp.
     *
     * Be aware, that this will work only for current dates in range of an
     * UNIX timestamp. For all other cases you should use an ISO 8601 representation,
     * which should work as well with Lucene range queries.
     *
     * @access private
     * @todo Refactor this to use PEAR Date
     * @param string $name The name of the field that should be stored
     */
    function _add_as_date_field($name)
    {
        if ($this->_schema->fields[$name]['type'] == 'date')
        {
            $this->add_date_pair($name, $this->_datamanager->types[$name]->value->getTime());
        }
        else
        {
            $string = $this->datamanager2_get_text_representation($this->_datamanager, $name);
            $timestamp = strtotime($string);
            if ($timestamp === -1)
            {
                debug_add("The string representation of the field {$name} could not be parsed into a timestamp; treating as 0.", MIDCOM_LOG_INFO);
                debug_print_r('String representation was:', $string);
                $timestamp = 0;
            }
            $this->add_date_pair($name, $timestamp);
        }
    }

    /**
     * This helper will process the given field using the guidelines given in
     * the class documentation.
     *
     * @param string $name The name of the field that should be automatically processed.
     */
    function _process_auto_field($name)
    {
        switch ($name)
        {
            case 'abstract':
                debug_add("Adding field {$name} as abstract");
                $this->abstract = $this->datamanager2_get_text_representation($this->_datamanager, $name);
                break;

            case 'title':
                debug_add("Adding field {$name} as title");
                $this->title = $this->datamanager2_get_text_representation($this->_datamanager, $name);
                break;

            case 'author':
                debug_add("Adding field {$name} as author");
                $this->author = $this->datamanager2_get_text_representation($this->_datamanager, $name);
                break;

            default:
                debug_add("Adding field {$name} to content");
                $this->content .= $this->datamanager2_get_text_representation($this->_datamanager, $name) . "\n";
                break;

        }
    }
}

?>