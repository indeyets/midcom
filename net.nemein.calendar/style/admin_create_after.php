<script type="text/javascript">
    <?php
    if (isset($data['cancelled']))
    {
        echo "alert(\"You clicked cancel\");\n";
    }
    else
    {
        echo "alert(\"You created event {$data['event']->title}\");\n";
    }
    ?>
</script>