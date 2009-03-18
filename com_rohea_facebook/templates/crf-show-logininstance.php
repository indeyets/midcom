<?php
/**
 * @package com_rohea_facebook
 */
?>

<script src="http://static.ak.connect.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php" type="text/javascript"></script> 

<h1 class='qaiku_page_title' i18n:translate="">key: Register your account</h1>

<div id="qaiku_wide">

<div id='qaiku_wide_left'>


<h2 i18n:translate=''>key: Do you use Facebook?</h2>
<div i18n:translate=''>key: Use Facebook to login for service:</div>

<br/><br />

<fb:login-button onlogin="window.location.href='${com_rohea_facebook/registration_url}';"></fb:login-button>


</div>

</div>
  
<script type="text/javascript">
FB.init("${com_rohea_facebook/api_key}", "/static/com_rohea_facebook/xd_receiver.htm");   

</script>