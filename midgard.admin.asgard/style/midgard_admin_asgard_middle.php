                    <?php
                    if (isset($data['object']))
                    {
                        $navigation = new midgard_admin_asgard_navigation($data['object'], &$data);
                    }
                    else
                    {
                        $navigation = new midgard_admin_asgard_navigation(null, &$data);
                    }
                    $navigation->draw();
                    ?>
                </div>
                <div id="content">
                    <div class="page-title">
                        <?php
                        echo "<h1>";
                        
                        if (   isset($data['object'])
                            && isset($data['object']->lang))
                        {
                            // FIXME: It would be better to reflect whether object is MultiLang
                            if (   $data['object']->lang == 0
                                && isset($data['language_code'])
                                && $data['language_code'] !== '')
                            {
                                echo "<span class=\"object_language\">" . $_MIDCOM->i18n->get_string('in fallback language', 'midgard.admin.asgard') . '</span>';
                            }
                            elseif ($data['object']->lang != 0)
                            {
                                $lang = new midcom_baseclasses_database_language($data['object']->lang);
                               echo "<span class=\"object_language\">" .  sprintf($_MIDCOM->i18n->get_string('in %s', 'midgard.admin.asgard'), $lang->name) . '</span>';
                            }
                        }
                        
                        echo "{$data['view_title']}</h1>\n";
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