<h1><?php echo sprintf($_MIDCOM->i18n->get_string('edit element %s', 'midcom.admin.styleeditor'), htmlspecialchars("<({$data['style_element']})>")); ?></h1>
<div id="midcom_admin_styleeditor_style">
    <?php
    if ($data['style_element_default_contents'] != '')
    {
    ?>
    <fieldset class="component_default">
        <legend class="<?php if (isset($data['style_element_object'])) { echo 'hidden'; } else { echo 'visible'; } ?>" onclick="javascript:toggle_twisty('midcom_admin_styleeditor_style_contents');">
            <?php echo $_MIDCOM->i18n->get_string('component default', 'midcom.admin.styleeditor'); ?>
            <img class="twisty" src="<?php echo MIDCOM_STATIC_URL; ?>/midcom.admin.styleeditor/twisty-<?php echo (isset($data['style_element_object'])) ? 'hidden' : 'down'; ?>.gif" alt="-" />
        </legend>
        <div id="midcom_admin_styleeditor_style_contents" style="display: <?php if (isset($data['style_element_object'])) { echo 'none'; } else { echo 'block'; } ?>;" class="description">
            <div class="wrapper">
                <button class="copy" onclick="javascript:copy_to_edit('midcom_admin_styleeditor_style_edit', this.parentNode.getElementsByTagName('pre')[0].innerHTML);">
                    <?php echo $_MIDCOM->i18n->get_string('copy to editor', 'midcom.admin.styleeditor'); ?>
                </button>
                <pre id="midcom_admin_styleeditor_style_view">&(data['style_element_default_contents']);</pre>
            </div>
        </div>
    </fieldset>
    <?php
    }
    ?>
    <form class="midcom_admin_styleeditor_styleeditor" method="post" action="." enctype="multipart/form-data">
        <fieldset>                                                                                                                            
            <legend><?php echo $_MIDCOM->i18n->get_string('element from file', 'midcom.admin.styleeditor'); ?></legend>                            
            <input type="file" name="midcom_admin_styleeditor_style_file" id="midcom_admin_styleeditor_style_file" class="file" />            
        </fieldset> 
        <fieldset>
            <legend><?php echo $_MIDCOM->i18n->get_string('local element', 'midcom.admin.styleeditor'); ?></legend>
            <?php
            if (   isset($data['style_element_object'])
                && $data['style_element_object']->sitegroup != $_MIDGARD['sitegroup'])
            {
                echo "<pre>\n";
                if (isset($data['style_element_object']))
                {
                    echo htmlentities($data['style_element_object']->value);
                }
                echo "</pre>\n";
            }
            else
            {
            ?>
            <textarea cols="60" rows="20" id="midcom_admin_styleeditor_style_edit" name="midcom_admin_styleeditor_style_edit" class="element"><?php
            if (isset($data['style_element_object']))
            {
                echo $data['style_element_object']->value;
            }
            ?></textarea>
            <div class="form_toolbar">
                <input type="submit" class="save" accesskey="s" name="midcom_admin_styleeditor_style_save" value="<?php echo $_MIDCOM->i18n->get_string('save', 'midcom'); ?>" />
                <?php
                if (   isset($data['style_element_object'])
                    && $data['style_element_object']->can_do('midgard:delete'))
                {
                    $disabled = '';           
                    ?>
                    <input type="submit" class="delete"&(disabled:h); name="midcom_admin_styleeditor_style_delete" value="<?php echo $_MIDCOM->i18n->get_string('delete', 'midcom'); ?>" />
                    <?php
                }
                ?>
            </div>
            <?php
            }
            ?>
        </fieldset>
    </form>
</div>