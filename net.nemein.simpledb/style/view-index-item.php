<?php
global $view, $view_columns, $view_name, $view_datamanager;
?>
  <tr>
    <?php
    $i = 0;
    foreach($view_columns as $key => $field) {
      ?>
      <td><?php 
      if ($view[$key]) {
        if ($i == 0) {
          echo "<a href=\"$view_name\">";
          $view_datamanager->display_view_field($key);
          echo "</a>";
        } else {
          $view_datamanager->display_view_field($key); 
        }
      }
      ?></td>
      <?php
      $i++;
    }
    ?>
  </tr>