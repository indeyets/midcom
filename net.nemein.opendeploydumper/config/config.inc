'attachment_rewrite' => '/portal_att_root/%1s_%2s', // sprint_r definition for attachment url rewriting, %1s is an applicable guid, %2s is the filename
'link_prefix' => null, // Rewrite normal links to use this as prefix (PONDER: Do we need rewrite capability like above ??)
'metadata_domain' => 'com.interwoven.opendeploy', // All name/value pairs in this parameter domain are dumped to the metadata file
'dump_directory' => '/var/spool/opendeploy', // Where to dump the files to.
'check_approves' => false, // Dump only approved objects (scheduling is handled separately btw)
'metadata_suffix' => '.md.properties',
'time_format' => '%D %I:%M %p', // Passed to stftime when dumping timestamp to properties file
