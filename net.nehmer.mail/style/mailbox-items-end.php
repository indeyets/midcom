<?php
?>
            </tbody>
        </table>
        <?php
        if (isset($data['qb_pager'])
            && is_object($data['qb_pager'])
            && method_exists($data['qb_pager'], 'show_pages'))
        {
            echo "<div class=\"net_nehmer_mail_pager\">\n";
            $data['qb_pager']->show_pages();
            echo "</div>\n";
        }
        ?>
        <?php
        if ($data['show_actions'])
        {
        ?>
        <div class="list-actions">
            <select id="net_nehmer_mail_actions" name="net_nehmer_mail_actions" size="1" onchange="this.form.submit();">
                <option value=""><?php $data['l10n']->show('with selected'); ?>:</option>
                <?php
                    foreach ($data['actions'] as $action => $title)
                    {
                        echo "<option value=\"{$action}\">{$title}</option>";
                    }
                ?>
            </select>
            <input type="submit"
                   name="&(data['perform_button_name']);"
                   value="<?php $data['l10n']->show('perform action'); ?>"
            />
        </div>
        <?php
        }
        ?>
        </form>
    </div>
</div>