<?php
$view_data =& $_MIDCOM->get_custom_context_data('request_data');
?>

    </ol>
</div>

<?php
$view_data['post_qb']->show_pages();
?>