<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>


<?php 
echo "<li>";
echo $data['player_username'];
echo " <input type=\"checkbox\" name=\"{$data['pending']->playerguid}\"/>";
echo "</li>";
?>