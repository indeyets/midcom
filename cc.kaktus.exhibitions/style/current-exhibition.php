<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $data['datamanager']->get_content_html();
?>
<h1>&(view['title']);</h1>
<?php
$data['handler']->show_thumbnails();
?>
<div id="cc_kaktus_exhibitions_image_placeholder_wrapper">
    <div id="cc_kaktus_exhibitions_image_placeholder">
<?php
if (preg_match("/<a href=['\"](.+?)['\"]/i", @$data['first_thumbnail']['image'], $regs))
{
    echo "        <img id=\"cc_kaktus_exhibitions_image_placeholder_image\" src=\"{$regs[1]}\" alt=\"\" />\n";
}
?>
    </div>
    <div id="cc_kaktus_exhibitions_image_placeholder_text">
        <?php echo $data['first_thumbnail']['title']; ?>
    </div>
</div>
&(view['description']:h);
