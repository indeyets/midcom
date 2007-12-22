<?php
/**
 * @package midcom.helper.schemaapi
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Types and widgets use the same overarching interface so no need for different classes there.
 *
 * @package midcom.helper.schemaapi
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
