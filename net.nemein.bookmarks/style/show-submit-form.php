<?php
global $view_bookmark, $net_nemein_bookmarks_bookmark;

if ($view_bookmark)
{
    echo "<h1>".$GLOBALS["view_l10n"]->get("edit bookmark")."</h1>\n";
}
else
{
    echo "<h1>".$GLOBALS["view_l10n"]->get("add bookmark")."</h1>\n";
}

if (isset($GLOBALS["net_nemein_bookmarks_processing_message"]))
{
    echo "<div class=\"processing_message\">".$GLOBALS["net_nemein_bookmarks_processing_message"]."</div>";
}
?>
    <form name="net_nemein_bookmarks_add" method="post" class="datamanager">
        <input class="net_nemein_bookmarks_field" type="hidden" name="net_nemein_bookmarks_add_user" value="&(_MIDGARD['user']); ?> ?>" />
        <label for="net_nemein_bookmarks_add_title">Title:
            <input class="shorttext" type="text" name="net_nemein_bookmarks_add_title" id="net_nemein_bookmarks_add_title" value="&(net_nemein_bookmarks_bookmark["title"]);" />
        </label>
        <label for="net_nemein_bookmarks_add_url">URL:
            <input class="shorttext" type="text" name="net_nemein_bookmarks_add_url" id="net_nemein_bookmarks_add_url" value="&(net_nemein_bookmarks_bookmark["url"]);"/>
        </label>
        <label for="net_nemein_bookmarks_add_extended">Extended:
            <input class="shorttext" type="text" name="net_nemein_bookmarks_add_extended" id="net_nemein_bookmarks_add_extended" value="&(net_nemein_bookmarks_bookmark["extended"]);" />
        </label>
        <label for="net_nemein_bookmarks_add_tags">Tags:
            <input class="shorttext" type="text" name="net_nemein_bookmarks_add_tags" id="net_nemein_bookmarks_add_tags" value="&(net_nemein_bookmarks_bookmark["tags"]);" />
        </label>
        <?php
        if (!$_MIDGARD['user'])
        {
            // Query for username and password if not logged in
            ?>
            <label for="net_nemein_bookmarks_username">Username:
                <input class="shorttext" type="text" name="net_nemein_bookmarks_username" id="net_nemein_bookmarks_username" />
            </label>
            <label for="net_nemein_bookmarks_password">Password:
                <input class="shorttext" type="password" name="net_nemein_bookmarks_password" id="net_nemein_bookmarks_password" />
            </label>
            <?php
        }
        ?>
        <div class="form_toolbar">
            <input type="submit" name="net_nemein_bookmarks_add_submit" value="Submit" /><br/>
        </div>
    </form>