<?php
global $view;
global $view_name;
global $view_date;
global $view_enable_details;

$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<div class="news-item">
    <h2>
        <span class="postinfo"><?php echo strftime("%x", $view_date); ?></span>
        <span class="news-item-title"><?php
        if ($view_enable_details)
        { 
            ?><a href="&(prefix);&(view_name);.html">&(view["title"]);</a><?php
        } 
        else 
        {
            ?>&(view["title"]);<?php
        } 
        ?></span>
    </h2>

    <div class="news-item-abstract">
        &(view["abstract"]:f);
    </div>
</div>