<?php
$GLOBALS["midcom"]->cache->content->content_type("text/xml");
$GLOBALS["midcom"]->header("Content-type: text/xml; charset=UTF-8");
/* Debugging
$GLOBALS["midcom"]->cache->content->content_type("text/plain");
$GLOBALS["midcom"]->header("Content-type: text/plain; charset=UTF-8");
*/
echo "<hourReports version=\"2.0\">\n";
?>
