<?php
/**
 * @package net.nemein.attention
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: fetchlastfm.php 4795 2006-12-19 13:42:39Z rambo $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Cron handler for fetching person's attention data from Last.fm via tastebroker.org
 * @package net.nemein.attention
 */
class net_nemein_attention_cron_fetchlastfm extends midcom_baseclasses_components_cron_handler
{
    /**
     * Fetches LastFM attention for users
     */
    function _on_execute()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add('_on_execute called');
        
        $lastfm = net_nemein_attention_importer::create('lastfm');
        $lastfm->seek_lastfm_users();

        debug_add('Done');
        debug_pop();
        return;
    }
}
?>