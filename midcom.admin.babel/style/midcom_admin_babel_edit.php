<?php

//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $data['plugin_anchorprefix'];

// available languages
$languages = $data['l10n']->_language_db;
// available compoments

$components = array_keys($_MIDCOM->componentloader->manifests);
asort($components);

$deflang = $languages[$_MIDCOM->i18n->get_fallback_language()]['enname'];
$editlang = $languages[$data['view_lang']]['enname'];
?>
<h1><?php 
    echo sprintf($data['l10n']->get('edit strings for %s [%s]'), $data['component_translated'], $editlang);
?></h1>

<form method="post" action="&(prefix);save/&(data['view_component']);/&(data['view_lang']);/" class="datamanager">

<div class="form_toolbar">
  <input type="submit" name="f_submit" accesskey="s" value="<?php echo $data['l10n_midcom']->get('save');?>" class="save">
  <input type="submit" name="f_cancel" accesskey="c" value="<?php echo $data['l10n_midcom']->get('cancel');?>" class="cancel">
</div>

<table border="1">
    <thead>
        <tr>
            <th><?php echo $data['l10n']->get('string-id')?></th>
            <th>&(deflang); (<?php echo $data['l10n']->get('default');?>)</th>
            <?php 
            if ($data['view_lang'] != 'en') 
            { 
                ?>
                <th>&(editlang);</th>
                <?php 
            } 
            ?>
        </tr>
    </thead>
    
    <tbody>
    
        <tr class="newstring">
            <td><input type="text" name="new_stringid" size="35" /></td>
            <td><textarea type="text" name="new_en" cols="40" rows="2" wrap="virtual"></textarea></td>
            <?php 
            if ($data['view_lang'] != 'en') 
            { 
                ?>
                <td><textarea type="text" name="new_loc" cols="40" rows="2" wrap="virtual"></textarea></td>
                <?php 
            } 
            ?>
        </tr>

        <?php
        $count = 0;
        foreach ($data['view_strings'] as $id => $str) 
        {
            $en = $str['en'];
            $loc = $str[$data['view_lang']];
            ?>
            <tr>
                <th>&(id);</th>
                <?php 
                if ($data['view_lang'] != 'en') 
                { 
                    ?>
                    <td>&(en);</td>
                    <?php 
                } 
                ?>
                <td><input type="hidden" name="string_id[&(count);]" value="&(id);" /><textarea name="string_value[&(count);]" cols="40" rows="2" wrap="virtual"><?php echo htmlspecialchars($loc); ?></textarea></td>
            </tr>   
            <?php
            $count++;
        }
        ?>
    </tbody>
</table>

<div class="form_toolbar">
  <input type="submit" name="f_submit" accesskey="s" value="<?php echo $data['l10n_midcom']->get('save');?>" class="save">
  <input type="submit" name="f_cancel" accesskey="c" value="<?php echo $data['l10n_midcom']->get('cancel');?>" class="cancel">
</div>

</form>