<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$task =& $data['task'];
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());
?>
<div class="org_openpsa_projects_projectbroker">
    <div class="main">
        <h1><?php echo $data['task']->title; ?></h1>

        <form method="post">
            <ul class="prospects" id="prospects_list">
            </ul>
            <div class="form_toolbar">
                <input type="submit" accesskey="s" class="save" name="save" value="<?php echo $data['l10n_midcom']->get('save'); ?>" />
                <input type="submit" accesskey="c" class="cancel" name="cancel" value="<?php echo $data['l10n_midcom']->get('cancel'); ?>" />
            </div>
        </form>

        <script type="text/javascript">
            prospects_handler = new project_prospects_renderer('prospects_list', '<?php echo $node[MIDCOM_NAV_FULLURL]; ?>', '<?php echo $task->guid; ?>');
            /* TODO: Make interval */
            prospects_handler.get_prospect_list();
        </script>
    </div>
    <div class="sidebar">
    </div>
</div>