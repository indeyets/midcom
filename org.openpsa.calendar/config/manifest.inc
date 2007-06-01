
  'name' => 'org.openpsa.calendar',
  'purecode' => false,
  'privileges' => 
  array (
    'read' => MIDCOM_PRIVILEGE_ALLOW,
    'reserve' => MIDCOM_PRIVILEGE_ALLOW,
  ),
  'class_definitions' => 
  array (
    0 => 'midcom_dba_classes.inc',
  ),
  'version' => '2.0.14beta1',
  'state' => 'beta',
  'package.xml' => 
  array (
    'summary' => 'Group calendar',
    'description' => 'Group calendar for workgroups.',
    'maintainers' => 
    array (
      'rambo' => 
      array (
        'name' => 'Eero af Heurlin',
        'email' => 'eero.afheurlin@iki.fi',
        'role' => 'lead',
      ),
      'bergie' => 
      array (
        'name' => 'Henri Bergius',
        'email' => 'henri.bergius@iki.fi',
        'role' => 'lead',
      ),
    ),
    'dependencies' => 
    array (
      'midcom.helper.datamanager' => 
      array (
      ),
      'org.openpsa.core' => 
      array (
      ),
      'org.openpsa.mail' => 
      array (
      ),
      'org.openpsa.helpers' => 
      array (
      ),
      'org.openpsa.calendarwidget' => 
      array (
      ),
      'org.openpsa.contacts' => 
      array (
      ),
      'org.openpsa.notifications' => 
      array (
      ),
      'net.nemein.wiki' => 
      array (
      ),
      'midcom' => 
      array (
      ),
    ),
  ),
  'customdata' => 
  array (
    'midcom.services.cron' => 
    array (
      0 => 
      array (
        'handler' => 'org_openpsa_calendar_cron_reporthours',
        'recurrence' => MIDCOM_CRON_HOUR,
      ),
    ),
  ),
