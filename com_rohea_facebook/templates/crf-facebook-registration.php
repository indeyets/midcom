<link rel='stylesheet' href='/static/com_rohea_facebook/com_rohea_facebook.css' type='text/css' />


<script src="http://static.ak.connect.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php" type="text/javascript"></script> 

<h1 class='qaiku_page_title' i18n:translate="">key: Register your account</h1>

<div id='qaiku_wide'>

<div id='qaiku_wide_left'>
<!--
< tal:replace="structure com_rohea_account/registration_form/widgets/name/as_html" />
<span tal:replace="structure com_rohea_account/registration_form/widgets/title/as_html" />
<input type='text' tal:attributes="name com_rohea_account/registration_form/title/get_input_name" value="${fields/title/raw}" />
-->
    <!-- <span tal:replace="structure com_rohea_account/registration_form/start_as_html" /> -->
<!--     <form method='post' action="&(data['registration-form-action']);"> -->
    <!-- <span tal:replace="structure com_rohea_account/registration_form/start_as_html" /> -->
<!--     <form method='post' action="&(data['registration-form-action']);"> -->

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
FB.init("${com_rohea_facebook/api_key}", "/static/com_rohea_facebook/xd_receiver.htm");   

</script>

<br />
<div class='com_rohea_facebook_regtop'>
    <div class="com_rohea_facebook_menuselected" id="divnew"><a href="#" id="com_rohea_facebook_newuser" i18n:translate="">key: New user to Qaiku</a></div> 
    <div class='com_rohea_facebook_padding'>&nbsp;</div>
    <div class="com_rohea_facebook_menunotselected" id="divold"><a href="#" id="com_rohea_facebook_olduser" i18n:translate="">key: I've used Qaiku before</a></div>
</div>

<div id='com_rohea_account_registration'>
  
  <div class='left'>
    <span i18n:translate="">key: We just need a little more info of you. This will be asked one time only. </span>   
  </div>
  
  <div class='left'>

<!--    <span tal:replace="structure com_rohea_account/registration_form/start_as_html" />-->


    <form method="post" class="midcom_helper_datamanager" id='formSubmit'>
      <table style='width: 400px;'>
        <tr>
          <td>
            <p i18n:translate="">key: Username</p>            
          </td>
          <td>
            <p><input id='reg_username' type='text' size='20' tal:attributes="name fields/username/get_input_name" value="${fields/username/raw}" /></p>
	    <p class='reg_status'> ${com_rohea_facebook/errors/username}</p>
          </td>
        </tr>
       <?php 
       /*
        Remioved for accounts_project
        <tr>
          <td><p i18n:translate="">key: User interface language</p></td>
          <td>
            <p>
              <select id='dropdown_language' type='dropdown' tal:attributes="name fields/uilanguage/get_input_name">
              <tal:block tal:define="global current_ui_language com_rohea_facebook/current_ui_language" />
                <tal:block tal:repeat="lang com_rohea_facebook/translated_languages">
                  <tal:block tal:condition="php:current_ui_language==lang.code">
                   <option value="${lang/code}" selected="selected">
                ${lang/native}
                </option>
                  </tal:block>
                  <tal:block tal:condition="php:current_ui_language!=lang.code">
                   <option value="${lang/code}">
                ${lang/native}
                </option>
                  </tal:block>
                </tal:block>
                */
                ?>
                <?php /*
                <optgroup label='Popular languages'>
                  <option name='language_code' value='en'>English</option>                
                </optgroup>
                <optgroup label='All languages'>

                foreach($data['languages_list'] as $language) {                  
                  echo("<option name='language_code' value='".$language->code."'");
                  if ($language->code == 'fi') echo(" selected='selected'");
                  echo(">".$language->name."</option>");
                }
                </optgroup>
                */ ?>
                
                <?php
                /*
                 Remioved for accounts_project
              </select>
            </p>
          </td>
        </tr>
       
        */
        ?>
          <tr>
          <td><p i18n:translate="">key: Default writing language</p></td>
          <td>
            <p>
              <select id='dropdown_language' type='dropdown' tal:attributes="name fields/primarylanguage/get_input_name">
              <tal:block tal:define="global current_primary_language com_rohea_facebook/current_primary_language" />
                <tal:block tal:repeat="lang com_rohea_facebook/languages">
                  <tal:block tal:condition="php:current_primary_language==lang.code">
                   <option value="${lang/code}" selected="selected">
                ${lang/native}
                </option>
                  </tal:block>
                  <tal:block tal:condition="php:current_primary_language!=lang.code">
                   <option value="${lang/code}">
                ${lang/native}
                </option>
                  </tal:block>
                </tal:block>
                <?php /*
                <optgroup label='Popular languages'>
                  <option name='language_code' value='en'>English</option>                
                </optgroup>
                <optgroup label='All languages'>

                foreach($data['languages_list'] as $language) {                  
                  echo("<option name='language_code' value='".$language->code."'");
                  if ($language->code == 'fi') echo(" selected='selected'");
                  echo(">".$language->name."</option>");
                }
                </optgroup>
                */ ?>
              </select>
            </p>
          </td>
        </tr>        



<!--        
        <tr>
          <td><p i18n:translate="">key: Your location</p></td>
          <td>
            <p>
              <select id='dropdown_location' type='dropdown' tal:attributes="name fields/country/get_input_name">
                <option value='' i18n:translate=''>key: - Select -</option>
                <option tal:repeat="country com_rohea_account/countries" tal:attributes="value country/code">
                ${country/name}
                </option>
                <?php
                /*
                foreach($data['countries_list'] as $country) {
                  echo("<option value='".$country->code."'");
                  if ($data['detected_country']->code == $country->code)
                  {
                    echo(" selected");
                  }
                  echo(">".$country->name."</option>");
                }
                */
                ?>
              </select>
            </p>
	    <p class='reg_status'></p>
          </td>
        </tr>     
-->         
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
          <td><p i18n:translate="">key: Retype password</p></td>
          <td>
            <p>
              <input id='pw2' type='password' name='pw2' size='20' />
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
<!--    <span tal:replace="structure com_rohea_account/registration_form/end_as_html" /> -->
	</form>
  </div>
  <!--
  <div class='middle'>
    <p i18n:translate="">or</p>
  </div>
  <div class='right'>
    <h2>Google ID</h2>
<?php // echo midcom_services_permalinks::resolve_permalink($this->_topic->guid); ?>
    <h2>OpenID</h2>
  </div>
  -->
</div>

<div id='com_rohea_account_linking' class='com_rohea_facebook_hidden'>

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

<script type="text/javascript">
    
    $("#com_rohea_facebook_newuser").click(function() {   
        $("#com_rohea_account_registration").removeClass('com_rohea_facebook_hidden');
        $("#divnew").addClass('com_rohea_facebook_menuselected').removeClass('com_rohea_facebook_menunotselected');
        $("#divold").addClass('com_rohea_facebook_menunotselected').removeClass('com_rohea_facebook_menuselected');
        $("#com_rohea_account_linking").addClass('com_rohea_facebook_hidden');
        return false;
    });
    $("#com_rohea_facebook_olduser").click(function() {
        $("#com_rohea_account_registration").addClass('com_rohea_facebook_hidden');
        $("#com_rohea_account_linking").removeClass('com_rohea_facebook_hidden');
        $("#divnew").addClass('com_rohea_facebook_menunotselected').removeClass('com_rohea_facebook_menuselected');
        $("#divold").addClass('com_rohea_facebook_menuselected').removeClass('com_rohea_facebook_menunotselected');        
        return false;
    });
    
    <tal:block tal:condition="not:com_rohea_facebook/show_newuser_dialog">
        $("#com_rohea_account_registration").addClass('com_rohea_facebook_hidden');
        $("#com_rohea_account_linking").removeClass('com_rohea_facebook_hidden');
        $("#divnew").addClass('com_rohea_facebook_menunotselected').removeClass('com_rohea_facebook_menuselected');
        $("#divold").addClass('com_rohea_facebook_menuselected').removeClass('com_rohea_facebook_menunotselected');    
    </tal:block>
    
</script>

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
