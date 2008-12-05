<?php
/**
 * @package net_nemein_comettest
 */
?>
<h1 id="time">please wait..</h1>
<script>
	var request = new pi.comet();
	request.environment.setUrl("unixtime/");
	request.event.push = function(RESPONSE){
		document.getElementById("time").innerHTML=RESPONSE;
	};
	request.send();
</script>