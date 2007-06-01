<?php
 //$data =& $_MIDCOM->get_custom_context_data('request_data');
 $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
 $a_id = $data['article_id'];
 $a_name = $data['article_name'];

$session =  new midcom_service_session();
$msg = "";
if ($session->exists('msg')) {
    $msg = $session->get('msg'); 
    $session->remove('msg');
}
?>
<div class="aish1"><h1><?php echo $data['l10n']->get("Move article"); ?>: &(a_name);</h1></div>
<p>
<?php 
    echo $data['l10n']->get("Select the topic you want to move the article to:"); ;
?>  
</p>
<script language="javascript" type="text/javascript">
//<![CDATA[
function set_moveto_value(id) {
   //var moveto = document.getElementById("f_moveto");
   // TODO: get the topic name.
    
   document.f_move_article.f_moveto.value = id;
   
   //moveto.value = id;
   if (window.confirm("<?php echo sprintf($data['l10n']->get("Move article '%s' to "), $a_name); ?>" + document.f_move_article.f_moveto.value + "?" )) {
    document.f_move_article.submit();
   } else {
    
   }
   
}
// ]]>
</script>
<form method="post"  name="f_move_article" action="&(prefix);article/move/&(a_id);.html" enctype="multipart/form-data">

<input class="hidden" name="f_moveto" type="hidden"  value="" />
<div style="padding-left:3em;">
<?php
    $select = new midcom_admin_content_navigation_treemenu();
    $select->show_leaves(false);
    $select->set_node_action('javascript:set_moveto_value(%s);',false);
    echo  $select->to_html();

?>
</div>

</form>
