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
