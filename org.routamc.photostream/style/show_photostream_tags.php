<div class="org_routamc_photostream">
<h1><?php echo $data['view_title']; ?></h1>

    <ul class="tags">
        <?php
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        
        foreach ($data['tags'] as $tag => $count)
        {
            $class = 'not-very-popular';
            if ($count >= 60)
            {
                $class = 'very-popular';
            }
            elseif ($count >= 40)
            {
                $class = 'popular';
            }
            elseif ($count > 10)
            {
                $class = 'somewhat-popular';
            }
            
            echo "<li class=\"{$class}\">";
            $tag_link = "<a href=\"{$prefix}tag/all/{$tag}/\" class=\"tag\" rel=\"tag\">{$tag}</a>";
            echo sprintf($data['l10n']->get('%d photos tagged with %s'), $count, $tag_link);
            echo "</li>\n";
        }
        ?>
    </ul>
</div>