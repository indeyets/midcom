<?php

class MidcomDBAWriter {

    protected $type;
    protected $moduleRoot;
    protected $schema;
    /**
     * @param $schema SchemaReader
     * @param $moduleRoot the path to the root of the module directory.
     * @param $type the name of the database object.
     * @param $db the midcomdba name of the db object ( not in use p.t. )
     */
    public function __construct (  $schema, $moduleRoot , $type  , $db = false ) 
    {
        $this->schema = $schema;
        $this->moduleRoot = $moduleRoot;
        $this->type = $type;
        $this->db = $db;
    }

    public function write(  ) {
        $new = array(
            'table' => $this->schema->getTable(  ),
            'old_class_name' => null,
            'new_class_name' => $this->type,
            'midcom_class_name' =>  ( $this->db) ? $this->db : $this->type . "_db"
        );
        if ( file_exists( $this->moduleRoot  . "/config/midcomdba.inc" ) ) {
            $content = file_get_contents(  $this->moduleRoot  . "/config/midcomdba.inc"  );
            eval (" \$cont = array ( $content ) ; ");
            foreach ( $cont as $def => $var ) {
                if ( $var['new_class_name']  == $this->type ) {
                    echo "Warning: midcomdba.inc definition for type exists, will not overwrite.\n";
                    return;

                   // throw new Exception( "Class {$this->type} allready exists in midcomdba.inc!" );
                }
            }
        } else {
            $cont = array(  );
        }
        
        $dbdefs = "";
        $cont[] = $new;
        ob_start(  );
        foreach ( $cont as $dbo )     {
            var_export( $dbo );
            echo ",\n";
        }
        $dbdefs = ob_get_contents(  );
        ob_end_clean(  );
        file_put_contents( $this->moduleRoot . "/config/midcomdba.inc" , $dbdefs);

    }


}
