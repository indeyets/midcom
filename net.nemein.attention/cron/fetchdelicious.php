<?php
/**
 * @package net.nemein.attention
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: fetchdelicious.php 4795 2006-12-19 13:42:39Z rambo $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Cron handler for fetching person's attention data from del.icio.us via tastebroker.org
 * @package net.nemein.attention
 */
class net_nemein_attention_cron_fetchdelicious extends midcom_baseclasses_components_cron_handler
{
    /**
     * Fetches del.icio.us attention for users
     */
    function _on_execute()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add('_on_execute called');
        
        $delicious = net_nemein_attention_importer::create('delicious');
        $delicious->seek_delicious_users();

        debug_add('Done');
        debug_pop();
        return;
    }
}
?>