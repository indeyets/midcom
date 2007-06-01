<?php
$view = $data['message_view'];

$metadata = midcom_helper_metadata::retrieve($data['message']);
$author_user = $_MIDCOM->auth->get_user($metadata->get('author'));
$author = $author_user->get_storage();

$seconds_elapsed = time() - $metadata->get('published');
$days_elapsed = floor($seconds_elapsed / 60 / 60 / 24); 
$hours_elapsed = floor(($seconds_elapsed / 60 / 60) - $days_elapsed * 24);
$minutes_elapsed = floor(($seconds_elapsed / 60) - $hours_elapsed * 60);
?>
<li class="message hentry">
    <span class="author vcard">
        <span class="fn">&(author.name);</span>
    </span>:
    <span class="entry-content">
        &(view['status']:h);
    </span>
    <abbr class="published" title="<?php echo gmdate('Y-m-d\TH:i:s\Z', $metadata->get('published')); ?>">
        <?php
        if ($days_elapsed > 0)
        {
            echo "{$days_elapsed} days, {$hours_elapsed} hours and {$minutes_elapsed} minutes ago";
        }
        elseif ($hours_elapsed > 0)
        {
            echo "{$hours_elapsed} hours and {$minutes_elapsed} minutes ago";
        }
        else
        {
            echo "{$minutes_elapsed} minutes ago";
        }
        ?>
    </abbr>
</li>