<?php
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $_MIDCOM->i18n->get_current_language(); ?>" lang="<?php echo $_MIDCOM->i18n->get_current_language(); ?>">
    <head>
        <title><(title)> -  <?php echo $_MIDCOM->get_context_data(MIDCOM_CONTEXT_PAGETITLE); ?></title>
        <?php
        $_MIDCOM->print_head_elements();
        ?>
    </head>
    <body<?php $_MIDCOM->print_jsonload(); ?> class="style-editor">
        <div id="container">
            <div class="breadcrumb">
                <?php
                $nap = new midcom_helper_nav();
                echo $nap->get_breadcrumb_line();
                ?>
            </div>
