                    <?php
                    $navigation = new midgard_admin_asgard_navigation($data['object'], &$data);
                    $navigation->draw();
                    ?>
                </div>
                <div id="content">
                    <div class="page-title">
                        <?php
                        echo "<h1>{$data['view_title']}</h1>\n";
                        ?>
                    </div>
                
                    <div id="breadcrumb">
                        <?php
                        $nap = new midcom_helper_nav();
                        echo $nap->get_breadcrumb_line(" &gt; ", null, 1);
                        ?>
                    </div>
                    <div id="toolbar">
                        <?php
                        echo $data['asgard_toolbar']->render();
                        ?>
                    </div>
                    <div id="content-text">