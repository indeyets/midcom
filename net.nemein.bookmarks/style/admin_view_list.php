<?php
global $view;
global $view_tag;

// TODO: cache this?
$creator = mgd_get_person($view->author);

// Linkify tags
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$tagline = "";
$tags = explode(" ",$view->content);
if (is_array($tags))
{
    $tags_shown = 0;
    foreach($tags as $tag)
    {
        $tags_shown++;
        $tagline .= "<a href=\"".$prefix."list/".rawurlencode($tag)."\">".$tag."</a>";
        if ($tags_shown < count($tags))
        {
            $tagline .= " ";
        }
    }
}

?>
<div class="bookmark">
  <h2><a href="<?php echo $view->url; ?>"><?php echo $view->title; ?></a></h2>
  <div class="bookmark-extended"><?php echo $view->abstract; ?></div>
  <div class="bookmark-metadata"><?php 
  echo sprintf($GLOBALS["view_l10n"]->get("to %s by %s on %s"),$tagline,$creator->name,
      strftime("%x", $view->created));
  echo " <a href='" . $prefix . "view/" . $view->id . ".html'>Edit</a>"; 
  ?></div>
</div>
