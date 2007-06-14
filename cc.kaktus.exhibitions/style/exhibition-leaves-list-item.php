<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$view = $data['datamanager']->get_content_html();
?>
            <li class="sortable">
                <input type="hidden" name="sortable[]" value="<?php echo $data['leaf']->guid; ?>" />
                <?php
                if (   isset($view['image'])
                    && preg_match('/<(img.+?)>/', $view['image'], $regs))
                {
                    echo "<{$regs[1]}>\n";
                }
                ?>
                &(view['title']:h);
                <span class="edit">
                    <a target="_blank" href="&(prefix:h);edit/<?php echo $data['leaf']->guid; ?>/"><?php echo $data['l10n']->get('edit'); ?></a>
                </span>
            </li>
