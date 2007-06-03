                    </div>
                    <div id="object_metadata">
                        <?php
                        $view_metadata = $_MIDCOM->metadata->get_view_metadata();
                        if ($view_metadata)
                        {
                            $editor = new midcom_db_person($view_metadata->get('editor'));
                            $edited = $view_metadata->get('edited');
                            $creator = new midcom_db_person($view_metadata->get('creator'));
                            $created = $view_metadata->get('created');
                            echo sprintf($data['l10n']->get('created by %s on %s'), "<a href=\"{$_MIDGARD['self']}__mfa/asgard/object/view/{$editor->guid}/\">$creator->name</a>", strftime('%c', $created)) . "\n";
                            if ($edited != $created)
                            {
                                $revision = $view_metadata->get('revision');
                                echo sprintf($data['l10n']->get('last edited by %s on %s (revision %s)'), "<a href=\"{$_MIDGARD['self']}__mfa/asgard/object/view/{$editor->guid}/\">$editor->name</a>", strftime('%c', $edited), $revision) . "\n";
                            }
                        }
                        
                        if (   isset($data['object'])
                            && isset($data['object']->lang)
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
                            echo "<select class=\"language_chooser\" onchange=\"window.location='/__mfa/asgard/object/view/{$data['object']->guid}/' + this.options[this.selectedIndex].value;\">\n";
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
                <img src="<?php echo MIDCOM_STATIC_URL; ?>/Javascript_protoToolkit/images/midgard-logo.png" alt="M" /> 
                <strong>Asgard for Midgard <?php echo substr(mgd_version(), 0, 3); ?></strong>.
                Copyright &copy; 1998 - <?php echo date('Y'); ?> <a href="http://www.midgard-project.org/">The Midgard Project</a>.
                Midgard is a <a href="http://en.wikipedia.org/wiki/Free_software">free software</a> available under
                <a href="http://www.gnu.org/licenses/lgpl.html">GNU Lesser General Public License</a>.<br />
                devel-xen-devel.nemein.net: Apache/2.0.54 (Debian GNU/Linux) DAV/2 PHP/4.3.10-19 Midgard/1.8.3
            </span>
        </div>
    </body>
</html>
