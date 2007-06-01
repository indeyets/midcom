<?php

require_once "phing/Task.php";

require_once "SchemaReader.php";
require_once "midcomDBAWriter.php";
require_once "midcom_schema_factory.php";
require_once 'configwriter.php';


class CreateCrud extends Task {

	function __construct()
	{

	}

	protected $returnProperty; // name of property to set to return value

	/**
	 * The root path to where the module is stored.
	 */
	private $root = null;
	/**
	 * The target directory where the packagefile should be saved.
	 */
	protected $target_dir = null;

	public function setRoot($str)
	{
		$this->root = $str;
	}

	/** Sets property name to set with return value of function or expression.*/
	public function setReturnProperty($r)
	{
		$this->returnProperty = $r;
	}
    /**
     * The name of the midcom
     */
    protected $module;

    public function setModule ( $m  ) {
        $this->module = $m;
    }
    /**
     * The classtype to make crud for
     */
    protected $type;

    public function setType ( $type ) {
        $this->type = $type;
    }

    /**
     * The schemafile to use
     */
    protected $schema;
    public function setSchema( $s ) 
    {
        $this->schema = $s;
    }

    public $moduleRoot ;


	/**
	 * The init method: Do init steps.
	 */
	public function init()
	{
	    echo "Make CRUD - not war! \n"	;
        $this->moduleRoot = $this->root . "/" . $this->type;
	}
	/**
	 * The main entry point method.
	 */
	public function main()
	{
	
        $this->moduleRoot = $this->root . "/" . $this->module;
        $schema = new SchemaReader( $this->schema, $this->type  );

        $midcomdba = new MidcomDBAWriter( $schema, $this->moduleRoot , $this->type  );
        $midcomdba->write();

        $dmschema = new midcom_schema_factory( $schema, $this->moduleRoot,$this->type  );
        $dmschema->write(  );

        $configwriter = new configwriter( $this->moduleRoot );
        $configwriter->content[$this->type . "_schemadb"] = "file://" . str_replace( '.', '_', $this->module ) . "/config/schemadb_" . $this->type . ".inc";
        $configwriter->save(  );



    }    
}
