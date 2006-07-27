<?php
// Available Request keys: total, page, total_pages, first, last
$data =& $_MIDCOM->get_custom_context_data('request_data');

if ($data['total'] > 0)
{
?>
<div class="net_nehmer_publications_pagelist">
<p>
<?php
    printf($data['l10n']->get('%d publications found on %d page(s).'),
        $data['total'],
        $data['total_pages']);

    if ($data['total_pages'] > 1)
    {
        echo "<br />\n";

        if ($data['page'] > 1)
        {
            $previous = $data['page'] - 1;
            $pagelink = ($previous != 1) ? "?page={$previous}" : '';
            echo "<a href='{$data['pagelink_prefix']}'>&laquo;</a>\n";
            echo "<a href='{$data['pagelink_prefix']}{$pagelink}'>&lsaquo;</a>\n";
        }

        for ($i = 1; $i <= $data['total_pages']; $i++)
        {
            if ($i == 1)
            {
                $pagelink = '';
            }
            else
            {
                $pagelink = "?page={$i}";
            }

            if ($data['page'] == $i)
            {
                echo "{$i}\n";
            }
            else
            {
                echo "<a href='{$data['pagelink_prefix']}{$pagelink}'>{$i}</a>\n";
            }
        }

        if ($data['page'] < $data['total_pages'])
        {
            $next = $data['page'] + 1;
            echo "<a href='{$data['pagelink_prefix']}?page={$next}'>&rsaquo;</a>\n";
            echo "<a href='{$data['pagelink_prefix']}?page={$data['total_pages']}'>&raquo;</a>\n";
        }
    }
?>
</p>
</div>
<?php } ?>
