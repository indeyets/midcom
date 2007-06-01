/** 
 * What currency this OpenPSA uses internally, might be needed 
 * when copying data between different systems
 * Uses the three-letter ISO 4217 currency code
 */
'currency'  => 'EUR',
/**
 * Currency symbol for display
 * if NULL, defaults to the ISO 4217 currency code
 */
'currency_symbol' => 'Û',
/**
 * Task/Project Datamanager schema snippet
 */
'schemadb_task' => 'file:/org/openpsa/projects/config/schemadb_default_task.inc',
'schemadb_task_dm2' => 'file:/org/openpsa/projects/config/schemadb_default_task_dm2.inc',
'schemadb_project' => 'file:/org/openpsa/projects/config/schemadb_default_project.inc',
'schemadb_project_dm2' => 'file:/org/openpsa/projects/config/schemadb_default_project_dm2.inc',
/**
 * Hour Reporting Datamanager schema snippet
 */
'schemadb_hours' => 'file:/org/openpsa/projects/config/schemadb_default_hours.inc',
'schemadb_hours_dm2' => 'file:/org/openpsa/projects/config/schemadb_default_hours_dm2.inc',
/**
 * Project forum and news configuration
 */
'enable_project_news' => false,
'enable_project_forum' => false,
'list_projects_by_status' => true,