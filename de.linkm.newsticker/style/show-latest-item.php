<?php
global $view;
global $view_name;
global $view_date;
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<div class="news-item">
    <span class="postinfo"><?php echo strftime("%x", $view_date); ?></span>
    <a href="&(prefix);&(view_name);.html" class="news-item-title">&(view["title"]);</a>
</div>