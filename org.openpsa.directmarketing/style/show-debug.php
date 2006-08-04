<?php
$view_data =& $GLOBALS['midcom']->get_custom_context_data('request_data');
echo "<p>\n";
echo 'time:' . time() . "<br>\n";
/*
phpinfo();
*/

echo "<pre>\n";
print_r($_MIDGARD);
echo "</pre>\n";


/*
$messto = org_openpsa_smslib::factory('messto');
$messto->location = 'https://www.messto.com/send';
$messto->user = 'xx';
$messto->password = 'xx';

//$stat = $messto->send_sms('+358405401446', 'Messtoa testaillen, ääkkösiä kans. Sender alpha. Time: ' . date('H:i:s'), 'Nemein Oy');
$stat = $messto->send_sms('+358405401446', 'Messtoa testaillen, ääkkösiä kans. Sender not set. Time: ' . date('H:i:s'));
echo "<p>Status: {$stat}, errstr: {$messto->errstr} ({$messto->errcode})</p>\n";
*/

/*
$msg = new org_openpsa_directmarketing_campaign_message();
echo "Random token: " . $msg->_create_email_token() . "<br>\n";
echo 'time:' . time() . "<br><br/>\n";
*/

/*
$campaign = new org_openpsa_directmarketing_campaign('95172031f1a44bb382d77cb1055a60ba');
echo "campaign->rules: <pre>\n" . sprint_r($campaign->rules) . "</pre>\n";

$solver = new org_openpsa_directmarketing_campaign_ruleresolver();
$rret = $solver->resolve($campaign->rules);
$eret = $solver->execute();
echo "campaign->rules matches: <pre>\n" . sprint_r($eret) . "</pre>\n";
*/
/*
$campaign->update_smart_campaign_members();
echo 'time:' . time() . "<br>\n";
*/

/*
$rules = array(
    'type' => 'AND',
    'classes' => array(
        array (
            'type' => 'OR',
            'class' => 'org_openpsa_contacts_person',
            'rules' => array(
                array(
                    'property' => 'email',
                    'match' => 'LIKE',
                    'value' => '%@%'
                ),
                array(
                    'property' => 'handphone',
                    'match' => '<>',
                    'value' => ''
                ),
            ),
        ),
        array (
            'type' => 'AND',
            'class' => 'midgard_parameter',
            'rules' => array(
                array(
                    'property' => 'tablename',
                    'match' => '=',
                    'value' => 'person'
                ),
                array(
                    'property' => 'domain',
                    'match' => '=',
                    'value' => 'openpsa_test'
                ),
                array(
                    'property' => 'name',
                    'match' => '=',
                    'value' => 'param_match'
                ),
                array(
                    'property' => 'value',
                    'match' => '=',
                    'value' => 'bar'
                ),
            ),
        ),
    ),
);
$solver = new org_openpsa_directmarketing_campaign_ruleresolver();
$rret = $solver->resolve($rules);
echo "rret = {$rret}<br>\n";
$eret = $solver->execute();
echo "eret: <pre>\n" . sprint_r($eret) . "</pre>\n";
*/


/*
$stat = midcom_services_at_interface::register(time()+120, 'org.openpsa.directmarketing', 'at_test', array('foo' => 'bar'));
if ($stat)
{
    echo "midcom_services_at_interface::register(time()+120, 'org.openpsa.directmarketing', 'at_test', array('foo' => 'bar')); registered<br>\n";
}
else
{
    echo 'failed to register, errstr: ' . mgd_errstr() . "<br>\n";
}
*/


?>
