<?php
/**
 * @package se.anykey.mmslib
 */

/**
 * mms library
 * http://www.hellkvist.org/software/
 * Available under GPL license
 *
 * Startup loads main class, which is used for all operations.
 *
 * @package se.anykey.mmslib
 */
class se_anykey_mmslib_interface extends midcom_baseclasses_components_interface
{

    function se_anykey_mmslib_interface()
    {
        parent::midcom_baseclasses_components_interface();

        $this->_component = 'se.anykey.mmslib';
        $this->_purecode = true;
        $this->_autoload_files = Array('mmslib.php');
    }

    function _on_initialize()
    {
        return true;
    }
}
?>