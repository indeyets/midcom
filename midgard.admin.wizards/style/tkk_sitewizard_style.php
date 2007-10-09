<?php
/**
 * This is the styleelement I use to show the index
 * Use this to get variables etc from the handler:
 */
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<h1><?php echo $data['l10n']->get('select style'); ?></h1>

<form method="post" name="tkk_sitewizard_style">

    <fieldset class="style">
      <img src="<?php echo MIDCOM_STATIC_URL; ?>/midgard.admin.sitewizard/no-style-preview.jpg" width="130" alt="Custom" />
      <label for="tkk_sitewizard_style_select_template_custom" class="action_select">
      <input type="radio" name="tkk_sitewizard_style_select_template" value="custom"
      id="tkk_sitewizard_style_select_template_custom" /><?php echo $data['l10n']->get('custom'); ?>
      </label>
      <div class="description"><?php echo $data['l10n']->get('completely empty style'); ?></div>
    </fieldset>
<?php    
        foreach ($data['templates'] as $template)
        {
            // Clean up the template display name
            $template_name = str_replace("template_","",$template->name);
    
            // template metadata
            // TODO: Get from PEAR
            $template_description = $template->parameter('midgard.admin.sitewizard', 'template_description');
            $template_credits = $template->parameter('midgard.admin.sitewizard', 'template_credits');
    
            // template preview thumbnail
            $template_image = $template->getattachment('__preview.jpg');
            if ($template_image)
            {
                $template_image_url = "{$_MIDGARD['self']}midcom-serveattachmentguid-{$template_image->guid}/{$template_image->name}";
            }
            else
            {
                $template_image_url = MIDCOM_STATIC_URL."/midgard.admin.sitewizard/no-style-preview.jpg";
            }
    
        ?>
            <fieldset class="style">
              <img src="&(template_image_url);" width="130" alt="&(template_name);" />
              <label for="tkk_sitewizard_style_select_template_&(template.id);" class="action_select">
              <input type="radio" name="tkk_sitewizard_style_select_template" value="&(template.id);"
              id="tkk_sitewizard_style_select_template_&(template.id);" />&(template_name);</label>
              <div class="credits"><?php echo $template_credits; ?></div>
              <div class="description"><?php echo $template_description; ?></div>
            </fieldset>
        
        <?php
        }
?>
           
        <input type="submit" name="tkk_sitewizard_style_submit" value="<?php echo $data['l10n']->get('next'); ?>">
        </form> 
