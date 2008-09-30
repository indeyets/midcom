<?php
/**
 * @package org.openpsa.directmarketing
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: import.php,v 1.4 2006/06/19 09:39:42 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * org.openpsa.directmarketing campaign handler and viewer class.
 * @package org.openpsa.directmarketing
 */
class org_openpsa_directmarketing_handler_import extends midcom_baseclasses_components_handler
{
    /**
     * The schema databases used for importing to various objects like persons and organizations
     *
     * @var Array
     * @access private
     */
    var $_schemadbs = array();

    /**
     * Datamanagers used for saving various objects like persons and organizations
     *
     * @var Array
     * @access private
     */
    var $_datamanagers = array();

    /**
     * Array containing list of all fields used in the schema, indexed by schema
     *
     * @var Array
     * @access private
     */
    var $_merged_fields = array();

    function __construct()
    {
        parent::__construct();
    }

    function _prepare_handler($args)
    {
        // Mass importing is for now better left for admins only
        // TODO: Add smarter per-type ACL checks
        $_MIDCOM->auth->require_admin_user();

        // Try to load the correct campaign
        $this->_request_data['campaign'] = new org_openpsa_directmarketing_campaign($args[0]);
        if (!$this->_request_data['campaign'])
        {
            return false;
        }

        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "campaign/{$this->_request_data['campaign']->guid}/",
                MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n_midcom']->get("back"),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_left.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            )
        );

        $_MIDCOM->bind_view_to_object($this->_request_data['campaign']);

        $this->_load_schemas();

        $this->_request_data['import_status'] = array
        (
            'already_subscribed' => 0,
            'subscribed_new' => 0,
            'subscribed_existing' => 0,
            'failed_create' => 0,
            'failed_add' => 0,
        );

        //Disable limits
        // TODO: Could this be done more safely somehow
        @ini_set('memory_limit', -1);
        @ini_set('max_execution_time', 0);
    }

    /**
     * This function prepares the schemadb containing merged schema for the nullstorage controller.
     *
     * @access private
     */
    function _load_schemas()
    {
        // We try to combine these schemas to provide a single centralized controller
        $this->_schemadbs['campaign_member'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_campaign_member'));
        if (!$this->_schemadbs['campaign_member'])
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Could not load campaign member schema database.');
            // This will exit.
        }
        $this->_schemadbs['person'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_person'));
        if (!$this->_schemadbs['person'])
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Could not load person schema database.');
            // This will exit.
        }
        $this->_schemadbs['organization_member'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_organization_member'));
        if (!$this->_schemadbs['organization_member'])
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Could not load organization member schema database.');
            // This will exit.
        }
        $this->_schemadbs['organization'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_organization'));
        if (!$this->_schemadbs['organization'])
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Could not load organization schema database.');
            // This will exit.
        }

        // Generate the merged schema
        $this->_merged_fields['all'] = array();
        foreach ($this->_schemadbs as $schemadb_type => $schemadb)
        {
            if (!array_key_exists($schemadb_type, $this->_merged_fields))
            {
                $this->_merged_fields[$schemadb_type] = array();
            }

            foreach ($schemadb['default']->fields as $name => $field)
            {
                // Check for dupes, all field names must be unique in merged schema
                if (array_key_exists($name, $this->_merged_fields['all']))
                {
                    $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                        "Duplicate field name '{$name}' found in schema database '{$schemadb_type}', cannot compute merged set. Aborting.");
                    // This will exit.
                }

                // Add the field definition here
                $this->_merged_fields['all'][$name] = true;
                $this->_merged_fields[$schemadb_type][$name] = $field;
            }
        }
    }

    /** 
     * Load the datamanagers for different types
     */
    function _load_datamanagers()
    {
        $this->_datamanagers['campaign_member'] = new midcom_helper_datamanager2_datamanager($this->_schemadbs['campaign_member']);
        if (!$this->_datamanagers['campaign_member'])
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for campaign members.");
            // This will exit.
        }
        $this->_datamanagers['person'] = new midcom_helper_datamanager2_datamanager($this->_schemadbs['person']);
        if (!$this->_datamanagers['person'])
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for persons.");
            // This will exit.
        }
        $this->_datamanagers['organization_member'] = new midcom_helper_datamanager2_datamanager($this->_schemadbs['organization_member']);
        if (!$this->_datamanagers['organization_member'])
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for organization members.");
            // This will exit.
        }
        $this->_datamanagers['organization'] = new midcom_helper_datamanager2_datamanager($this->_schemadbs['organization']);
        if (!$this->_datamanagers['organization'])
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for organizations.");
            // This will exit.
        }
    }

    /**
     * Process the datamanager
     * 
     * @access private
     * @param String $type        Subscription type
     * @param array $subscriber   
     * @param mixed $object       
     * @return boolean            Indicating success
     */
    function _datamanager_process($type, $subscriber, $object)
    {
        if (   !array_key_exists($type, $subscriber)
            || count($subscriber[$type]) == 0)
        {
            // No fields for this type, skip DM phase
            return true;
        }

        // Load datamanager2 for the object
        if (!$this->_datamanagers[$type]->autoset_storage($object))
        {
            return false;
        }

        // Set all given values into DM2
        foreach ($subscriber[$type] as $key => $value)
        {
            if (array_key_exists($key, $this->_datamanagers[$type]->types))
            {
                $this->_datamanagers[$type]->types[$key]->value = $value;
            }
        }

        // Save the object
        if (!$this->_datamanagers[$type]->save())
        {
            return false;
        }

        return true;
    }

    /**
     * Clean the new objects
     * 
     * @access private
     */
    function _clean_new_objects()
    {
        foreach ($this->_request_data['new_objects'] as $key => $object)
        {
            $object->delete();
        }
    }

    function _import_subscribers_person($subscriber)
    {
        $person = null;
        if (   array_key_exists('email', $subscriber['person'])
            && $subscriber['person']['email'])
        {
            // Perform a simple email test. More complicated duplicate checking is best left to the o.o.contacts duplicate checker
            $qb = org_openpsa_contacts_person::new_query_builder();
            $qb->add_constraint('email', '=', $subscriber['person']['email']);
            $persons = $qb->execute_unchecked();
            if (count($persons) > 0)
            {
                // Match found, use it
                $person = $persons[0];
            }
        }

        if (   !$person
            && array_key_exists('handphone', $subscriber['person'])
            && $subscriber['person']['handphone'])
        {
            // Perform a simple cell phone test. More complicated duplicate checking is best left to the o.o.contacts duplicate checker
            $qb = org_openpsa_contacts_person::new_query_builder();
            $qb->add_constraint('handphone', '=', $subscriber['person']['handphone']);
            $persons = $qb->execute_unchecked();
            if (count($persons) > 0)
            {
                // Match found, use it
                $person = $persons[0];
            }
        }

        if (!$person)
        {
            // We didn't have person matching the email in DB. Create a new one.
            $person = new org_openpsa_contacts_person();
            if (!$person->create())
            {
                $this->_request_data['new_objects']['person'] =& $person;
                debug_add("Failed to create person, reason " . mgd_errstr());
                $this->_request_data['import_status']['failed_create']++;
                return false;
                // This will skip to next
            }
        }

        if (!$this->_datamanager_process('person', $subscriber, $person))
        {
            return false;
        }

        return $person;
    }

    function _import_subscribers_campaign_member($subscriber, $person)
    {
        // Check if person is already in campaign
        $member = null;
        $qb = org_openpsa_directmarketing_campaign_member::new_query_builder();
        $qb->add_constraint('person', '=', $person->id);
        $qb->add_constraint('campaign', '=', $this->_request_data['campaign']->id);
        $qb->add_constraint('orgOpenpsaObtype', '<>', ORG_OPENPSA_OBTYPE_CAMPAIGN_TESTER);
        $members = $qb->execute_unchecked();
        if (count($members) > 0)
        {
            // User is or has been subscriber earlier, update status
            $member = $members[0];

            if ($member->orgOpenpsaObtype == ORG_OPENPSA_OBTYPE_CAMPAIGN_MEMBER)
            {
                $this->_request_data['import_status']['already_subscribed']++;
            }
            else
            {
                $member->orgOpenpsaObtype = ORG_OPENPSA_OBTYPE_CAMPAIGN_MEMBER;
                if ($member->update())
                {
                    if (array_key_exists('person', $this->_request_data['new_objects']))
                    {
                        $this->_request_data['import_status']['subscribed_new']++;
                    }
                    else
                    {
                        $this->_request_data['import_status']['subscribed_existing']++;
                    }
                }
                else
                {
                    $this->_request_data['import_status']['failed_add']++;
                    return false;
                }
            }
        }

        if (!$member)
        {
            // Not a subscribed member yet, add
            $member = new org_openpsa_directmarketing_campaign_member();
            $member->person = $person->id;
            $member->campaign = $this->_request_data['campaign']->id;
            $member->orgOpenpsaObtype = ORG_OPENPSA_OBTYPE_CAMPAIGN_MEMBER;
            if (!$member->create())
            {
                $this->_request_data['import_status']['failed_add']++;
                return false;
            }
            $this->_request_data['new_objects']['campaign_member'] =& $member;
            $this->_request_data['import_status']['subscribed_new']++;
        }

        if (!$this->_datamanager_process('campaign_member', $subscriber, $person))
        {
            // Failed to handle campaign member via DM
            return false;
        }

        return $member;
    }

    function _import_subscribers_organization($subscriber)
    {
        $organization = null;
        if (   array_key_exists('official', $subscriber['organization'])
            && $subscriber['organization']['official'])
        {
            // Perform a simple check for existing organization. More complicated duplicate checking is best left to the o.o.contacts duplicate checker
            $GLOBALS['org_openpsa_contacts_group_autoload_members'] = false;
            $qb = org_openpsa_contacts_group::new_query_builder();

            if (   array_key_exists('company_id', $this->_schemadbs['organization']['default']->fields)
                && array_key_exists('company_id', $subscriber['organization'])
                && $subscriber['organization']['company_id'])
            {
                // Imported data has a company id, we use that instead of name
                $qb->add_constraint($this->_schemadbs['organization']['default']->fields['company_id']['storage']['location'], '=', $subscriber['organization']['company_id']);
            }
            else
            {
                // Seek by official name
                $qb->add_constraint('official', '=', $subscriber['organization']['official']);

                if (   array_key_exists('city', $this->_schemadbs['organization']['default']->fields)
                    && array_key_exists('city', $subscriber['organization'])
                    && $subscriber['organization']['city'])
                {
                    // Imported data has a city, we use also that for matching
                    $qb->add_constraint($this->_schemadbs['organization']['default']->fields['city']['storage']['location'], '=', $subscriber['organization']['city']);
                }
            }

            $organizations = $qb->execute_unchecked();
            if (count($organizations) > 0)
            {
                // Match found, use it

                // Use first match
                $organization = array_shift($organizations);
            }
        }

        if (!$organization)
        {
            // We didn't have person matching the email in DB. Create a new one.
            $organization = new org_openpsa_contacts_group();
            if (!$organization->create())
            {
                $this->_request_data['new_objects']['organization'] =& $organization;
                debug_add("Failed to create organization, reason " . mgd_errstr());
                return null;
            }
        }

        if (!$this->_datamanager_process('organization', $subscriber, $organization))
        {
            return null;
        }

        return $organization;
    }

    function _import_subscribers_organization_member($subscriber, $person, $organization)
    {
        // Check if person is already in organization
        $member = null;
        $qb = midcom_db_member::new_query_builder();
        $qb->add_constraint('uid', '=', $person->id);
        $qb->add_constraint('gid', '=', $organization->id);
        $members = $qb->execute_unchecked();
        if (count($members) > 0)
        {
            // Match found, use it

            // Use first match
            $member = $members[0];
        }

        if (!$member)
        {
            // We didn't have person matching the email in DB. Create a new one.
            $member = new midcom_db_member();
            $member->uid = $person->id;
            $member->gid = $organization->id;
            if (!$member->create())
            {
                $this->_request_data['new_objects']['organization_member'] =& $member;
                debug_add("Failed to create organization member, reason " . mgd_errstr());
                return false;
            }
        }

        if (!$this->_datamanager_process('organization_member', $subscriber, $member))
        {
            return false;
        }

        return $member;
    }

    /**
     * Takes an array of new subscribers and processes each of them using datamanager2.
     */
    function _import_subscribers($subscribers)
    {
        if (!is_array($subscribers))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to read proper list of users to import');
            // This will exit
        }

        $this->_load_datamanagers();

        foreach ($subscribers as $subscriber)
        {
            // Submethods will register any objects they create to this array so we can clean them up as needed
            $this->_request_data['new_objects'] = array();

            // Create or update person
            $person = $this->_import_subscribers_person($subscriber);
            if (!$person)
            {
                // Clean up possible created data
                $this->_clean_new_objects();

                // Skip to next
                continue;
            }

            // Create or update membership
            $campaign_member = $this->_import_subscribers_campaign_member($subscriber, $person);
            if (!$campaign_member)
            {
                // Clean up possible created data
                $this->_clean_new_objects();

                // Skip to next
                continue;
            }

            if (   array_key_exists('organization', $subscriber)
                && count($subscriber['organization']) > 0)
            {
                // Create or update organization
                $organization = $this->_import_subscribers_organization($subscriber);
                if (is_null($organization))
                {
                    // Clean up possible created data
                    $this->_clean_new_objects();

                    // Skip to next
                    continue;
                }

                // Create or update organization member
                $organization_member = $this->_import_subscribers_organization_member($subscriber, $person, $organization);
                if (!$organization_member)
                {
                    // Clean up possible created data
                    $this->_clean_new_objects();

                    // Skip to next
                    continue;
                }
            }

            // All done, import the next one
            debug_add("Person $person->name (#{$person->id}) all processed");
        }
    }

    /**
     * Update the breadcrumb line
     * 
     * @access private
     * @param String $handler_id
     * @param array $args
     */
    function _update_breadcrumb($handler_id, $args)
    {
        $tmp = array();
        $tmp[] = array
        (
            MIDCOM_NAV_URL => "campaign/{$args[0]}/",
            MIDCOM_NAV_NAME => $this->_request_data['campaign']->title,
        );
        $tmp[] = array
        (
            MIDCOM_NAV_URL => "campaign/import/{$args[0]}/",
            MIDCOM_NAV_NAME => $this->_l10n->get('import subscribers'),
        );
        
        switch ($handler_id)
        {
            case 'import_simpleemails':
                $tmp[] = array
                (
                    MIDCOM_NAV_URL => "campaign/import/simpleemails/{$args[0]}/",
                    MIDCOM_NAV_NAME => $this->_l10n->get('email addresses'),
                );
                break;
            
            case 'import_vcards':
                $tmp[] = array
                (
                    MIDCOM_NAV_URL => "campaign/import/vcards/{$args[0]}/",
                    MIDCOM_NAV_NAME => $this->_l10n->get('vcards'),
                );
                break;
            
            case 'import_csv_file_select':
                $tmp[] = array
                (
                    MIDCOM_NAV_URL => "campaign/import/csv/{$args[0]}/",
                    MIDCOM_NAV_NAME => $this->_l10n->get('csv'),
                );
                break;
            
            case 'import_csv_field_select':
                $tmp[] = array
                (
                    MIDCOM_NAV_URL => "campaign/import/csv/{$args[0]}/",
                    MIDCOM_NAV_NAME => $this->_l10n->get('csv'),
                );
                break;
        }
        
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
          
    }
    
    /**
     * Phase for selecting the import type
     * 
     * @access public
     * @param String $handler_id    Name of the request handler
     * @param array $args           Variable arguments
     * @param array &$data          Public request data, passed by reference
     * @return boolean              Indicating success
     */
    function _handler_index($handler_id, $args, &$data)
    {
        $this->_prepare_handler($args);
        if (!$this->_request_data['campaign'])
        {
            return false;
        }

        // Update the breadcrumb line
        $this->_update_breadcrumb($handler_id, $args);

        return true;
    }

    /**
     * Show the selection list for import types
     * 
     * @access public
     * @param String $handler_id    Name of the request handler
     * @param array &$data          Public request data, passed by reference
     * @return boolean              Indicating success
     */
    function _show_index($handler_id, &$data)
    {
        midcom_show_style('show-import-index');
    }

    /**
     * Phase for importing simple email addresses
     * 
     * @access public
     * @param String $handler_id    Name of the request handler
     * @param array $args           Variable arguments
     * @param array &$data          Public request data, passed by reference
     * @return boolean              Indicating success
     */
    function _handler_simpleemails($handler_id, $args, &$data)
    {
        $this->_prepare_handler($args);
        if (!$this->_request_data['campaign'])
        {
            return false;
        }

        // Update the breadcrumb line
        $this->_update_breadcrumb($handler_id, $args);

        if (array_key_exists('org_openpsa_directmarketing_import_separator', $_POST))
        {
            $this->_request_data['time_start'] = time();

            $this->_request_data['contacts'] = array();

            switch ($_POST['org_openpsa_directmarketing_import_separator'])
            {
                case 'N':
                    $this->_request_data['separator'] = "\n";
                    break;
                case ';':
                    $this->_request_data['separator'] = ";";
                    break;
                case ',':
                default:
                    $this->_request_data['separator'] = ",";
                    break;
            }
            
            // Initialize the raw contact data
            $contacts_raw = '';
            
            if (is_uploaded_file($_FILES['org_openpsa_directmarketing_import_upload']['tmp_name']))
            {
                $contacts_raw = file_get_contents($_FILES['org_openpsa_directmarketing_import_upload']['tmp_name']);
            }
            
            if (isset($_POST['org_openpsa_directmarketing_import_textarea']))
            {
                $contacts_raw .= $_POST['org_openpsa_directmarketing_import_textarea'];
            }
            
            if ($contacts_raw)
            {
                // Make sure we only have NL linebreaks
                $contacts_raw = preg_replace("/\n\r|\r\n|\r/", "\n", $contacts_raw);
                $contacts = explode($this->_request_data['separator'], $contacts_raw);
                if (count($contacts) > 0)
                {
                    foreach ($contacts as $contact)
                    {
                        $contact = trim($contact);
                        
                        // Skip the empty lines already now
                        if (!$contact)
                        {
                            continue;
                        }
                        
                        $this->_request_data['contacts'][] = array
                        (
                            'person' => array
                            (
                                'email' => strtolower($contact),
                            )
                        );
                    }
                }
            }

            if (count($this->_request_data['contacts']) > 0)
            {
                $this->_import_subscribers($this->_request_data['contacts']);
            }

            $this->_request_data['time_end'] = time();
        }

        return true;
    }

    /**
     * Show the import phase of email addresses
     * 
     * @access public
     * @param String $handler_id    Name of the request handler
     * @param array &$data          Public request data, passed by reference
     */
    function _show_simpleemails($handler_id, &$data)
    {
        if (   $this->_request_data['import_status']['subscribed_new'] == 0
            && $this->_request_data['import_status']['subscribed_existing'] == 0)
        {
            midcom_show_style('show-import-simpleemails-form');
        }
        else
        {
            midcom_show_style('show-import-status');
        }
    }

    /**
     * Phase for importing vcards
     * 
     * @access public
     * @param String $handler_id    Name of the request handler
     * @param array $args           Variable arguments
     * @param array &$data          Public request data, passed by reference
     * @return boolean              Indicating success
     */
    function _handler_vcards($handler_id, $args, &$data)
    {
        $this->_prepare_handler($args);
        if (!$this->_request_data['campaign'])
        {
            return false;
        }

        // Update the breadcrumb line
        $this->_update_breadcrumb($handler_id, $args);

        if (array_key_exists('org_openpsa_directmarketing_import', $_POST))
        {
            $this->_request_data['contacts'] = array();

            $this->_request_data['time_start'] = time();

            if (is_uploaded_file($_FILES['org_openpsa_directmarketing_import_upload']['tmp_name']))
            {
                require_once 'Contact_Vcard_Parse.php';
                $parser = new Contact_Vcard_Parse();
                $cards = @$parser->fromFile($_FILES['org_openpsa_directmarketing_import_upload']['tmp_name']);

                if (count($cards) > 0)
                {
                    foreach ($cards as $card)
                    {
                        // Empty the person array before going through vCard data
                        $contact = array
                        (
                            'person'              => array(),
                            'organization'        => array(),
                            'organization_member' => array(),
                        );

                        // Start parsing
                        if (   array_key_exists('N', $card)
                            && array_key_exists('value', $card['N'][0])
                            && is_array($card['N'][0]['value']))
                        {
                            // FIXME: We should do something about character encodings
                            $contact['person']['lastname'] = $card['N'][0]['value'][0][0];
                            $contact['person']['firstname'] = $card['N'][0]['value'][1][0];
                        }

                        if (array_key_exists('TEL', $card))
                        {
                            foreach ($card['TEL'] as $number)
                            {
                                if (array_key_exists('param', $number))
                                {
                                    if (array_key_exists('TYPE', $number['param']))
                                    {
                                        switch ($number['param']['TYPE'][0])
                                        {
                                            case 'CELL':
                                                $contact['person']['handphone'] = $number['value'][0][0];
                                                break;
                                            case 'HOME':
                                                $contact['person']['homephone'] = $number['value'][0][0];
                                                break;
                                            case 'WORK':
                                                $contact['person']['workphone'] = $number['value'][0][0];
                                                break;
                                        }
                                    }
                                }
                            }
                        }

                        if (array_key_exists('ORG', $card))
                        {
                            $contact['organization']['official'] = $card['ORG'][0]['value'][0][0];
                        }

                        if (array_key_exists('TITLE', $card))
                        {
                            $contact['organization_member']['title'] = $card['TITLE'][0]['value'][0][0];
                        }

                        if (array_key_exists('EMAIL', $card))
                        {
                            $contact['person']['email'] = $card['EMAIL'][0]['value'][0][0];
                        }

                        if (array_key_exists('X-SKYPE-USERNAME', $card))
                        {
                            $contact['person']['skype'] = $card['X-SKYPE-USERNAME'][0]['value'][0][0];
                        }

                        if (array_key_exists('UID', $card))
                        {
                            $contact['person']['external-uid'] = $card['UID'][0]['value'][0][0];
                        }
                        elseif (array_key_exists('X-ABUID', $card))
                        {
                            $contact['person']['external-uid'] = $card['X-ABUID'][0]['value'][0][0];
                        }

                        //$contact['rawdata'] = $card;

                        if (count($contact['person']) > 0)
                        {
                            // We have parsed some contact info.

                            // Convert fields from latin-1 to MidCOM charset (usually utf-8)
                            foreach($contact as $type => $fields)
                            {
                                foreach ($fields as $key => $value)
                                {
                                    $contact[$type][$key] = iconv('ISO-8859-1', $_MIDCOM->i18n->get_current_charset(), $value);
                                }
                            }

                            // TODO: Make sanity checks before adding

                            $this->_request_data['contacts'][] = $contact;
                        }
                    }
                }
            }

            if (count($this->_request_data['contacts']) > 0)
            {
                $this->_import_subscribers($this->_request_data['contacts']);
            }

            $this->_request_data['time_end'] = time();
        }

        return true;
    }

    /**
     * Show the vcard import interface
     * 
     * @access public
     * @param String $handler_id    Name of the request handler
     * @param array &$data          Public request data, passed by reference
     */
    function _show_vcards($handler_id, &$data)
    {
        if (   $this->_request_data['import_status']['subscribed_new'] == 0
            && $this->_request_data['import_status']['subscribed_existing'] == 0)
        {
            midcom_show_style('show-import-vcard-form');
        }
        else
        {
            midcom_show_style('show-import-status');
        }
    }

    /**
     * Phase for importing CSV. This interface lets user to define what the fields of the CSV represent
     * 
     * @access public
     * @param String $handler_id    Name of the request handler
     * @param array $args           Variable arguments
     * @param array &$data          Public request data, passed by reference
     * @return boolean              Indicating success
     */
    function _handler_csv_select($handler_id, $args, &$data)
    {
        $this->_prepare_handler($args);
        if (!$data['campaign'])
        {
            return false;
        }

        // Update the breadcrumb
        $this->_update_breadcrumb($handler_id, $args);

        if (array_key_exists('org_openpsa_directmarketing_import_separator', $_POST))
        {
            $data['time_start'] = time();

            $data['rows'] = array();

            switch ($_POST['org_openpsa_directmarketing_import_separator'])
            {
                case ';':
                    $data['separator'] = ';';
                    break;

                case ',':
                default:
                    $data['separator'] = ',';
                    break;
            }


            if (is_uploaded_file($_FILES['org_openpsa_directmarketing_import_upload']['tmp_name']))
            {
                // Copy the file for later processing
                $data['tmp_file'] = tempnam($GLOBALS['midcom_config']['midcom_tempdir'], 'org_openpsa_directmarketing_import_csv');
                $src = fopen($_FILES['org_openpsa_directmarketing_import_upload']['tmp_name'], 'r');
                $dst = fopen($data['tmp_file'], 'w+');
                while (! feof($src))
                {
                    $buffer = fread($src, 131072); /* 128 kB */
                    fwrite($dst, $buffer, 131072);
                }
                fclose($src);
                fclose($dst);

                // Read cell headers from the file
                $read_rows = 0;
                $handle = fopen($_FILES['org_openpsa_directmarketing_import_upload']['tmp_name'], 'r');
                $separator = $data['separator'];
                $total_columns = 0;
                while (   $read_rows < 2
                       && $csv_line = fgetcsv($handle, 1000, $separator))
                {
                    if ($total_columns == 0)
                    {
                        $total_columns = count($csv_line);
                    }
                    $columns_with_content = 0;
                    foreach ($csv_line as $value)
                    {
                        if ($value != '')
                        {
                            $columns_with_content++;
                        }
                    }
                    $percentage = round(100 / $total_columns * $columns_with_content);

                    if ($percentage >= 20)
                    {
                        $data['rows'][] = $csv_line;
                        $read_rows++;
                    }
                }
            }

            $data['time_end'] = time();
        }

        return true;
    }

    /**
     * Show the CSV selection phase where user defines which field in CSV corresponds to which schema fields
     * 
     * @access public
     * @param String $handler_id    Name of the request handler
     * @param array $args           Variable arguments
     * @param array &$data          Public request data, passed by reference
     * @return boolean              Indicating success
     */
    function _show_csv_select($handler_id, &$data)
    {
        if (array_key_exists('rows', $data))
        {
            // Present user with the field matching form
            $data['schemadbs'] = $this->_schemadbs;
            midcom_show_style('show-import-csv-select');
        }
        else
        {
            // Present user with upload form
            midcom_show_style('show-import-csv-form');
        }
    }

    /**
     * Handle the CSV import phase
     * 
     * @access public
     * @param String $handler_id    Name of the request handler
     * @param array $args           Variable arguments
     * @param array &$data          Public request data, passed by reference
     * @return boolean              Indicating success
     */
    function _handler_csv($handler_id, $args, &$data)
    {
        $this->_prepare_handler($args);
        if (!$data['campaign'])
        {
            return false;
        }

        // Update the breadcrumb
        $this->_update_breadcrumb($handler_id, $args);

        $data['contacts'] = array();

        if (!array_key_exists('org_openpsa_directmarketing_import_separator', $_POST))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'No CSV separator specified.');
            // This will exit.
        }

        if (!file_exists($_POST['org_openpsa_directmarketing_import_tmp_file']))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'No CSV file available.');
            // This will exit.
        }

        $data['time_start'] = time();

        $data['rows'] = array();
        $data['separator'] = $_POST['org_openpsa_directmarketing_import_separator'];

        // Start processing the file
        $read_rows = 0;
        $total_columns = 0;
        $handle = fopen($_POST['org_openpsa_directmarketing_import_tmp_file'], 'r');
        $separator = $data['separator'];

        while ($csv_line = fgetcsv($handle, 1000, $separator))
        {

            if ($total_columns == 0)
            {
                $total_columns = count($csv_line);
            }
            $columns_with_content = 0;
            foreach ($csv_line as $value)
            {
                if ($value != '')
                {
                    $columns_with_content++;
                }
            }
            $percentage = round(100 / $total_columns * $columns_with_content);

            if ($percentage >= 20)
            {
                $data['rows'][] = $csv_line;
                $read_rows++;
            }
            else
            {
                // This line has no proper content, skip
                continue;
            }

            $contact = array();

            if ($read_rows == 1)
            {
                // First line is headers, skip
                continue;
            }
            foreach ($csv_line as $field => $value)
            {
                // Process the row accordingly
                $field_matching = $_POST['org_openpsa_directmarketing_import_csv_field'][$field];
                if (   $field_matching
                    && strstr($field_matching, ':'))
                {
                    $matching_parts = explode(':', $field_matching);
                    $schemadb = $matching_parts[0];
                    $schema_field = $matching_parts[1];

                    if (   !array_key_exists($schemadb, $this->_schemadbs)
                        || !array_key_exists($schema_field, $this->_schemadbs[$schemadb]['default']->fields))
                    {
                        // Invalid matching, skip
                        continue;
                    }

                    if ($value == '')
                    {
                        // No value, skip
                        continue;
                    }

                    if (!array_key_exists($schemadb, $contact))
                    {
                        $contact[$schemadb] = array();
                    }

                    $contact[$schemadb][$schema_field] = $value;
                }
            }

            if (count($contact) > 0)
            {
                $data['contacts'][] = $contact;
            }
        }

        if (count($data['contacts']) > 0)
        {
            /*
            echo "<pre>\n";
            print_r($data['contacts']);
            echo "</pre>\n";
            die();
            */
            $this->_import_subscribers($data['contacts']);
        }

        $data['time_end'] = time();

        return true;
    }

    /**
     * Show the CSV import phase
     * 
     * @access public
     * @param String $handler_id    Name of the request handler
     * @param array &$data          Public request data, passed by reference
     */
    function _show_csv($handler_id, &$data)
    {
        midcom_show_style('show-import-status');
    }

}
?>