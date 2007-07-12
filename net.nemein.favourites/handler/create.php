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

    function _handler_create($handler_id, $args, &$data)
    {
        $guid = $args[1];
	$objectType_eval = $args[0] . "()";
	$objectType = $args[0];
	echo $guid;
	echo $objectType;

        // Trying to get the object
        $obj = null;	
	eval("\$obj = new $objectType_eval;");
	$obj->get_by_guid($guid);
	print_r($obj);

        if (isset($_POST['net_nemein_favourite_title']))
        {
            // Create favorite object here
            $favourite = new midcom_net_nemein_favourites_favourite();        
            $favourite->objectType = $objectType;
	    $favourite->objectGuid = $guid;
	    $favourite->objectTitle = $_POST['net_nemein_favourite_title'];

	    if (!$favourite->create())
	    {
	    echo "NO GO";
                return false;
	    }
            
            // Redirecting back to the previous page
	    //$_MIDCOM->relocate($_POST['net_nemein_favourites_referer']);
            //This will exit
	}
	elseif (is_object($obj))
	{
            $title = null;
       
            // Trying to figure out a reasonable title for favourite object
	    if (isset($obj->extra) && !empty($obj->extra))
	    {
                $title = $obj->extra;
	    }
	    elseif (isset($obj->title) && !empty($obj->title))
	    {
                $title = $obj->title;
	    }
	    elseif (isset($obj->name) && !empty($obj->name))
	    {
                $title = $obj->name;
	    }

            // Special cases
	    if (isset($obj->__table__) && $obj->__table__ == 'person')
	    {
                $title = $obj->firstname . " " .$obj->lastname;
	    }

	    echo "WE HAVE TITLE " . $title . " ";

            $this->_favourite_title = $title;
	    $this->_my_way_back = $_SERVER['HTTP_REFERER'];
	}

        return true;
    }

    function _show_create($handler_id, &$data)
    {
        $this->_request_data['favourite_title'] = $this->_favourite_title;
	$this->_request_data['my_way_back'] = $this->_my_way_back;
        midcom_show_style('show_add_favourite');
    }
}

?>
