<?php
// $data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $data['datamanager']->get_content_html();
?>
        <li>
            <input type="hidden" name="net_nemein_personnel_index[]" value="&(data['person_id']:h);" />
            <img src="<?php echo MIDCOM_STATIC_URL; ?>/stock-icons/16x16/stock_person.png" alt="" />
            <span class="person name">&(view['rname']:h);</span>
        </li>
