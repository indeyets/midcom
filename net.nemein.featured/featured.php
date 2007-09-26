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
    	else
    	{
    	    $substyle_parameter = "/midcom-substyle-featured";
    	    if ($this->defaultStyle != '')
    	    {
    	        $substyle_parameter = "/midcom-substyle-{$this->defaultStyle}";
    	    }
    	}

        if (substr($this->objectLocation, 0, 1) != '/')
        {
            $this->objectLocation = "/{$this->objectLocation}";
        }

        // Dynamic loading the featured stuff
        $_MIDCOM->dynamic_load($substyle_parameter . $this->objectLocation);
    }

}

?>
