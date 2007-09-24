<?php
$favourite_object = $data['favourite_object'];
?>
    <li class="net_nemein_favourites_list_item net_nemein_favourites_type_<?php echo $favourite_object->objectType; ?>" 
        id="<?php echo $favourite_object->objectGuid; ?>" >
        <a href="<?php echo $_MIDGARD['prefix']; ?>/midcom-permalink-<?php echo $favourite_object->objectGuid; ?>" class="net_nemein_favourites_list_item_link">
            <?php echo $favourite_object->objectTitle; ?>
        </a>
        <span class="published">(<?php
        if ($favourite_object->bury)
        {
            echo sprintf($data['l10n']->get('buried on %s'), strftime('%x', $favourite_object->metadata->published));
        }
        else
        {
            echo sprintf($data['l10n']->get('favourited on %s'), strftime('%x', $favourite_object->metadata->published));
        }
        ?>)</span>
        <?php
        if ($data['favourite_object']->can_do('midgard:delete'))
        {
            echo " <a href=\"delete/{$favourite_object->guid}.html\" title=\"{$data['l10n']->get('delete')}\">";
            echo "<img style=\"border: 0px;\" src=\"/midcom-static/stock-icons/16x16/trash.png\" alt=\"delete\"/>";
            echo "</a>";
        }
      ?>
    </li>

