<?php
/**
 * @package midgard.admin.wizards
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

$vhost_config = "
<VirtualHost *:__HOST_PORT__>
ServerName __SERVER_NAME__
ServerAlias __SERVER_ALIAS__
DocumentRoot __DOC_ROOT__

<Directory __DOC_ROOT__>
        Options +SymLinksIfOwnerMatch
        Allow from all
</Directory>

MidgardBlobDir /var/lib/midgard/blobs/midgard

<Directory /var/lib/midgard/blobs/midgard>
        Allow from all
</Directory>

MidgardEngine on

# You may uncomment this line and comment next two to disable caching facility.
#MidgardRootfile /usr/lib/apache2/modules/midgard-root-nommp.php
MidgardPageCacheDir /var/cache/midgard/midgard
MidgardRootFile __MIDGARD_ROOT__

<Directory /var/cache/midgard>
        Allow from all
</Directory>

MidgardDefaultRealm \"Midgard\"
MidgardDatabase __MYSQL_DB_USER_PASS__
RLimitCPU 20 60
RLimitMem 67108864 134217728

<Directory /usr/share/php>
        Allow from all
</Directory>

# Global virtual host level PHP settings for all Midgard applications
#
# Required for uploading attachments
php_admin_flag file_uploads On
# Required by majority of the Midgard PHP applications
php_flag magic_quotes_gpc Off
# Required by MidCOM <= 1.4 (comment this out with newer releases)
#php_flag short_open_tag On
# Recommended at least for MidCOM AIS (20M is the minimum requirement)
# (change and enable if you want to set different value than in php.ini)
# Required by midcom-template
php_flag register_globals On

# Location specific PHP settings for certain Midgard applications
#
<Location /spider-admin>
 php_flag register_globals On
</Location>

<Location /aegir>
 php_flag short_open_tag On
 php_flag register_globals On
</Location>

AddDefaultCharset utf-8

# Uncomment if you want to redirect all midcom-admin requests to
# secured host. Keep in mind that mod_rewrite module is mandatory
# and should be loaded in Apache configuration.
# More docs about configuration may be found at:
# http://www.midgard-project.org/midcom-permalink-ebfd755b5fc58087bc4f5771585c63eb
# --- SSL rewrite Start ---
#RewriteEngine On
#RewriteCond %{REQUEST_URI} !^/midcom-admin.*
#RewriteCond %{REQUEST_URI} !^/midcom-static.*
#RewriteCond %{REQUEST_URI} !\.(gif|jpg|css|ico)$
#RewriteRule /(.*)$ http://devel-xen-stable.nemein.net/

#RewriteEngine On
#RewriteCond %{REQUEST_URI} ^/midcom-admin.*
#RewriteRule /(.*)$ https://devel-xen-stable.nemein.net/
# --- SSL rewrite End ---

</VirtualHost>
";
?>