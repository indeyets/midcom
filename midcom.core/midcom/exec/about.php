<?php
/**
 * About Midgard screen
 * TODO: Include the community and contributors somehow
 */
$_MIDCOM->auth->require_valid_user();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>About Midgard</title>
        <style type="text/css">
            body
            {
                font-family: "Bitstream Vera Sans", "Arial", "Helvetica", sans-serif;
                margin: 0px;
                padding: 20px;
                padding-top: 45px;  
                padding-left: 140px;
                color: #333333;
                background-color: #ffffff;
                background-image: url('<?php echo MIDCOM_STATIC_URL; ?>/stock-icons/logos/midgard-fade.png');
                background-position: top;
                background-repeat: repeat-x;
            }
            #grouplogo
            {
                position: absolute;
                left: 16px;
                top: 10px;
            }
            #grouplogo img
            {
                border: 0px;
            }
            body a
            {
                text-decoration: none;
                font-weight: bold;
                color: #663822;
                background-color: transparent;
            }
            body a:hover
            {
                text-decoration: underline;
            }
            body h1
            {
                margin-top: 0px; 
                margin-bottom: 45px;            
            }
            body table caption
            {
                text-align: left;
                padding-bottom: 0px;
                font-weight: bold;
            }
            body table td
            {
                padding-right: 10px;
                vertical-align: top;
                min-width: 100px;
            }
            body table td.warning
            {
                color: #cc0000;
            }
            body table
            {
                margin-bottom: 10px;
            }            
        </style>
        <link rel="shortcut icon" type="image/ico" href="<?php echo MIDCOM_STATIC_URL; ?>/stock-icons/logos/favicon.ico" />
    </head>
    <body>
        <div id="grouplogo">
            <a href="http://www.midgard-project.org/"><img src="<?php echo MIDCOM_STATIC_URL; ?>/stock-icons/logos/midgard-project.gif" alt="Midgard" /></a>
        </div>
        <div id="content">
            <h1>About Midgard</h1>
            <p>
                <a href="http://www.midgard-project.org/">Midgard</a> is a Content management Toolkit. It is Free Software that can be used to construct interactive web applications. <a href="http://www.midgard-project.org/midgard/">Learn more &raquo;</a>
            </p>

            <p>
                Copyright&copy;1999-<?php echo date('Y'); ?> <a href="http://www.midgard-project.org/community/">The Midgard Project</a>. <a href="">Some rights reserved</a>.
            </p>
            
            <?php
            if (   $_MIDGARD['admin']
                && $_MIDGARD['sitegroup'] != 0
                && function_exists('mgd_get_sitegroup_size'))
            {
                $sitegroup = mgd_get_sitegroup($_MIDGARD['sitegroup']);
                echo "<table class=\"disk\">\n";
                echo "    <caption>{$sitegroup->name} disk status</caption>\n";
                echo "    <tbody>\n";                

                $usage = mgd_get_sitegroup_size($sitegroup->id);
                echo "        <tr>\n";
                echo "            <td>Space used</td><td>" . midcom_helper_filesize_to_string($usage) . "</td>\n";
                echo "        </tr>\n";

                // FIXME: For some reason not all 1.7 installs have midgard_quota defined                
                if (   $_MIDGARD['config']['quota']
                    && class_exists('midgard_quota'))
                {
                    $qb = new MidgardQueryBuilder('midgard_quota');
                    $qb->add_constraint('tablename', '=', 'wholesg');
                    $qb->add_constraint('typename', '=', '');
                    $quotas = $qb->execute();
                    
                    if (count($quotas) > 0)
                    {
                        $quota = $quotas[0]->sgsizelimit;
                        $available = $quota - $usage;
                        
                        if ($available < 0)
                        {
                            $available = $available * -1;
                            // Some Midgard configurations allow user to exceed quota
                            echo "        <tr>\n";
                            echo "            <td class=\"warning\">Quota</td><td class=\"warning\">Space of " . midcom_helper_filesize_to_string($quota) . " exceeded by <strong>" . midcom_helper_filesize_to_string($available) . "</strong></td>\n";
                            echo "        </tr>\n";
                        }
                        else
                        {
                            echo "        <tr>\n";
                            echo "            <td>Quota</td><td>" . midcom_helper_filesize_to_string($available) . " of " . midcom_helper_filesize_to_string($quota) . " available</td>\n";
                            echo "        </tr>\n";
                        }
                    }                
                }

                echo "    </tbody>\n";
                echo "</table>\n";
            }
            ?>
            
            <table class="apps">
                <caption>Your installed applications</caption>
                <thead>
                    
                </thead>
                <tbody>
                    <tr>
                        <td><a href="http://www.midgard-project.org/midgard/">Midgard</a></td>
                        <td><?php echo mgd_version(); ?></td>
                        <td>Web Toolkit</td>
                    </tr>
                    <tr>
                        <td><a href="http://www.midgard-project.org/documentation/midcom/">MidCOM</a></td>
                        <td><?php echo $GLOBALS['midcom_version']; ?></td>
                        <td>Component Framework for PHP</td>
                    </tr>
                    <tr>
                        <td><a href="http://www.php.net/">PHP</a></td>
                        <td><?php echo phpversion(); ?></td>
                        <td>Web programming language</td>
                    </tr>
                    <tr>
                        <td><a href="http://httpd.apache.org/">Apache</a></td>
                        <td><?php 
                            // FIXME: IIRC, there was a function for getting this info
                            $server_software = explode(' ', $_SERVER['SERVER_SOFTWARE']);
                            $apache = explode('/', $server_software[0]);
                            echo $apache[1];
                            ?></td>
                        <td>Web server</td>
                    </tr>
                </tbody>
            </table>
            <?php
            // TODO: Check if MidCOM is up to date
            ?>
        </div>
    </body>
</html>