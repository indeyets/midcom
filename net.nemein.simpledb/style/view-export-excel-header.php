<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $data['datamanager']->get_array();
$columns = $data['columns'];
?>
<table>
    <thead>
        <tr>
<?php
foreach ($columns as $key => $name)
{
?>
            <th class="&(key:h);">
                <?php echo utf8_decode($name); ?>
            </th>
<?php
}
?>
        </tr>
    </thead>
    <tbody>
