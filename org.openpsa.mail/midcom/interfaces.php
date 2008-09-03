<?php
/**
 * @package org.openpsa.mail
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
/**
 * OpenPSA mail library, handles encoding/sending and decoding.
 *
 * Startup loads main class, which is used for all operations.
 *
 * @package org.openpsa.mail
 */
class org_openpsa_mail_interface extends midcom_baseclasses_components_interface
{

    function __construct()
    {
        parent::__construct();

        $this->_component = 'org.openpsa.mail';
    }

    function _on_initialize()
    {
        return true;
    }
}


?>