<?php
?>
            </tbody>
        </table>
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
        </form>
    </div>
</div>