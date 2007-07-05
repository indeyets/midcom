/**
 * This is the configuration file for the module.
 * This is where you set default values for different configuration variables
 */
/* The sort order to use */
'sort_order' => 'score',

/* The schema database to use */
'schemadb' => 'file:/org/maemo/calendar/config/schemadb_default.inc',

/* The schema in the database to use to edit articles 
 * note the name here and in schemadb_default.inc
 * This is used when having created the complete crud component - not just the basic one. 
 * */
'schema' => 'default',

"default_view" => 3, // week

"week_slot_length" => 30, // minutes
"week_row_height" => 20, // see week_row_unit
"week_row_unit" => "px", // px=pixels, %=percent
"month_slot_length" => 60, // minutes
"day_slot_length"  => 60,  // minutes
"day_start_hour"   => 7,   // hour "working day" starts
"day_end_hour"     => 18,  // hour "working day" ends

"month_column_width" => 14,
