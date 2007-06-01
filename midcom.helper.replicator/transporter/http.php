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

    var $url = false;
    var $_username = false;
    var $_password = false;
    var $use_force = false;


    function midcom_helper_replicator_transporter_http($subscription)
    {
         $ret = parent::midcom_helper_replicator_transporter($subscription);
         if (!$this->_read_configuration_data())
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

    /**
     * Main entry point for processing the items received from queue manager
     */
    function process(&$items)
    {
        $GLOBALS['midcom_helper_replicator_logger']->push_prefix(__CLASS__ . '::' . __FUNCTION__);
        // POST each item as midcom_helper_replicator_import_xml
        $GLOBALS['midcom_helper_replicator_logger']->log(sprintf('Sending %d keys to %s', count($items), $this->url), MIDCOM_LOG_INFO);
        foreach ($items as $key => $data)
        {
            $client = new org_openpsa_httplib();
            $client->basicauth['user'] = $this->_username;
            $client->basicauth['password'] = $this->_password;
            $post_vars = array
            (
                'midcom_helper_replicator_import_xml' => &$data,
                'midcom_helper_replicator_use_force' => (int)$this->use_force,
            );
            $response = $client->post($this->url, $post_vars);
            if (   $response === false
                || stristr($response, 'error'))
            {
                if ($response)
                {
                    $error_string = strip_tags(str_replace("\n", ' ', $response));
                }
                else
                {
                    $error_string = $client->error;
                }
                $GLOBALS['midcom_helper_replicator_logger']->log("Failed to send key {$key}, error: {$error_string}", MIDCOM_LOG_WARN);
                /**
                 * PONDER: try next items in queue (some of them might have depended on this
                 * or just return (NOTE: with true, because otherwise previous items are not removed from queue)
                 */
                continue;
            }
            $GLOBALS['midcom_helper_replicator_logger']->log("Succesfully sent key {$key}", MIDCOM_LOG_INFO);
            unset($items[$key]);
        }
        unset($key, $data);

        $GLOBALS['midcom_helper_replicator_logger']->pop_prefix();
        return true;
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
