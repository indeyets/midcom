<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');

$favourite_object = $data['favourite_object'];

?>

  <li class="net_nemein_favourites_list_item net_nemein_favourites_type_<?php echo $favourite_object->objectType; ?>" 
      id="<?php echo $favourite_object->objectGuid; ?>" >
      <?php
      echo "<a href=\"delete/{$favourite_object->guid}.html\" title=\"{$data['l10n']->get('delete')}\">
          <img style=\"border: 0px;\" src=\"/midcom-static/stock-icons/16x16/trash.png\" alt=\"delete\"/></a> ";
      ?>
    <a href="<?php echo $_MIDGARD['prefix']; ?>/midcom-permalink-<?php echo $favourite_object->objectGuid; ?>" class="net_nemein_favourites_list_item_link">
    <?php echo $favourite_object->objectTitle; ?></a> <?php echo "Type: " . $favourite_object->objectType; ?>
  </li>

