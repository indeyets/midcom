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
class midcom_helper_replicator_transporter_email extends midcom_helper_replicator_transporter
{
    var $recipient = false;
    var $subject = 'From midcom_helper_replicator_transporter_email::process()';
    var $message = "See the XML files attached\n\n/midcom.helper.replicator";

    function midcom_helper_replicator_transporter_email($subscription)
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
     * Reads transport configuration from subscriptions parameters
     *
     * Also does some sanity checking
     */
    function _read_configuration_data()
    {
        if (!method_exists($this->subscription, 'list_parameters'))
        {
            // can't list parameters, dummy subscription ??
            return false;
        }
        $params = $this->subscription->list_parameters('midcom_helper_replicator_transporter_email');
        if (!is_array($params))
        {
            // Error reading parameters
            return false;
        }
        if (!array_key_exists('recipient', $params))
        {
            return false;
        }
        $this->recipient = $params['recipient'];

        return $_MIDCOM->load_library('org.openpsa.mail');
    }

    /**
     * Main entry point for processing the items received from queue manager
     */
    function process(&$items)
    {
        // Very rudimentary mail format checking
        if (!preg_match('/.*@.*/', $this->recipient))
        {
            return false;
        }
        $mail = new org_openpsa_mail();
        $mail->subject = $this->subject;
        $mail->to = $this->recipient;

        $i = 1;
        foreach ($items as $key => $data)
        {
            $att = array
            (
                'name' => sprintf('%010d', $i) . 'xml',
                'mimetype' => 'text/xml',
                'content' => $data,
            );
            $mail->attachments[] = $att;
            unset($att, $items[$key]);
            $i++;
        }
        unset($key, $data);

        if (!$mail->send())
        {
            // TODO: error reporting
            return false;
        }
        return true;
    }

    function get_information()
    {
        $recipient = $this->subscription->get_parameter('midcom_helper_replicator_transporter_email', 'recipient');
        $info = sprintf($this->_l10n->get('email to %s'), $recipient);
        return $info;
    }

    function add_ui_options(&$schema)
    {
        $schema->append_field
        (
            'recipient', 
            array
            (
                'title' => $_MIDCOM->i18n->get_string('email recipient', 'midcom.helper.replicator'),
                'storage' => array
                (
                    'location' => 'parameter',
                    'domain'   => 'midcom_helper_replicator_transporter_email'
                ),
                'required' => true,
                'type' => 'text',
                'widget' => 'text',
            )
        );
    }

}
?>
