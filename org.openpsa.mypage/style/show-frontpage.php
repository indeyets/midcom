<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<div class="main">
    <?php
    foreach ($data['wide_items'] as $path => $node)
    {
        $_MIDCOM->dynamic_load($path);
    }
    ?>

    <?php
    // Weekly report (TODO: Make configurable)
    $reports_node = midcom_helper_find_node_by_component('org.openpsa.reports');
    if (!empty($reports_node))
    {
        echo "<div class=\"weekly_report\">\n";
        $_REQUEST['org_openpsa_reports_query_data'] = array();
        $query =& $_REQUEST['org_openpsa_reports_query_data'];
        $query['start'] = array('timestamp' => time());
        $query['end'] = array('timestamp' => time()+3600);
        //TODO: Make configurable
        $query['style'] = 'builtin:weekly';
        $query['resource'] = $_MIDCOM->auth->user->id;
        $query['task'] = 'all';
        $query['component'] = 'org.openpsa.projects';
        $query['grouping'] = 'person';
        $query['invoiceable_filter'] = -1;
        $query['mimetype'] = 'text/html';
        $query['skip_html_headings'] = true;
        $_MIDCOM->dynamic_load($reports_node[MIDCOM_NAV_RELATIVEURL] . 'projects/get/');
        echo "</div>\n";
    }
    ?>

</div>
<div class="content-text">
        <?php
        if (   isset($data['leftbar_items'])
            && is_array($data['leftbar_items']))
        {
            foreach ($data['leftbar_items'] as $path => $node)
            {
                if (strstr($node[MIDCOM_NAV_COMPONENT], 'org.openpsa'))
                {
                    $_MIDCOM->dynamic_load($path);
                }
                else
                {
                    echo "<div class=\"area\">\n";
                    $_MIDCOM->dynamic_load($path);
                    echo "</div>\n";
                }
            }
        }
        ?>
</div>
<div class="content-text">
    <?php
    if (   isset($data['main_items'])
        && is_array($data['main_items']))
    {
        foreach ($data['main_items'] as $path => $node)
        {
            if (strstr($node[MIDCOM_NAV_COMPONENT], 'org.openpsa'))
            {
                $_MIDCOM->dynamic_load($path);
            }
            else
            {
                echo "<div class=\"area\">\n";
                $_MIDCOM->dynamic_load($path);
                echo "</div>\n";
            }
        }
    }

    ?>
</div>
<div class="sidebar">
    <?php
    foreach ($data['sidebar_items'] as $path => $node)
    {
        if (strstr($node[MIDCOM_NAV_COMPONENT], 'org.openpsa'))
        {
            $_MIDCOM->dynamic_load($path);
        }
        else
        {
            echo "<div class=\"area\">\n";
            $_MIDCOM->dynamic_load($path);
            echo "</div>\n";
        }
    }
    ?>
</div>