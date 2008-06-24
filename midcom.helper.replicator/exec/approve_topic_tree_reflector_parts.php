<?php
if (   !isset($_POST['auth_token'])
    || empty($_POST['auth_token']))
{
    $_MIDCOM->auth->require_admin_user();
}
else
{
    $_MIDCOM->auth->request_sudo();
    $obj = new midcom_db_topic((int)$_REQUEST['root_id']);
    if (!is_a($obj, 'midcom_db_topic'))
    {
        $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Could not fetch topic");
        // This will exit();
    }
    if ($obj->parameter('midcom.helper.replicator', 'approve_auth_token') !== $_POST['auth_token'])
    {
        $obj->parameter('midcom.helper.replicator', 'approve_auth_token', '');
        $_MIDCOM->generate_error(MIDCOM_ERRFORBIDDEN, 'Invalid auth_token');
        // This will exit
    }
    $obj->parameter('midcom.helper.replicator', 'approve_auth_token', '');
}
$_MIDCOM->load_library('midcom.helper.reflector');
$_MIDCOM->load_library('org.openpsa.httplib');

switch($_SERVER['SERVER_PORT'])
{
    case 80:
        $GLOBALS['approve_topic_tree_uri'] = "http://{$_SERVER['SERVER_NAME']}{$_SERVER['REQUEST_URI']}";
        break;
    case 443:
        $GLOBALS['approve_topic_tree_uri'] = "https://{$_SERVER['SERVER_NAME']}{$_SERVER['REQUEST_URI']}";
        break;
    default:
        if (!empty($_SERVER['HTTPS']))
        {
            $GLOBALS['approve_topic_tree_uri'] = 'https://';
        }
        else
        {
            $GLOBALS['approve_topic_tree_uri'] = 'http://';
        }
        $GLOBALS['approve_topic_tree_uri'] .= "{$_SERVER['SERVER_NAME']}:{$_SERVER['SERVER_PORT']}{$_SERVER['REQUEST_URI']}";
        break;
}

function create_token()
{
    //Use mt_rand if possible (faster, more random)
    if (function_exists('mt_rand'))
    {
        $rand = 'mt_rand';
    }
    else
    {
        $rand = 'rand';
    }
    $tokenchars = 'abcdefghijklmnopqrstuvwxyz0123456789';
    $token = $tokenchars[$rand(0, strlen($tokenchars) - 11)];
    for ($i = 1; $i < 18; $i++)
    {
        $token .= $tokenchars[$rand(0, strlen($tokenchars) - 1)];
    }
    return $token;
}


//Disable limits
// TODO: Could this be done more safely somehow
@ini_set('memory_limit', -1);
@ini_set('max_execution_time', 0);

function approve_object_reflectorrecursive($obj)
{
    $class = get_class($obj);
    $meta =& midcom_helper_metadata::retrieve($obj);
    echo "Approving {$class} #{$obj->id}, ";
    $meta->approve();
    echo mgd_errstr() . "<br/>\n";
    flush();
    $children = midcom_helper_reflector_tree::get_child_objects($obj);
    if (empty($children))
    {
        return;
    }
    foreach ($children as $child_class => $child_objects)
    {
        foreach ($child_objects as $k => $child)
        {
            if (!is_a($child, 'midgard_topic'))
            {
                approve_object_reflectorrecursive($child);
            }
            else
            {
                // Start another background job
                $http_client = new org_openpsa_httplib();
                $token = create_token();
                $child->parameter('midcom.helper.replicator', 'approve_auth_token', $token);
                $post_variables = array('root_id' => $child->id, 'auth_token' => $token);
                $response = $http_client->post($GLOBALS['approve_topic_tree_uri'], $post_variables, array('User-Agent' => 'midcom-exec-midcom.helper.replicator/approve_topic_tree_reflector_parts.php'));
                if ($response === false)
                {
                    // returned with failure
                    echo "failure.<br/>\n   Background processing failed, error: {$http_client->error}<br/>\n";
                    echo "Url: " . $GLOBALS['approve_topic_tree_uri'] . "?" . join("=", $post_variables);
                    $body = $http_client->_client->getResponseBody();
                    if (!empty($body))
                    {
                        echo "   Background response body:<br/>\n---<br/>\n{$body}<br/>\n---<br/>\n<br/>\n";
                    }
                    unset($body);
                }
                else
                {
                    echo $response;
                }
                flush();
            }
            unset($child_objects[$k], $children[$child_class][$k], $child);
        }
        unset($children[$child_class], $child_objects);
    }
    unset($children);
}

if (   !isset($_REQUEST['root_id'])
    || empty($_REQUEST['root_id']))
{
    $site_root = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ROOTTOPIC);
    $default_id = $site_root->id;

    if ($default_id == 0)
    {
        $default_id = '';
    }
?>
<h1>Approve topic tree</h1>
<p>Enter id of topic to start from, current root_topic is <?php echo $default_id; ?>.</p>
<form method="post">
    Root style id: <input name="root_id" type="text" size=5 value="<?php echo $default_id; ?>" />
    <input type="submit" value="approve" />
</form>
<?php
}
else
{
    while(@ob_end_flush());

    $root = (int)$_REQUEST['root_id'];
    $root_topic = new midcom_db_topic($root);
    if (!is_a($root_topic, 'midcom_db_topic'))
    {
        $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Could not fetch topic #{$root}");
        // This will exit();
    }

    approve_object_reflectorrecursive($root_topic);

    ob_start();
}
?>