<?php
/**
 * @package org.openpsa.products
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: configuration.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/** @ignore */
require_once(MIDCOM_ROOT . '/midcom/core/handler/configdm.php');

/**
 * component configuration screen.
 *
 * This class extends the standard configdm mechanism as we need a few hooks for
 * the schemadb list.
 *
 * @package org.openpsa.products
 */
class org_openpsa_products_handler_configuration extends midcom_core_handler_configdm
{
    function org_openpsa_products_handler_configuration()
    {
        parent::midcom_core_handler_configdm();
    }

}