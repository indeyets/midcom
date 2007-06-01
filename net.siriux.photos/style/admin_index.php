<?php
global $view_title;
global $view_ids;
global $view_startfrom;
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$attachmentserver = $_MIDCOM->midgard->self . "midcom-serveattachmentguid-";

// Create and prepare a toolbar
$toolbar = new midcom_helper_toolbar('midcom_toolbar midcom_toolbar_in_content');
$toolbar->add_item(Array (
    MIDCOM_TOOLBAR_URL => '',
    MIDCOM_TOOLBAR_LABEL => $GLOBALS['view_l10n_midcom']->get('edit'),
    MIDCOM_TOOLBAR_HELPTEXT => null,
    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
    MIDCOM_TOOLBAR_ENABLED => true
));
$toolbar->add_item(Array (
    MIDCOM_TOOLBAR_URL => '',
    MIDCOM_TOOLBAR_LABEL => $GLOBALS['view_l10n_midcom']->get('delete'),
    MIDCOM_TOOLBAR_HELPTEXT => null,
    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
    MIDCOM_TOOLBAR_ENABLED => true
));
$toolbar->add_item(Array ( 
    // This one is for the approval
    MIDCOM_TOOLBAR_URL => '',
    MIDCOM_TOOLBAR_LABEL => null,
    MIDCOM_TOOLBAR_HELPTEXT => null,
    MIDCOM_TOOLBAR_ICON => null,
    MIDCOM_TOOLBAR_ENABLED => true
));
$toolbar->add_item(Array (
    MIDCOM_TOOLBAR_URL => '',
    MIDCOM_TOOLBAR_LABEL => $GLOBALS['view_l10n']->get('rotate left'),
    MIDCOM_TOOLBAR_HELPTEXT => null,
    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/rotate_ccw.png',
    MIDCOM_TOOLBAR_ENABLED => true
));
$toolbar->add_item(Array (
    MIDCOM_TOOLBAR_URL => '',
    MIDCOM_TOOLBAR_LABEL => $GLOBALS['view_l10n']->get('rotate right'),
    MIDCOM_TOOLBAR_HELPTEXT => null,
    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/rotate_cw.png',
    MIDCOM_TOOLBAR_ENABLED => true
));


midcom_show_style('index_navigation');

if ($view_ids) 
{ 
?>
  <table border="0" cellspacing="0" cellpadding="5" width="100%">
<?php 
    foreach ($view_ids as $id) 
    { 
        $photo = new siriux_photos_Photo($id); 
        $data = $photo->datamanager->data;
        $meta =& midcom_helper_metadata::retrieve($photo->article->guid()); 
?>
      <tr>
        <td valign="middle" align="left"><img src="&(attachmentserver);&(photo.thumbnail);/thumbnail_&(data['name']);.jpg" /></td>
        <td valign="top" align="left" width="100%"><p><b><?php echo $GLOBALS["view_l10n"]->get("name"); ?>:</b> &(data['name']);<br />
          <b><?php echo $GLOBALS["view_l10n"]->get("title"); ?>:</b> &(data['title']);</p>
<?php
	    $toolbar->update_item_url(0, "edit/{$data['_storage_id']}?startfrom={$view_startfrom}");
	    $toolbar->update_item_url(1, "delete/{$data['_storage_id']}?startfrom={$view_startfrom}");
	    if ($meta->is_approved())
	    {
	        $toolbar->update_item_url(2, "?startfrom={$view_startfrom}&unapprove={$data['_storage_id']}");
	        $toolbar->items[2][MIDCOM_TOOLBAR_LABEL] = $GLOBALS['view_l10n_midcom']->get('unapprove');
	        $toolbar->items[2][MIDCOM_TOOLBAR_HELPTEXT] = $GLOBALS['view_l10n_midcom']->get('approved');
	        $toolbar->items[2][MIDCOM_TOOLBAR_ICON] = 'stock-icons/16x16/approved.png';
	    }
	    else
	    {
	        $toolbar->update_item_url(2, "?startfrom={$view_startfrom}&approve={$data['_storage_id']}");
	        $toolbar->items[2][MIDCOM_TOOLBAR_LABEL] = $GLOBALS['view_l10n_midcom']->get('approve');
	        $toolbar->items[2][MIDCOM_TOOLBAR_HELPTEXT] = $GLOBALS['view_l10n_midcom']->get('unapproved');
	        $toolbar->items[2][MIDCOM_TOOLBAR_ICON] = 'stock-icons/16x16/not_approved.png';
	    }
	    $toolbar->update_item_url(3, "rotate_ccw/{$data['_storage_id']}.html?startfrom={$view_startfrom}");
	    $toolbar->update_item_url(4, "rotate_cw/{$data['_storage_id']}.html?startfrom={$view_startfrom}");
	    echo $toolbar->render();
?>
</td>
      </tr>
    <?php } ?>
  </table>
<?php } 
midcom_show_style('index_navigation');
?>
