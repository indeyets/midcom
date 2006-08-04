<?php
$view_data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

$manager = new midcom_db_person($view_data['project_dm']['manager']);
$manager_card = new org_openpsa_contactwidget($manager);

$customer = new midcom_db_group($view_data['project_dm']['customer']);
?>
<tr>
    <td><?php 
        echo "<a href=\"{$prefix}project/{$view_data['project']->guid}/\">{$view_data['project_dm']['title']}</a>\n";
        ?></td>
    <td><?php echo $manager_card->show_inline(); ?></td>
    <td><?php 
    if ($view_data['contacts_node'])
    {
        echo "<a href=\"{$view_data['contacts_node'][MIDCOM_NAV_FULLURL]}group/{$customer->guid}/\">{$customer->official}</a>"; 
    }
    else
    {
        echo $customer->official; 
    }
    ?></td>
    <td><?php echo $view_data['project_dm']['start']['local_strdate']; ?></td>
    <td><?php echo $view_data['project_dm']['end']['local_strdate']; ?></td>
    <td>
  <?php
    if (array_key_exists($_MIDGARD['user'], $view_data['project']->resources))
    {
        echo $view_data['l10n']->get('you are project participant');
    }
    elseif (array_key_exists($_MIDGARD['user'], $view_data['project']->contacts))
    {
        echo $view_data['l10n']->get('you are project subscriber');
        echo '<form method="post" class="subscribe" action="'.$prefix.'project/'.$view_data['project']->guid.'/unsubscribe/"><input type="submit" class="unsubscribe" value="'.$view_data['l10n']->get('unsubscribe').'" /></form>';
    }
    else
    {
        echo $view_data['l10n']->get('you are not subscribed to project'); 
        echo '<form method="post" class="subscribe" action="'.$prefix.'project/'.$view_data['project']->guid.'/subscribe/"><input type="submit" value="'.$view_data['l10n']->get('subscribe').'" /></form>';
    }
  ?>
  </td>
</tr>