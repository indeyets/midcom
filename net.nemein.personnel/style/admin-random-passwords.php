<?php
$n = 10;
$length = 8;
$non_alphas = false;
$max_amount = 100;
$max_length = 16;

$similars = array
(
    'I', 'l', '1', '0', 'O',
);

if (isset($_GET['f_submit']))
{
    $strong = false;
    $no_similars = false;
}
else
{
    $strong = true;
    $no_similars = true;
}

extract($_GET);


echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $_MIDCOM->i18n->get_content_language(); ?>" lang="<?php echo $_MIDCOM->i18n->get_content_language(); ?>">
    <head>
        <title><?php echo $data['l10n']->get('passwords'); ?> | <?php echo sprintf($data['l10n']->get('%s passwords'), $n); ?></title>
        <link rel="stylesheet" type="text/css" href="<?php echo MIDCOM_STATIC_URL; ?>/net.nemein.personnel/passwd.css" media="all" />
    </head>
    <body id="net_nemein_personnel_user_account">
        <form method="get" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
            <label for="amount">
                <span class="label"><?php echo $data['l10n']->get('amount'); ?></span> <input type="text" name="n" id="amount" value="<?php echo $n; ?>" size="2"  maxlength="4" /> (<?php echo sprintf($data['l10n']->get('maximum %s'), $max_amount); ?>)
            </label>
            <label for="length">
                <span class="label"><?php echo $data['l10n']->get('password length'); ?></span> <input type="text" name="length" id="length" value="<?php echo $length; ?>" size="2" maxlength="2" /> (<?php echo sprintf($data['l10n']->get('maximum %s'), $max_length); ?>)
            </label>
            <label for="repeated_characters">
                <input type="checkbox" id="repeated_characters" name="strong" value="1" <?php if ($strong) { echo ' checked="checked"'; } ?> />
                <span class="label"><?php echo $data['l10n']->get('prevent repeating characters'); ?></span>
            </label>
            <label for="similar_characters">
                <input type="checkbox" id="similar_characters" name="no_similars" value="1" <?php if ($no_similars) { echo ' checked="checked"'; } ?> />
                <span class="label"><?php echo $data['l10n']->get('prevent similar characters'); ?> (<em><?php echo implode(', ', $similars); ?></em>)</span>
            </label>
            <input type="submit" name="f_submit" value="<?php echo $data['l10n']->get('generate'); ?>" />
        </form>
<?php
if (   !is_numeric($n)
    || $n <= 0
    || !is_numeric($length)
    || $length <= 0)
{
    echo $data['l10n']->get('use positive numeric values');
}
elseif ((int) $n > $max_amount
    || (int) $length > $max_length)
{
    echo $data['l10n']->get('only up to 1000 passwords with maximum length of 16 characters');
}
else
{
?>
        <pre>
<?php

for ($i = 0; $i < $n; $i++)
{
    $string = '';
    for ($x = 0; $x < $length; $x++)
    {
        $rand = (int) rand(48, 122);
        $char = chr($rand);
        
        $k = 0;
        
        while (   !ereg('[a-zA-Z0-9]', $char)
               || (   $strong
                   && strlen($string) > 0
                   && strstr($string, $char))
               || (   $no_similars
                   && in_array($char, $similars)))
        {
            $rand = (int) rand(48, 122);
            $char = chr($rand);
            
            $k++;
//            echo "[{$k}:{$char}]";
        }
        
        echo $char;
//        echo " ({$rand}) ";
        $string .= $char;
    }
    echo "\n";
}

?>
        </pre>
<?php
}
?>
    </body>
</html>