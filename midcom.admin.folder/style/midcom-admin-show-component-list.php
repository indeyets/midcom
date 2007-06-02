<div class="form_field midcom_admin_content_componentlist">
    <dl class="components">
        <?php
        foreach (midcom_admin_folder_folder_management::get_component_list($data['topic']->component) as $path => $information)
        {
            if ($path === $data['topic']->component)
            {
                $selected = ' checked="checked"';
            }
            else
            {
                $selected = '';
            }
            ?>
            <dt class="&(information['state']:h);"><label><input type="radio" name="f_type" value="&(path:h);"&(selected:h); /><strong>&(information['name']:h);</strong> <span class="component">(&(path:h); <span class="version">&(information['version']:h);</span>)</span></label></dt>
            <?php
            if (array_key_exists('description', $information))
            {
                ?>
                <dd class="&(information['state']:h);">&(information['description']:h);</dd>
                <?php
            }
        }
        ?>
    </dl>
    <?php
    if ($_MIDCOM->auth->admin)
    {
        ?>
        <div class="note">
            <?php
            echo sprintf($_MIDCOM->i18n->get_string('install more %s from %s', 'midcom.admin.folder'),
                         'http://www.midgard-project.org/documentation/midcom-components/',
                         'http://pear.midcom-project.org/');
            ?>
        </div>
        <?php
    }
    ?>
</div>

