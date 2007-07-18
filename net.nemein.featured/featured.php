<?php

class net_nemein_featured_item_dba extends __net_nemein_featured_item_dba
{
    function net_nemein_featured_featured_dba()
    {
        parent::__net_nemein_featured_featured_dba();
    }

    function load_featured_item($substyle = "")
    {
        $substyle_parameter = "";

        if (!empty($substyle))
	{
            $substyle_parameter = "/midcom-substyle-" . $substyle;
	}

        // Dynamic loading the featured stuff
        $_MIDCOM->dynamic_load($substyle_parameter . $this->objectLocation);
    }

}

?>
