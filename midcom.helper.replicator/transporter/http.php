<?php
/**
* @package midcom.helper.replicator
* @author The Midgard Project, http://www.midgard-project.org
* @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
* @copyright The Midgard Project, http://www.midgard-project.org
* @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
*/

/**
 * @package midcom.helper.replicator
 */
class midcom_helper_replicator_transporter_http extends midcom_helper_replicator_transporter
{
    var $_client = false;
    var $url = false;
    var $_username = false;
    var $_password = false;
    var $use_force = false;

    function midcom_helper_replicator_transporter_http($subscription)
    {
         $ret = parent::midcom_helper_replicator_transporter($subscription);
         $_MIDCOM->componentloader->load_graceful('org.openpsa.httplib');
         if (   !class_exists('org_openpsa_httplib')
             || !$this->_read_configuration_data())
         {
            $x = false;
            return $x;
         }
         return $ret;
    }

    /**
     * Reads transport configuration fomr subscriptions parameters
     *
     * Also does some sanity checking
     **/
    function _read_configuration_data()
    {
        if (!method_exists($this->subscription, 'list_parameters'))
        {
            // can't list parameters, dummy subscription ??
            return false;
        }
        $params = $this->subscription->list_parameters('midcom_helper_replicator_transporter_http');
        if (!is_array($params))
        {
            // Error reading parameters
            return false;
        }
        if (   !isset($params['url'])
            || empty($params['url']))
        {
            return false;
        }
        $this->url = $params['url'];
        if (   !isset($params['username'])
            || empty($params['username']))
        {
            return false;
        }
        $this->_username = $params['username'];
        if (   !isset($params['password'])
            || empty($params['password']))
        {
            return false;
        }
        $this->_password = $params['password'];
        if (   !isset($params['use_force'])
            || empty($params['use_force']))
        {
            return false;
        }
        $this->use_force = $params['use_force'];


        return $_MIDCOM->load_library('org.openpsa.httplib');
    }

    function _post_item(&$key, &$items, $retry_count = 0)
    {
        $data =& $items[$key];
        $this->_client = new org_openpsa_httplib();
        $client =& $this->_client;
        $client->basicauth['user'] = $this->_username;
        $client->basicauth['password'] = $this->_password;
        $post_vars = array
        (
            'midcom_helper_replicator_import_xml' => &$data,
            'midcom_helper_replicator_use_force' => (int)$this->use_force,
        );
        $response = $client->post($this->url, $post_vars);
        if (   $response !== false
            && stristr($response, 'error') === false)
        {
            // Key sent OK.
            return true;
        }

        /**
         * Sending failed
         *
         * Start doing the moves
         */

        // Get error message
        if ($response)
        {
            // non-empty response body, means we found 'error' as a string there
            $error_string = strip_tags(str_replace("\n", ' ', $response));
            $response_body = $response;
        }
        else
        {
            // empty response body, we failed earlier, get error message from http_request and what little body we might have
            $error_string = $client->error;
            $reponse_body = $client->_client->getResponseBody();
        }

        // Special case for remote end segfaults
        if (   $error_string === 'Malformed response.'
            && $retry_count < 5)
        {
            // Likely the remote end segfaulted, recursing to retry up-to 5 times
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Remote returned malformed response, most likely segfault, retry_count={$retry_count}", MIDCOM_LOG_INFO);
            debug_pop();
            usleep(250000); // 0.25 second delay
            if ($this->_post_item($key, $items, $retry_count+1))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Malformed response retry succeeded on count {$retry_count}", MIDCOM_LOG_INFO);
                debug_pop();
                return true;
            }
        }

        // TODO: Other immediate retries ??

        // Log the failure details
        $msg = "Failed to send key {$key}, error: {$error_string}";
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add($msg, MIDCOM_LOG_WARN);
        debug_print_r('Response body: ', $response_body);
        unset($response_body);
        debug_pop();
        $GLOBALS['midcom_helper_replicator_logger']->log($msg, MIDCOM_LOG_WARN);
        unset($msg);
        return false;
    }

    function _real_process(&$items, $retry_count = 0)
    {
        foreach ($items as $key => $data)
        {
            if (!$this->_post_item(&$key, &$items))
            {
                /**
                 * PONDER: try next items in queue (some of them might depend on this) or just return ?
                 * (NOTE: with true, because otherwise none of the previous items are removed from queue)
                 */
                 continue;
            }
            $GLOBALS['midcom_helper_replicator_logger']->log("Succesfully sent key {$key}", MIDCOM_LOG_INFO);
            unset($items[$key]);
        }
        unset($key, $data);
        
        if (   !empty($items)
            && $retry_count < 3)
        {
            /**
             * Recursing retries 
             *
             *  - There might be some dependencies that couldn't get queued in correct order for some reason
             *  - There might have been some temporary error that _post_item would not catch correctly
             */
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add(sprintf('We still have %d items left, retrying (retry_count=%d)', count($items), $retry_count), MIDCOM_LOG_INFO);
            debug_pop();
            usleep(1500000); // 1.5 second delay
            return $this->_real_process($items, $retry_count+1);
        }

        return true;
    }

    /**
     * Main entry point for processing the items received from queue manager
     */
    function process(&$items)
    {
        $GLOBALS['midcom_helper_replicator_logger']->push_prefix(__CLASS__ . '::' . __FUNCTION__);
        // POST each item as midcom_helper_replicator_import_xml
        $GLOBALS['midcom_helper_replicator_logger']->log(sprintf('Sending %d keys to %s', count($items), $this->url), MIDCOM_LOG_INFO);
        $ret = $this->_real_process($items);
        $GLOBALS['midcom_helper_replicator_logger']->pop_prefix();
        return $ret;
    }

    function get_information()
    {
        $info = sprintf($this->_l10n->get('POST to %s'), $this->url);
        return $info;
    }

    function add_ui_options(&$schema)
    {
        $schema->append_field
        (
            'url', 
            array
            (
                'title' => $_MIDCOM->i18n->get_string('URL to POST to', 'midcom.helper.replicator'),
                'storage' => array
                (
                    'location' => 'parameter',
                    'domain'   => 'midcom_helper_replicator_transporter_http'
                ),
                'required' => true,
                'type' => 'text',
                'widget' => 'text',
            )
        );
        $schema->append_field
        (
            'username', 
            array
            (
                'title' => $_MIDCOM->i18n->get_string('username', 'midcom.helper.replicator'),
                'storage' => array
                (
                    'location' => 'parameter',
                    'domain'   => 'midcom_helper_replicator_transporter_http'
                ),
                'required' => true,
                'type' => 'text',
                'widget' => 'text',
            )
        );
        $schema->append_field
        (
            'password', 
            array
            (
                'title' => $_MIDCOM->i18n->get_string('password', 'midcom.helper.replicator'),
                'storage' => array
                (
                    'location' => 'parameter',
                    'domain'   => 'midcom_helper_replicator_transporter_http'
                ),
                'required' => true,
                'type' => 'text',
                'widget' => 'text',
            )
        );
        $schema->append_field
        (
            'use_force', 
            array
            (
                'title' => $_MIDCOM->i18n->get_string('use force when importing', 'midcom.helper.replicator'),
                'storage' => array
                (
                    'location' => 'parameter',
                    'domain'   => 'midcom_helper_replicator_transporter_http'
                ),
                'required' => true,
                'type' => 'select',
                'widget' => 'select',
                'type_config' => array
                (
                    'options' => array
                    (
                        0 => $_MIDCOM->i18n->get_string('no'),
                        1 => $_MIDCOM->i18n->get_string('yes'),
                    ),
                ),
            )
        );
    }

}
?>
