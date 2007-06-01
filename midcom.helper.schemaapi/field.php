<?php

class midcom_helper_schemaapi_field 
{
    protected $name;
    protected $storage;
    protected $title;
    protected $widget;
    protected $type;

    public function __construct ( $name, $storage, $title ) 
    {
        $this->name = $name;
        $this->storage = $storage;
        $this->title = $title;
    }

   
    public function set_type ( $type ) {

        $this->type = $type;
    }

    public function set_widget ( $widget ) {
        $this->widget = $widget;
    }

    public function asArray(  ) {
        $ret = array(  );
        $ret[$this->name] = array( 
                'title' => $this->title,
                'storage' => $this->storage,
                'type' => $this->type->get_name(  ),
                //'type_config' => $this->type->get_config(  ),
                'widget' => $this->widget->get_name(  ),
                //'widget_config' => $this->widget->get_config( ) ,
                );
        if ( count ( $this->widget->get_config(  ) ) > 0  ) 
        {
            $ret[$this->name]['widget_config'] = $this->widget->get_config(  );
        }
        if ( count ( $this->type->get_config(  ) ) ) 
        {
            $ret[$this->name]['type_config'] = $this->type->get_config(  );
        }
        return $ret;
    }
}
