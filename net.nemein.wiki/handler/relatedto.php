<?php
/**
 * @package net.nemein.wiki
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * project related to handler
 * 
 * @package net.nemein.wiki
 */
class net_nemein_wiki_handler_relatedto extends org_openpsa_relatedto_handler_relatedto
{
    function net_nemein_wiki_handler_relatedto()
    {
        parent::__construct();
        $this->realcomponent = 'net.nemein.wiki';
    }

    /* The normally used methods are handled in the relatedto components class
       if for some reason you need more functionality that is only useful for
       this specific component, then add handler here, otherwise add handlers to
       the "prototype" */
}

?>