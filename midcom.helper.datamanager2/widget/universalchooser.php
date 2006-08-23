<?php
/**
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/** Base class include */
require_once('radiocheckselect.php');

/**
 * Datamanger 2 universal "chooser" widget.
 *
 * Based on the radiocheckselect widget
 *
 * It can only be bound to a select type (or subclass thereoff), and inherits the configuration
 * from there as far as possible.
 *
 * Note for this widget to work correctly you probably need these two set in type_config
 * (not strictly required, if your options list/callback always contains everything needed, which is unlikely)
 *     'require_corresponding_option' => false,
 *     'allow_other'    => true,
 *
 * @package midcom.helper.datamanager2
 */
class midcom_helper_datamanager2_widget_universalchooser extends midcom_helper_datamanager2_widget_radiocheckselect
{
    /**
     * Class to search for
     *
     * @var string
     */
    var $class = false;

    /**
     * Which component the searched class belongs to
     *
     * @var string
     */
    var $component = false;

    /**
     * Field/property to use for the title in listings
     *
     * @var string
     */
    var $titlefield = 'name';

    /**
     * Field/property to use as the key/id
     *
     * @var string
     */
    var $idfield = 'guid';

    /**
     * Associative array of constraints (besides the search term), always AND
     *
     * Example:
     *     'constraints' => array (
     *         array(
     *             'field' => 'username',
     *             'op' => '<>',
     *             'value' => '',
     *         ),
     *     ),
     *
     * @var array
     */
    var $constraints = array();

    /**
     * Fields/properties to search the keyword for, always OR and specified after the constaints above
     *
     * Example:
     *      'searchfields' => array('firstname', 'lastname', 'email', 'username'),
     *
     * @var array
     */
    var $searchfields = array();

    /**
     * associative array of ordering info, always added last
     *
     * Example: 
     *     'orders' => array(array('lastname' => 'ASC'), array('firstname' => 'ASC')),
     *
     * @var array
     */
    var $orders = array();
    /**
     * Allow creation of new objects (depends on components support as well for the actual work)
     *
     * @var boolean
     */
    var $allow_create = false;

   /**
     * The initialization event handler verifies the correct type.
     *
     * @return bool Indicating Success
     */
    function _on_initialize()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (is_a('midcom_helper_datamanager2_type_select', $this->_type))
        {
            debug_add("Warning, the field {$this->name} is not a select type or subclass thereoff, you cannot use the universalchooser widget with it.",
                MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }

        if (empty($this->class))
        {
            debug_add("Warning, the field {$this->name} does not have class defined.",
                MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }

        if (empty($this->component))
        {
            debug_add("Warning, the field {$this->name} does not have component the class {$this->class} belongs to defined.",
                MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }

        if (empty($this->searchfields))
        {
            debug_add("Warning, the field {$this->name} does not have searchfields defined, it can never return results.",
                MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }
        
        if (!$this->_check_class())
        {
            debug_add("Warning, cannot get class {$this->class} loaded.",
                MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }

        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/Pearified/Javascript/Prototype/prototype.js');
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/midcom.helper.datamanager2/universalchooser.js');
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/midcom.services.uimessages/protoGrowl.js');
        $_MIDCOM->add_link_head(
            array(
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . '/midcom.services.uimessages/protoGrowl.css'
            )
        );
        $_MIDCOM->add_link_head(
            array(
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . '/midcom.helper.datamanager2/universalchooser.css'
            )
        );

        debug_pop();
        return true;
    }
    
    function _check_class()
    {
        if (class_exists($this->class))
        {
            return true;
        }
        $_MIDCOM->componentloader->load($this->component);
        return class_exists($this->class);
    }
    
    function _get_single_key($key)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("calling {$this->class}::new_query_builder()");
        $qb = call_user_func(array($this->class, 'new_query_builder'));
        debug_add("adding constraint {$this->idfield}={$key}");
        $qb->add_constraint($this->idfield, '=', $key);
        $results = $qb->execute();
        debug_print_r('Got results:', $results);
        if (empty($results))
        {
            debug_pop();
            return false;
        }
        debug_pop();
        return $results[0];
    }
    
    function _get_key_value($key)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $object = $this->_get_single_key($key);
        if (!is_object($object))
        {
            // Could not object, or got wrong type of object
            debug_add("Could not get object for key: {$key}", MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }
        $titlefield =& $this->titlefield;
        $value = $object->$titlefield;
        debug_pop();
        return $value;
    }

    /**
     * Adds checkboxes / radioboxes to the form.
     */
    function add_elements_to_form()
    {
        $idsuffix = $this->_create_random_suffix();
        debug_push_class(__CLASS__, __FUNCTION__);
        $elements = Array();

        $existing_elements = $this->_type->selection;
        foreach ($existing_elements as $key)
        {
            debug_add("Processing key {$key}");
            $value = $this->_get_key_value($key);
            if ($value === false)
            {
                debug_add('Got strict boolean false as value, skipping field');
                continue;
            }
            debug_add("Adding field '{$key}' => '{$value}'");
            if ($this->_type->allow_multiple)
            {
                $elements[] =& HTML_QuickForm::createElement
                (
                    'checkbox',
                    $key,
                    $key,
                    $this->_translate($value),
                    Array('class' => 'checkbox')
                );
            }
            else
            {
                $elements[] =& HTML_QuickForm::createElement
                (
                    'radio',
                    null,
                    $key,
                    $this->_translate($value),
                    $key,
                    Array(
                        'class' => 'radiobutton',
                        'id' => "universalchooser_{$idsuffix}_{$key}",
                    )
                );
            }
        }


        /*
        TODO: Add shared secret based hash creation (checked in handler end) to make sure the request actually
        comes from universalchooser (or someone who A: is competent B: has access to the secret)
        */

        // Serialize the parameter we need in the search end
        $searchconstraints_serialized = "idsuffix={$idsuffix}";
        $serialize = array('component', 'class', 'titlefield', 'idfield', 'searchfields');
        foreach ($serialize as $field)
        {
            $data = $this->$field;
            if (is_array($data))
            {
                foreach ($data as $k => $v)
                {
                    $searchconstraints_serialized .= '&' . rawurlencode("{$field}[{$k}]") . '=' . rawurlencode($v);
                }
            }
            else
            {
                $searchconstraints_serialized .= "&{$field}=" . rawurlencode($data);
            }
        }
        foreach ($this->constraints as $key => $data)
        {
            $searchconstraints_serialized .= '&' . rawurlencode("constraints[{$key}]['field']") . '=' . rawurlencode($data['field']);
            $searchconstraints_serialized .= '&' . rawurlencode("constraints[{$key}]['op']") . '=' . rawurlencode($data['op']);
            $searchconstraints_serialized .= '&' . rawurlencode("constraints[{$key}]['value']") . '=' . rawurlencode($data['value']);
        }
        foreach ($this->orders as $key => $data)
        {
            foreach ($data as $prop => $sort)
            {
                $searchconstraints_serialized .= '&' . rawurlencode("orders[{$key}][{$prop}]") . '=' . rawurlencode($sort);
            }
        }

        // Start a new group to not to clutter the values
        $elements2 = array();
        // Hidden input for Ajax url
        $nav = new midcom_helper_nav();
        $root_node = $nav->get_node($nav->get_root_node());
        $url = $root_node[MIDCOM_NAV_FULLURL] . 'midcom-exec-midcom.helper.datamanager2/universalchooser_handler.php';
        $elements2[] =& HTML_QuickForm::createElement
            (
                'hidden',
                "widget_universalchooser_search_{$idsuffix}_url",
                $url,
                array(
                    'id' => "widget_universalchooser_search_{$idsuffix}_url",
                    )
            );

        // Hidden input for mode
        $mode = 'single';
        if ($this->_type->allow_multiple)
        {
            $mode = 'multiple';
        }
        $elements2[] =& HTML_QuickForm::createElement
            (
                'hidden',
                "widget_universalchooser_search_{$idsuffix}_mode",
                $mode,
                array(
                    'id' => "widget_universalchooser_search_{$idsuffix}_mode",
                    )
            );

        // Hidden input for the serialized constraints
        $elements2[] =& HTML_QuickForm::createElement
            (
                'hidden',
                "widget_universalchooser_search_{$idsuffix}_constraints",
                $searchconstraints_serialized,
                array(
                    'id' => "widget_universalchooser_search_{$idsuffix}_constraints",
                    )
            );

        // Hidden input for the id parent label
        $elements2[] =& HTML_QuickForm::createElement
            (
                'hidden',
                "widget_universalchooser_search_{$idsuffix}_labelid",
                $this->name . '_universalchooser_' . $idsuffix . '_label',
                array(
                    'id' => "widget_universalchooser_search_{$idsuffix}_labelid",
                    )
            );
        $elements2[] =& HTML_QuickForm::createElement
            (
                'hidden',
                "widget_universalchooser_search_{$idsuffix}_fieldname",
                $this->name,
                array(
                    'id' => "widget_universalchooser_search_{$idsuffix}_fieldname",
                    )
            );

        // Text input for the search box
        $elements2[] =& HTML_QuickForm::createElement
            (
                'text',
                "widget_universalchooser_search_{$idsuffix}",
                "widget_universalchooser_search_{$idsuffix}",
                array(
                    'onKeyUp'       => "midcom_helper_datamanager2_widget_universalchooser_search_onkeyup('{$idsuffix}')",
                    'class'         => 'shorttext universalchooser_search',
                    'autocomplete'  => 'off',
                    'id'            => "widget_universalchooser_search_{$idsuffix}",
                    )
            );

        $group =& $this->_form->addGroup($elements, $this->name, $this->_translate($this->_field['title']), "<br />");
        $group2 =& $this->_form->addGroup($elements2, $this->name . '_universalchooser_' . $idsuffix, '', '');
        $group2->setAttributes(Array('class' => 'midcom_helper_datamanager2_widget_universalchooser'));
        if ($this->_type->allow_multiple)
        {
            $group->setAttributes(Array('class' => 'radiobox'));
        }
        else
        {
            $group->setAttributes(Array('class' => 'checkbox'));
        }
        debug_pop();
    }

    /**
     * Creates random string of 8 characters
     *
     * Used to generate the random suffix to distinguish between instances
     * TODO: Use together with hashed "password" to secure the searching interface
     * @return string random string
     */
    function _create_random_suffix()
    {
        //Use mt_rand if possible (faster, more random)
        if (function_exists('mt_rand'))
        {
            $rand = 'mt_rand';
        }
        else
        {
            $rand = 'rand';
        }
        $tokenchars = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $token = $tokenchars[$rand(0, strlen($tokenchars) - 11)];
        for ($i = 1; $i < 8; $i++)
        {
            $token .= $tokenchars[$rand(0, strlen($tokenchars) - 1)];
        }
        return $token;
    }

    /**
     * The defaults of the widget are mapped to the current selection.
     */
    function get_default()
    {
        if ($this->_type->allow_multiple)
        {
            $defaults = Array();
            foreach ($this->_type->selection as $key)
            {
                $defaults[$key] = true;
            }
            return Array($this->name => $defaults);
        }
        else
        {
            if (count($this->_type->selection) > 0)
            {
                return Array($this->name => $this->_type->selection[0]);
            }
            else if ($this->_field['required'])
            {
                // Select the first radiobox always when this is a required field:
                $all = $this->_type->list_all();
                reset($all);
                return Array($this->name => key($all));
            }
            else
            {
                return null;
            }
        }
    }

    /**
     * The current selection is compatible to the widget value only for multiselects.
     * We need minor typecasting otherwise.
     */
    function sync_type_with_widget($results)
    {
        if ($this->_type->allow_multiple)
        {
            $this->_type->selection = Array();

            if ($results[$this->name])
            {
                $all_elements = $this->_type->list_all();
                foreach ($all_elements as $key => $value)
                {
                    if (array_key_exists($key, $results[$this->name]))
                    {
                        $this->_type->selection[] = $key;
                    }
                }
            }
        }
        else
        {
            if ($results[$this->name])
            {
                $this->_type->selection = Array($results[$this->name]);
            }
        }
    }

    function render_content()
    {
        if ($this->_type->allow_multiple)
        {
            echo '<ul>';
            if (count($this->_type->selection) == 0)
            {
                echo '<li>' . $this->_translate('type select: no selection') . '</li>';
            }
            else
            {
                foreach ($this->_type->selection as $key)
                {
                    echo '<li>' . $this->_get_key_value($key) . '</li>';
                }
            }
            echo '</ul>';
        }
        else
        {
            if (count($this->_type->selection) == 0)
            {
                echo $this->_translate('type select: no selection');
            }
            else
            {
                echo $this->_get_key_value($this->_type->selection[0]);
            }
        }
        /* TODO: What to do with this ??
        if ($this->_type->allow_other)
        {
            if (! $this->_type->allow_multiple)
            {
                echo '; ';
            }
            echo $this->_translate($this->othertext) . ': ';
            echo implode(',', $this->_type->others);
        }
        */
    }

}

?>