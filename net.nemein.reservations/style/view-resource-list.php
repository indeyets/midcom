<form method="get" action="&(_MIDGARD['uri']:h);">
    <label for="new_resource">
        <?php echo $data['l10n']->get('select resources to compare'); ?>
    </label>
    <select name="resources[]" multiple="multiple">
    <?php
    foreach ($data['resources'] as $guid => $resource)
    {
        if (   isset($_GET['resources'])
            && in_array($guid, $_GET['resources']))
        {
            $selected = ' selected="selected"';
        }
        else
        {
            $selected = '';
        }
        
        echo "            <option{$selected} value=\"{$guid}\">{$resource}</option>\n";
    }
    ?>
    </select>
    <br />
    <input type="submit" name="f_submit" value="<?php echo $data['l10n']->get('submit'); ?>" />
</form>
