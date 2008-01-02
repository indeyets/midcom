<?php

class SchemaReader {
    // the midcomschema file
    protected $dom;
    // the objectype
    protected $type;
    public function __construct( $file , $type ) 
    {
        $this->file = $file;
        $this->dom = new DomDocument;
        if ($this->dom->load( $file )) echo "File $file loaded..\n";
        $this->xp = new DomXpath( $this->dom );
        $this->type = $type;

        echo $this->dom->saveXML( );
        $this->checkType(  );
    }

    protected function checkType (  ){
        $vars = $this->getProperties(  );
var_dump($vars->length);
        if ( $vars->length == 0 ) {
            throw new Exception( "Cannot create crud with missing type or empty type!" );
        }
    }
    /**
     * Get the variables for a function
     * @return object DomList of the properties for the type
     */
    public function getProperties(  ) {
        $q = sprintf( '/Schema/type[@name="%s"]/property', $this->type );
        echo $q ."\n";
        $vars = $this->xp->query( $q ) ;
        return $vars;
    }


    public function getTable(  ) {
        $q = sprintf( '/Schema/type[@name="%s"]', $this->type );
        $type = $this->xp->query( $q );
        return $type->item( 0 )->getAttribute( 'table' );
    }



}
