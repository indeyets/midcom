<?php
/**
 * @package org.openpsa.projects
 * @author Nemein Oy http://www.nemein.com/
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * MidCOM wrapped access to the MgdSchema class, keep logic here
 *
 * @package org.openpsa.projects
 */
class midcom_org_openpsa_expense extends __midcom_org_openpsa_expense
{
    function __construct($id = null)
    {
        return parent::__construct($id);
    }

    function get_parent_guid_uncached()
    {
        if ($this->task != 0)
        {
            $parent = new org_openpsa_projects_task($this->task);
            return $parent;
        }
        else
        {
            return null;
        }
    }

    function _prepare_save()
    {
        //Make sure we have creator
        if (!$this->creator)
        {
            $this->creator = $_MIDGARD['user'];
        }
        //And created
        if (!$this->created)
        {
            $this->created = date('Y-m-d H:i:s');
        }
        //Make sure date is set
        if (!$this->date)
        {
            $this->date = time();
        }
        //Make sure person is set
        if (!$this->person)
        {
            $this->person = $_MIDGARD['user'];
        }
        //Is task is not set abort
        if (!$this->task)
        {
            return false;
        }
        return true;
    }

    function _locale_set()
    {
        $this->_locale_backup = setlocale(LC_NUMERIC, '0');
        setlocale(LC_NUMERIC, 'C');
    }

    function _locale_restore()
    {
        setlocale(LC_NUMERIC, $this->_locale_backup);
    }

    function _on_creating()
    {
        $this->_locale_set();
        return $this->_prepare_save();
    }

    function _on_created()
    {
        $this->_locale_restore();
    }

    function _on_updating()
    {
        $this->_locale_set();
        return $this->_prepare_save();
    }

    function _on_updated()
    {
        $this->_locale_restore();
    }
}

/**
 * Another wrap level to get to component namespace
 * @package org.openpsa.projects
 */
class org_openpsa_projects_expense extends midcom_org_openpsa_expense
{
    function __construct($identifier=NULL)
    {
        return parent::__construct($identifier);
    }
}
?>