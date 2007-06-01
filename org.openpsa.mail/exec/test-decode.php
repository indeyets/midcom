<?php
$_MIDCOM->auth->require_admin_user();

echo "<p>\n";

$mail = new org_openpsa_mail();

if (   !isset($_POST['decodepath'])
    || empty($_POST['decodepath']))
{
?>
    <h2>Decode test email</h2>
    <form method="post">
        <p>
            Path to file: <input type="text" name="decodepath" value="/tmp/test.eml"/><br/>
            <input type="submit" value="Decode" />
        </p>
    </form>
<?php
}
else
{
    if (!preg_match('|^/tmp/|', $_POST['decodepath']))
    {
        echo "Path {$_POST['decodepath']} is not under /tmp/, we only allow decoding of files there<br>\n";
    }
    else
    {
        $data = file_get_contents($_POST['decodepath']);
        $mail->body = $data;
        $mail->mime_decode();
        $fields = array
        (
            'from',
            'to',
            'subject',
            'body',
            'html_body'
        );
        foreach ($fields as $k)
        {
            $v = str_replace(array('<', '>'), array('&lt;', '&gt;'), $mail->$k);
            echo "mail->{$k}: ";
            if (strpos($v, "\n"))
            {
                echo "\n<pre>\n{$v}\n</pre><br/>\n";
            }
            else
            {
                echo "<tt>{$v}</tt><br/>\n";
            }
        }
        echo "mail->headers (trough htmlentities()):\n<pre>\n";
        echo htmlentities(sprint_r($mail->headers));
        echo "</pre>\n";
        echo "List of attachments\n<ol>\n";
        foreach($mail->attachments as $data)
        {
            $len = strlen($data['content']);
            echo "<li><tt>{$data['name']}</tt> is of type <tt>{$data['mimetype']}</tt> and {$len} bytes in size</li>\n";
        }
        echo "</ol>\n";

        echo "Full decoded mail object (trough htmlentities()):\n<pre>\n";
        echo htmlentities(sprint_r($mail));
        echo "</pre>\n";
    }
}

echo "</p>\n";
?>