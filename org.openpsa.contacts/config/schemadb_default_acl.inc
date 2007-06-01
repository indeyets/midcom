'default' => array (
    'name'        => 'default',
    'description' => 'Permissions',
    'fields'      => array (
        // Metadata
        'calendar' => array (
            'description' => 'enable calendar',
            'datatype'    => 'privilege',
            'privilege'   => 'midgard:create',
            'start_fieldgroup' => array (
                'title' => 'Application modules',
                'css_group' => 'area',
            ),
            /* Set the calendar root event and the assignee prior to initializing DM */
        ),

        'projects' => array (
            'description' => 'enable project creation',
            'datatype'    => 'privilege',
            'privilege'   => 'midgard:create',
            'privilege_classname' => 'org_openpsa_projects_project',
        ),

        'contact_creation' => array (
            'description' => 'enable contact creation',
            'datatype'    => 'privilege',
            'privilege'   => 'midgard:create',
            'privilege_classname' => 'org_openpsa_contacts_person',
            /* Set the contacts root group and assignee prior to initializing */
        ),

        'contact_editing' => array (
            'description' => 'enable editing contacts created by others',
            'datatype'    => 'privilege',
            'privilege'   => 'midgard:update',
            'privilege_classname' => 'org_openpsa_contacts_person',
            /* Set the contacts root group and assignee prior to initializing */
        ),

        'organization_creation' => array (
            'description' => 'enable organization creation',
            'datatype'    => 'privilege',
            'privilege'   => 'midgard:create',
            'privilege_classname' => 'org_openpsa_contacts_group',
        ),

        'organization_editing' => array (
            'description' => 'enable editing organizations created by others',
            'datatype'    => 'privilege',
            'privilege'   => 'midgard:update',
            'privilege_classname' => 'org_openpsa_contacts_group',
        ),

        'invoices_creation' => array (
            'description' => 'enable invoice creation',
            'datatype'    => 'privilege',
            'privilege'   => 'midgard:create',
            'privilege_classname' => 'org_openpsa_invoices_invoice',
        ),

        'invoices_editing' => array (
            'description' => 'enable editing invoices created by others',
            'datatype'    => 'privilege',
            'privilege'   => 'midgard:update',
            'privilege_classname' => 'org_openpsa_invoices_invoice',
        ),

        'campaigns_creation' => array (
            'description' => 'enable campaign creation',
            'datatype'    => 'privilege',
            'privilege'   => 'midgard:create',
            'privilege_classname' => 'org_openpsa_directmarketing_campaign',
        ),

        'campaigns_editing' => array (
            'description' => 'enable editing campaigns created by others',
            'datatype'    => 'privilege',
            'privilege'   => 'midgard:update',
            'privilege_classname' => 'org_openpsa_directmarketing_campaign',
            'end_fieldgroup' => '',
        ),

        'salesproject_creation' => array (
            'description' => 'enable salesproject creation',
            'datatype'    => 'privilege',
            'privilege'   => 'midgard:create',
            'privilege_classname' => 'org_openpsa_sales_salesproject',
        ),
    )
)