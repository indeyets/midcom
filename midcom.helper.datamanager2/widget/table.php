<?php
/**
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * @ignore
 */
die('The class midcom_helper_datamanager2_widget_table has been disabled, it' .
    'violates MidCOM namespacing and is not safe for multiple usage within the' .
    'same form.');

/**
 * Datamanager 2 simple table widget
 *
 * As with all subclasses, the actual initialization is done in the initialize() function,
 * not in the constructor, to allow for error handling.
 *
 * This widget supports all types that export the <i>rows</i> variable as the place to get
 * values. Atm that is only the parameters type.
 *
 * @package midcom.helper.datamanager2
 */
class midcom_helper_datamanager2_widget_table extends midcom_helper_datamanager2_widget
{

    /**
     * row widths
     * @todo make row widths adjustable in schema.
     */
    var $widths = array () ;

    /**
     * add an empty row at the bottom
     * @access private
     * @var boolean
     */
    var $plusrow = true;
    /**
     * The dummyrow used to create a plusrow.
     * @access private
     * @var array of html_quickform_element's
     */
    var $_plusrow_group =array();

    /**
     * The initialization event handler post-processes the maxlength setting.
     *
     * @return boolean Indicating Success
     */
    function _on_initialize()
    {
        if (   !array_key_exists('rows', $this->_type)
            || !is_array($this->_type->rows)
            || is_object($this->_type->rows))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Warning, the field {$this->name} does not have a value member or it is an array or object, you cannot use the text widget with it.",
                MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }

        if ($this->plusrow)
        {
            $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/midcom.helper.datamanager/tablerowadder.js');
        }

        return true;
    }

    /**
     * Adds a simple single-line text form element at this time.
     */
    function add_elements_to_form()
    {
        $attributes = Array
        (
            'size' => 4,
            'class' => 'shorttext',
            'style' => "width: 10em;",
        );

        $table = &$this->_form->createElement('group', 'datamanager_table',null, null,"\n",false);
        $table_rows = array();

        $row_group  = &$this->_form->createElement('group', 'datamanager_table_rows_header' ,null,null,"\n",false);

        foreach ($this->_type->headers as $key => $header)
        {
            $attributes['id'] = $this->name . "_" . $header;
            $attributes['style'] = "width: " . $this->widths[$key];
            $element = &$this->_form->createElement('static');
            $element->setValue("$header");
            $group[] = $element;
        }

        $row_group->setElements($group);
        $table_rows[] = $row_group;
        $i = 0;
        foreach ($this->_type->rows as $key => $row)
        {

            $attributes = Array
            (
                'size' => 4,
                'class' => 'shorttext',
                'style' => "width: 10em;",
            );

            $row_group  = &$this->_form->createElement('group', 'datamanager_table_rows',null,null, "\n", true);
            //$attributes['id'] = $this->name . "_" . $header;
            $group = array();

            foreach ($row as $column => $value)
            {
                $group[] = & $this->_add_element($key,$column,$value);

                /**
                 * Use the first row as template for the plusrow.
                 */
                if ($this->plusrow && $key == 1)
                {
                    $this->_plusrow_group[] = & $this->_add_element($key,$column,"");
                }
            }

            $row_group->setElements($group);
            $table_rows[] = $row_group;
            $i++;

        }

        if ($this->plusrow)
        {
            $table_rows[] = $this->_create_plusrow(count($table_rows)-1) ;
        }
        $table->setElements($table_rows);
        $this->_form->addElement($table, 'datamanager_table');
    }
    /**
     * Creates an element that is returened and added to a row
     * @param int row number
     * @param int column number
     * @param mixed value
     * @return reference the new element
     */
    function & _add_element($row, $column, $value)
    {

        $type = 'text';
        $name = "$row][$column" ;
        if (array_key_exists($column, $this->widths))
        {
            $attributes['style'] = "width: " . $this->widths[$column];
        }

        if (array_key_exists($column, $this->types))
        {
            $type = $this->types[$column];
        }



        switch ($type)
        {
            case 'checkbox':
                $element = &$this->_form->createElement($type,$name,null,null, $attributes );
                $element->setChecked((bool) $value);
                break;

            default:
                $element = &$this->_form->createElement($type,$name,null, $attributes );
                $element->setValue($value);
                break;
        }
        return $element;
    }

    /**
     * create an empty row at the bottom of
     * the table
     * @param the rownumber (used as id)
     */
    function _create_plusrow ($rownum)
    {
        $row_key = 0;

        // update rownames to get the correct rownumber.
        foreach ($this->_plusrow_group as $row_key => $element_)
        {

            $name = "$rownum][$row_key" ;
            $this->_plusrow_group[$row_key]->setName($name);

        }

        $row_key++;
        $name = "$rownum][" . $row_key;

        if (array_key_exists($row_key, $this->widths))
        {
            $attributes['style'] = "width: " . $this->widths[$row_key];
        }

        $element = &$this->_form->createElement('static',$name,null, "\n", array());
        $element->setValue("<a href='#' onclick='add_row(this)' ><img style='padding:0.5em;' src='".MIDCOM_STATIC_URL."/midcom.admin.aegir/plus.png' /></a>");
        $this->_plusrow_group[] = &$element;

        $row_group  = &$this->_form->createElement('group', 'datamanager_table_rows',null,null, "\n", true);
        $row_group->setElements($this->_plusrow_group);

        return $row_group;
    }


    function get_default()
    {
        return $this->_type->rows;
    }

    function sync_type_with_widget($results)
    {

        foreach ($results['datamanager_table_rows'] as $id => $row)
        {
            foreach ($row as $key => $value)
            {
                $this->_type->rows[$id][$key] = $value;
            }
        }

    }

    function is_frozen()
    {
        return false;
    }
}

?>