<?php 
   $prefix = $GLOBALS["view_contentmgr"]->viewdata["admintopicprefix"] . "topic/"; 
   global $view_contentmgr;
   global $view;
   global $view_l10n;
   global $view_l10n_midcom;

   $data =& $view_contentmgr->viewdata;
   $context = $data["context"];
   $nav = new midcom_helper_nav($data["context"]);;

  require_once (MIDCOM_ROOT . '/midcom/helper/itemlist.php');
  //$nav = midcom_helper_itemlist::factory( $view->parameter("midcom.helper.nav", "navorder"), &$nav , &$view);
  $component = $GLOBALS['midcom']->get_context_data(MIDCOM_CONTEXT_COMPONENT);
  $leaves = false;
  if ($component == 'de.linkm.taviewer') {
      $leaves = $nav->list_leaves($nav->get_current_node());
  }
  $nodes  = $nav->list_nodes($nav->get_current_node());
?>
<SCRIPT language="javascript">
function populate( type) {
    
    var list = document.getElementById(type);
    
    if (type == 'topic') {
    var list = document.getElementById("topic");
    var size = <?php echo count($nodes) ?>;
    if (size < 5) { size = 5; }
    list.options.length=size;
    list.size=size;
    <?php

    if ($nodes) foreach ($nodes as $order => $item) {
          $obj = $nav->get_node( $item );
        print "list.options[$order].text = \"{$obj[MIDCOM_NAV_NAME]}\";\n list.options[$order].value = \"{$obj[MIDCOM_NAV_GUID]}\";\n";
    } 
    ?>}
    
    if (type == 'article' )  {
        var size = <?php echo count($leaves) ?>;
        if (size < 5) { size = 5; }
        list.options.length=size;
        list.size=size;
    <?php 
        if ($leaves) foreach ($leaves as $order => $item) {
             $obj = $nav->get_leaf( $item );
            print "list.options[$order].text = \"{$obj[MIDCOM_NAV_NAME]}\";\n   list.options[$order].value = \"{$obj[MIDCOM_NAV_GUID]}\";\n";
        }
    ?>
    }
}

// Browser Sniffer
var isIE = (document.getElementById && document.all)?true:false;
var isNS4 = (document.layers)?true:false;
var isNS6 = (document.getElementById && !document.all)?true:false;

var obj = (isIE)?"document.all":"document.getElementById"

function updownpress(what) {

var thisform=document.getElementById("sortform");
  
  var thisselect = document.getElementById(what) 
  var updown = document.getElementById("updown_" + what); 
 var line =  thisselect.selectedIndex;
 if (updown.checked) {
   
  if (line > 0) {
     var oldval = thisselect.options[line].value;
     var oldtext = thisselect.options[line].text;
     thisselect.options[line].value = 
             thisselect.options[line-1].value ;
     thisselect.options[line].text = 
         thisselect.options[line-1].text ;
     thisselect.options[line-1].value = oldval;
     thisselect.options[line-1].text = oldtext;
         thisselect.selectedIndex= -1;
    
       }
 } else {  

 
   if (line < thisselect.options.length) {
     var oldval = thisselect.options[line].value;
     var oldtext = thisselect.options[line].text;
     thisselect.options[line].value = 
             thisselect.options[line+1].value ;
     thisselect.options[line].text = 
          thisselect.options[line+1].text ;
     thisselect.options[line+1].value = oldval;
     thisselect.options[line+1].text = oldtext;
        thisselect.selectedIndex = -1;     
       

   }
 
 
 }
// now work out what to do the work on!
 
  var output='';
 
  for (i=0;i<thisselect.options.length;i++) {
    output =  output  + thisselect.options[i].value + '|'; 
  }
  

  var x;
  x = document.getElementById(what +"result");
  x.value=output;
  
}
</script>

<div class="aish1"><?php echo $view_l10n->get("Set order"); ?></div>
<form id="sortform"  method="post" action="&(prefix);scoreok" enctype="multipart/form-data">

   <input type="hidden" id="topicresult" name="topicresult" value="">
   <input type="hidden" id="articleresult" name="articleresult" value="">
   
   <input type="hidden" name="id" value="547">

   <input type="hidden" name="type" value="topic">

    <div class="form_description"><?php echo $view_l10n->get("Select arrow up or down and click on the element you want to move"); ?>.</div>

    <div class="form_description"><?php echo $view_l10n->get("Sort topics"); ?>
        <input type="radio" name="updown" id="updown_topic" value="0" checked>
        <img src="<?php echo MIDCOM_STATIC_URL . "/stock-icons/16x16/up.png"; ?>" align="absmiddle">
        <span style="border-left: 1px solid #000000">&nbsp;</span>
        <input type="radio" name="updown" id="updown_topic" value="1">
        <img src="<?php echo MIDCOM_STATIC_URL . "/stock-icons/16x16/down.png"; ?>" align="absmiddle">
         <select style="width:100%;" id="topic" name="topic"   onchange="updownpress('topic');">
        </select>
        <script language="javascript" > populate('topic'); </script>

<?php if($leaves) { ?>
    <div class="form_description"><?php echo $view_l10n->get("Sort articles"); ?></div>
        <input type="radio" name="updown_article" id="updown_article" value="0" checked>
        <img src="<?php echo MIDCOM_STATIC_URL . "/stock-icons/16x16/up.png"; ?>" align="absmiddle">
        <span style="border-left: 1px solid #000000">&nbsp;</span>        
        <input type="radio" name="updown_article" id="updown_article" value="1">
        <img src="<?php echo MIDCOM_STATIC_URL . "/stock-icons/16x16/down.png"; ?>" align="absmiddle">
        

        <select style="width:100%;" id="article" name="article" onchange="updownpress('article');">
        </select>
        <script language="javascript" > populate('article'); </script>
<?php } else { ?>
    <div class="form_description"><?php echo $view_l10n->get("The objects in this topic cannot be sorted"); ?>.</div>
<?php } ?>
    <div class="form_toolbar">
        <input type="submit" class="button"  name="update" value="<?php echo $view_l10n_midcom->get("save"); ?>">
        <input type="submit" class="button"  name="f_finish_save" value="<?php echo $view_l10n_midcom->get("save and close"); ?>">
        <input type="submit" class="button"  name="f_finish_nosave" value="<?php echo $view_l10n_midcom->get("cancel"); ?>">
    </div>

</form>
