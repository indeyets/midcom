<?php
global $view_title;
global $view_layouts;
global $midcom;
global $config;
global $unification_errors_bookmarks;
global $unification_errors_delicious;

$prefix = $GLOBALS['view_contentmgr']->config->get('site_prefix');

if ($config)
{ 
?>
    <h2><?php echo $GLOBALS["view_l10n"]->get("unify bookmarks with http://del.icio.us"); ?></h2>
    <div id="unify">
        <form name="net_nemein_bookmarks_unify" method="post">
            <?php echo $GLOBALS["view_l10n"]->get("delicious username"); ?><br/>
            <input type="text" name="net_nemein_bookmarks_unify_username" /><br/>
            <?php echo $GLOBALS["view_l10n"]->get("delicious password"); ?><br/>
            <input type="password" name="net_nemein_bookmarks_unify_password" /><br/>
            <input type="submit" name="net_nemein_bookmarks_unify_submit" value="<?php 
                echo $GLOBALS["view_l10n"]->get("unify"); ?>" />
        </form>
    <div>
<?php
    if (isset($_POST['net_nemein_bookmarks_unify_submit']))
    {
        if (count($unification_errors_bookmarks) == 0 && count($unification_errors_delicious) == 0)
        {
            echo $GLOBALS["view_l10n"]->get("unification successful");
        }
        else
        {
            echo $GLOBALS["view_l10n"]->get("unification failed") . "<br/>";
            foreach ($unification_errors_bookmarks as $error)
            {
                echo $error . "<br/>";
            }
            foreach ($unification_errors_delicious as $error)
            {
                echo $error . "<br/>";
            }
        }
    }
}

// TODO: We should get the node address from NAP instead
$nav = new midcom_helper_nav($GLOBALS['view_contentmgr']->viewdata["context"]);
$view_url = $nav->view_current_page_url($prefix);
$server_name = $_SERVER['SERVER_NAME'];
$server_port = "";
if ($_SERVER['SERVER_PORT'] != 80)
{
    $server_port = ":".$_SERVER['SERVER_PORT'];
}
?>
<h2><?php echo $GLOBALS["view_l10n"]->get("posting bookmarks"); ?></h2>

<p>
<?php echo $GLOBALS["view_l10n"]->get("use this link to create a bookmark."); ?> 
<a href="javascript:location.href='http://&(server_name);&(server_port);&(view_url);submitbookmark?v=2&url='+encodeURIComponent(location.href)+'&title='+encodeURIComponent(document.title)" onclick="window.alert('Add this link to your bookmarks toolbar folder');location.href='http://&(server_name);';" >Post to net.nemein.bookmarks</a>
</p>

<h2><?php echo $GLOBALS["view_l10n"]->get("bookmarks"); ?></h2>