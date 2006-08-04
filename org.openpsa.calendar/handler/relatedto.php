<?php
/**
 * @package org.openpsa.calendar
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id: relatedto.php,v 1.1 2006/05/12 16:47:24 rambo Exp $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Event related to handler
 * 
 * @package org.openpsa.calendar
 */
class org_openpsa_calendar_handler_relatedto extends org_openpsa_relatedto_handler_relatedto
{
    function org_openpsa_calendar_handler_relatedto()
    {
        parent::org_openpsa_relatedto_handler_relatedto();
        $this->realcomponent = 'org.openpsa.calendar';
    }

    /* The normally used methods are handled in the relatedto components class
       if for some reason you need more functionality that is only usefull for
       this specific component, then add handler here, otherwise add handlers to
       the "prototype" */
}

?>
