<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$view =& $data['view_reservation'];
?>
        <tr>
            <td class="datetime">
                <?php echo $data['event_widget']->render_timelabel($data['show_date']) . "\n"; ?>
            </td>
            <td class="title">
                <a href="&(data['event_url']);">&(view['title']:h);</a>
            </td>
            <td class="resources">
                <ul class="resources">
<?php
            foreach ($data['event']->resources as $id => $bool)
            {
                $resource =& $data['resources_by_id'][$id];
                $resource_url = "{$data['prefix']}view/{$resource->name}.html";
?>
                    <li class="&(resource.type);"><a href="&(resource_url);">&(resource.title);</a></li>
<?php
            }
?>
                </ul>
            </td>
            <td class="participants">
                <ul class="participants">
<?php
            foreach ($data['event']->participants as $id => $bool)
            {
                $person = new midcom_db_person($id);
                $contact = new org_openpsa_contactwidget($person);
?>
                    <li><?php echo $contact->show_inline(); ?></li>
<?php
            }
?>
                </ul>
            </td>
            <td class="description">
                &(view['description']:h);
            </td>
        </tr>
