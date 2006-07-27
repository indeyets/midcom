<?
echo '<?xml version="1.0" encoding="UTF-8"?>';
/*
 * Created on Sep 17, 2005
 * @author tarjei huse
 * @package midcom.admin.aegir 
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * 
 */
?>
<html><head>
  <title>Aegir 2 </title>
<style type="text/css">
    body { color: #000000; background-color: #FFFFFF; }
    address { font-size: smaller; }
    a:link { color: #0000CC; }

    #login_warning { color: red; }
    p.login_message { padding-left:6em; }
    
    form label { 
        padding-top:0.5em; 
        display:block;
    }
    form label input, form label select { 
        display:block;
        border: 1px #7590AE solid;
        width:9em;
        
    }
    form label select {
        margin-top:2px;
    }
    #midcom_services_auth_frontend_form_submit {
        background:#7590AE;
        color: #FFFFFF;
        margin-top:0.5em;
        border: 1px black solid; 
    }
    form {
        padding-top: 1em;
        padding-left:4em;
        
    }
    div.form {
        margin-left:100px;
        border: 1px #7590AE solid;
        
        width:16em;      
    }
    #content {
        padding-left:20em;
        padding-top:5em;"
    }
</style>
<? echo    $_MIDCOM->print_head_elements(); ?>


</head>

<body >
<div id="content" >
<img src="<? echo MIDCOM_STATIC_URL; ?>/midcom.admin.aegir/aegir-login.jpg" />

<p class='login_message'><?php 
    echo $_MIDCOM->i18n->get_string('login message - please enter credencials', 
    'midcom');?></p>
    
<div class="form">    
    
    
    
    


