<?php
// Bind the view data, remember the reference assignment:
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());
?>

<h1><?php echo sprintf($data['l10n']->get('orphaned pages in wiki %s'), $data['wiki_name']); ?></h1>

<?php 
if (count($data['orphans']) > 0) 
{ 
    ?>
    <ul>
    <?php
    foreach ($data['orphans'] as $orphan) 
    {
        $orphan_link = $_MIDCOM->permalinks->create_permalink($orphan->guid);
        ?>
        <li><a href="&(orphan_link);">&(orphan.title);</a></li>
        <?php
    }
    ?>
    </ul>
    <?php 
} 
else 
{ 
    ?>
    <p><?php echo $data['l10n']->get('no orphans'); ?></p>
    <?php 
} 
?>