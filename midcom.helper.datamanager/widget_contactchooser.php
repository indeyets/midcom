<?php
/**
 * @package midcom.helper.datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a widget to choose persons from org.openpsa.contacts
 *
 * @todo Values display
 *
 * @package midcom.helper.datamanager
 */
class midcom_helper_datamanager_widget_contactchooser extends midcom_helper_datamanager_widget {

    /**
     * URL to the OpenPSA Contacts installation's FOAF search interface.
     * For example https://openpsa.example.net/contacts/search/foaf/
     *
     * @var string
     */
    var $contacts_url;

    /**
     * Whether this is a multiple select or not
     * @var boolean
     */
    var $_multiple = false;

    /**
     * Whether to look up DBE parameters of persons
     * @var boolean
     */
    var $enable_dbe = true;

    function _constructor (&$datamanager, $field, $defaultvalue) {
 	    parent::_constructor ($datamanager, $field, $defaultvalue);

        if (!array_key_exists('widget_contactchooser_contacts_url', $this->_field))
        {
            // This component absolutely requires the URL, no sensible fallback
            // Autoprobe the URL via NAP
            $this->_field['widget_contactchooser_contacts_url'] = $this->_find_contacts_url();
        }

        if (array_key_exists('datatype', $this->_field))
        {
            if ($this->_field['datatype'] == 'array')
            {
                $this->_multiple = true;
            }
        }

        $this->contacts_url = $this->_field['widget_contactchooser_contacts_url'];

        // This component uses Ajax, include the handler javascripts
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL."/org.openpsa.helpers/messages.js");
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL."/org.openpsa.helpers/ajaxutils.js");
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL."/midcom.helper.datamanager/contactchooser_ajax.js");

    }

    function _find_contacts_url()
    {
        $contacts_url = null;
        $contacts_node = midcom_helper_find_node_by_component('org.openpsa.contacts');
        if (isset($contacts_node[MIDCOM_NAV_FULLURL]))
        {
            $contacts_url = $contacts_node[MIDCOM_NAV_FULLURL].'search/foaf/';
        }
        return $contacts_url;
    }

    function _read_formdata ()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        //Make sure we have correctly set _multiple (for some reason the contructor set value is lost)
        if (array_key_exists('datatype', $this->_field))
        {
            if ($this->_field['datatype'] == 'array')
            {
                $this->_multiple = true;
            }
        }

        // Read form data only if we have submitted
        if (array_key_exists("midcom_helper_datamanager_submit", $_POST))
        {
            //Reset multiple selection array (since we only get information about checked boxes)
            if ($this->_multiple)
            {
                $this->_value = array();
            }
            if (array_key_exists($this->_fieldname, $_POST))
            {
                debug_add("Got POST:\n---\n".sprint_r($_POST[$this->_fieldname])."---\n");
                foreach ($_POST[$this->_fieldname] as $key => $value)
                {
                    if ($value == 'on')
                    {
                        if ($this->_multiple)
                        {
                            $this->_value[$key] = true;
                        }
                        else
                        {
                            $this->_value = $key;
                        }
                    }
                }
                debug_add("_value is now:\n---\n".sprint_r($this->_value)."---\n");
            }
        }
        debug_pop();
    }

    function _display_person($id)
    {
        $person = new midcom_baseclasses_database_person($id);
        if (class_exists('org_openpsa_contactwidget'))
        {
            $contact = new org_openpsa_contactwidget($person);
            echo "<li>".$contact->show_inline()."</li>\n";
        }
        else
        {
            echo "<li>{$person->lastname}, {$person->firstname}</li>\n";
        }
    }

    function draw_view()
    {
        if (!$this->_value) {
            return true;
        }
        echo "<div class=\"form_contactchooser\">\n";

        //Do we have IDs or GUIDs as values.
        $idtype = $this->_datatype2identifier();

        if ($this->_multiple)
        {
            if (count($this->_value) > 0)
            {
                echo "<ul>\n";
                foreach ($this->_value as $id => $value)
                {
                    $this->_display_person($id);
                }
                echo "</ul>\n";
            }
        }
        else
        {
            echo "<ul>\n";
            $this->_display_person($this->_value);
            echo "</ul>\n";
        }
        echo "</div>\n";
    }

    function draw_widget () {
        echo '<script type="text/javascript" language="text/javascript" src="'.MIDCOM_STATIC_URL . '/org.openpsa.helpers/ajaxutils.js"></script>';
        //echo '<script type="text/javascript" language="text/javascript">';
        //include(MIDCOM_STATIC_ROOT . '/org.openpsa.helpers/ajaxutils.js');
        //echo '</script>';
        echo '<div class="widget_contactchooser">';
        // This should be hidden unless there are selected results, then made visible using JS
        echo "<ul class=\"widget_contactchooser_selected hidden\" id=\"widget_contactchooser_selected_{$this->_fieldname}\">\n";
        if ($this->_value)
        {
            //Make the result list visible.
            echo "<script language='javascript'>ooAjaxSetClass(document.getElementById('widget_contactchooser_selected_{$this->_fieldname}'), 'widget_contactchooser_selected', true)</script>\n";
            if ($this->_multiple)
            {
                foreach ($this->_value as $key => $value)
                {
                    $person = new midcom_baseclasses_database_person($key);
                    if ($value)
                    {
                        $checked = "checked='checked'";
                    }
                    else
                    {
                        $checked = '';
                    }
                    /*
                    echo "<li><input type='checkbox' checked='checked' name='{$this->_fieldname}[{$key}]' id='widget_contactchooser_{$this->_fieldname}_{$key}' />";
                    echo "<label for=\"widget_contactchooser_{$this->_fieldname}_{$key}\">{$person->rname}</label></li>\n";
                    */
                    //This way seems to be necessary due to IE stupidness, though the one above is cleaner
                    echo "<li><label for=\"widget_contactchooser_{$this->_fieldname}_{$key}\">";
                    echo "<input type='checkbox' {$checked} name='{$this->_fieldname}[{$key}]' id='widget_contactchooser_{$this->_fieldname}_{$key}' />";
                    echo "{$person->rname}</label></li>\n";
                }
            }
            else
            {
                $person = new midcom_baseclasses_database_person($this->_value);
                /*
                echo "<li><input type='checkbox' checked='checked' name='{$this->_fieldname}[{$this->_value}]' id='widget_contactchooser_{$this->_fieldname}' />";
                echo "<label for=\"widget_contactchooser_{$this->_fieldname}\">{$person->rname}</label></li>\n";
                */
                //This way seems to be necessary due to IE stupidness, though the one above is cleaner
                echo "<li><label for=\"widget_contactchooser_{$this->_fieldname}\">";
                echo "<input type='checkbox' checked='checked' name='{$this->_fieldname}[{$this->_value}]' id='widget_contactchooser_{$this->_fieldname}' />";
                echo "{$person->rname}</label></li>\n";
            }
        }
        echo "</ul>\n";

        //Check datatype, set return mode
        $modeString = $this->_datatype2identifier();

        //Check multiple, set action mode
        if ($this->_multiple)
        {
            $modeString .= ', multiple';
        }
        else
        {
            $modeString .= ', single';
        }

        echo "<input type='hidden' id='{$this->_fieldname}_ajaxWidgetMode' value='{$modeString}' />\n";
        echo "<input type='hidden' id='{$this->_fieldname}_ajaxUrl' value='{$this->contacts_url}' />\n";
        echo "<input type='hidden' id='{$this->_fieldname}_ajaxFunction' value='ooAjaxContactsWidget' />\n";
        // TODO: Support Safari <input type="search" />
        // http://weblogs.mozillazine.org/hyatt/archives/2004_07.html#005890
        echo "<input type='text' autocomplete='off' class='ajax_editable' id='{$this->_fieldname}' onfocus='ooAjaxFocus(this);' onblur='ooAjaxBlur_noSave(this);' onkeyup='ooAjaxChange(this);' />\n";

        // Here we place the search results, hidden by default
        echo "<ul class=\"widget_contactchooser_resultset\" style=\"display: none;\" id=\"widget_contactchooser_resultset_{$this->_fieldname}\"></ul>\n";
        echo '</div>';

    }

    function _datatype2identifier()
    {
        switch ($this->_field['datatype'])
        {
            case 'integer':
            case 'array':
                return 'id';
            break;
            default:
                //fall-trough intentional
            case 'text':
                return 'guid';
            break;
        }
    }

}


?>