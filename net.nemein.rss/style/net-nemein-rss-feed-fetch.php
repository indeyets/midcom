<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');

if (isset($data['feed']))
{
    ?>
    <h1><?php echo sprintf($_MIDCOM->i18n->get_string('fetch feed %s', 'net.nemein.rss'), $data['feed']->title); ?></h1>
    <?php
}
else
{
    ?>
    <h1><?php echo $_MIDCOM->i18n->get_string('fetch feeds', 'net.nemein.rss'); ?></h1>
    <?php
}

if (count($data['items']) == 0)
{

    echo '<p>' . $_MIDCOM->i18n->get_string('no items found in feed', 'net.nemein.rss') . "</p>\n";
}
else
{
    echo "<table>\n";
    echo "    <thead>\n";
    echo "        <tr>\n";
    echo "            <th>" . $_MIDCOM->i18n->get_string('date', 'midcom') . "</th>\n";
    echo "            <th>" . $_MIDCOM->i18n->get_string('remote item', 'net.nemein.rss') . "</th>\n";
    echo "            <th>" . $_MIDCOM->i18n->get_string('local item', 'net.nemein.rss') . "</th>\n";
    echo "        </tr>\n";
    echo "    </thead>\n";
    echo "    <tbody>\n";
    foreach ($data['items'] as $item)
    {
        echo "<tr>\n";
        //$date = net_nemein_rss_fetch::parse_item_date($item);
        $date = $item['date_timestamp'];
        if ($date == 0)
        {
            echo "    <td>" . $_MIDCOM->i18n->get_string('n/a', 'net.nemein.rss') . "</td>\n";
        }
        else
        {
            echo "    <td>" . strftime('%x %X', $date) . "</td>\n";
        }
        echo "    <td><a href=\"{$item['link']}\">{$item['title']}</a></td>\n";
        
        if (!$item['local_guid'])
        {
            echo "    <td>" . $_MIDCOM->i18n->get_string('not in local database', 'net.nemein.rss') . "</td>\n";
        }
        else
        {
            $local_article = new midcom_db_article($item['local_guid']);
            $local_link = $_MIDCOM->permalinks->create_permalink($item['local_guid']);
            echo "    <td><a href=\"{$local_link}\">{$local_article->title}</a></td>\n";
        }
        
        echo "</tr>\n";
    }
    echo "    </tbody>\n";
    echo "</table>\n";
}
?>