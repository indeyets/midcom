<?php
/**
 * @package net.nehmer.branchenbuch
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Branchenbuch Schema helper class.
 *
 * This class encaspulates a few function used to manage schemas within the
 * branchenbuch, which are all tied to the account component.
 *
 * @package net.nehmer.branchenbuch
 */

class net_nehmer_branchenbuch_schemamgr extends midcom_baseclasses_components_purecode
{
    /**
     * The topic we are working on.
     *
     * @var midcom_db_topic
     * @access private
     */
    var $_topic = null;

    /**
     * net.nehmer.account Remote management interface class instance.
     * This can be used by outside callees to keep the number of instances down.
     *
     * @var net_nehmer_account_remote
     * @access public
     */
    var $remote = null;


    /**
     * Creates an instance of this class bound to the topic referenced by the
     * argument.
     *
     * @param midcom_db_topic $topic The topic to bind to.
     */
    function net_nehmer_branchenbuch_schemamgr($topic)
    {
        $this->_component = 'net.nehmer.branchenbuch';
        parent::midcom_baseclasses_components_purecode();

        $this->_topic = $topic;
        $this->_load_topic_configuration($this->_topic);

        $_MIDCOM->componentloader->load('net.nehmer.account');
        $interface =& $_MIDCOM->componentloader->get_interface_class('net.nehmer.account');
        $this->remote = $interface->create_remote_controller($this->_config->get('account_topic'));
    }

    /**
     * This function returns a schema class instance matching the user passed to the
     * method. It defaults to the currently active user.
     *
     * The schema will be preprocessed according to the _preprocess_account_schema()
     * helper method.
     *
     * @param mixed $type This is either a midcom_core_user instance (in which case the type
     *     is determined from the account), an explicit account type (string), or null, in
     *     which case the call defaults to the current user.
     * @return midcom_helper_datamanager2_schema The schema database in use for the given user.
     */
    function get_account_schema($type = null)
    {
        $schema = $this->remote->get_account_schema($type);
        $this->_preprocess_account_schema($schema);
        return $schema;
    }

    /**
     * Little helper function, gets the configuratio of a given type regarding
     * alterations to the schema and display defaults.
     *
     * Valid type configuration keys:
     *
     * - Array readonly_list: A list of fields that should be made readonly.
     * - Array hidden_list: A list of fields that should be made hidden.
     * - Array field_order: Custom field ordering, replaces original field ordering
     *     of the schema. Any fields omitted in the new field order will be appended
     *     in original ordering. Any unknown fields in this list will trigger a HTTP
     *     500 error.
     * - string default_view: One of 'alpha' or 'all', used only in the display code
     *     deciding wether the alphabetical or full listing respectivly should be
     *     displayed by default.
     *
     * @param string $typename The name of the account type.
     * @return Array The configuration associated with this type.
     */
    function get_type_config($typename)
    {
        $config = $this->_config->get('type_config');
        if (array_key_exists($typename, $config))
        {
            $config = $config[$typename];
            if (! array_key_exists('readonly_list', $config))
            {
                $config['readonly_list'] = Array();
            }
            if (! array_key_exists('hidden_list', $config))
            {
                $config['hidden_list'] = Array();
            }
            if (! array_key_exists('hidden_list', $config))
            {
                $config['hidden_list'] = Array();
            }
            if (! array_key_exists('default_view', $config))
            {
                $config['default_view'] = 'all';
            }
        }
        else
        {
            $config = Array
            (
                'readonly_list' => Array(),
                'hidden_list' => Array(),
                'field_order' => Array(),
                'default_view' => 'all',
            );
        }

        return $config;
    }

    /**
     * Constructs a relative URL to display a given category based on its configuration
     * settings. This lets you define via configuration wether you want the full listing
     * or the first alphabet letter listing automatically.
     *
     * @param string $name The name of the category to generate a relative URL for.
     * @param string $guid The GUID of the category to generate a relative URL for.
     */
    function get_root_category_url($name, $guid)
    {
        $config = $this->get_type_config($name);
        switch ($config['default_view'])
        {
            case 'alpha':
                return "category/list/alpha/{$guid}/A.html";

            case 'all':
                return "category/list/{$guid}.html";

            case 'customsearch':
                return "category/customsearch/{$guid}.html";

            default:
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    "Unknown default view mode {$config['default_view']} found in type {$name}. Cannot continue.");
                // This will exit.
        }
    }

    /**
     * This function preprocesses the account schema for the requirements of this component.
     * It is used by the various schemadb preparation functions.
     *
     * Note: Keep this in sync with the corresponding function in the entry.php class.
     *
     * @param midcom_helper_datamanager2_schema A reference to the schema to process.
     * @access private
     */
    function _preprocess_account_schema(&$schema)
    {
        $config = $this->get_type_config($schema->name);

        // Step 1: Add the additional fields specified in the configuration.
        $additional_fields_config = $this->_config->get('additional_fields');
        if (array_key_exists($schema->name, $additional_fields_config))
        {
            $additional_fields = $additional_fields_config[$schema->name];
        }
        else
        {
            $additional_fields = $additional_fields_config['default'];
        }
        foreach ($additional_fields as $name => $field)
        {
            // Check if the field name is used already. If so, log an error
            // and ignore silently.
            if (array_key_exists($name, $schema->fields))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("The additional field {$name} cannot be added, the name is already in use.",
                    MIDCOM_LOG_ERROR);
                debug_pop();
                continue;
            }
            $schema->append_field($name, $field);
        }

        // Step 2: Rewrite field order
        if (count($config['field_order'] > 0))
        {
            $user_field_order = $config['field_order'];
            $original_field_order = $schema->field_order;
            // Validate
            foreach ($user_field_order as $fieldname)
            {
                if (! in_array($fieldname, $original_field_order))
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_print_r('user_field_order:', $user_field_order);
                    debug_print_r('original_field_order:', $original_field_order);
                    debug_pop();
                    $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                        "Field {$fieldname} is not known in schema {$schema->name}, cannot rewrite field order.");
                    // This will exit.
                }
            }
            $unset_fields = array_diff($original_field_order, $user_field_order);
            $new_field_order = array_merge($user_field_order, $unset_fields);
            $schema->field_order = $new_field_order;
        }

        // Step 3: Adjust field defaults: storage location and user-set hidden/readonly settings
        foreach ($schema->fields as $name => $copy)
        {
            if ($copy['storage']['location'] != 'parameter')
            {
                switch ($copy['storage']['location'])
                {
                    case 'firstname':
                    case 'lastname':
                    case 'address':
                    case 'postcode':
                    case 'city':
                    case 'homepage':
                    case 'email':
                    case 'homephone':
                    case 'workphone':
                    case 'handphone':
                        // Nothing to do here, as these fields are available.
                        break;

                    default:
                        $schema->fields[$name]['storage']['location'] = 'parameter';
                        $schema->fields[$name]['storage']['domain'] = 'midcom.helper.datamanager2';
                        break;
                }
            }

            if (in_array($name, $config['readonly_list']))
            {
                $schema->fields[$name]['readonly'] = true;
            }
            if (in_array($name, $config['hidden_list']))
            {
                $schema->fields[$name]['hidden'] = true;
            }
        }

        // Step 4: Enforce component-required options.
        if (array_key_exists('username', $schema->fields))
        {
            $schema->fields['username']['hidden'] = true;
        }
        if (array_key_exists('category', $schema->fields))
        {
            $schema->fields['category']['readonly'] = true;
        }
        else
        {
            // Manually add the category field.
            $schema->append_field('category', Array
            (
                'title' => 'category',
                'type' => 'select',
                'type_config' => Array
                (
                    'option_callback' => 'net_nehmer_branchenbuch_callbacks_categorylister',
                    'option_callback_arg' => $schema->name,
                ),
                'widget' => 'select',
                'storage' => 'branche',
                'readonly' => true,
            ));
        }
    }


}

?>
