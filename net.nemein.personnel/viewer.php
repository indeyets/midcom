<?php
/**
 * @package net.nemein.personnel
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Person viewer Site interface class.
 *
 * @package net.nemein.personnel
 */
class net_nemein_personnel_viewer extends midcom_baseclasses_components_request
{
    function net_nemein_personnel_viewer($topic, $config)
    {
        parent::midcom_baseclasses_components_request($topic, $config);
    }

    /**
     * Initializes the request switch
     */
    function _on_initialize()
    {
        // Show list of the persons
        $this->_request_switch['view-index'] = array
        (
            'handler' => array('net_nemein_personnel_handler_view', 'index'),
        );
        
        // Show alphabetical order
        if ($this->_config->get('enable_alphabetical'))
        {
            $this->_request_switch['view-index-alpha'] = array
            (
                'handler' => array('net_nemein_personnel_handler_view', 'index'),
                'fixed_args' => array ('alpha'),
                'variable_args' => 1,
            );
        }
        
        // Show a subgroup list
        // Match /group/<group guid>/
        $this->_request_switch['subgroup-list'] = array
        (
            'handler' => array ('net_nemein_personnel_handler_view', 'group'),
            'fixed_args' => array ('group'),
            'variable_args' => 1,
        );
        
        // Sort the personnel
        // Match /order
        if ($this->_config->get('sort_order') === 'sorted and grouped')
        {
            // Sorting of the personnel
            $this->_request_switch['sort_order_grouped'] = array
            (
                'handler' => array('net_nemein_personnel_handler_order', 'grouped'),
                'fixed_args' => array ('order'),
            );
        }
        
        // View person in a group
        // Match /group/<group guid>/<person identificator>
        $this->_request_switch['view-grouped-person'] = array
        (
            'handler' => array('net_nemein_personnel_handler_view', 'person'),
            'fixed_args' => array ('group'),
            'variable_args' => 2,
        );
        
        // Create a user account
        // Match /account/<person identificator>
        $this->_request_switch['account'] = array
        (
            'handler' => array('net_nemein_personnel_handler_account', 'account'),
            'fixed_args' => array('account'),
            'variable_args' => 1,
        );
        
        // Generate random passwords
        // Match /passwords
        $this->_request_switch['passwords'] = array
        (
            'handler' => array('net_nemein_personnel_handler_account', 'passwords'),
            'fixed_args' => array('passwords'),
        );
        
        // Show a person according to the username or GUID
        // Match /<person identificator>
        $this->_request_switch['view-person'] = array
        (
            'handler' => array('net_nemein_personnel_handler_view', 'person'),
            'variable_args' => 1,
        );
        
        // 
        
        /*
         * not yet implemented
         *
        if ($this->_config->get('enable_foaf'))
        {
            $this->_request_switch['foaf-all'] = array
            (
                'handler' => array('net_nemein_personnel_handler_foaf', 'all'),
                'fixed_args' => array('foaf.rdf'),
            );
            $this->_request_switch['foaf-person'] = array
            (
                'handler' => array('net_nemein_personnel_handler_foaf', 'person'),
                'fixed_args' => array('foaf'),
                'variable_args' => 1,
            );
        }

        if ($this->_config->get('enable_vcard'))
        {
            $this->_request_switch['vcard-all'] = array
            (
                'handler' => array('net_nemein_personnel_handler_vcard', 'all'),
                'fixed_args' => array('vcard.vcf'),
            );
            $this->_request_switch['vcard-person'] = array
            (
                'handler' => array('net_nemein_personnel_handler_vcard', 'person'),
                'fixed_args' => array('vcard'),
                'variable_args' => 1,
            );
        }
         */

        /* CSV export */
        $this->_request_switch['csv-export-redirect'] = Array
        (
            'handler' => Array('net_nemein_personnel_handler_csv', 'export'),
            'fixed_args' => Array('csv', 'export'),
            'variable_args' => 0,
        );
        $this->_request_switch['csv-export'] = Array
        (
            'handler' => Array('net_nemein_personnel_handler_csv', 'export'),
            'fixed_args' => Array('csv', 'export'),
            'variable_args' => 1,
        );
        /* CSV import */
        $this->_request_switch['csv-import'] = Array
        (
            'handler' => Array('net_nemein_personnel_handler_csv', 'import'),
            'fixed_args' => Array('csv', 'import'),
        );

        // Administrative stuff
        $this->_request_switch['admin-edit-group'] = array
        (
            'handler' => array('net_nemein_personnel_handler_admin', 'editgroup'),
            'fixed_args' => array('admin', 'edit', 'group'),
            'variable_args' => 1,
        );
        $this->_request_switch['admin-edit'] = array
        (
            'handler' => array('net_nemein_personnel_handler_admin', 'edit'),
            'fixed_args' => array('admin', 'edit'),
            'variable_args' => 1,
        );
        $this->_request_switch['admin-delete'] = array
        (
            'handler' => array('net_nemein_personnel_handler_admin', 'delete'),
            'fixed_args' => array('admin', 'delete'),
            'variable_args' => 1,
        );
        $this->_request_switch['admin-create'] = array
        (
            'handler' => array('net_nemein_personnel_handler_admin', 'create'),
            'fixed_args' => array('admin', 'create'),
        );

        $this->_request_switch['search'] = array
        (
            'handler' => array('net_nemein_personnel_handler_search', 'search'),
            'fixed_args' => array('search'),
        );
        
        // Match /config/
        $this->_request_switch['config'] = array
        (
            'handler' => array('midcom_core_handler_configdm', 'configdm'),
            'schemadb' => 'file:/net/nemein/personnel/config/schemadb_config.inc',
            'schema' => 'config',
            'fixed_args' => array('config'),
        );
    }

    /**
     * The handle callback populates the toolbars.
     */
    function _on_handle($handler, $args)
    {
        $this->_populate_node_toolbar();
        
        if ($handler === 'config')
        {
            $this->_get_members();
            $this->_get_schemadbs();
        }
        
        return true;
    }
    
    /**
     * Get a list of group members for the configuration page
     * 
     * @access private
     */
    function _get_members()
    {
        if (!$this->_config->get('group'))
        {
            $GLOBALS['net_nemein_personnel_members'] = array ();
            return;
        }
        
        $qb = midcom_db_member::new_query_builder();
        if (version_compare(mgd_version(), '1.8.0alpha1', '>'))
        {
            $qb->add_constraint('gid.guid', '=', $this->_config->get('group'));
            $qb->add_order('uid.lastname');
            $qb->add_order('uid.firstname');
        }
        else
        {
            $group = new midcom_db_group($this->_config->get('group'));
            $qb->add_constraint('gid', '=', $group->id);
        }
        
        $members = array ();
        
        foreach ($qb->execute_unchecked() as $membership)
        {
            $person = new midcom_db_person($membership->uid);
            $members[$person->guid] = $person->rname;
        }
        
        asort($members);
        
        $GLOBALS['net_nemein_personnel_members'] = $members;
    }

    function _get_schemadbs()
    {
        $GLOBALS['net_nemein_personnel_schemadbs'] = array_merge
        (
            Array
            (
                '' => $this->_l10n->get('default setting')
            ),
            $this->_config->get('schemadbs')
        );
    }



    /**
     * Populates the node toolbar depending on the users rights.
     *
     * Currently, creation is only allowed for administrator accounts. In the future,
     * create on both persons in general and group members below the selected group
     * should be appropriate. (Needs rethinking.)
     *
     * @access protected
     */
    function _populate_node_toolbar()
    {
        if ($_MIDCOM->auth->admin)
        {
            $this->_node_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "admin/create.html",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('create person'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_person.png',
                )
            );
        }

        if (   $this->_topic->can_do('midgard:update')
            && strstr($this->_config->get('sort_order'), 'sorted'))
        {
            $this->_node_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "order/",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('sort personnel manually'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_people.png',
                )
            );
        }
        
        if (   $this->_topic->can_do('midgard:update')
            && $this->_topic->can_do('midcom:component_config'))
        {
            $this->_node_toolbar->add_item(array(
                MIDCOM_TOOLBAR_URL => 'config.html',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
                MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
            ));
        }
    }

    /**
     * Simple helper, gives the base URL for a person (either username.html or
     * guid.html, depending on the username).
     *
     * All 1 argument handlers are filtered here.
     *
     * @param midcom_db_person The person to query.
     * @return string The URL to use.
     */
    function get_url($person, $guid = null)
    {
        $prefix = '';
        if (!is_null($guid))
        {
            $prefix = "group/{$guid}/";
        }
        
        if (   $person->username
            && $person->username != 'vcard.vcf'
            && $person->username != 'foaf.rdf')
        {
            return "{$prefix}{$person->username}.html";
        }
        else
        {
            return "{$prefix}{$person->guid}.html";
        }
    }

    /**
     * Indexes a person.
     *
     * This function is usually called statically from various handlers.
     *
     * @param midcom_helper_datamanager2_datamanager $dm The Datamanager encaspulating the person.
     * @param midcom_services_indexer $indexer The indexer instance to use.
     * @param midcom_db_topic The topic which we are bound to. If this is not an object, the code
     *     tries to load a new topic instance from the database identified by this parameter.
     */
    function index(&$dm, &$indexer, $topic)
    {
        if (!is_object($topic))
        {
            $tmp = new midcom_db_topic($topic);
            if (! $tmp)
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    "Failed to load the topic referenced by {$topic} for indexing, this is fatal.");
                // This will exit.
            }
            $topic = $tmp;
        }

        // Don't index directly, that would loose a reference due to limitations
        // of the index() method. Needs fixes there.

        $nav = new midcom_helper_nav();
        $node = $nav->get_node($topic->id);

        $document = $indexer->new_document($dm);
        $document->topic_guid = $topic->guid;
        $document->topic_url = $node[MIDCOM_NAV_FULLURL];
        $document->read_metadata_from_object($dm->storage->object);
        $document->component = $topic->component;
        $indexer->index($document);
    }

    /*
    var $_article;
    var $_layout;
    var $_view;
    var $_preferred_person;

    var $_group;
    var $_shown_persons;
    var $_max_persons_in_row;
    var $_total_persons;

    var $errcode;
    var $errstr;

    function ___net_nemein_personnel_viewer($topic, $config) {

        $this->_debug_prefix = "net.nemein.personnel viewer::";

        $this->_config = $config;
        $this->_topic = $topic;

        $this->_person = false;

        $this->_view = false;
        $this->_shown_persons = array();
        $this->_max_persons_in_row = $this->_config->get("persons_in_row");

        $this->errcode = MIDCOM_ERROK;
        $this->errstr = "";

        $GLOBALS['midcom_component_data']['net.nemein.personnel']['active_leaf'] = false;

        $this->_total_persons = 0;
        $this->_group = new midcom_baseclasses_database_group($this->_config->get("group"));
        if ($this->_group)
        {
            $qb = midcom_baseclasses_database_member::new_query_builder();
            $qb->add_constraint('gid', '=', $this->_group->id);
            $qb->hide_invisible = false; // don't hide unapproved membership records.
            $persons = $this->_get_persons_for_memberships($qb->execute());
            $this->_total_persons = count($persons);
        }

        $this->_preferred_person = $this->_config->get("preferred_person");

        $this->_go = false;
    }

    /**
     * Internal helper which wraps the membership->person transformation in an
     * ACL safe way.
     *
     * @param array $membership A resultset that was queried using midcom_baseclasses_database_member::new_query_builder()
     * @return array An array of midcom_baseclasses_database_person() objects.
     * /
    function _get_persons_for_memberships($memberships)
    {
        $result = array();
        foreach ($memberships as $membership)
        {
            $person = new midcom_baseclasses_database_person($membership->uid);
            if (   $person
                && $person->is_object_visible_onsite())
            {
                // We have access to the person.
                $result[] = $person;
            }
        }
        return $result;
    }

    function ___can_handle($argc, $argv) {

        debug_push($this->_debug_prefix . "can_handle");

        // Schema Database
        debug_add("Loading Schema Database", MIDCOM_LOG_DEBUG);
        $path = $this->_config->get("schemadb");
        $data = midcom_get_snippet_content($path);
        eval("\$GLOBALS[\"net_nemein_personnel_layouts\"] = array ( {$data} );");

        $this->_layout = new midcom_helper_datamanager($GLOBALS["net_nemein_personnel_layouts"]);
        if (!$this->_layout) {
            $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT, "Datamanager could not be instantinated.");
            // This will exit.
        }

        if ($argc == 0) {
          $this->_view = "index";
          return true;
        }
        if ($argc == 1) {

          // Display FOAF output at /foaf.rdf
          if ($argv[0] == "foaf.rdf" && $this->_config->get("enable_foaf")) {
            // FOAF output support
            if ($this->_group) {
              $qb = midcom_baseclasses_database_member::new_query_builder();
              $qb->add_constraint('gid', '=', $this->_group->id);
              $qb->hide_invisible = false; // don't hide unapproved membership records.
              $persons = $this->_get_persons_for_memberships($qb->execute());

              $this->_displayFOAFHeaders();
              echo "<foaf:Group>\n";
              echo "<foaf:name>".utf8_encode(htmlspecialchars($this->_group->name))."</foaf:name>\n";
              foreach ($persons as $person)
              {
                  echo "<foaf:member>\n";
                  $this->_displayFOAF($person);
                  echo "</foaf:member>\n";
              }
              echo "</foaf:Group>\n";
              $this->_displayFOAFFooters();
            }
          }

          // Display vCard output at /contacts.vcf
          if ($argv[0] == "contacts.vcf" && $this->_config->get("enable_vcard")) {
            // vCard output support
            if ($this->_group) {
              $this->_displayVCardHeaders();

              $qb = midcom_baseclasses_database_member::new_query_builder();
              $qb->add_constraint('gid', '=', $this->_group->id);
              $qb->hide_invisible = false; // don't hide unapproved membership records.
              $persons = $this->_get_persons_for_memberships($qb->execute());

              foreach ($persons as $person)
              {
                  $this->_displayVCard($person);
              }
              $GLOBALS["midcom"]->finish();
              exit();
              return true;
            }
          }

          // Try to get person
          if ($this->_getPerson($argv[0])) {
            debug_pop();
            return true;
          }
        }
        if ($argc == 2) {
          // Display FOAF output for person at /foaf/username.rdf
          if ($argv[0] == "foaf" && $this->_config->get("enable_foaf")) {
            $person_uri = str_replace(".rdf","",$argv[1]);
            if ($this->_getPerson($person_uri)) {
              $this->_displayFOAFHeaders();
              $this->_displayFOAF($this->_person);
              $this->_displayFOAFFooters();
            }
          }

          // Display vCard output for person at /vcard/username.vcard
          if ($argv[0] == "vcard" && $this->_config->get("enable_vcard")) {
            $person_uri = str_replace(".vcf","",$argv[1]);
            if ($this->_getPerson($person_uri)) {
              $this->_displayVCardHeaders();
              $this->_displayVCard($this->_person);
              $GLOBALS["midcom"]->finish();
              exit();
            }
          }

        }
        debug_pop();
        return false;
    }

    function _encodeString($string) {
      // Lifted from OpenPSA Mail() class
      preg_match_all("/[^\x20-\x7e]/", $string, $matches);
      $cache = array();
      if (count ($matches[0])>0) {
        $newstring = $string;
        while (list ($k, $char) = each ($matches[0])) {
          $code="=".dechex(ord($char));
          $hex=str_pad(strtoupper(dechex(ord($char))),2,"0", STR_PAD_LEFT);
          if (array_key_exists($hex,$cache)) continue;
          $newstring=str_replace($char, "=$hex", $newstring);
          $cache[$hex]=1;
        }
        $string = $newstring;
        //$string="=?ISO-8859-1?Q?".$newstring."?=";
      }
      return $string;
//      return base64_encode($string);
    }

    function _getPerson ($getperson) {

        debug_push($this->_debug_prefix . "_getPerson");

        debug_add("looking for person '$getperson'", MIDCOM_LOG_DEBUG);

        if (isset($this->_group)) {
          $qb = midcom_baseclasses_database_member::new_query_builder();
          $qb->add_constraint('gid', '=', $this->_group->id);
          $qb->hide_invisible = false; // don't hide unapproved membership records.
          $persons = $this->_get_persons_for_memberships($qb->execute());

          foreach ($persons as $person)
          {
              if ($person->username && $person->username == $getperson) {
                $this->_person = $person;
              // Match by GUID if username is not available
              } elseif ($person->guid == $getperson) {
                $this->_person = $person;
              }
              if ($this->_person) {
                debug_add("found person", MIDCOM_LOG_DEBUG);
                $this->errcode = MIDCOM_ERROK;
                debug_pop();
                $this->_view = "person";
                $GLOBALS['midcom_component_data']['net.nemein.personnel']['active_leaf'] = $this->_person->id;
                return true;
              }
          }
        }
    }

    function _displayFOAFHeaders() {
      $GLOBALS["midcom"]->cache->content->content_type("text/xml");
      $GLOBALS["midcom"]->header("Content-type: text/xml; charset=UTF-8");
      echo "<rdf:RDF\n";
      echo "xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\"\n";
      echo "xmlns:foaf=\"http://xmlns.com/foaf/0.1/\">\n";
    }

    function _displayFOAF($person) {
      if (is_object($person)) {
        echo "<foaf:Person>\n";
        echo "<foaf:name>".utf8_encode(htmlspecialchars($person->name))."</foaf:name>\n";
        echo "<foaf:firstName>".utf8_encode(htmlspecialchars($person->firstname))."</foaf:firstName>\n";
        echo "<foaf:surname>".utf8_encode(htmlspecialchars($person->lastname))."</foaf:surname>\n";
        if ($person->username) {
          echo "<foaf:nick >$person->username</foaf:nick>\n";
        }
        if ($person->email) {
          echo "<foaf:mbox rdf:resource=\"mailto:$person->email\" />\n";
        }
        if ($person->homepage) {
          echo "<foaf:homepage rdf:resource=\"$person->homepage\" />\n";
        }
        echo "</foaf:Person>\n";
      }
    }

    function _displayFOAFFooters() {
      echo "</rdf:RDF>\n";
      $GLOBALS["midcom"]->finish();
      exit();
    }

    function _displayVCardHeaders() {
      $GLOBALS["midcom"]->cache->content->content_type("text/x-vcard");
      $GLOBALS["midcom"]->header("Content-type: text/x-vcard");
    }

    function _displayVCard($person) {
      if (is_object($person)) {
        $i18n =& $GLOBALS["midcom"]->get_service("i18n");
        $charset = $i18n->get_current_charset();
        echo "BEGIN:VCARD\n";
        echo "VERSION:2.1\n";
        echo "N;ENCODING=Quoted-Printable;CHARSET=".$charset.":".$this->_encodeString($person->lastname).";".$this->_encodeString($person->firstname)."\n";
        echo "FN;ENCODING=Quoted-Printable;CHARSET=".$charset.":".$this->_encodeString($person->name)."\n";
        echo "UID:".$person->guid()."@Midgard\n";
        if ($person->workphone) {
          echo "TEL;WORK:".$person->workphone."\n";
        }
        if ($person->handphone) {
          echo "TEL;CELL:".$person->handphone."\n";
        }
        if ($person->homephone) {
          echo "TEL;HOME:".$person->homephone."\n";
        }
        if ($person->email) {
          echo "EMAIL;INTERNET:".$person->email."\n";
        }
        echo "END:VCARD\n";
      }
    }
    function ___handle() {

        global $midcom;
        global $net_nemein_personnel_layouts;

        debug_push($this->_debug_prefix . "handle");

        if ($this->errcode != MIDCOM_ERROK) {
            debug_pop();
            return false;
        }

        // FOAF autodetection
        $url_prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        if ($this->_config->get("enable_foaf"))
        {
            $GLOBALS["midcom"]->add_link_head(
                array(
                    'rel' => 'meta',
                    'type' => 'application/rdf+xml',
                    'title' => 'FOAF',
                    'href' => $url_prefix."foaf.rdf",
                )
            );
        }

        if ($this->_person) {
          $this->_layout = new midcom_helper_datamanager($net_nemein_personnel_layouts);
          if (! $this->_layout)
            $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT,
                "Datamanager class could not be instantinated.");

          if (! $this->_layout->init($this->_person))
            $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT,
                "Datamanager class could not be initialized.");

          // set nap element
          $GLOBALS['midcom_component_data']['net.nemein.personnel']['active_leaf'] = $this->_person->id;
          $GLOBALS['midcom']->set_pagetitle($this->_person->name);

          // initialize layout
          $substyle = $this->_layout->get_layout_name();
          if ($substyle != "default") {
            debug_add ("pushing substyle $substyle", MIDCOM_LOG_DEBUG);
            $midcom->substyle_append($substyle);
          }
        } else {
          $GLOBALS['midcom']->set_pagetitle($this->_topic->extra);
        }
        debug_pop();
        return true;
    }

    function _displayPerson($person_id,$persons_in_row) {

      $person = new midcom_baseclasses_database_person($person_id);

      debug_print_r("person", $person);
      debug_print_r("group", $this->_group);

      $qb = midcom_baseclasses_database_member::new_query_builder();
      $qb->add_constraint('gid', '=', $this->_group->id);
      $qb->add_constraint('uid', '=', $person->id);
      $qb->hide_invisible = false;
      $is_member = $qb->count();

      if ($person && $is_member) {

        if ($this->_config->get("filter_default") == true) {
          $display_person = true;
          $skip_person_loop = false;
        } else {
          $display_person = false;
          $skip_person_loop = false;
        }

        /* Alphabetical filtering support
         * Run before datamanager schema loading for better performance
        if ($this->_config->get("enable_alphabetical") && isset($_REQUEST["net_nemein_personnel_alphabetical"])) {
          $skip_person_loop = false;
         if (strtolower(substr($person->lastname,0,1)) != strtolower($_REQUEST["net_nemein_personnel_alphabetical"])) {
            $display_person = false;
            $filter_out = true;
          } else {
            $display_person = true;
          }
        }
        */

        /* Don't skip the loop if filters are enabled and present
        if ($this->_config->get("enable_filtering") && isset($_REQUEST["net_nemein_personnel_filter"])) {
          $skip_person_loop = false;
          // Faster department and office filtering
          if (isset($_REQUEST["net_nemein_personnel_filter"]["department"])) {
            if ($_REQUEST["net_nemein_personnel_filter"]["department"] != $person->department) {
              $display_person = false;
              $filter_out = true;
            }
          }
          if (isset($_REQUEST["net_nemein_personnel_filter"]["office"])) {
            if ($_REQUEST["net_nemein_personnel_filter"]["office"] != $person->office) {
              $display_person = false;
              $filter_out = true;
            }
          }
        }
        * /

        // Don't go to the person display loop for users filtered out alphabetically
        if (!$skip_person_loop && ($display_person || !isset($filter_out))) {
          if (!isset($filter_out)) {
            $filter_out = false;
          }

          // Initialize datamanager schema for the person
          if (!$this->_layout->init($person)) {
            $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT,"Layout class could not be initialized.");
          }

          global $view_link;
          global $view;
          $view = $this->_layout->get_array();

          // Show person only once
          if (in_array($person->guid(),$this->_shown_persons)) {
            $display_person = false;
          }

          /* Filtering support
          if ($this->_config->get("enable_filtering") && isset($_REQUEST["net_nemein_personnel_filter"])) {
            if (is_array($_REQUEST["net_nemein_personnel_filter"])) {
              foreach ($_REQUEST["net_nemein_personnel_filter"] as $field => $filter) {
                if (!stristr($view[$field],$filter)) {
                  $display_person = false;
                  $filter_out = true;
                } else {
                  if (!$this->_config->get("filter_default") && !$filter_out) {
                    $display_person = true;
                  }
                }
              }
            }
          }
          */

          /* Group filtering
          if ($this->_config->get("enable_filtering")
              && array_key_exists('net_nemein_personnel_filtergroup', $_GET)
              && is_array($_GET['net_nemein_personnel_filtergroup']))
          {
              // Find the fields in the schema
              foreach ($this->_layout->_layoutdb['default']['fields'] as $name => $field)
              {
                  if (array_key_exists('filtergroup', $field))
                  {
                      foreach ($_GET['net_nemein_personnel_filtergroup'] as $group => $value)
                      {
                          if (   $field['filtergroup'] == $group
                              && $view[$name] == $value)
                          {
                              // Found the correct field from the group

                              if (   array_key_exists('net_nemein_personnel_filtergroup_childfilter_min', $_GET)
                                  && array_key_exists('childfield', $field)
                                  && array_key_exists($field['childfield'], $view))
                              {
                                  // Check that the "child field" matches too
                                  if ($view[$field['childfield']] >= (int) $_GET['net_nemein_personnel_filtergroup_childfilter_min'])
                                  {
                                      if (!$filter_out)
                                      {
                                          $display_person = true;
                                      }
                                  }
                                  else
                                  {
                                      $display_person = false;
                                      $filter_out = true;
                                  }
                              }
                              else
                              {
                                  // Show it
                                  if (!$filter_out)
                                  {
                                      $display_person = true;
                                  }
                              }
                          }
                      }
                  }
              }
          }
          * /

          if ($display_person) {

            if ($persons_in_row == 0) {
              midcom_show_style("show-index-row-header");
            }
            $persons_in_row++;
            $this->_shown_persons[] = $person->guid();

            if ($person->username) {
              $view_link = $person->username;
            } else {
              $view_link = $person->guid;
            }

            midcom_show_style("show-index-item");

            if ($persons_in_row == $this->_max_persons_in_row) {
              midcom_show_style("show-index-row-footer");
              $persons_in_row = 0;
            } elseif (count($this->_shown_persons) == $this->_total_persons) {
              midcom_show_style("show-index-row-footer");
            }
          }
        }
      }
      return $persons_in_row;
    }

    function ___show() {

        debug_push($this->_debug_prefix . "show");

        if ($this->_view == "person") {

          global $view;
          $view = $this->_layout->get_array();
          midcom_show_style("show-person");

        } else {


          if (isset($this->_group)) {

            global $view_topic;
            global $view_link;
            $view_topic = $this->_topic;
            $persons_in_row = 0;

            midcom_show_style("show-index-header");

            if ($this->_config->get("enable_alphabetical")) {
              global $view_group;
              $view_group = $this->_group;
              midcom_show_style("show-index-alphabet");
            }

            if ($this->_preferred_person && $this->_preferred_person != "false") {
              // Show preferred person first
              $person = new midcom_baseclasses_database_person($this->_preferred_person);
              if ($person) {
                $persons_in_row = $this->_displayPerson($person->id,$persons_in_row);
              }
            }

            $qb = midcom_baseclasses_database_member::new_query_builder();
            $qb->add_constraint('gid', '=', $this->_group->id);
            $qb->hide_invisible = false; // don't hide unapproved membership records.
            $persons = $this->_get_persons_for_memberships($qb->execute());
            $total_persons = count($persons);

            foreach ($persons as $person)
            {
                $persons_in_row = $this->_displayPerson($person->id,$persons_in_row);
            }
            midcom_show_style("show-index-footer");
          }
        }

        debug_pop();
        return true;
    }


    function ___get_metadata() {

        if ($this->_person) {
            return array (
                MIDCOM_META_CREATOR => $this->_person->creator,
                MIDCOM_META_EDITOR  => false,
                MIDCOM_META_CREATED => $this->_person->created,
                MIDCOM_META_EDITED  => false
            );
        }
        else
            return false;
    }
    */

}
?>
