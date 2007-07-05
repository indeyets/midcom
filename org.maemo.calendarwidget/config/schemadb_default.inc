// 2DO: Add l10n strings

/* This is an example article, taken from the de.linkm.taviewer default
 * schema.
 *
 * When overriding this schema database with component specific data using
 * a snippet that is usually put into the snippetdir 
 * /sitegroup-config/org.maemo.calendarwidget/
 * and configured using the configuration key "schemadb" all fields that are
 * within the component required section must be copied verbatim (you may
 * change the description field though).
 */

'default' => array (
    'name'        => 'default',
    'description' => 'example article',
    'fields'      => array (
    
    	/* Start of component required fields */
        
        'title' => array (
            'description' => 'title',
            'datatype'    => 'text',
            'location'    => 'title', 
            'required' => true,
        ),
        
        /* End of component required fields */
        
        'content' => array (
            'description' => 'text',
            'datatype'    => 'text',
            'location'    => 'content',
            'widget'      => 'html'
        ),
        
        'image' => array (
            'description' => 'image',
            'datatype'    => 'image',
            'widget_image_simple' => true,
        )
    )
), // default

