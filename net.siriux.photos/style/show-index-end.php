<?php
global $view_curcol;
global $view_thumbs_x;

while ($view_curcol < $view_thumbs_x) {
    ?><td>&nbsp;</td><?php
    $view_curcol++;
}
?>
  </tr>
</table>

<?php midcom_show_style('index_navigation'); ?>