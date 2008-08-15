<?php
/**
 * @package net.nehmer.account
 * @author Henri Bergius, http://bergie.iki.fi
 * @copyright Nemein Oy, http://www.nemein.com
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM wrapper class for per-module karma objects
 *
 * @package net.nehmer.account
 */
class net_nehmer_account_karma_dba extends __net_nehmer_account_karma_dba
{
    function __construct($src = null)
    {
        $this->_use_rcs = false;
        parent::__construct($src);
    }
}
?>