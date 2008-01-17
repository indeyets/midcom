<?php
$component =& $data['component_data'];
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<div class="midgard_admin_asgard_components_component">
    <div class="maintainers">
        <?php
        if (count($component['maintainers']) > 0)
        {
            echo "<h3>" . $_MIDCOM->i18n->get_string('created by', 'midgard.admin.asgard') . "</h3>\n";
            echo "<ul>\n";
            
            foreach ($component['maintainers'] as $username => $maintainer)
            {
                $status = 'active';
                if (   isset($maintainer['active'])
                    && $maintainer['active'] == 'no')
                {
                    $status = 'passive';
                }
                
                // TODO: Replace gravatar with photo from Midgard site as soon as we have a URL method for it
                $gravatar_url  = "http://www.gravatar.com/avatar.php?gravatar_id=" . md5($maintainer['email']) . "&amp;size=20";
                $gravatar_url .= '&amp;default=' . urlencode('http://www.midgard-project.org/midcom-static/stock-icons/16x16/stock_person.png');
                
                echo "<li class=\"{$status} vcard\"><span class=\"photoarea\"><img class=\"photo\" src=\"{$gravatar_url}\" /></span><a href=\"mailto:{$maintainer['email']}\" class=\"email\"><span class=\"fn\">{$maintainer['name']}</a></a> (<a href=\"http://www.midgard-project.org/community/account/view/{$username}/\">{$username}</a>)</li>\n";
            }
            
            echo "</ul>\n";
        }
        ?>
    </div>
    
    <h2><img src="<?php echo MIDCOM_STATIC_URL; ?>/&(component['icon']);" alt="" /> &(component['name']);</h2>

    <div class="meta">
        <p class="version">&(component['version']);</p>

        <p class="description">&(component['title']);</p>
    </div>

    <div class="help">
        <?php
        $help = new midcom_admin_help_help();
        $files = $help->list_files($data['component']);
        if (count($files) > 0)
        {
            echo "<h3>" . $_MIDCOM->i18n->get_string('component help', 'midcom.admin.help') . "</h3>\n";
            echo "<ul>\n";
            foreach ($files as $identifier => $filedata)
            {
                echo "<li><a href=\"{$prefix}__ais/help/{$data['component']}/{$identifier}/\" target=\"_blank\">{$filedata['subject']}</a></li>\n";
            }
            echo "</ul>\n";
        }
        ?>
    </div>
</div>