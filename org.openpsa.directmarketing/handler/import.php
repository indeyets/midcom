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
    var $_toolbars = null;
    
    function org_openpsa_directmarketing_handler_import()
    {
        parent::midcom_baseclasses_components_handler();
        $this->_toolbars = &midcom_helper_toolbars::get_instance();
    }
    
    function _import_subscribers($subscribers)
    {
        if (!is_array($subscribers))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to read proper list of users to import');
            // This will exit   
        }
        
        foreach ($subscribers as $subscriber)
        {
            if (   array_key_exists('email', $subscriber)
                && $subscriber['email'])
            {
                // First we perform simple email test
                // More complicated duplicate checking is best left to the o.o.contacts dupe finder
                $qb = midcom_db_person::new_query_builder();
                $qb->add_constraint('email', '=', $subscriber['email']);
                $persons = $qb->execute();
                if (count($persons) > 0)
                {
                    // Match found, add to campaign
                    
                    // Use first match
                    $person = $persons[0];
                    
                    // Check if person is already in campaign
                    $qb = org_openpsa_directmarketing_campaign_member::new_query_builder();
                    $qb->add_constraint('person', '=', $person->id);
                    $qb->add_constraint('campaign', '=', $this->_request_data['campaign']->id);
                    $qb->add_constraint('orgOpenpsaObtype', '<>', ORG_OPENPSA_OBTYPE_CAMPAIGN_TESTER);
                    $members = $qb->execute();
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
                            $stat = $member->update();
                            if ($stat)
                            {
                                $this->_request_data['import_status']['subscribed_existing']++;
                            }
                            else
                            {
                                $this->_request_data['import_status']['failed_add']++;
                            }
                        }
                    }
                    else
                    {
                        // Not a subscribed member yet, add
                        $member = new org_openpsa_directmarketing_campaign_member();
                        $member->person = $person->id;
                        $member->campaign = $this->_request_data['campaign']->id;
                        $member->orgOpenpsaObtype = ORG_OPENPSA_OBTYPE_CAMPAIGN_MEMBER;
                        $stat = $member->create();
                        if ($stat)
                        {
                            $this->_request_data['import_status']['subscribed_existing']++;
                        }
                        else
                        {
                            $this->_request_data['import_status']['failed_add']++;
                        }
                    }
                }
                else
                {
                    // New person, create and add
                    
                    $person = new midcom_db_person();
                    $params = Array();
                    foreach ($subscriber as $field => $value)
                    {
                        switch ($field)
                        {
                            case 'organization':
                                // TODO: Create/Add to group
                            case 'guid':
                                $params[$field] = $value;
                                break;
                            default:
                                $person->$field = $value;
                                break;
                        }
                    }
                    
                    $stat = $person->create();
                    if ($stat)
                    {
                        $person = new midcom_db_person($person->id);
                        
                        foreach ($params as $name => $value)
                        {
                            $person->parameter('org.openpsa.contacts', $name, $value);
                        }
                        
                        $member = new org_openpsa_directmarketing_campaign_member();
                        $member->person = $person->id;
                        $member->campaign = $this->_request_data['campaign']->id;
                        $member->orgOpenpsaObtype = ORG_OPENPSA_OBTYPE_CAMPAIGN_MEMBER;
                        $stat = $member->create();
                        if ($stat)
                        {
                            $this->_request_data['import_status']['subscribed_new']++;
                        }
                        else
                        {
                            // Clean up
                            $person->delete();
                            $this->_request_data['import_status']['failed_create']++;
                        }
                    }
                    else
                    {
                        $this->_request_data['import_status']['failed_create']++;
                    }
                }
            }
        }
    }
    
    function _prepare_handler($args)
    {
        // No sense to allow the whole import thing if user can't create persons
        $_MIDCOM->auth->require_user_do('midgard:create', null, 'midcom_db_person');
        
        // Try to load the correct campaign
        $this->_request_data['campaign'] = new org_openpsa_directmarketing_campaign($args[0]);
        if (!$this->_request_data['campaign'])
        {
            return false;
        }
        
        $this->_toolbars->bottom->add_item(
            Array(
                MIDCOM_TOOLBAR_URL => "campaign/{$this->_request_data['campaign']->guid}/",
                MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n_midcom']->get("back"),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_left.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            )
        );
        
        $this->_request_data['import_status'] = Array(
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

    function _handler_index($handler_id, $args, &$data)
    {
        $this->_prepare_handler($args);
        if (!$this->_request_data['campaign'])
        {
            return false;
        }
        
        return true;
    }
    
    function _show_index($handler_id, &$data)
    {
        midcom_show_style('show-import-index');  
    }
    
    function _handler_simpleemails($handler_id, $args, &$data)
    {
        $this->_prepare_handler($args);
        if (!$this->_request_data['campaign'])
        {
            return false;
        }

        if (array_key_exists('org_openpsa_directmarketing_import_separator', $_POST))
        {
            $this->_request_data['contacts'] = array();
            
            switch ($_POST['org_openpsa_directmarketing_import_separator'])
            {
                case 'N':
                    $this->_request_data['separator'] = "\n";
                    break;
                case ',':
                default:
                    $this->_request_data['separator'] = ",";
                    break;
            }
            if (is_uploaded_file($_FILES['org_openpsa_directmarketing_import_upload']['tmp_name']))
            {
                $contacts_raw = file_get_contents($_FILES['org_openpsa_directmarketing_import_upload']['tmp_name']);
                $contacts = explode($this->_request_data['separator'], $contacts_raw);
                if (count($contacts) > 0)
                {
                    foreach ($contacts as $contact)
                    {
                        $this->_request_data['contacts'][] = Array(
                            'email' => strtolower($contact),
                        );
                    }
                }
            }
            
            if (count($this->_request_data['contacts']) > 0)
            {
                $this->_import_subscribers($this->_request_data['contacts']);
            }
        }
        
        return true;
    }
    
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
    
    function _handler_vcards($handler_id, $args, &$data)
    {
        $this->_prepare_handler($args);
        if (!$this->_request_data['campaign'])
        {
            return false;
        }

        if (array_key_exists('org_openpsa_directmarketing_import', $_POST))
        {
            $this->_request_data['contacts'] = array();
            
            if (is_uploaded_file($_FILES['org_openpsa_directmarketing_import_upload']['tmp_name']))
            {
                require_once 'Contact_Vcard_Parse.php';            
                $parser = new Contact_Vcard_Parse();
                $cards = @$parser->fromFile($_FILES['org_openpsa_directmarketing_import_upload']['tmp_name']);
                
                if (count($cards) > 0)
                {
                    foreach ($cards as $card)
                    {
                        $contact = Array();
                        
                        // Start parsing
                        if (   array_key_exists('N', $card)
                            && array_key_exists('value', $card['N'][0])
                            && is_array($card['N'][0]['value']))
                        {
                            // FIXME: We should do something about character encodings
                            $contact['lastname'] = $card['N'][0]['value'][0][0];
                            $contact['firstname'] = $card['N'][0]['value'][1][0];
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
                                                $contact['handphone'] = $number['value'][0][0];
                                                break;
                                            case 'HOME':
                                                $contact['homephone'] = $number['value'][0][0];
                                                break;
                                            case 'WORK':
                                                $contact['workphone'] = $number['value'][0][0];
                                                break;
                                        }
                                    }
                                }
                            }
                        }
                        
                        if (array_key_exists('ORG', $card))
                        {
                            $contact['organization'] = $card['ORG'][0]['value'][0][0];
                        }

                        if (array_key_exists('EMAIL', $card))
                        {
                            $contact['email'] = $card['EMAIL'][0]['value'][0][0];
                        }

                        if (array_key_exists('UID', $card))
                        {
                            $contact['guid'] = $card['UID'][0]['value'][0][0];
                        }
                        elseif (array_key_exists('X-ABUID', $card))
                        {
                            $contact['guid'] = $card['X-ABUID'][0]['value'][0][0];
                        }

                        //$contact['rawdata'] = $card;

                        if (count($contact) > 0)
                        {
                            // We have parsed some contact info. 
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
        }
        
        return true;
    }
    
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

}
?>