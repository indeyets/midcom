<?php
foreach (range('A', 'Z') as $letter) { 
  if (isset($_REQUEST["net_nemein_organizations_alphabetical"]) && $_REQUEST["net_nemein_organizations_alphabetical"] == $letter) {
    $class="alphabetical_selected";
  } else {
    $class="alphabetical";
  }
  ?>
   <a href="?net_nemein_organizations_alphabetical=&(letter);" class="&(class);">&(letter);</a> 
  <?php
} ?>