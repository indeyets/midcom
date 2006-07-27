<?php
global $view_gallery_name;
global $view_gallery_title;
global $view;
global $view_thumbs_x;
global $view_curcol;



$view_curcol++;

if ($view_curcol > $view_thumbs_x) {
    ?></tr><tr><?php
    $view_curcol = 1;
}

$GLOBALS["midcom"]->dynamic_load($GLOBALS['view_gallery_dl_url']);
?>
