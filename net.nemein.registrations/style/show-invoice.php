<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
/**
 * NOTE: You *must* override this element to get any sensible use out of it,
 * This is just a crude example on what you could do.
 *
 * See net_nemein_registrations_registration_dba::populate_compose_data for
 * some more handily ready-populated DM2 instances etc.
 */
echo "<?xml version=\"1.0\"?>\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
    <head>
        <title><(title)> - <?php echo $_MIDCOM->get_context_data(MIDCOM_CONTEXT_PAGETITLE); ?></title>
        <?php
        $_MIDCOM->print_head_elements();
        ?>
    </head>
    <body class="<?php echo $_MIDCOM->metadata->get_page_class(); ?>"<?php $_MIDCOM->print_jsonload(); ?>>
        <h1>This element must be overridden</h1>
        <?php
        if ($data['registration']->price <> 0)
        {
            // You want to pass the amount through a formatter since raw floats/doubles may look funky
            echo "        <h2>Amount: ". sprintf('%0.2f', $data['registration']->price) . "&euro;</h2>\n";
            echo "        <p>Reference: {$data['registration']->reference}</p>\n";
        }
        ?>
        <h2>Registrar info</h2>
        <?php $data['registrar_dm']->display_view(); ?>
        <h2>Additional info</h2>
        <?php $data['registration_dm']->display_view(); ?>
    </body>
</html>