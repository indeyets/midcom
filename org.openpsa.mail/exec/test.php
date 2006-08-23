<?php
$_MIDCOM->auth->require_valid_user();

echo "<p>\n";


if (   !isset($_POST['to'])
    || empty($_POST['to'])
    || !isset($_POST['from'])
    && empty($_POST['from']))
{
?>
    <h2>Send test email</h2>
    <form method="post">
        <p>
            From: <input name="from" size=50 type="text" value="example@example.com"/><br/>
            To: <input name="to" size=50 type="text" value="test@nemein.com" /><br/>
            Subject: <input name="subject" size=50 type="text" value="Testing o.o.mail with special chars (ÄäÖöÅå€)"/><br/>
            Message:<br/>
            <textarea rows=40 cols=80 name="body">Test body with special chars (Ää Öö Åå €)
            
/Test person</textarea><br/>
            <input type="submit" value="Send" />
        </p>
    </form>
<?php
}
else
{
    $mail = new org_openpsa_mail();
    $mail->subject = $_POST['subject'];
    $mail->body = $_POST['body'];
    $mail->to = $_POST['to'];
    $mail->from = $_POST['from'];
    $ret = $mail->send();
    
    echo "mail->send returned {$ret}<br>\n";
}

echo "</p>\n";
?>