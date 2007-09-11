<script type="text/javascript">
    <?php
    if (! isset($data['cancelled']))
    {
        echo "window.parent.add_item({$data['jsdata']});";
    }
    ?>
    window.parent.close_dialog();
</script>