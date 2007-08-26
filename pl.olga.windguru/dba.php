<?php

class pl_olga_windguru_status_dba extends __pl_olga_windguru_status_dba
{
    function pl_olga_windguru_status_dba($id = null)
    {
        return parent::__pl_olga_windguru_status_dba($id);
    }
    
   
    /**
     * Human-readable label for cases like Asgard navigation
     */
    function get_label()
    {
		switch ($this->status)
		{
			case WG_STATUS_GFS:
				return "Model GFS";
			case WG_STATUS_NWW3:
				return "Model NWW3";
			default:
				return "Internal";
		}
    }
}

class pl_olga_windguru_cache_dba extends __pl_olga_windguru_cache_dba
{
    function pl_olga_windguru_cache_dba($id = null)
    {
        return parent::__pl_olga_windguru_cache_dba($id);
    }


    /**
     * Human-readable label for cases like Asgard navigation
     */
    function get_label()
    {
        return $this->spot;
    }
}

?>