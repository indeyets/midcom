<?php
echo '<?'.'xml version="1.0" encoding="UTF-8"?'.">\n";
$_MIDCOM->add_link_head
(
    array
    (
        'rel' => 'stylesheet',
        'type' => 'text/css',
        'href' => MIDCOM_STATIC_URL.'/midcom.services.auth/style.css',
    )
);
$title = 'About Midgard';
$_MIDCOM->auth->require_valid_user();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
    <head>
        <title>Midgard CMS - <?php echo $title; ?></title>
        <?php echo $_MIDCOM->print_head_elements(); ?>
        <style type="text/css">
            <!--
            #content
            {
                font-size: 1.2em;
                text-align: left;
                width: 625px;
                margin: 0px 20px;
                height: 325px;
            }
            #bottom #version
            {
                padding-top: 50px;   
            }
            -->
        </style>
        <link rel="shortcut icon" href="<?php echo MIDCOM_STATIC_URL; ?>/stock-icons/logos/favicon.ico" />
    </head>

    <body>
    <div id="container">
        <div id="branding">
        <div id="title"><h1>Midgard CMS</h1><h2><?php echo $title; ?></h2></div>
        <div id="grouplogo"><a href="http://www.midgard-project.org/"><img src="<?php echo MIDCOM_STATIC_URL; ?>/stock-icons/logos/midgard-bubble-104x104.gif" width="104" height="104" alt="Midgard" title="Midgard" /></a></div>
        </div>
        <div class="clear"></div>
        <div id="content">
            <p>
                    <a href="http://www.midgard-project.org/">Midgard</a> is a Content management Toolkit. It is Free Software that can be used to construct interactive web applications. <a href="http://www.midgard-project.org/midgard/">Learn more &raquo;</a>
            </p>
            <p>
                    Copyright&copy;1999-<?php echo date('Y'); ?> <a href="http://www.midgard-project.org/community/">The Midgard Project</a>. <a href="http://www.gnu.org/licenses/lgpl.html">Free software</a>.
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

                // FIXME: For some reason not all 1.7 and 1.9 installs have midgard_quota defined                
                if (   isset($_MIDGARD['config']['quota'])
                    && $_MIDGARD['config']['quota']
                    && class_exists('midgard_quota'))
                {
                    $qb = new midgard_query_builder('midgard_quota');
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
                            if (   $apache
                                && count($apache) > 1)
                            {
                                echo $apache[1];
                            }
                            ?></td>
                        <td>Web server</td>
                    </tr>
                </tbody>
            </table>
            <p>See also list of <a href="<?php echo "{$_MIDGARD['self']}midcom-exec-midcom/credits.php"; ?>">MidCOM Components and Developers</a>.</p>
            <?php
            // TODO: Check if MidCOM is up to date
            ?>
            </div>

            <div id="footer">
                <div class="midgard">
                    Copyright &copy; 1998-<?php echo date('Y'); ?> <a href="http://www.midgard-project.org/">The Midgard Project</a>. Midgard is <a href="http://en.wikipedia.org/wiki/Free_software">free software</a> available under <a href="http://www.gnu.org/licenses/lgpl.html">GNU Lesser General Public License</a>.
                </div>
            </div>
    </div>
    </body>
</html>
