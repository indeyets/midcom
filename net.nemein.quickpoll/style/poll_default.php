<?php
$view = $data['view_article'];
$view_id = $data['article']->guid;

$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$submit_string = $data['l10n']->get('vote');
$vote_count_string = $data['l10n']->get('vote count');
?>
<div class="hentry">
    <h1 class="headline">&(view['title']:h);</h1>

    <p class="excerpt">&(view['abstract']:h);</p>

    <div class="content">
        <?php
        if (   array_key_exists('manage',$data) 
            && $data['manage'])
        {
            ?>
            &(view["options"]:h);
            <?php
        }
        elseif (   array_key_exists('voted',$data) 
                && $data['voted']
                && isset($data['vote_count']))
        {
            ?>
            &(view["options"]:h);
            <br />
            &(vote_count_string);: &(data['vote_count']);
            <?php
        }
        else
        {
            ?>
            <form method="post"  id="net_nemein_quickpoll_vote_form" name="net_nemein_quickpoll_vote_form" action="&(prefix);vote/&(view_id);/">
                &(view["options"]:h);
                <input type="submit" value="&(submit_string);" />
            </form>
            <?php
        }
        ?>
    </div>
</div>

<?php
if (isset($data['qb']))
{
    $data['qb']->show_pages();    
}
?>