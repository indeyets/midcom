<?php
/**
 * @package org.openpsa.sales
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: relatedto.php,v 1.1 2006/05/10 16:31:05 rambo Exp $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * salesproject list handler
 *
 * @package org.openpsa.sales
 */
class org_openpsa_sales_handler_relatedto extends org_openpsa_relatedto_handler_relatedto
{
    function __construct()
    {
        parent::__construct();
        $this->realcomponent = 'org.openpsa.sales';
    }

    /* The normally used methods are handled in the relatedto components class
       if for some reason you need more functionality that is only useful for
       this specific component, then add handler here, otherwise add handlers to
       the "prototype" */
}

?>