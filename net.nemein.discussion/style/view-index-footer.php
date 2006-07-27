<?php
$view_data =& $_MIDCOM->get_custom_context_data('request_data');
?>
    </ul>
</div>

<?php
$view_data['thread_qb']->show_pages();
?>