<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

$manager = new midcom_db_person($data['project_dm']['manager']);
$manager_card = new org_openpsa_contactwidget($manager);

$customer = new midcom_db_group($data['project_dm']['customer']);
?>
<tr>
    <td><?php
        echo "<a href=\"{$prefix}project/{$data['project']->guid}/\">{$data['project_dm']['title']}</a>\n";
        ?></td>
    <td><?php echo $manager_card->show_inline(); ?></td>
    <td><?php
    if ($data['contacts_url'])
    {
        echo "<a href=\"{$data['contacts_url']}group/{$customer->guid}/\">{$customer->official}</a>";
    }
    else
    {
        echo $customer->official;
    }
    ?></td>
    <td><?php echo $data['project_dm']['start']['local_strdate']; ?></td>
    <td><?php echo $data['project_dm']['end']['local_strdate']; ?></td>
    <td>
  <?php
    $data['project']->get_members();
    if (array_key_exists($_MIDGARD['user'], $data['project']->resources))
    {
        echo $data['l10n']->get('you are project participant');
    }
    elseif (array_key_exists($_MIDGARD['user'], $data['project']->contacts))
    {
        echo $data['l10n']->get('you are project subscriber');
        echo '<form method="post" class="subscribe" action="' . $prefix.'project/' . $data['project']->guid . '/unsubscribe/"><input type="submit" class="unsubscribe" value="' . $data['l10n']->get('unsubscribe') . '" /></form>';
    }
    else
    {
        echo $data['l10n']->get('you are not subscribed to project');
        echo '<form method="post" class="subscribe" action="' . $prefix.'project/' . $data['project']->guid . '/subscribe/"><input type="submit" value="' . $data['l10n']->get('subscribe') . '" /></form>';
    }
  ?>
  </td>
</tr>