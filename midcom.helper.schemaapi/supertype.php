<?php
/**
 * Types and widgets use the same overarching interface so no need for different classes there.
 */
class midcom_helper_schemaapi_supertype {

    protected $name;
    protected $config = array(  );
    public function __construct( $name )
    {
        $this->name = $name;    
    }

    public function get_name(  ) 
    {
        return $this->name;
    }

    public function get_config(  ) 
    {
        return $this->config;
    }
}
