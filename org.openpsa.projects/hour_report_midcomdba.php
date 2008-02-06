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
class midcom_org_openpsa_hour_report extends __midcom_org_openpsa_hour_report
{
    // Simple readonly boolean property on whethe this is invoiced
    var $is_invoiced = false;
    var $is_approved = false;

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
        //Make sure our hours property is a float
        $this->hours = (float)$this->hours;
        $this->hours = round($this->hours, 2);

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

    function _on_loaded()
    {
        if (   $this->invoiced != '0000-00-00 00:00:00'
            && $this->invoiced != '0000-00-00 00:00:00+0000'
            && $this->invoiced)
        {
            $this->is_invoiced = true;
        }

        if (   $this->approved != '0000-00-00 00:00:00'
            && $this->approved != '0000-00-00 00:00:00+0000'
            && $this->approved
            && $this->approver)
        {
            $this->is_approved = true;
        }

        return parent::_on_loaded();
    }

    function _on_creating()
    {
        $this->_locale_set();
        return $this->_prepare_save();
    }

    function _on_created()
    {
        $this->_locale_restore();
        //Try to mark the parent task as started
        $parent = $this->get_parent_guid_uncached();
        if (is_object($parent))
        {
            $parent->start($this->person);
            $parent->update_cache();
        }
        return true;
    }

    function _on_updating()
    {
        $this->_locale_set();
        return $this->_prepare_save();
    }

    function _on_updated()
    {
        $this->_locale_restore();

        $parent = $this->get_parent();
        if ($parent)
        {
            $parent->update_cache();
        }

        return true;
    }

    function _on_deleted()
    {
        $parent = $this->get_parent();
        if ($parent)
        {
            $parent->update_cache();
        }
    }

    /**
     * Mark the hour report as approved
     */
    function approve()
    {
        $this->approver = $_MIDGARD['user'];

        // FIXME: This used to store unix timestamp so old data must be fixed
        $this->approved = gmdate('Y-m-d H:i:s', time());

        return $this->update();
    }
}

/**
 * Another wrap level to get to component namespace
 * @package org.openpsa.projects
 */
class org_openpsa_projects_hour_report extends midcom_org_openpsa_hour_report
{
    function __construct($identifier=NULL)
    {
        return parent::__construct($identifier);
    }
}
?>