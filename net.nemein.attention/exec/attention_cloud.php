<?php
$_MIDCOM->auth->require_valid_user();
?>
<html>
    <head>
        <style>
            body
            {
                font-size: 0.8em;
            }
            ul.cloud
            {
                margin: 0px;
                padding: 0px;
                text-align: center;
            }
            ul.cloud li
            {
                list-style: none;
                display: inline;
                margin-right: 1em;
                font-size: smaller;
                white-space: nowrap;
            }
            ul.cloud li em
            {
                font-size: larger;
                font-style: normal;
            }
        </style>
    </head>
    <body>
        <?php
        $exporter = net_nemein_attention_exporter::create('cloud');
        $person = $_MIDCOM->auth->user->get_storage();
        
        if (isset($_GET['profile']))
        {
            // Export only a given profile
            $exporter->export($person, $_GET['profile']);
        }
        else
        {
            // Export all user's APML data
            $exporter->export($person);
        }
        ?>
    </body>
</html>