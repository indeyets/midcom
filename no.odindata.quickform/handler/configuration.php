<?php
/**
 * @package no.odindata.quickform
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: configuration.php 3145 2007-03-27 10:09:00Z NetBlade $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/** @ignore */
require_once(MIDCOM_ROOT . '/midcom/core/handler/configdm.php');

/**
 *
 * @package no.odindata.quickform
 */
class no_odindata_quickform_handler_configuration extends midcom_core_handler_configdm
{
    function no_odindata_quickform_handler_configuration()
    {
        parent::__construct();
    }

    /**
     * Populate a single global variable with the current schema database, so that the
     * configuration schema works again.
     *
     * @todo Rewrite this to use the real schema select widget, which is based on some
     *     other field which contains the URL of the schema.
     */
    function _on_handler_configdm_preparing()
    {
        $GLOBALS['no_odindata_quickform_schemadbs'] = array_merge
        (
            Array
            (
                '' => $this->_l10n->get('default setting')
            ),
            $this->_config->get('schemadbs')
        );
    }
}

?>