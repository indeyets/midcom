<?php
/**
 * @package midcom.helper.replicator
 * @author Nemein Oy, http://www.nemein.com/
 * @version $Id: subscription.php,v 1.4 2006/05/11 15:43:12 rambo Exp $
 * @copyright Nemein Oy, http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * MidCOM wrapped base class, keep logic here
 *
 * @package midcom.helper.replicator
 */
class midcom_helper_replicator_subscription_dba extends __midcom_helper_replicator_subscription_dba
{
    var $filters = false;
    var $autoserialize_filters = false;

    function __construct($id = null)
    {
        return parent::__construct($id);
    }

    function _unserialize_filters()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Called for #{$this->id} ({$this->title})");
        debug_pop();
        if (empty($this->filtersSerialized))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("filtersSerialized is empty");
            debug_pop();
            // No filters, we get away easy....
            $this->filters = array();
            $this->autoserialize_filters = true;
            return true;
        }

        $eval = "Array({$this->filtersSerialized});";

        $tmpfile = tempnam('', 'midcom_helper_replicator_subscription_dba_unserialize_filters');
        $fp = fopen($tmpfile, 'w');
        fwrite($fp, "<?php {$eval} ?>");
        fclose($fp);
        $parse_results = `php -l {$tmpfile}`;
        //debug_add("'php -l {$tmpfile}' returned: \n===\n{$parse_results}\n===\n");
        unlink($tmpfile);

        if (strstr($parse_results, 'Parse error'))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("'php -l {$tmpfile}' returned: \n===\n{$parse_results}\n===\n");
            debug_add('Invalid filter definition', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        eval("\$this->filters = {$eval}");
        $this->autoserialize_filters = true;
        return true;
    }

    function _serialize_filters()
    {
        // FIXME: there are a bunch of corner cases with this and DM2, solve them later
        return;
        if (!$this->autoserialize_filters)
        {
            // unserialize failed, do not muck about
            return;
        }
        $this->filtersSerialized = trim(preg_replace('%^\s*array\((.*)\)\s*$%msi', '\\1', var_export($this->filters, true)));
    }

    function _on_loaded()
    {
        $this->_unserialize_filters();
        return true;
    }

    function _on_creating()
    {
        if (!is_array($this->filters))
        {
            $this->filters = array();
        }
        $this->autoserialize_filters = true;
        $this->_serialize_filters();
        return true;
    }

    function _on_updating()
    {
        $this->_serialize_filters();
        return true;
    }
}
?>