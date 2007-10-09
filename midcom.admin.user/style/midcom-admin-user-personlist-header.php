<form method="get">
    <label>
        <span><?php echo $_MIDCOM->i18n->get_string('search', 'midcom.admin.user'); ?></span>
        <input type="text" name="midcom_admin_user_search" value="<?php if (isset($_REQUEST['midcom_admin_user_search'])) { echo $_REQUEST['midcom_admin_user_search']; } ?>" />
    </label>
    <input type="submit" />
    <div class="helptext">
        <?php 
        echo sprintf($_MIDCOM->i18n->get_string('the following fields will be searched: %s', 'midcom.admin.user'), implode(', ', $data['search_fields']));
        ?>
    </div>
</form>

<?php
if (count($data['persons']) > 0)
{
    ?>
    <form method="post">
    <table>
        <thead>
            <tr>
                <th>&nbsp;</th>
                <?php
                foreach ($data['list_fields'] as $field)
                {
                    echo '<th>' . $_MIDCOM->i18n->get_string($field, 'midcom.admin.user') . "</th>\n";
                }
                ?>
            </tr>
        </thead>
        <tbody>
    <?php
}
?>