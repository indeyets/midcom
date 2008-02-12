<h1>&(data['view_title']);</h1>

<?php
echo "<table>\n";
echo "    <thead>\n";
echo "        <tr>\n";
echo "            <th>" . $data['l10n']->get('posted') . "</th>\n";
echo "            <th>" . $data['l10n']->get('comment') . "</th>\n";

$additional = $data['config']->get('additional_vote_keys');
foreach ($additional as $field)
{
    echo "            <th>{$field}</th>\n";
}
echo "            <th>&nbsp;</th>\n";
echo "        </tr>\n";
echo "    </thead>\n";
echo "    <tbody>\n";

foreach ($data['votes'] as $vote)
{
    $view = $data['votes_controllers'][$vote->guid]->get_content_html();
    echo "        <tr>\n";
    echo "            <td>" . strftime('%x %X', $vote->metadata->published) . "</td>\n";
    echo "            <td>{$view['comment']}</td>\n";

    foreach ($additional as $field)
    {
        echo "            <td>{$view[$field]}</td>\n";
    }
    echo "            <td>" . $data['votes_toolbars'][$vote->guid]->render() . "</td>\n";
    echo "        </tr>\n";
}

echo "    </tbody>\n";
echo "</table>\n";

$data['qb']->show_pages();
?>