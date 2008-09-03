<?php
/**
 * @author tarjei huse
 * @package midcom.helper.imagepopup
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * @package midcom.helper.imagepopup
 */
class midcom_helper_imagepopup_interface extends midcom_baseclasses_components_interface {
    function midcom_helper_imagepopup_interface () {
        parent::__construct();

        $this->_component = 'midcom.helper.imagepopup';
        $this->_purecode = true;
        $this->_autoload_files = Array();
        $this->_autoload_libraries = Array(
            'midcom.helper.datamanager2',
            );

    }

}

?>