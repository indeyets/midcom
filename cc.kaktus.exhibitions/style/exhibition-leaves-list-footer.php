<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
        </ul>
        <div class="trash">
            <h2><?php echo $data['l10n']->get('delete'); ?></h2>
            <input type="hidden" name="sortable[]" value="delete" />
            <p>
                <?php echo $data['l10n']->get('drag here the leaves to be deleted'); ?>
            </p>
            <ul class="sortable" id="cc_kaktus_exhibitions_leaf_list_trash">
            
            </ul>
        </div>
        <div class="form_toolbar">
            <input type="submit" class="save" name="f_submit" value="<?php echo $data['l10n_midcom']->get('save'); ?>" />
            <input type="submit" class="cancel" name="f_cancel" value="<?php echo $data['l10n_midcom']->get('cancel'); ?>" />
        </div>
    </form>
    <script type="text/javascript">
        // <!--
            sections = new Array();
            sections[0] = 'cc_kaktus_exhibitions_leaf_list';
            sections[1] = 'cc_kaktus_exhibitions_leaf_list_trash';
            
            Sortable.create('cc_kaktus_exhibitions_leaf_list', {tag:'li', dropOnEmpty:true, containment:sections});
            Sortable.create('cc_kaktus_exhibitions_leaf_list_trash', {tag:'li', dropOnEmpty:true, containment:sections});
        // -->
    </script>
</div>