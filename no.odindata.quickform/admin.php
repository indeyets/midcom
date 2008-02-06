<?php
/**
 * Admin class for quickform
 * @package no.odindata.quickform
 */

/**
 * @package no.odindata.quickform
 */
class no_odindata_quickform_admin extends midcom_baseclasses_components_request_admin {

    /*
    var $_debug_prefix;

    var $_prefix;
    var $_config;
    var $_topic;
    var $_l10n;
    var $_l10n_midcom;
    var $_config_dm;
    var $_auth;


    var $_mode;

    var $errcode;
    var $errstr;

    var $_local_toolbar;
    var $_topic_toolbar;
    */

    function no_odindata_quickform_admin($topic, $config) {
        parent::midcom_baseclasses_components_request_admin($topic, $config);
     }

    function _on_initialize()
    {
        // Load the content topic and the schema DB
        $this->_load_schema_database();

        // Populate the request data with references to the class members we might need
        $this->_request_data['datamanager'] =& $this->_datamanager;


        // The only thing we do is configuration
        $this->_request_switch[] = Array
        (
            'handler' => 'config_dm',
            'fixed_args' => Array('config'),
            'schemadb' => 'file:/no/odindata/quickform/config/schemadb_config.inc',
            'schema' => 'config',
            'disable_return_to_topic' => false
        );
        $this->_request_switch[] = Array
        (
            'handler' => 'config_dm',
            'schemadb' => 'file:/no/odindata/quickform/config/schemadb_config.inc',
            'schema' => 'config',
            'disable_return_to_topic' => false
        );
    }


    /**
     * Internal helper, loads the configured schema database into the class.
     * It is not yet evaluated by a datamanager, only the file is loaded.
     *
     * @see $_schemadb
     * @see $_schemadb_index
     * @access private
     */
    function _load_schema_database()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $path = $this->_config->get('schemadb');
        $data = midcom_get_snippet_content($path);
        eval("\$this->_schemadb = Array ({$data}\n);");

        // This is a compatibility value for the configuration system
        $GLOBALS['de_linkm_taviewer_schemadbs'] =& $this->_schemadbs;

        if (is_array($this->_schemadb))
        {
            if (count($this->_schemadb) == 0)
            {
                debug_add('The schema database was empty, we cannot use this.', MIDCOM_LOG_ERROR);
                debug_print_r('Evaluated data was:', $data);
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    'Could not load the schema database associated with this topic: The schema DB was empty.');
                // This will exit.
            }
            foreach ($this->_schemadb as $schema)
            {
                $this->_schemadb_index[$schema['name']] = $schema['description'];
            }
        }
        else
        {
            debug_add('The schema database was no array, we cannot use this.', MIDCOM_LOG_ERROR);
            debug_print_r('Evaluated data was:', $data);
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Could not load the schema database associated with this topic. The schema DB was no array.');
            // This will exit.
        }
        debug_pop();
    }

     /**
     * This function adds all of the standard items (configuration and create links)
     * to the topic toolbar.
     *
     * @access private
     */
    function _prepare_topic_toolbar()
    {
        $this->_topic_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => 'config.html',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
            MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN =>
            (
                   ! $_MIDCOM->auth->can_do('midgard:update', $this->_topic)
                || ! $_MIDCOM->auth->can_do('midcom:component_config', $this->_topic)
            )
        ));


    }


    function get_metadata() {
        return FALSE;
    }


} // admin

?>