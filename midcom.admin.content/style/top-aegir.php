<? $prefix = $_MIDGARD['prefix']; ?>    
    <div id="ais_top_menu">
            <ul id="ais_top_menu_list">
                <li><a href="#">Aegir</a>
                <ul><!-- todo: add a logoutlink! -->
                    <li><a href="#">logout</a></li>
                    <li><a href="<?php echo $GLOBALS['REQUEST_URI']; ?>?midcom_admin_content_aegir=0" >Turn of Aegir</a></li>
                 </ul>
                </li>
                <li><a href="#">Portal</a>
                <ul>
                    <li><a href="#">view</a></li>
                    <li><a href="#">prefs</a></li>
                    <li><a href="#">portal</a></li>
                </ul>
                </li>
                <li><a href="#">Help</a></li>
            </ul>
    </div>
    <div id="ais_location_bar">
            <?php
             midcom_show_style('location');
            ?>
    </div>
