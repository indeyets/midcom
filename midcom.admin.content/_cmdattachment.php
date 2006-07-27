<?php

class midcom_admin_content__cmdattachment {
    
    var $_argv;
    var $_contentadm;
    var $_topic;
    var $_view;
    var $_attachment_id;
    
    function midcom_admin_content__cmdattachment ($argv, &$contentadm) {
        $this->_argv = $argv;
        $this->_contentadm = &$contentadm;
        $this->_topic = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
    }


    function execute () {
        debug_push("Content Admin, Attachment Command Execute");
        
        $_MIDCOM->auth->require_do('midgard:attachments', $this->_topic);
        $_MIDCOM->auth->require_do('midgard:update', $this->_topic);
        
        if (count ($this->_argv) == 0)
            $this->_argv[] = "show"; 
        
        switch ($this->_argv[0]) {
            case "show":
                $this->_view = "attachments_index";
                debug_pop();
                return true;
            
            case "create":
                $this->_view = "attachments_create";
                debug_pop();
                return true;

            case "createok":
                debug_pop();
                return $this->_admin_attachments_upload();

            case "delete":
                if (count($this->_argv) != 2) {
                    $this->_contentadm->errstr = "This Attachnment does not exist.";
                    $this->_contentadm->errcode = MIDCOM_ERRNOTFOUND;
                    debug_add($this->_contentadm->errstr);
                    debug_pop();
                    return false;
                }
                $this->_attachment_id = $this->_argv[1];
                $this->_view = "attachments_delete";
                debug_pop();
                return true;

            case "deleteok":
                if (count($this->_argv) != 2) {
                    $this->_contentadm->errstr = "This Attachnment does not exist.";
                    $this->_contentadm->errcode = MIDCOM_ERRNOTFOUND;
                    debug_add($this->_contentadm->errstr);
                    debug_pop();
                    return false;
                }
                $this->_attachment_id = $this->_argv[1];
                debug_pop();
                return $this->_admin_attachments_delete();

            default:
                $this->_contentadm->errstr = "Unkown Command in Execute Handler, this should not happen.";
                $this->_contentadm->errcode = MIDCOM_ERRCRIT;
                debug_add($this->_contentadm->errstr);
                debug_pop();
                return false;
        };
        
    }

    function _admin_attachments_upload () {
        debug_push ("Data Admin, Attachment Upload");
        $this->_view = "attachments_index";

        if (array_key_exists("f_cancel", $_REQUEST)) {
            debug_add("Cancel pressed");
            debug_pop();
            return true;
        }

        if (!array_key_exists("f_submit", $_REQUEST)) {
            $this->_contentadm->errstr = "The submit button was not in the request data.";
            $this->_contentadm->errcode = MIDCOM_ERRCRIT;
            debug_add($this->_contentadm->errstr);
            debug_pop();
            return false;
        }
        

        // Uploaded files are in _FILES autoglobal on PHP 4.3+ and _REQUEST on earlier
        if (version_compare(phpversion(),"4.3.0",">=")) 
            $the_file = $_FILES["f_file"];
        else
            $the_file = $_REQUEST["f_file"];

        if (trim($the_file["name"]) == "") {
            $this->_contentadm->msg = "Error: No file was uploaded.";
            debug_pop();
            return true;
        }


        if (trim($_REQUEST["f_filename"]) == "")
            $name = trim($the_file["name"]);
        else
            $name = trim($_REQUEST["f_filename"]);

        // $topic = mgd_get_topic ($this->_attachment_topic_id);
        $attachment = $this->_topic->getattachment($name);
        
        if ($attachment !== false) {
            $this->_contentadm->msg = "Error: an Attachment with this name already exists.";
            debug_pop();
            return true;
        }
        
        $id = $this->_topic->createattachment($name, trim($_REQUEST["f_title"]),
          $the_file["type"]);
        
        if (!$id) {
            $this->_contentadm->msg = "Error: Could not create Attachment: " . mgd_errstr();
            return true;
        }
        $attachment = mgd_get_attachment($id);
        
        $dst = $this->_topic->openattachment($name);
        $src = fopen($the_file["tmp_name"], "r");
        while (!feof($src))
            fwrite($dst,fread($src,65536));
        
        fclose($dst);
        fclose($src);
        $this->_contentadm->msg = "Attachment created successfully.";
        $GLOBALS['midcom']->cache->invalidate($attachment->guid());
        debug_add("Invalidated Midcom Cache.");
        debug_pop();
        return true;
    }

    function _admin_attachments_delete () {
        debug_push("Data Admin, Attachment Delete");

        $this->_view = "attachments_index";
        $this->_contentadm->msg = "";
        
        if (array_key_exists("f_cancel", $_REQUEST)) {
            debug_add("Cancel pressed");
            debug_pop();
            return true;
        }

        if (!array_key_exists("f_submit", $_REQUEST)) {
            $this->_contentadm->errstr = "The submit button was not in the request data.";
            $this->_contentadm->errcode = MIDCOM_ERRINTERNAL;
            debug_add($this->_contentadm->errstr);
            debug_pop();
            return false;
        }
        
        $attachment = mgd_get_attachment($this->_attachment_id);
        if (! mgd_delete_extensions($attachment)) {
            $this->_contentadm->msg = "Error: Could not delete Attachment: " . mgd_errstr();
            debug_pop();
            return true;
        }
        
        if (! mgd_delete_attachment($attachment->id)) {
            $this->_contentadm->msg = "Error: Could not delete Attachment: " . mgd_errstr();
            debug_pop();
            return true;
        }
        
        $this->_contentadm->msg = "Done.";
        $GLOBALS['midcom']->cache->invalidate($attachment->guid());
        debug_add("Invalidated Midcom Cache.");
        debug_pop();
        return true;
    }

    function show () {
        eval ("\$this->_show_$this->_view();");
    }

    function _show_attachments_index () {
        global $view;
        
        $attachments = mgd_fetch_to_array($this->_topic->listattachments());
        
        if (!$attachments) {
            midcom_show_style("attachments-index-none");
        } else {
            midcom_show_style("attachments-index");
            foreach ($attachments as $id) {
                $view = mgd_get_attachment ($id);
                midcom_show_style("attachments-index-element");
            }
            midcom_show_style("attachments-index-");
        }
    }

    function _show_attachments_create () {
        midcom_show_style("attachments-create");
    }
    
    function _show_attachments_delete () {
        $GLOBALS["view"] = mgd_get_attachment ($this->_attachment_id);
        midcom_show_style("attachments-delete");
    }
}

?>