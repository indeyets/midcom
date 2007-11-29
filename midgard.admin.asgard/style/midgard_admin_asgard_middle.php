                    <?php
                    $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
                    echo "<a href=\"{$prefix}__mfa/asgard/\">";
                    echo "<img src=\"" . MIDCOM_STATIC_URL . "/midgard.admin.asgard/asgard2.png\" id=\"asgard_logo\" title=\"Asgard\" alt=\"Asgard\" />";
                    echo "</a>\n";
                    
                    if (isset($data['object']))
                    {
                        $navigation = new midgard_admin_asgard_navigation($data['object'], &$data);
                    }
                    else
                    {
                        $navigation = new midgard_admin_asgard_navigation(null, &$data);
                    }
                    $navigation->draw();
                    
                    if (($width = midgard_admin_asgard_plugin::get_preference('offset')))
                    {
                        $width += 2;
                        $content_offset = " style=\"margin-left: {$width}px\"";
                    }
                    else
                    {
                        $content_offset = '';
                    }
                    ?>
                </div>
                <div id="content"&(content_offset:h);>
                    <div class="page-title">
                        <?php
                        echo "<h1>";
                        
                        if (   isset($data['object'])
                            && isset($data['object']->lang))
                        {
                            // FIXME: It would be better to reflect whether object is MultiLang
                            if (   $data['object']->lang == 0
                                && isset($data['language_code'])
                                && $data['language_code'] !== '')
                            {
                                echo "<span class=\"object_language\">" . $_MIDCOM->i18n->get_string('in fallback language', 'midgard.admin.asgard') . '</span>';
                            }
                            elseif ($data['object']->lang != 0)
                            {
                                $lang = new midcom_baseclasses_database_language($data['object']->lang);
                               echo "<span class=\"object_language\">" .  sprintf($_MIDCOM->i18n->get_string('in %s', 'midgard.admin.asgard'), $lang->name) . '</span>';
                            }
                        }
                        
                        echo "{$data['view_title']}</h1>\n";
                        
                        ?>
                    </div>

                    <div id="breadcrumb">
                        <?php
                        $nap = new midcom_helper_nav();
                        echo $nap->get_breadcrumb_line(" &gt; ", null, 1);
                        ?>
                    </div>
<?php
$toolbar_style = '';
if (($position = midgard_admin_asgard_plugin::get_preference('toolbar_mode')))
{
    $toolbar_style = " style=\"position: {$position}\" class=\"{$position}\"";
}
?>
                    <div id="toolbar"&(toolbar_style:h);>
<?php
echo $data['asgard_toolbar']->render();

if ($position === 'absolute')
{
?>
<script type="text/javascript">
    // <![CDATA[
        $j('#toolbar')
            .draggable({
                stop: function()
                {
                    var offset = $j('#toolbar').offset();
                    jQuery.post(MIDCOM_PAGE_PREFIX + '__mfa/asgard/preferences/ajax/',
                    {
                        toolbar_x: offset.left,
                        toolbar_y: offset.top
                    });
                }
            })
            .css({
                position: 'fixed !important',
                top: '<?php echo midgard_admin_asgard_plugin::get_preference('toolbar_y'); ?>px',
                left: '<?php echo midgard_admin_asgard_plugin::get_preference('toolbar_x'); ?>px',
                cursor: 'move'
            })
            .resizable();
        
    // ]]>
</script>
<?php
}
?>

                    </div>
                    <div id="content-text">
