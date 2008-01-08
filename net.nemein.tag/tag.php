<?php
/**
 * @package net.nemein.tag
 * @author Henri Bergius, http://bergie.iki.fi
 * @copyright Nemein Oy, http://www.nemein.com
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM wrapped class for access to tags
 *
 * @package net.nemein.tag
 */
class net_nemein_tag_dba extends __net_nemein_tag_dba
{
    function net_nemein_tag_dba($id = null)
    {
        $this->_use_rcs = false;
        return parent::__net_nemein_tag_dba($id);
    }

    function get_by_tag($tag)
    {
        if (empty($tag))
        {
            return false;
        }
        $qb = net_nemein_tag_dba::new_query_builder();
        $qb->add_constraint('tag', '=', $tag);
        $results = $qb->execute();
        if (   is_array($results)
            && count($results) > 0)
        {
            return $results[0];
        }
        return false;
    }

    function get_parent_guid_uncached()
    {
        return null;
    }

    function _on_creating()
    {
        if (!$this->validate_tag($this->tag))
        {
            return false;
        }
        if ($this->_check_duplicates() > 0)
        {
            return false;
        }
        return true;
    }

    /**
     * Ensure validity of given tag
     *
     * @param string $tag Tag to validate
     * @return boolean Whether tag is valid
     */
    function validate_tag($tag)
    {
        if (empty($tag))
        {
            $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('net.nemein.tag', 'net.nemein.tag'), sprintf($_MIDCOM->i18n->get_string('tag "%s" is not valid. tags may not be empty', 'net.nemein.tag'), $tag), 'info');
            return false;
        }
        if (!is_string($tag))
        {
            $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('net.nemein.tag', 'net.nemein.tag'), sprintf($_MIDCOM->i18n->get_string('tag "%s" is not valid. tags must be valid strings', 'net.nemein.tag'), $tag), 'info');
            return false;
        }
        if (is_numeric($tag))
        {
            $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('net.nemein.tag', 'net.nemein.tag'), sprintf($_MIDCOM->i18n->get_string('tag "%s" is not valid. tags may not be numeric', 'net.nemein.tag'), $tag), 'info');
            return false;
        }
        if (   strstr($tag, '"')
            || strstr($tag, "'"))
        {
            $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('net.nemein.tag', 'net.nemein.tag'), sprintf($_MIDCOM->i18n->get_string('tag "%s" is not valid. tags may not contain quotes', 'net.nemein.tag'), $tag), 'info');
            return false;
        }

        return true;
    }

    function _on_updating()
    {
        if (!$this->validate_tag($this->tag))
        {
            return false;
        }
        if ($this->_check_duplicates() > 0)
        {
            return false;
        }
        return true;
    }

    function _check_duplicates()
    {
        $qb = net_nemein_tag_dba::new_query_builder();
        if ($this->id)
        {
            $qb->add_constraint('id', '<>', $this->id);
        }
        $qb->add_constraint('tag', '=', $this->tag);
        return $qb->count_unchecked();
    }

    /**
     * By default all authenticated users should be able to do
     * whatever they wish with tag objects, later we can add
     * restrictions on object level as necessary.
     */
    function get_class_magic_default_privileges()
    {
        $privileges = parent::get_class_magic_default_privileges();
        $privileges['USERS']['midgard:create']  = MIDCOM_PRIVILEGE_ALLOW;
        $privileges['USERS']['midgard:update']  = MIDCOM_PRIVILEGE_ALLOW;
        $privileges['USERS']['midgard:read']    = MIDCOM_PRIVILEGE_ALLOW;
        return $privileges;
    }
}
?>