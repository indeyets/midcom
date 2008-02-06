<?php
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/xhtml; charset=utf-8" />
    <title><?php echo $_MIDCOM->get_context_data(MIDCOM_CONTEXT_PAGETITLE); ?></title>

    <?php echo $_MIDCOM->print_head_elements(); ?>

</head>

<body<?php echo $_MIDCOM->print_jsonload(); ?>>

    <div class="application">

        <!-- Header start -->
    <?php midcom_show_style("application-header"); ?>
        <!-- Header end -->

        <div class="container">
            <div id="calendar-loading" style="display: block;">
                <img src="<?php echo MIDCOM_STATIC_URL;?>/org.maemo.calendar/images/ajax-loading-big.gif" alt="" />
            </div>
            <div class="calendar-modal-window">
                <div class="calendar-modal-window-content">
                    <img src="<?php echo MIDCOM_STATIC_URL;?>/org.maemo.calendar/images/indicator.gif" alt="" /> Loading...
                </div>
            </div>
            <div class="container-helper">      
                <!-- Calendar start -->
                <div id="calendar-holder" style="display: none;">      
                <?php
                $data['maemo_calender']->show();
                ?>  
                </div>
                <!-- Calendar end -->
                <div class="event-toolbar-holder">
                </div>
            </div>      
        </div>

        <!-- Panel start -->
    <?php
    $data['panel']->show(true);
    ?>
        <!-- Panel end -->

    </div> <!-- Application end -->

<?php   
if ($_MIDCOM->auth->admin)
{
    $_MIDCOM->toolbars->show();
}
$_MIDCOM->uimessages->show();
?>

</body>
</html>