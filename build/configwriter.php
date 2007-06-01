<?php
/**
 * Ѕimple class for manipulating the confiġ.inc file.
 * note: does not keep comments!
 */
class configwriter 
{
    public $content = array(  );
    protected $moduleRoot;
    public function __construct ( $moduleRoot )  {
        $this->moduleRoot = $moduleRoot;
        if ( file_exists( $this->moduleRoot . "/config/config.inc" ) ) 
        {
            $raw = file_get_contents( $moduleRoot . "/config/config.inc" );
            eval ( "\$this->content = array (  $raw);" );
        }
        $this->content = $this->content;
        
    }

    public function save(  ) {
        var_dump( $this->content );
        ob_start(  );
        foreach ( $this->content as $key => $var ) {
            echo "'$key' => ";
            var_export( $var);
            echo ",\n";
        }
        $ret = ob_get_contents(  );
        ob_end_clean(  );
        file_put_contents( $this->moduleRoot . "/config/config.inc", $ret );
    }
}
