<?php
/**
 * @package net_nemein_comettest
 */
//$session = new midcom_core_services_sessioning('net_nemein_comettest');
// $session->set('string','hello');
?>
<h1 id="time">please wait..</h1>

<?php

// $sess = new midcom_core_services_sessioning('net_nemein_comettest');
// 
// if ($sess->exists('string'))
// {
//     $val = $sess->get('string');
//     echo "Founded var1: {$val}<br/>\n";
// //    $sess->remove('var1');
// }
// else
// {
//     echo "not found";
// }

?>

<div class="post_test_area">
    <div class="results">
        input something...
    </div>
    <div class="input_form_holder">
        <form name="input_form" onsubmit="send_string(); return false;">
            <input type="text" name="echo_string" id="echo_string_input" value="" />
            <input type="submit" name="submit" value="Send" />
        </form>
    </div>
</div>

<script type="text/javascript">
    var time_action_method = function(resp) {
        jQuery('#time').html(resp);
    };
    
    var req = jQuery.midcom.helpers.comet.start('unixtime/', time_action_method);
    
    function send_string()
    {
        var echo_string_input = jQuery('#echo_string_input');
        var string = echo_string_input.val();
        echo_string_input.val('');
        
        var preq = jQuery.midcom.helpers.comet.start('saver/', 'post', {string: string});
    }
   
    var last = '';
    var send_action_method = function(resp) {
        // console.log("last received: ");
        // console.log(last);
        // cache = resp + "<br />";
        var now = '' + resp;
        if (now.length != last.length)
        {
            // console.log("send received: ");
            // console.log(now);
            last = ''+now;
            jQuery('.post_test_area .results').html(last);            
        }
    };
    
    var plreq = jQuery.midcom.helpers.comet.start('echoer/', send_action_method);
    
    //var preq = jQuery.midcom.helpers.comet.start('saver/', 'post', {string: 'hello world'});
    
    
    
</script>