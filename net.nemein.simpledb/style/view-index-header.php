<?php
global $view, $view_columns;
?>
<table border="1" cellspacing="0">
  <tr>
    <?php
    foreach($view_columns as $key => $field) {
      ?>
      <th>&(field);</th>
      <?php
    }
    ?>
  </tr>
