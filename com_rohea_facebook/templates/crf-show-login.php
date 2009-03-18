<?php
/**
 * @package com_rohea_facebook
 */
?>

<script src="http://static.ak.connect.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php" type="text/javascript"></script> 



<div class="login_sector">

<h2 i18n:translate=''>key: Do you use Facebook?</h2>
<div i18n:translate=''>key: Use Facebook to login for service:</div><br/><br />
<fb:login-button onlogin="window.location.href='${com_rohea_facebook/registration_url}';"></fb:login-button>

</div>

<script type="text/javascript">
FB.init("${com_rohea_facebook/api_key}", "${com_rohea_facebook/facebook_receiver}");   

</script>
