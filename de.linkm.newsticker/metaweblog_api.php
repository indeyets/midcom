<?php
/**
 * Meta Weblog API
 * 
 * @todo Document
 * @todo Reformat
 * 
 * @package de.linkm.newsticker
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/*
Server functions for implementing MetaWebLog API and Blogger API for newsticker
http://www.xmlrpc.com/metaWeblogApi
http://xmlrpc.free-conversant.com/docs/bloggerAPI

Henri Bergius <henri.bergius@iki.fi>

TODO:
  Category support if available as selection list in schema
  Rest of Blogger API
*/

// Include PEAR XML-RPC library
error_reporting(E_ERROR);
include_once("XML/RPC/Server.php");
error_reporting(E_ALL);

// Check if the library was found
if (class_exists("XML_RPC_Server")) {

  function de_linkm_newsticker_metaweblog_helper_getCategoryField() {

    // Parse schema
    $schema = $GLOBALS["de_linkm_newsticker_layouts"]["default"];

    if (isset($schema["fields"]["categories"]) && $schema["fields"]["categories"]["datatype"] == "multiselect") {
      // Multiple select
      $schema["fields"]["categories"]["name"] = "categories";
      return $schema["fields"]["categories"];

    } elseif (isset($schema["fields"]["category"]) && ((isset($schema["fields"]["category"]["widget"]) && $schema["fields"]["category"]["widget"] == "select") || $schema["fields"]["category"]["datatype"] == "multiselect")) {
      // Select or multiple select
      $schema["fields"]["category"]["name"] = "category";
      return $schema["fields"]["category"];

    } else {
      return false;
    }

  }

  function de_linkm_newsticker_metaweblog_helper_getCategories() {

    $categories = array();

    $field = de_linkm_newsticker_metaweblog_helper_getCategoryField();

    // Multiple select categories
    if ($field && isset($field["multiselect_selection_list"])) {
      foreach ($field["multiselect_selection_list"] as $key => $description) {
        $categories[$key] = $description;
      }
    }

    // Select categories
    if ($field && isset($field["widget_select_choices"])) {
      foreach ($field["widget_select_choices"] as $key => $description) {
        $categories[$key] = $description;
      }
    }

    if (count($categories) == 0) {
      $topic = mgd_get_topic($GLOBALS["de_linkm_newsticker_topic"]);
      $categories[$topic->name] = $topic->extra;
    }

    return $categories;

  }

  function de_linkm_newsticker_metaweblog_helper_categoriesToStorage($categories,$storage) {
    $categories_field = de_linkm_newsticker_metaweblog_helper_getCategoryField();
    if ($categories_field && is_array($categories)) {

      // Check for unknown categories
      foreach($categories as $key => $category) {
        if (isset($categories_field["multiselect_selection_list"])) {
          if (!array_key_exists($category,$categories_field["multiselect_selection_list"])) {
            unset($categories[$key]);
          }
        } else {
          if (!array_key_exists($category,$categories_field["widget_select_choices"])) {
            unset($categories[$key]);
          }
        }
      }

      // Store the categories
      if (isset($categories_field["location"]) && $categories_field["location"] == "parameter") {

        if ($categories_field["datatype"] == "multiselect") {
          $storage->parameter("midcom.helper.datamanager","data_".$categories_field["name"],implode(",",$categories));
        } else {
          // Note: this always stores the first one
          $storage->parameter("midcom.helper.datamanager","data_".$categories_field["name"],$categories[0]);
        }
      }
    }
  }

  function de_linkm_newsticker_metaweblog_helper_categoriesFromStorage($storage) {
    $categories_field = de_linkm_newsticker_metaweblog_helper_getCategoryField();
    $categories = null;

    if ($categories_field) {

      // Load the categories
      if (isset($categories_field["location"]) && $categories_field["location"] == "parameter") {
        if ($categories_field["datatype"] == "multiselect") {
          if ($storage->parameter("midcom.helper.datamanager","data_".$categories_field["name"])) {
            $categories = explode(",",$storage->parameter("midcom.helper.datamanager","data_".$categories_field["name"]));
          }
        } else {
          if ($storage->parameter("midcom.helper.datamanager","data_category")) {
            $categories[0] = $storage->parameter("midcom.helper.datamanager","data_category");
          }
        }
      }

      if (is_array($categories)) {
        // Check for unknown categories
        foreach($categories as $key => $category) {
          if (isset($categories_field["multiselect_selection_list"])) {
            if (!array_key_exists($category,$categories_field["multiselect_selection_list"])) {
              unset($categories[$key]);
            }
          } else {
            if (!array_key_exists($category,$categories_field["widget_select_choices"])) {
              unset($categories[$key]);
            }
          }
        }
      }
    }

    return $categories;

  }

  function de_linkm_newsticker_metaweblog_createURL($local_path="",$skip_prefix = false) {
    $server_url = $GLOBALS["midcom"]->get_host_name();
    if ($skip_prefix) {
      $prefix = $GLOBALS["midgard"]->self;
    } else {
      $prefix = str_replace("rpc/metaweblog/","",$GLOBALS["midgard"]->uri);
    }
    return $server_url.$prefix.$local_path;
  }

  // MetaWebLog API

  //  metaWeblog.newPost
  function de_linkm_newsticker_metaweblog_newPost($params) {
    global $xmlrpcerruser;
//error_log("metaWeblog.newPost called", 0);
    // Authenticate into Midgard
    $username = $params->getparam(1);
    $username = $username->scalarval();
    $password = $params->getparam(2);
    $password = $password->scalarval();

    if ($username && $password) {
      mgd_auth_midgard($username, $password, 0);
      $midgard = mgd_get_midgard();

      if ($midgard->user) {

        // Instantiate the article
        $article = mgd_get_article();
        $article->author = $midgard->user;
        $article->topic = $GLOBALS["de_linkm_newsticker_topic"];

        $struct = $params->getparam(3);
        // Populate article with data from the request
        if ($struct->kindOf() == "struct") {

          $categories = null;

          while (list($key,$value) = $struct->structeach()) {

            if ($key == "title") {
              $article->title = $value->scalarval();
            }

            if ($key == "description") {
              $article->content = $value->scalarval();
            }

            if ($key == "mt_excerpt") {
              $article->abstract = $value->scalarval();
            }

            if ($key == "categories") {
              $categories = XML_RPC_decode($value);
            }

          }
        }

        // Check that the document has some content
        if ($article->title) {

          $stat = false;
          $tries = 0;
          $maxtries = 99;
          while(!$stat && $tries < $maxtries) {
            $article->name = midcom_generate_urlname_from_string($article->title);
            if ($tries > 0) {
              // Append an integer if articles with same name exist
              $article->name .= sprintf("-%03d", $tries);
            }
            $stat = $article->create();
            $tries++;
          }
          
          // Invalidate the cache
          $GLOBALS['midcom']->cache->invalidate_all();
          
          // Update the Index
          // We create a datamanager for this.
          $config = $_MIDCOM->get_custom_context_data('config');
          $datamanager = new midcom_helper_datamanager($config->get('schemadb'));
          if (! $datamanager)
	      {
	          debug_add('Warning, failed to create a datamanager instance with this schemapath:' . $this->_config->get('schemadb'),
	              MIDCOM_LOG_WARN);
	      } 
          else 
          {
	          if (! $datamanager->init($article))
		      {
		          debug_add("Warning, failed to initialize datamanager for Article {$article->id}. See Debug Log for details.", MIDCOM_LOG_WARN);
		          debug_print_r('Article dump:', $article);
		      }
              else
              {
			        $indexer =& $GLOBALS['midcom']->get_service('indexer');
			        $indexer->index($datamanager);
              }
          }

          // Return status information
          if ($stat) {
            $article = mgd_get_article($stat);
            if ($categories) {
              de_linkm_newsticker_metaweblog_helper_categoriesToStorage($categories,$article);
            }
            return new XML_RPC_Response(new XML_RPC_Value($article->guid(), "string"));
          } else {
            return new XML_RPC_Response(0,$xmlrpcerruser+mgd_errno(),mgd_errstr());
          }

        } else {
          return new XML_RPC_Response(0,$xmlrpcerruser+mgd_errno(),"Missing title");
        }
      }
    }

    return new XML_RPC_Response(0,$xmlrpcerruser+mgd_errno(),mgd_errstr());

  }

  // metaWebLog.editPost
  function de_linkm_newsticker_metaweblog_editPost($params) {
//error_log("metaWeblog.editPost called", 0);
    global $xmlrpcerruser;

    // Authenticate into Midgard
    $username = $params->getparam(1);
    $username = $username->scalarval();
    $password = $params->getparam(2);
    $password = $password->scalarval();

    if ($username && $password) {
      mgd_auth_midgard($username, $password, 0);
      $midgard = mgd_get_midgard();

      if ($midgard->user) {

        // Instantiate the article
        $article_id = $params->getparam(0);
        $article_id = $article_id->scalarval();

        $article = mgd_get_object_by_guid($article_id);
        if ($article && get_class($article) == "MidgardArticle" && $article->topic == $GLOBALS["de_linkm_newsticker_topic"]) {


          $struct = $params->getparam(3);
          // Populate article with data from the request
          if ($struct->kindOf() == "struct") {

            while (list($key,$value) = $struct->structeach()) {

              if ($key == "title") {
                $article->title = $value->scalarval();
              }

              if ($key == "description") {
                $article->content = $value->scalarval();
              }

              if ($key == "mt_excerpt") {
                $article->abstract = $value->scalarval();
              }

              if ($key == "categories") {
                $categories = XML_RPC_decode($value);
                de_linkm_newsticker_metaweblog_helper_categoriesToStorage($categories,$article);
              }

            }

            // Save the article
            $stat = $article->update();

            // Invalidate the cache
            $GLOBALS['midcom']->cache->invalidate_all();

            // Update the Index
            // We create a datamanager for this.
            $config = $_MIDCOM->get_custom_context_data('config');
            $datamanager = new midcom_helper_datamanager($config->get('schemadb'));
            if (! $datamanager)
	        {
	            debug_add('Warning, failed to create a datamanager instance with this schemapath:' . $this->_config->get('schemadb'),
	                MIDCOM_LOG_WARN);
	        } 
            else 
            {
		        if (! $datamanager->init($article))
		        {
		            debug_add("Warning, failed to initialize datamanager for Article {$article->id}. See Debug Log for details.", MIDCOM_LOG_WARN);
		            debug_print_r('Article dump:', $article);
		        }
                else
                {
			        $indexer =& $GLOBALS['midcom']->get_service('indexer');
			        $indexer->index($datamanager);
                }
            }

            
            // Return status information
            if ($stat) {
              return new XML_RPC_Response(new XML_RPC_Value($article->id, "string"));
              //return new XML_RPC_Response(new XML_RPC_Value(1, "boolean"));
            } else {
              return new XML_RPC_Response(0,$xmlrpcerruser+mgd_errno(),mgd_errstr());
            }
          }
        }
      }
    }

    return new XML_RPC_Response(0,$xmlrpcerruser+mgd_errno(),mgd_errstr());

  }

  // metaWebLog.getPost
  function de_linkm_newsticker_metaweblog_getPost($params) {
//error_log("metaWeblog.getPost called", 0);
    global $xmlrpcerruser;

    // Authenticate into Midgard
    $username = $params->getparam(1);
    $username = $username->scalarval();
    $password = $params->getparam(2);
    $password = $password->scalarval();

    if ($username && $password) {
      mgd_auth_midgard($username, $password, 0);
      $midgard = mgd_get_midgard();

      if ($midgard->user) {

        // Instantiate the article
        $article_id = $params->getparam(0);
        $article_id = $article_id->scalarval();

        $article = mgd_get_object_by_guid($article_id);

        if ($article && get_class($article) == "MidgardArticle" && $article->topic == $GLOBALS["de_linkm_newsticker_topic"]) {
          $article_created = date("Ymd", $article->created)."T".date("H:i:s", $article->created);
          $response_array = array();
          $response_array["postid"] = new XML_RPC_Value($article->guid(),"string");
          $response_array["title"] = new XML_RPC_Value($article->title,"string");
          if ($article->content) {
            $response_array["description"] = new XML_RPC_Value($article->content,"string");
          }
          if ($article->abstract) {
            $response_array["mt_excerpt"] = new XML_RPC_Value($articles->abstract,"string");
          }

          $categories = de_linkm_newsticker_metaweblog_helper_categoriesFromStorage($article);
          if ($categories) {
            $response_array["categories"] = XML_RPC_encode($categories);
          }

          $response_array["permaLink"] = new XML_RPC_Value(de_linkm_newsticker_metaweblog_createURL($article->name.".html"),"string");
          $response_array["link"] = new XML_RPC_Value(de_linkm_newsticker_metaweblog_createURL($article->name.".html"),"string");
          $response_array["dateCreated"] = new XML_RPC_Value($article_created,"dateTime.iso8601");
          return new XML_RPC_Response(new XML_RPC_Value($response_array,"struct"));
        }
      }
    }

    return new XML_RPC_Response(0,$xmlrpcerruser+mgd_errno(),mgd_errstr());

  }

  // metaWeblog.getRecentPosts
  function de_linkm_newsticker_metaweblog_getRecentPosts($params) {
//error_log("metaWeblog.getRecentPosts called", 0);
    global $xmlrpcerruser;

    // Authenticate into Midgard
    $username = $params->getparam(1);
    if ($username) {
      $username = $username->scalarval();
    }
    $password = $params->getparam(2);
    if ($password) {
      $password = $password->scalarval();
    }

    if ($username && $password) {
      mgd_auth_midgard($username, $password, 0);
      $midgard = mgd_get_midgard();

      if ($midgard->user) {

        // Instantiate the article
        $article_count = $params->getparam(3);
        $article_count = $article_count->scalarval();

        $response = array();

        $articles = mgd_list_topic_articles($GLOBALS["de_linkm_newsticker_topic"],"reverse created");
        if ($articles) {
          while ($articles->fetch() && $article_count > 0) {
            $article = mgd_get_article($articles->id);

            $article_created = date("Ymd", $article->created)."T".date("H:i:s", $article->created);

            $response_array = array();
            $response_array["postid"] = new XML_RPC_Value($article->guid(),"string");
            $response_array["title"] = new XML_RPC_Value($article->title,"string");
            $response_array["permaLink"] = new XML_RPC_Value(de_linkm_newsticker_metaweblog_createURL($article->name.".html"),"string");
            $response_array["link"] = new XML_RPC_Value(de_linkm_newsticker_metaweblog_createURL($article->name.".html"),"string");
            if ($article->content) {
              $response_array["description"] = new XML_RPC_Value($articles->content,"string");
            }
            if ($article->abstract) {
              $response_array["mt_excerpt"] = new XML_RPC_Value($articles->abstract,"string");
            }

            $categories = de_linkm_newsticker_metaweblog_helper_categoriesFromStorage($article);
            if ($categories) {
              $response_array["categories"] = XML_RPC_encode($categories);
            }

            $response_array["dateCreated"] = new XML_RPC_Value($article_created,"dateTime.iso8601");

            $response[] = new XML_RPC_Value($response_array,"struct");

            $article_count--;
          }
        }

        return new XML_RPC_Response(new XML_RPC_Value($response,"array"));
      }
    }

    return new XML_RPC_Response(0,$xmlrpcerruser+mgd_errno(),mgd_errstr());

  }

  // metaWeblog.getCategories
  function de_linkm_newsticker_metaweblog_getCategories($params) {
//error_log("metaWeblog.getCategories called", 0);
    global $xmlrpcerruser;

    // Authenticate into Midgard
    $username = $params->getparam(1);
    $username = $username->scalarval();
    $password = $params->getparam(2);
    $password = $password->scalarval();

    if ($username && $password) {
      mgd_auth_midgard($username, $password, 0);
      $midgard = mgd_get_midgard();

      if ($midgard->user) {

        $categories = de_linkm_newsticker_metaweblog_helper_getCategories("xml-rpc");
        $category_structs = array();
        foreach ($categories as $key => $description) {
          $category_structs[$key] = new XML_RPC_Value(array(
              "description" => new XML_RPC_Value($description,"string"),
              "htmlUrl" => new XML_RPC_Value(de_linkm_newsticker_metaweblog_createURL(),"string"),
              "rssUrl" => new XML_RPC_Value(de_linkm_newsticker_metaweblog_createURL("rss.xml"),"string")
            ),"struct");
        }
        // Return cats
        return new XML_RPC_Response(
          new XML_RPC_Value($category_structs,"struct")
        );
      }
    }
  }

  //  metaWeblog.newMediaObject
  function de_linkm_newsticker_metaweblog_newMediaObject($params) {
    global $xmlrpcerruser;

    // Authenticate into Midgard
    $username = $params->getparam(1);
    $username = $username->scalarval();
    $password = $params->getparam(2);
    $password = $password->scalarval();

    if ($username && $password) {
      mgd_auth_midgard($username, $password, 0);
      $midgard = mgd_get_midgard();

      if ($midgard->user) {

        $struct = $params->getparam(3);
        // Populate with data from the request
        if ($struct->kindOf() == "struct") {

          while (list($key,$value) = $struct->structeach()) {

            if ($key == "name") {
              $attachment_name = basename($value->scalarval()) ;
            }

            if ($key == "type") {
              $attachment_type = $value->scalarval();
            }

            if ($key == "bits") {
              // File contents base64 encoded but XML-RPC library handles that
              $attachment_content = $value->scalarval();
            }

          }
        }

        // Check that the attachment is correct
        if ($attachment_name && $attachment_type && $attachment_content) {

          $attachment_id = false;
          $topic = mgd_get_topic($GLOBALS["de_linkm_newsticker_topic"]);
          $attachments = $topic->listattachments();
          if ($attachments) {
            while ($attachments->fetch()) {
              if ($attachments->name == $attachment_name) {
                $attachment_id = $attachments->id;
              }
            }
          }

          if (!$attachment_id) {
            $attachment_id = $topic->createattachment($attachment_name,$attachment_name,$attachment_type);
          }

          if ($attachment_id) {

            // Write the contents to the attachment
            $attachment_handle = mgd_open_attachment($attachment_id,"w");
            fputs($attachment_handle, $attachment_content);
            fclose($attachment_handle);

            $attachment = mgd_get_attachment($attachment_id);
            $attachment_url = de_linkm_newsticker_metaweblog_createURL("midcom-serveattachmentguid-".$attachment->guid()."/".$attachment->name,true);

            return new XML_RPC_Response(new XML_RPC_Value(array(
              "url" => new XML_RPC_Value($attachment_url,"string"),
              "guid" => new XML_RPC_Value($attachment->guid(), "string"),
            ), "struct"));
          } else {
            return new XML_RPC_Response(0,$xmlrpcerruser+mgd_errno(),mgd_errstr());
          }

        } else {
          return new XML_RPC_Response(0,$xmlrpcerruser+mgd_errno(),"Missing title");
        }
      }
    }

    return new XML_RPC_Response(0,$xmlrpcerruser+mgd_errno(),mgd_errstr());

  }

  // Blogger API

  // blogger.deletePost
  function de_linkm_newsticker_blogger_deletePost($params) {
    global $xmlrpcerruser;

    // Authenticate into Midgard
    $username = $params->getparam(2);
    $username = $username->scalarval();
    $password = $params->getparam(3);
    $password = $password->scalarval();

    if ($username && $password) {
      mgd_auth_midgard($username, $password, 0);
      $midgard = mgd_get_midgard();

      if ($midgard->user) {

        // Instantiate the article
        $article_id = $params->getparam(1);
        $article_id = $article_id->scalarval();

        $article = mgd_get_object_by_guid($article_id);

        if ($article && get_class($article) == "MidgardArticle" && $article->topic == $GLOBALS["de_linkm_newsticker_topic"]) {

          $guid = $article->guid();
          $stat = midcom_helper_purge_object($guid);

          // Invalidate the cache
          $GLOBALS['midcom']->cache->invalidate_all();          
          
     	  // Update the index
		  $indexer =& $GLOBALS['midcom']->get_service('indexer');
		  $indexer->delete($guid);

          // Return status information
          if ($stat) {
            $article = mgd_get_article($stat);
            return new XML_RPC_Response(new XML_RPC_Value(1, "boolean"));
          } else {
            return new XML_RPC_Response(0,$xmlrpcerruser+mgd_errno(),mgd_errstr());
          }

        }
      }
    }
  }

  // blogger.getUsersBlogs
  function de_linkm_newsticker_blogger_getUsersBlogs($params) {
    global $xmlrpcerruser;

    // Authenticate into Midgard
    $username = $params->getparam(1);
    $username = $username->scalarval();
    $password = $params->getparam(2);
    $password = $password->scalarval();

    if ($username && $password) {
      mgd_auth_midgard($username, $password, 0);
      $midgard = mgd_get_midgard();

      if ($midgard->user) {

        $topic = mgd_get_topic($GLOBALS["de_linkm_newsticker_topic"]);

        return new XML_RPC_Response(new XML_RPC_Value(array(
              new XML_RPC_Value(array(
                "blogid" => new XML_RPC_Value($topic->guid(), "string"),
                "blogName" => new XML_RPC_Value($topic->extra, "string"),
                "url" => new XML_RPC_Value(de_linkm_newsticker_metaweblog_createURL(),"string"),
              ), "struct")
          ), "array"));
      }
    }
  }
}
?>
