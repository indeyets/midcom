<?php
echo "<h1>{$data['view_title']}</h1>\n";

if (count($data['help_files']) > 0)
{
    echo "<h2>" . $_MIDCOM->i18n->get_string('help', 'midcom.admin.help') . "</h2>\n";
    
    echo "<ul>\n";
    foreach ($data['help_files'] as $file_info)
    {
        $uri_string = basename($file_info['path']);
        $uri_parts = explode('.', $uri_string);
        $uri = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . "__ais/help/{$uri_parts[0]}.html";
        echo "<li><a href=\"{$uri}\">{$file_info['subject']}</a></li>\n";
    }
    echo "</ul>\n";
}

if (count($data['request_switch_info']) > 0)
{
    echo "<h2>" . $_MIDCOM->i18n->get_string('available urls', 'midcom.admin.help') . "</h2>\n";
    
    echo "<dl>\n";
    foreach ($data['request_switch_info'] as $request_id => $request_info)
    {
        echo "<dt>{$request_info['route']}</dt>\n";
        echo "<dd>\n";
        echo "    <table>\n";
        echo "        <tbody>\n";
        echo "            <tr>\n";
        echo "                <th>" . $data['l10n']->get('handler_id') . "</th>\n";
        echo "                <td>{$request_id}</td>\n";
        echo "            </tr>\n";

        if (isset($request_info['controller']))
        {
            echo "            <tr>\n";
            echo "                <th>" . $data['l10n']->get('controller') . "</th>\n";
            echo "                <td>{$request_info['controller']}</td>\n";
            echo "            </tr>\n";
        }

        if (isset($request_info['action']))
        {
            echo "            <tr>\n";
            echo "                <th>" . $data['l10n']->get('action') . "</th>\n";
            echo "                <td>{$request_info['action']}</td>\n";
            echo "            </tr>\n";
        }
        
        echo "        </tbody>\n";
        echo "    </table>\n";
        echo "</dd>\n";
    }
    echo "</dl>\n";
}
?>