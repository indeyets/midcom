<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . "__ais/imagepopup/";
?>
<div class="midcom_helper_imagepopup">
    <h1><?php echo $data['list_title']; ?></h1>
    
    <div id="top_navigation">
        <ul>
        <?php
        if ($data['list_type'] === 'folder')
        {
            if ($data['object'])
            {
                echo "<li><a href=\"{$prefix}{$data['schema_name']}/{$data['object']->guid}/\">" . $_MIDCOM->i18n->get_string('page', 'midcom') . "</a></li>";
            }
            echo "<li class=\"selected\"><a href=\"{$prefix}folder/{$data['schema_name']}/{$data['object']->guid}\">" . $_MIDCOM->i18n->get_string('folder', 'midcom') . "</a></li>";
        }
        else
        {
            echo "<li class=\"selected\"><a href=\"{$prefix}{$data['schema_name']}/{$data['object']->guid}/\">" . $_MIDCOM->i18n->get_string('page', 'midcom') . "</a></li>";
            echo "<li><a href=\"{$prefix}folder/{$data['schema_name']}/{$data['object']->guid}\">" . $_MIDCOM->i18n->get_string('folder', 'midcom') . "</a></li>";
        }

       ?>
       </ul>
    </div>
    <div id="files">
        <?php 
        $data['form']->display_form();
        ?>
    </div>
    
</div>