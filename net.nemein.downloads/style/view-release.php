<?php
global $view;
?>
<h1>&(view["title"]);</h1>

<?php if ($view["license"]) { ?>
  <p><?php echo $GLOBALS["view_l10n"]->get("license"); ?>: &(view["license"]);</p>
<?php } ?>

&(view["content"]:h);

<?php
// Show downloadables and enable comments

if (isset($view["download"])) {
  if (count($view["download"]) > 0) {
    ?><h2><?php echo $GLOBALS["view_l10n"]->get("downloads"); ?></h2>
    <ul><?php
    foreach ($view["download"] as $downloadable) {
      echo "<li>";
      if ($downloadable["description"]) {
        echo $downloadable["description"].": ";
      }
      echo "<a href=\"".$downloadable["url"]."\">";
      echo $downloadable["filename"];
      echo "</a>";
      $last_modified = strftime("%x",$downloadable["lastmod"]);
      $filesize = net_nemein_downloads_helper_filesize($downloadable["filesize"]);
      echo " (".sprintf($GLOBALS["view_l10n"]->get("%s, updated %s"),$filesize,$last_modified).")</li>";
    }
    echo "</ul>";
  }
} 
?>