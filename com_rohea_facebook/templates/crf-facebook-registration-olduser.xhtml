<link rel='stylesheet' href='/static/com_rohea_facebook/com_rohea_facebook.css' type='text/css' />


<script src="http://static.ak.connect.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php" type="text/javascript"></script> 

<h1 class='qaiku_page_title' i18n:translate="">key: Register your account</h1>

<div id='qaiku_wide'>

<div id='qaiku_wide_left'>

<span tal:define="global fields com_rohea_facebook/registration_form/widgets" />

<div id="fbaccountinfo" class='left' style="width:100%;">
<table>
<tr>

<td>
    <fb:profile-pic uid='loggedinuser' facebook-logo='true'></fb:profile-pic>
</td>

<td>
    <span i18n:translate="">key: Welcome </span>, <fb:name uid='loggedinuser' useyou='false'></fb:name><br /><br />
    <span i18n:translate="">key: Logged in to Facebook </span>
</td>

</tr>


</table>

</div>

<script type="text/javascript">
FB.init("${com_rohea_facebook/api_key}", "${com_rohea_facebook/facebook_receiver}");   

</script>

<br />
<div class='com_rohea_facebook_regtop'>
    <div class="com_rohea_facebook_menunotselected" id="divnew"><a href="${com_rohea_facebook/newuser_url}" id="com_rohea_facebook_newuser" i18n:translate="">key: New user to service</a></div> 
    <div class='com_rohea_facebook_padding'>&nbsp;</div>
    <div class="com_rohea_facebook_menuselected" id="divold"><a href="${com_rohea_facebook/olduser_url}" id="com_rohea_facebook_olduser" i18n:translate="">key: I've used this service before</a></div>
</div>


<div id='com_rohea_account_linking'>

  <div class='left'>
  
    <span i18n:translate="">key: Login with your existing account to connect it to Facebook </span>   
  
  </div>
  
  <div class='left'>

    <form method="post" class="midcom_helper_datamanager" id='formSubmit'>
      <input type='hidden' name='com_rohea_facebook_linking' value='true' />
      <table style='width: 400px;'>
        <tr>
          <td>
            <p i18n:translate="">key: Username</p>            
          </td>
          <td>
            <p><input id='username' type='text' size='20' name='username' value="${fields/username/raw}" /></p>
	    <p class='reg_status'> ${com_rohea_facebook/errors/username}</p>
          </td>
        </tr>
 
        <tr>
          <td><p i18n:translate="">key: Password</p></td>
          <td>
            <p>
              <input id='pw' type='password' name='pw' size='20' />
              <!--<span class='small' i18n:translate="">key: At least 6 characters</span>-->
            </p>
	    <p class='reg_status'></p>
          </td>
        </tr>
       
        <tr>
          <td colspan='2'>
            <button name="${com_rohea_facebook/registration_form/namespace}_save"type='submit' class='button button_blue'>
              <span i18n:translate="">key: Continue Registration</span>
            </button>          
          </td>
        </tr>
      </table>

      </form>
  </div>

</div>




</div>


<div id='qaiku_wide_right'>

</div>

<script type="text/javascript" src="/js/jquery.validate.pack.js"></script>
  <script type='text/javascript'>
  $(document).ready(function()
  {

jQuery.validator.addMethod("alphanumeric", function(value, element) {
	return this.optional(element) || /^[a-zA-Z0-9-]{3,12}$/i.test(value);
}, "Letters, numbers or hyphens only please");  


  $("#formSubmit").validate({
        rules: {
            ${fields/username/get_input_name}: {
                required: true,
                minlength: 3,
		alphanumeric: true,
		remote: "/account/registration/checkusername/"
            },
            pw: {
                required: true,
                minlength: 6
            },
            pw2: {
                required: true,
                minlength: 6,
                equalTo: "#pw"
            },
            ${fields/country/get_input_name}: "required",
            ${fields/email/get_input_name}: {
                required: true, 
                email: true
            }
        },
        messages: {
           ${fields/username/get_input_name}: {
                required: "<tal:block i18n:translate=''>key: username needed</tal:block>",
                minlength: jQuery.format("<tal:block i18n:translate=''>key: at least</tal:block> {0} <tal:block i18n:translate=''>key: characters</tal:block>"),
		alphanumeric: "<tal:block i18n:translate=''>key: Username can contain</tal:block>",
		remote: jQuery.format("{0} <tal:block i18n:translate=''>key: is already in use</tal:block>") 
            },
            pw: {
                required: "<tal:block i18n:translate=''>key: Password</tal:block>",
                minlength: jQuery.format("<tal:block i18n:translate=''>key: at least</tal:block> {0} <tal:block i18n:translate=''>key: characters</tal:block>")
            },
            pw2: {
                required: "<tal:block i18n:translate=''>key: Retype new password</tal:block>",
                minlength: jQuery.format("<tal:block i18n:translate=''>key: at least</tal:block> {0} <tal:block i18n:translate=''>key: characters</tal:block>"),
                equalTo: "<tal:block i18n:translate=''>key: password fields are equal</tal:block>"
            },
            ${fields/country/get_input_name}: {
                required: "<tal:block i18n:translate=''>key: choose one</tal:block>"
            },
            ${fields/email/get_input_name}: {
		required: "<tal:block i18n:translate=''>key: email needed</tal:block>",
                email: "<tal:block i18n:translate=''>key: incorrect email</tal:block>"
            }
        },
        // Error message is appended to next element
        errorPlacement: function(error, element) {
                error.appendTo(element.parent().next());
        },
        // set this class to error-labels to indicate valid fields
        success: function(label) {
            // set   as text for IE
            label.html(" ").addClass("checked");
        }
    });

  });

</script>


</div>
