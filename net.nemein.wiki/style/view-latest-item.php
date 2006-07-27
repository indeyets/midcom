<?php
$view_data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$page =& $view_data['wikipage'];

$author = new midcom_db_person($page->author);
$author_card = new org_openpsa_contactwidget($author);

if ($page->name == 'index')
{
    $url = $prefix;
}
else
{
    $url = "{$prefix}{$page->name}/";
}
?>
<li><a rel="note" class="subject url" href="&(url);">&(page.title);</a>
    <span class="creator"><?php echo $author_card->show_inline(); ?></span>
    <?php
    echo "<abbr class=\"dtposted\" title=\"".gmdate('Y-m-d\TH:i:s\Z', $page->created). "\">".strftime('%x %X', $page->created)."</abbr>\n";
    ?>
</li>