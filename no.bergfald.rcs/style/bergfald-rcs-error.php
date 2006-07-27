<?php
/*
 * Created on Aug 24, 2005
 *
 */
$request_data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<h1 style="color:red;padding:2em">
<? echo $request_data['error'] ?>
</h1>

