<?php
$n = 10;
$length = 8;
$non_alphas = false;
$max_amount = 100;
$max_length = 16;

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

if (!isset($_GET['ajax-form']))
{
?>
        <form method="get" action="<?php echo $_MIDGARD['uri']; ?>" id="midcom_admin_user_generated_passwords_form">
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
                <span class="label"><?php echo $data['l10n']->get('prevent similar characters'); ?> (<em>I, l, 1, 0, O</em>)</span>
            </label>
            <input type="submit" name="f_submit" value="<?php echo $data['l10n']->get('generate'); ?>" />
<?php
    if (isset($_GET['ajax']))
    {
        $time = time();
        echo "    <input type=\"hidden\" name=\"timestamp\" value=\"{$time}\" />\n";
        echo "    <input type=\"hidden\" name=\"ajax\" value=\"true\" />\n";
        echo "    <input type=\"hidden\" name=\"ajax-form\" value=\"false\" />\n";
    }
?>
        </form>
        <pre id="midcom_admin_user_generated_passwords_random">
<?php
}

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
    for ($i = 0; $i < $n; $i++)
    {
        echo midcom_admin_user_plugin::generate_password($length, $no_similars, $strong);
        echo "\n";
    }
    
}

if (!isset($_GET['ajax-form']))
{
    echo "</pre>\n";
    
    if (isset($_GET['ajax']))
    {
?>
<script type="text/javascript">
    // <![CDATA[
        $j('#midcom_admin_user_generated_passwords_form').submit(function()
        {
            $j('#midcom_admin_user_generated_passwords_form').ajaxSubmit
            (
                {
                    target : $j('#midcom_admin_user_generated_passwords_random')
                }
            );
            return false;
        });
    // ]]>
</script>
<?php
    }
}
?>