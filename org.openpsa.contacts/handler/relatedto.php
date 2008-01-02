<?php
/**
 * @package org.openpsa.contacts
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: relatedto.php,v 1.1 2006/05/17 14:44:28 rambo Exp $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Contact related to handler
 *
 * @package org.openpsa.contacts
 */
class org_openpsa_contacts_handler_relatedto extends org_openpsa_relatedto_handler_relatedto
{
    function org_openpsa_contacts_handler_relatedto()
    {
        parent::org_openpsa_relatedto_handler_relatedto();
        $this->realcomponent = 'org.openpsa.contacts';
    }

    /* The normally used methods are handled in the relatedto components class
       if for some reason you need more functionality that is only useful for
       this specific component, then add handler here, otherwise add handlers to
       the "prototype" */
}

?>