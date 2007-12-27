<?php
/**
 * @package net.nemein.attention
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
require_once(MIDCOM_ROOT . "/net/nemein/attention/importer/apml.php");

/**
 * Importer for APML files
 *
 * @package net.nemein.attention
 */
class net_nemein_attention_importer_lastfm extends net_nemein_attention_importer_apml
{   
    /**
     * Initializes the class. The real startup is done by the initialize() call.
     */
    function net_nemein_attention_importer_lastfm()
    {
         parent::net_nemein_attention_importer_apml();
    }
    
    function seek_lastfm_users()
    {
        // TODO: With 1.8 we can query parameters more efficiently
        $qb = new MidgardQueryBuilder('midgard_parameter');
        $qb->add_constraint('domain', '=','net.nemein.attention:lastfm');
        $qb->add_constraint('name', '=','username');
        $accounts = $qb->execute();
        if (count($accounts) > 0)
        {
            foreach ($accounts as $account_param)
            {
                $user = new midcom_db_person($account_param->parentguid);
                if (   $user
                    && $user->id)
                {
                    $this->import("http://research.sun.com:8080/AttentionProfile/apml/last.fm/{$account_param->value}", $user->id);
                }
            }
        }
    }
}
?>