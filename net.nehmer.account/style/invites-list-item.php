<?php
$invite =& $data['invite'];
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<li>
 <?php echo $invite->email . " (Sent: " . strftime("%d.%m.%y", $invite->metadata->created) . ") "; ?>
 <a href="<?php echo $prefix . "remind_invite/" . $invite->hash . ".html"; ?>">Remind</a> | 
 <a href="<?php echo $prefix . "delete_invite/" . $invite->hash . ".html"; ?>">Delete</a>
</li>