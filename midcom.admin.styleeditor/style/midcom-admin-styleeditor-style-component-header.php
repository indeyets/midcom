<a name="<?php echo str_replace('.', '_', $data['component']); ?>"></a>
<fieldset id="midcom_admin_styleeditor_<?php echo str_replace('.', '_', $data['component']); ?>">
        <legend onclick="javascript:toggle_twisty('<?php echo str_replace('.', '_', $data['component']); ?>_contents');">
            <?php echo $data['component_details']['name']; ?> (&(data['component']:h);)
            <img class="twisty" src="<?php echo MIDCOM_STATIC_URL; ?>/midcom.admin.styleeditor/twisty-<?php echo ($data['display'] === 'none') ? 'hidden' : 'down'; ?>.gif" alt="-" />
        </legend>
        <div id="<?php echo str_replace('.', '_', $data['component']); ?>_contents" style="display: &(data['display']);;" class="description">
            <div class="wrapper">
                <p>
                    <?php echo $data['component_details']['description']; ?>
                </p>
                <?php
                if ($data['help'])
                {
                    // If component has a style help text available, show it
                    ?>
                    <div class="help">
                        &(data['help']:h);    
                    </div>
                    <?php
                }
                else
                {
                    // Otherwise default to just listing the elements
                    echo "<ul>\n";
                    foreach ($data['style_elements'] as $style_element => $filename)
                    {
                        ?>
                            <li><a href="edit/&(style_element);/">&lt;(&(style_element);)&gt;</a></li>
                        <?php
                    }
                    echo "</ul>\n";
                }
                ?>
                <h2><?php echo sprintf($_MIDCOM->i18n->get_string('list of %s folders using the same style template path', 'midcom.admin.styleeditor'), $data['component']); ?></h2>
                <ul>
