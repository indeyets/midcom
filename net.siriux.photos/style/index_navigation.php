<?php
global $view_thumbs_x;
global $view_thumbs_y;
global $view_startfrom;
global $view_total;

$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

$numpics = ($view_thumbs_x * $view_thumbs_y);
$prev = $view_startfrom - $numpics;
$next = $view_startfrom + $numpics;

$show_from = $view_startfrom + 1;
$show_to = $view_startfrom + $numpics;
if ($show_to > $view_total) 
{
    $show_to = $view_total;
}

?>
<p>
<?php echo $GLOBALS["view_l10n"]->get("showing thumbnails for images"); ?> &(show_from);-&(show_to);<br />
<?php echo $GLOBALS["view_l10n"]->get("total"); ?> &(view_total); <?php echo $GLOBALS["view_l10n"]->get("pictures in gallery"); ?>
</p>

<p>
<?php echo $GLOBALS["view_l10n"]->get("page"); ?>: 
<?php
if ($prev >= 0) 
{ 
    ?><a href="&(prefix);<?php 
    if ($prev > 0) 
    {
        echo "?startfrom=$prev";
    } 
    ?>">&laquo; <?php 
    echo $GLOBALS["view_l10n"]->get("previous"); 
    ?> &(numpics);</a>&nbsp;<?php 
} 
for ($i = 0; $i < ($view_total / $numpics); $i++) {
    $st = $i * $numpics;
    $p = $i + 1;
    if ($i > 0) echo ",";
    if ($st == $view_startfrom) 
    {
        ?> <b>&(p);</b><?php
    } 
    else 
    {
        ?> <a href="&(prefix);<?php 
        if ($st > 0) 
        {
            echo "?startfrom=$st"; 
        }
        ?>">&(p);</a><?php
    }
}
if ($next < $view_total) 
{ 
    ?>&nbsp; <a href="?startfrom=&(next);"><?php 
    echo $GLOBALS["view_l10n"]->get("next"); 
    ?> &(numpics); &raquo;</a><?php 
} 
?>
</p>
