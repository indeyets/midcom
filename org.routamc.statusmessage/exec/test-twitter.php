<?php
$_MIDCOM->auth->require_valid_user();

$user = $_MIDCOM->auth->user->get_storage();

$twitter = org_routamc_statusmessage_importer::create('twitter');
$statuses = $twitter->get_twitter_status($user);

if ($statuses)
{
    echo "<h1>We got these status messages from Twitter</h1>\n";
    echo "<ul>\n";
    foreach ($statuses as $status)
    {
        echo "<li>{$status['authorname']}: {$status['text']} (" . strftime('%x %X', $status['published']) . ")</li>\n";
    }
    echo "</ul>\n";
}
else
{
    echo "Failed to get status messages, last error is {$twitter->error}";
}
?>