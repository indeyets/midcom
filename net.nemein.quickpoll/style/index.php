<?php
// Available request keys: article, datamanager, edit_url, delete_url, create_urls

$data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $data['view_article'];
$view_id = $data['article']->id;

if (version_compare(mgd_version(), '1.8.0alpha1', '>='))
{
    $publish_time = $data['article']->metadata->published;
}
else
{
    $publish_time = $data['article']->created;
}
$published = sprintf($data['l10n']->get('posted on %s.'), strftime('%Y-%m-%d %T %Z', $publish_time));
$permalink = $_MIDCOM->permalinks->create_permalink($data['article']->guid);
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$submit_string = $data['l10n']->get('vote');
$vote_count_string = $data['l10n']->get('vote count');
?>
<div class="hentry">
    <h1 class="headline">&(view['title']:h);</h1>

    <p class="published">&(published);</p>
    <p class="excerpt">&(view['abstract']:h);</p>

    <div class="content">
        <?php if (array_key_exists('image', $view) && $view['image']) { ?>
            <div style="float: right; padding: 5px;">&(view['image']:h);</div>
        <?php } ?>
        &(view["content"]:h);
        <br />
        <?php
        if (array_key_exists('manage',$data) && $data['manage'])
        {
        ?>a:
        &(view["options"]:h);
        <?php
        }
        elseif (array_key_exists('voted',$data) && $data['voted'])
        {
        ?>b:
        &(view["options"]:h);
        <br />
        &(vote_count_string);: &(data['vote_count']);
        <?php
        }
        else
        {
        ?>c:
        <form method="post"  id="net_nemein_quickpoll_vote_form" name="net_nemein_quickpoll_vote_form" action="&(prefix);vote/&(view_id);.html">
        &(view["options"]:h);
        <br /><br /><input type="submit" value="&(submit_string);" />
        </form>
        <?php
        }
        ?>
    </div>
</div>