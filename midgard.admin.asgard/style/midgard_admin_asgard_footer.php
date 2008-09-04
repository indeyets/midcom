                    </div>
                    <div id="object_metadata">
                        <?php
                        if (isset($data['object']->guid))
                        {
                            echo "GUID: {$data['object']->guid}, ID: {$data['object']->id}.\n";
                        }
                        $view_metadata = $_MIDCOM->metadata->get_view_metadata();
                        if ($view_metadata)
                        {
                            $editor = new midcom_db_person($view_metadata->get('editor'));
                            $edited = $view_metadata->get('edited');
                            $creator = new midcom_db_person($view_metadata->get('creator'));
                            $created = $view_metadata->get('created');
                            
                            if (!is_int($created))
                            {
                                $created = strtotime($created);
                            }
                            
                            if (!is_int($edited))
                            {
                                $edited = strtotime($edited);
                            }
                            
                            echo sprintf($_MIDCOM->i18n->get_string('created by %s on %s', 'midgard.admin.asgard'), "<a href=\"{$_MIDGARD['self']}__mfa/asgard/object/view/{$creator->guid}/\">$creator->name</a>", strftime('%c', $created)) . "\n";
                            if ($edited != $created)
                            {
                                $revision = $view_metadata->get('revision');
                                echo sprintf($_MIDCOM->i18n->get_string('last edited by %s on %s (revision %s)', 'midgard.admin.asgard'), "<a href=\"{$_MIDGARD['self']}__mfa/asgard/object/view/{$editor->guid}/\">$editor->name</a>", strftime('%c', $edited), $revision) . "\n";
                            }
                        }

                        if (   isset($data['object'])
                            && property_exists($data['object'], 'lang')
                            && !is_a($data['object'], 'midgard_host'))
                        {
                            // FIXME: It would be better to reflect whether object is MultiLang
                            $object_langs = $data['object']->get_languages();
                            $object_lang_ids = array();
                            if (is_array($object_langs))
                            {
                                foreach ($object_langs as $object_lang)
                                {
                                    $object_lang_ids[] = $object_lang->id;
                                }
                            }

                            $lang_qb = midcom_baseclasses_database_language::new_query_builder();
                            $lang_qb->add_order('name');
                            $langs = $lang_qb->execute();
                            $default_mode = midgard_admin_asgard_plugin::get_default_mode(&$data);
                            
                            echo "<select class=\"language_chooser\" onchange=\"window.location='{$_MIDGARD['self']}__mfa/asgard/object/{$default_mode}/{$data['object']->guid}/' + this.options[this.selectedIndex].value;\">\n";
                            echo "    <option value=\"\">" . $_MIDCOM->i18n->get_string('default language', 'midgard.admin.asgard') . "</option>\n";
                            foreach ($langs as $lang)
                            {
                                $class_extra = '';
                                if (in_array($lang->id, $object_lang_ids))
                                {
                                    $class_extra = ' exists';
                                }

                                $selected = '';
                                if ($lang->code == $data['language_code'])
                                {
                                    $selected = ' selected="selected"';
                                }

                                echo "    <option value=\"{$lang->code}\" class=\"{$lang->code}{$class_extra}\"{$selected}>{$lang->name}</option>\n";
                            }
                            echo "</select>\n";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <div id="siteinfo">
            <span class="copyrights">
                <img src="<?php echo MIDCOM_STATIC_URL; ?>/midcom.services.toolbars/images/midgard-logo.png" alt="(M)" />
                <strong>Asgard for Midgard <?php echo substr(mgd_version(), 0, 4); ?></strong>.
                Copyright &copy; 1998 - <?php echo date('Y'); ?> <a href="http://www.midgard-project.org/">The Midgard Project</a>.
                Midgard is a <a href="http://en.wikipedia.org/wiki/Free_software">free software</a> available under
                <a href="http://www.gnu.org/licenses/lgpl.html">GNU Lesser General Public License</a>.<br />
                &(_SERVER['SERVER_NAME']);: &(_SERVER['SERVER_SOFTWARE']);
            </span>
        </div>
    </body>
</html>
