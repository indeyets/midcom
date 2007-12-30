<?php

/**
 * Favourite object create handler
 *
 * @package net.nemein.favourites
 */
class net_nemein_favourites_handler_create extends midcom_baseclasses_components_handler
{
    var $_content_topic = null;
    var $_favourite_title = null;
    var $_my_way_back = null;

    /**
     * Simple default constructor.
     */
    function net_nemein_favourites_handler_create()
    {
        parent::midcom_baseclasses_components_handler();
    }

    function _on_initialize()
    {
       $this->_content_topic =& $this->_request_data['content_topic'];
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return bool Indicating success.
	 */
    function _handler_create($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        $guid = $args[1];
    	$objectType = $args[0];
    	$objectType_eval = $args[0] . "()";

        // Trying to get the object
        $obj = null;
    	eval("\$obj = new $objectType_eval;");
    	$obj->get_by_guid($guid);

        // Check if user has already favorited this
        $qb = net_nemein_favourites_favourite_dba::new_query_builder();
        $qb->add_constraint('metadata.creator', '=', $_MIDCOM->auth->user->guid);
        $qb->add_constraint('objectGuid', '=', $guid);
        if ($qb->count_unchecked() > 0)
        {
            $favs = $qb->execute();
            if ($favs[0]->bury)
            {
                $_MIDCOM->uimessages->add($this->_l10n->get('net.nemein.favourites'), $this->_l10n->get('you have already buried the item'), 'warning');
            }
            else
            {
                $_MIDCOM->uimessages->add($this->_l10n->get('net.nemein.favourites'), $this->_l10n->get('you have already favourited the item'), 'warning');
            }
            // Redirecting back to the previous page
    	    $_MIDCOM->relocate($_POST['net_nemein_favourites_referer']);
            // This will exit
        }

        if (isset($_POST['net_nemein_favourite_title']))
        {
            // Create favorite object here
            $favourite = new net_nemein_favourites_favourite_dba();
            $favourite->objectType = $objectType;
    	    $favourite->objectGuid = $guid;
    	    $favourite->objectTitle = $_POST['net_nemein_favourite_title'];

            if ($handler_id == 'bury')
            {
                $favourite->bury = true;
            }

            if (!$favourite->create())
            {
                if ($handler_id == 'bury')
                {
                    $_MIDCOM->uimessages->add($this->_l10n->get('net.nemein.favourites'), sprintf($this->_l10n->get('burying %s failed: %s'), $_POST['net_nemein_favourite_title'], mgd_errstr()), 'error');
                }
                else
                {
                    $_MIDCOM->uimessages->add($this->_l10n->get('net.nemein.favourites'), sprintf($this->_l10n->get('favouriting %s failed: %s'), $_POST['net_nemein_favourite_title'], mgd_errstr()), 'error');
                }
                $_MIDCOM->relocate($_POST['net_nemein_favourites_referer']);
                // This will exit
            }
            $_MIDCOM->cache->invalidate($guid);

            // Redirecting back to the previous page
    	    $_MIDCOM->relocate($_POST['net_nemein_favourites_referer']);
            //This will exit
    	}
    	elseif (is_object($obj))
    	{
            $title = null;

            // Trying to figure out a reasonable title for favourite object
            if (   isset($obj->extra)
                && !empty($obj->extra))
            {
                    $title = $obj->extra;
            }
            elseif (   isset($obj->title)
                    && !empty($obj->title))
            {
                    $title = $obj->title;
            }
            elseif (   isset($obj->name)
                    && !empty($obj->name))
            {
                    $title = $obj->name;
            }

            // Special cases
    	    if (   isset($obj->__table__)
    	        && $obj->__table__ == 'person')
    	    {
                $title = $obj->firstname . " " .$obj->lastname;
    	    }

            $this->_favourite_title = $title;

            if (isset($_GET['return']))
            {
                // We have a specified return URL
                $this->_my_way_back = $_GET['return'];
            }
            else
            {
                // Fall back to permalinks
        	    $this->_my_way_back = $_MIDCOM->permalinks->create_permalink($guid);
            }
    	}

    	if ($handler_id == 'bury')
    	{
    	   $data['view_title'] = $this->_l10n->get('bury item');
    	   $data['bury'] = true;
        }
        else
        {
    	   $data['view_title'] = $this->_l10n->get('add to favourites');
    	   $data['bury'] = false;
        }

    	$tmp = array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => '',
            MIDCOM_NAV_NAME => $data['view_title'],
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$data['view_title']}");
        return true;
    }

    function _show_create($handler_id, &$data)
    {

        $this->_request_data['favourite_title'] = $this->_favourite_title;
    	$this->_request_data['my_way_back'] = $this->_my_way_back;
        midcom_show_style('show_add_favourite');
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return bool Indicating success.
	 */
    function _handler_delete($handler_id, $args, &$data)
    {
        $favourite = new net_nemein_favourites_favourite_dba($args[0]);

        $favourite->require_do('midgard:delete');

    	if (!$favourite->delete())
        {
            // handle error
            $_MIDCOM->uimessages->add($this->_l10n->get('net.nemein.favourites'), sprintf($this->_l10n->get('deleting favourite %s failed: %s'), $favourite->objectTitle, mgd_errstr()), 'error');
    	}

        $_MIDCOM->cache->invalidate($favourite->objectGuid);

        return true;
    }

    function _show_delete($handler_id, &$data)
    {
        $_MIDCOM->relocate('');
    }
}

?>
