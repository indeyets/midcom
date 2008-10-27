<?php
/**
 * @package midcom.helper.schemaapi
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/** @ignore */
require_once(MIDCOM_ROOT . '/midcom/helper/schemaapi/field.php');
require_once(MIDCOM_ROOT . '/midcom/helper/schemaapi/supertype.php');

/**
 * @package midcom.helper.schemaapi
 */
class midcom_helper_schemaapi_schema
{

    protected $name = 'default';
    protected $description = "Autogenerated schema for ";
    protected $fields = array(  );

    public function __construct(  )
    {

    }

    public function asString (  )
    {
        $out = array(  );
        $out[$this->name] = array (
                'description' => $this->description,
                'fields' => $this->fields,
                );

        ob_start(  );
        var_export( $out );
        $ret = ob_get_contents(  );
        ob_end_clean(  );
        return $ret;
    }
    public function set_name( $name ) {

        $this->name  = $name;
    }

    public function set_description( $desc )
    {
        $this->description = $desc;
    }

    public function get_description(  )
    {
        return $this->description;
    }
    /**
     * adds a field to the fieldarray
     * @param midcom_helper_schemaapi_field
     */
    public function add_field( $field )
    {
        $this->fields = array_merge(  $this->fields ,$field->asArray(  ));
    }


    public static function load_widget( $widget ) {
        if ( !file_exists( dirname( __FILE__ ) . "/widget/$widget.php" ) )
        {

            $class =<<<EOF
<?php
class midcom_helper_schemaapi_widget_$widget extends midcom_helper_schemaapi_supertype
{

    public function __construct (  ) {
        parent::__construct( "$widget" );

    }

}
?>
EOF;

            file_put_contents( dirname( __FILE__ ) . "/widget/$widget.php", $class );
        }
        require_once(MIDCOM_ROOT . "/midcom/helper/schemaapi/widget/$widget.php");
    }
    public static function load_type( $type ) {
        if ( !file_exists( dirname( __FILE__ ) . "/type/$type.php" ) )
        {
            $class =<<<EOF
<?php
class midcom_helper_schemaapi_type_$type extends midcom_helper_schemaapi_supertype
{


    public function __construct ( ) {
        parent::__construct( "$type" );
    }

}
?>
EOF;

            file_put_contents( dirname( __FILE__ ) . "/type/$type.php", $class );
        }
        require_once(MIDCOM_ROOT . "/midcom/helper/schemaapi/type/{$type}.php");
    }


}
