<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());
?>
<h1>&(data['node_title']);</h1>
<a href="webcal://suda.co.uk/projects/X2V/get-vcal.php?uri=<?php echo rawurlencode($node[MIDCOM_NAV_FULLURL]); ?>" class="badge" style="font: 9px Geneva, Verdana, sans-serif; padding: 0 1.0em 1px 0; border: 1px solid #000; background: #31757B; color: #fff; text-decoration: none; text-align: center;" title=""><span style="background: #000; border-right: 1px solid #000; color: #FFF; padding: 1px 0.75em; margin-right: 0.1em;">&#8250;&#8250;&#8250;</span> hCalendar</a>