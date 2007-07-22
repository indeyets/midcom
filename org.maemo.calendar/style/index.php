<?php
/**
 * This is the style-element I use to show the index
 * Use this to get variables etc from the handler:
 */
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<div class="application">
    
    <!-- Header start -->
<?php midcom_show_style("application_header"); ?>
    <!-- Header end -->

    <div class="container">
        <div id="calendar-loading">
            <img src="<?php echo MIDCOM_STATIC_URL;?>/org.maemo.calendar/images/indicator.gif" alt="" />
        </div>      
        <div class="calendar-modal-window">
            <div class="calendar-modal-window-content">
                <img src="<?php echo MIDCOM_STATIC_URL;?>/org.maemo.calendar/images/indicator.gif" alt="" /> Loading...
            </div>
        </div>
        <div class="container-helper">      
            <!-- Calendar start -->
            <div id="calendar-holder">      
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
$data['panel']->show();
?>
    <!-- Panel end -->
    
</div> <!-- Application end -->