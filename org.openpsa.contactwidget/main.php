<?php
/**
 * Class for rendering person records
 *
 * Uses the hCard microformat for output.
 * @package org.openpsa.contactwidget
 * @author Henri Bergius, http://bergie.iki.fi
 * @version $Id: main.php,v 1.20 2006/06/13 10:50:51 rambo Exp $
 * @copyright Nemein Oy, http://www.nemein.com
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @link http://www.microformats.org/wiki/hcard hCard microformat documentation
 * @link http://www.midgard-project.org/midcom-permalink-922834501b71daad856f35ec593c7a6d Contactwidget usage documentation
 */
class org_openpsa_contactwidget extends midcom_baseclasses_components_purecode
{
    /**
     * Do we have our contact data ?
     */
    var $_data_read_ok = false;

    /**
     * Contant information of the person being displayed
     */
    var $contact_details = array();

    /**
     * Optional URI to person details
     * @var string
     */    
    var $link = null;

    /**
     * Optional HTML to be placed into the card
     * @var string
     */    
    var $extra_html = null;

    /**
     * Optional HTML to be placed into the card (before any other output in the DIV)
     * @var string
     */    
    var $prefix_html = null;

    /**
     * Whether to show person's groups in a list
     * @var boolean
     */        
    var $show_groups = true;
    
    /**
     * Whether to generate links to the groups using NAP
     * @var boolean
     */
    var $link_contacts = true;
    
    /**
     * Default NAP org.openpsa.contacts node to be used for linking to groups. Will be autoprobed if not supplied.
     * @var Array
     */
    var $contacts_node;
    
    /**
     * DBE service ID of a user
     * @var string
     */
    var $dbe_serviceid = null;
    
    /**
     * Time of last DBE synchronization
     * @var timestamp
     */
    var $dbe_synchronized = null;
    
    /**
     * Initializes the class and stores the selected person to be shown
     * The argument should be a MidgardPerson object. In the future DM
     * Array format will also be supported.
     * 
     * @param mixed $person Person to display either as MidgardPerson or Datamanager array
     */
    function org_openpsa_contactwidget($person = null)
    {
        $this->_component = 'org.openpsa.contactwidget';
    
        parent::midcom_baseclasses_components_purecode();
        
        // Hack to make $contacts_node static
        static $contacts_node_local;
        $this->contacts_node =& $contacts_node_local;
        
        // Read properties of provided person object/DM array
        // TODO: Handle groups as well
        if (is_object($person))
        {
            $this->_data_read_ok = $this->read_object($person);
        }
        elseif (is_array($person))
        {
            $this->_data_read_ok = $this->read_array($person);
        }
    }
    
    /**
     * Read properties of a person object and populate local fields accordingly
     */
    function read_object($person)
    {
        if (   !is_object($person)
            && !is_a($person, 'midcom_baseclasses_database_person')
            && !is_a($person, 'midcom_org_openpsa_person')
            )
        {
            // Given $person is not one
            return false;
        }
        // Database identifiers
        $this->contact_details['guid'] = $person->guid;
        $this->contact_details['id'] = $person->id;
    
        // TODO: Handle persons with empty first/last name
        if (   $person->firstname == ''
            && $person->lastname == '')
        {
            $this->contact_details['firstname'] = '';
            $this->contact_details['lastname'] = "Person #{$person->id}";
        }
        else
        {
            $this->contact_details['firstname'] = $person->firstname;
            $this->contact_details['lastname'] = $person->lastname;
        }

        if ($person->handphone)
        {
            $this->contact_details['handphone'] = $person->handphone;
        }
        
        if ($person->workphone)
        {
            $this->contact_details['workphone'] = $person->workphone;
        }
        
        if ($person->homephone)
        {
            $this->contact_details['homephone'] = $person->homephone;
        }
                
        if ($person->email)
        {
            $this->contact_details['email'] = $person->email;
        }
                
        if ($person->homepage)
        {
            $this->contact_details['homepage'] = $person->homepage;
        }
        
        if (   $this->_config->get('jabber_enable_presence')
            && $person->parameter('org.openpsa.jabber', 'jid'))
        {
            $this->contact_details['jid'] = $person->parameter('org.openpsa.jabber', 'jid');
        }
        
        if (   $this->_config->get('skype_enable_presence')
            && $person->parameter('org.openpsa.skype', 'name'))
        {
            $this->contact_details['skype'] = $person->parameter('org.openpsa.skype', 'name');
        }     

        // DBE service ID of the person
        // TODO: Make optional?
        $this->dbe_serviceid = $person->parameter('org.openpsa.dbe', 'serviceID');
        $this->dbe_synchronized = $person->parameter('org.openpsa.dbe', 'synchronized');
        
        return true;
    }
    
    function determine_url()
    {
        if ($this->link)
        {
            return $this->link;
        }
        elseif ($this->link_contacts)
        {
            if (!$this->contacts_node)
            {
                $this->contacts_node = midcom_helper_find_node_by_component('org.openpsa.contacts');
            }
            
            if (!$this->contacts_node)
            {
                return null;
            }
            else
            {
                $url = "{$this->contacts_node[MIDCOM_NAV_FULLURL]}person/{$this->contact_details['guid']}/";
                return $url;
            }            
        }
    }
    
    /**
     * Show selected person object inline. Outputs hCard XHTML.
     */
    function show_inline()
    {
        if (!$this->_data_read_ok)
        {
            return false;
        }
        $inline_string = '';
        
        // Start the vCard
        $label = '';
        $extra_class = '';
        
        // DBE node information
        if ($this->dbe_serviceid)
        {            
            if (!$this->dbe_synchronized)
            {
                $extra_class = " vcard-dbe-unsynchronized";
                $label .= $this->_l10n->get('never synchronized')." ({$this->dbe_serviceid})";
            }
            else
            {
                $extra_class = " vcard-dbe";
                $label .= sprintf($this->_l10n->get('last synchronized %s'), strftime('%x %X', $this->dbe_synchronized))." ({$this->dbe_serviceid})";
            }
        }          
        
        $inline_string .= "<span class=\"vcard{$extra_class}\" title=\"{$label}\">";

        if (array_key_exists('guid', $this->contact_details))
        {
            // Identifier
            $inline_string .= "<span class=\"uid\" style=\"display: none;\">{$this->contact_details['guid']}</span>";
        }

        // The Name sequence
        $inline_string .= "<span class=\"n\">";
        
        $linked = false;
        if (   $this->link
            || $this->link_contacts)
        {
            $url = $this->determine_url();
            if ($url)
            {
                $inline_string .= "<a href=\"{$url}\">";
                $linked = true;
            }
        }
                
        $inline_string .= "<span class=\"given-name\">{$this->contact_details['firstname']}</span> <span class=\"family-name\">{$this->contact_details['lastname']}</span>";
        
        if ($linked)
        {
            $inline_string .= "</a>";
        }
        
        $inline_string .= "</span>";             

        $inline_string .= "</span>";
        
        return $inline_string;
    }
    
    /**
     * Show the selected person. Outputs hCard XHTML.
     */    
    function show()
    {
        if (!$this->_data_read_ok)
        {
            return false;
        }
        // Start the vCard
        echo "<div class=\"vcard\" id=\"org_openpsa_contactwidget-{$this->contact_details['guid']}\">\n";
        if ($this->prefix_html)
        {
            echo $this->prefix_html;
        }

        // Show picture
        // TODO: Implement photo also in local way
        if (   $this->_config->get('gravatar_enable')
            && array_key_exists('email', $this->contact_details))
        {
            $size = $this->_config->get('gravatar_size');
            $gravatar_url = "http://www.gravatar.com/avatar.php?gravatar_id=".md5($this->contact_details['email'])."&size=".$size;
            echo "<img src=\"{$gravatar_url}\" class=\"photo\" style=\"float: right; margin-left: 4px;\" />\n";
        }
        
        if (array_key_exists('guid', $this->contact_details))
        {
            // Identifier
            echo "<span class=\"uid\" style=\"display: none;\">{$this->contact_details['guid']}</span>";
        }
        
        // The Name sequence
        echo "<div class=\"n\">\n";
        
        $linked = false;
        if (   $this->link
            || $this->link_contacts)
        {
            $url = $this->determine_url();
            if ($url)
            {
                echo "<a href=\"{$url}\">";
                $linked = true;
            }
        }
        
        echo "<span class=\"given-name\">{$this->contact_details['firstname']}</span> <span class=\"family-name\">{$this->contact_details['lastname']}</span>";

        if ($linked)
        {
            echo "</a>";
        }

        echo "</div>\n";
        
        // Contact information sequence
        echo "<ul>\n";
        if ($this->extra_html)
        {
            echo $this->extra_html;
        }
        
        if (   $this->show_groups
            && array_key_exists('id', $this->contact_details))
        {
            $qb = $_MIDCOM->dbfactory->new_query_builder('midcom_db_member');
            $qb->add_constraint('uid', '=', $this->contact_details['id']);
            $memberships = $_MIDCOM->dbfactory->exec_query_builder($qb);
            if ($memberships)
            {
                foreach ($memberships as $membership)
                {
                    echo "<li class=\"org\">";
                    $group = new midcom_baseclasses_database_group($membership->gid);
                    
                    if ($membership->extra)
                    {
                        echo "<span class=\"title\">{$membership->extra}</span>, ";
                    }
                    
                    if ($group->official)
                    {
                        $group_label = $group->official;
                    }
                    else
                    {
                        $group_label = $group->name;
                    }
                    
                    if ($this->link_contacts)
                    {
                        if (!$this->contacts_node)
                        {
                            $this->contacts_node = midcom_helper_find_node_by_component('org.openpsa.contacts');
                        }
                        
                        if (!$this->contacts_node)
                        {
                            $this->link_contacts = false;
                        }
                        else
                        {
                            $group_label = "<a href=\"{$this->contacts_node[MIDCOM_NAV_FULLURL]}group/{$group->guid}/\">{$group_label}</a>";
                        }
                    }
                    
                    echo "<span class=\"organization-name\">{$group_label}</span>";
                    echo "</li>\n";
                }
            }
        }
        
        if (array_key_exists('handphone', $this->contact_details))
        {
            echo "<li class=\"tel cell\">{$this->contact_details['handphone']}</li>\n";
        }
        
        if (array_key_exists('workphone', $this->contact_details))
        {
            echo "<li class=\"tel work\">{$this->contact_details['workphone']}</li>\n";
        }

        if (array_key_exists('homephone', $this->contact_details))
        {
            echo "<li class=\"tel home\">{$this->contact_details['homephone']}</li>\n";
        }

        if (array_key_exists('email', $this->contact_details))
        {
            echo "<li class=\"email\"><a href=\"mailto:{$this->contact_details['email']}\">{$this->contact_details['email']}</a></li>\n";
        }
        
        if (array_key_exists('homepage', $this->contact_details))
        {
            echo "<li class=\"url\"><a href=\"{$this->contact_details['homepage']}\">{$this->contact_details['homepage']}</a></li>\n";
        } 

        if (array_key_exists('skype', $this->contact_details))
        {
            echo "<li class=\"tel skype\"";
            if (empty($_SERVER['HTTPS']))
            {
                // TODO: either complain enough to Skype to have them allow SSL to this server or have some component (o.o.contacts) proxy the image
                echo " style=\"background-image: url('http://mystatus.skype.com/smallicon/{$this->contact_details['skype']}');\"";
            }
            echo "><a href=\"skype:{$this->contact_details['skype']}?call\">{$this->contact_details['skype']}</a></li>\n";
        }
             
        // Instant messaging contact information
        if (array_key_exists('jid', $this->contact_details))
        {
            echo "<li class=\"jabber\"";
            $edgar_url = $this->_config->get('jabber_edgar_url');
            echo " style=\"background-image: url('{$edgar_url}?jid={$this->contact_details['jid']}&type=image');\"";
            echo "><a href=\"xmpp:{$this->contact_details['jid']}\">{$this->contact_details['jid']}</a></li>\n";
        }
        
        /*
        // DBE node information
        if ($this->dbe_serviceid)
        {
            $synchronized = $person->parameter('org.openpsa.dbe', 'synchronized');
            
            $class = "dbe";
            $label = sprintf($this->_l10n->get('last synchronized %s'), strftime('%x %X', $synchronized));
            if (!$synchronized)
            {
                $class = "dbe-unsynchronized";
                $label = $this->_l10n->get('never synchronized');
            }
            
            echo "<li class=\"{$class}\" title=\"{$this->dbe_serviceid}\">{$label}</li>";
        }*/
              
        echo "</ul>\n";
        
        echo "</div>\n";
    }
}
?>