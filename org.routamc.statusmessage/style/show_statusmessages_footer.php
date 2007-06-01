</ul>
<?php
if (   isset($data['qb'])
    && is_object($data['qb'])
    && method_exists($data['qb'], 'show_pages'))
{
    $data['qb']->show_pages();
}
?>
</div>