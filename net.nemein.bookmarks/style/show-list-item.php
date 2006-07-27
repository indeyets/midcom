<?php
global $view;
global $view_date;
global $view_tags;

// TODO: cache this?
$creator = mgd_get_person($view["author"]);

// Linkify tags
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$tagline = "";
$tags_shown = 0;
foreach($view_tags as $tag)
{
    $tags_shown++;
    $tagline .= "<a href=\"".$prefix.rawurlencode($tag).".html\" rel=\"tag\">".$tag."</a>";
    if ($tags_shown < count($view_tags))
    {
        $tagline .= " ";
    }
}
?>
<div class="xfolkentry">
  <h2><a href="&(view["url"]);" class="taggedlink">&(view["title"]);</a></h2>
  <div class="description">&(view["extended"]);</div>
  <div class="meta"><?php 
  $date_string = "<abbr class=\"posted\" title=\"".date('Ymd', $view_date)."\">".strftime("%x", $view_date)."</abbr>";
  $creator_string = "<span class=\"poster fn\">{$creator->name}</span>";
  echo sprintf($GLOBALS["view_l10n"]->get("to %s by %s on %s"),$tagline, $creator_string, $date_string); 
  ?></div>
</div>