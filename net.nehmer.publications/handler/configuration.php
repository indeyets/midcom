<?php
/**
 * @package net.nehmer.publications
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/** @ignore */
require_once(MIDCOM_ROOT . '/midcom/core/handler/configdm.php');

/**
 * n.n.publications component configuration screen.
 *
 * This class extends the standard configdm mechanism as we need a few hooks for the
 * symlink topic stuff.
 *
 * @package net.nehmer.publications
 */
class net_nehmer_publications_handler_configuration extends midcom_core_handler_configdm
{
    function net_nehmer_publications_handler_configuration()
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
        $GLOBALS['net_nehmer_publications_schemadbs'] = array_merge
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