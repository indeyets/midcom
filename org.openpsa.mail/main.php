<?php
/**
 * Class for handling email encode/decode and sending
 *
 * Gracefully degrades in functionality if certain PEAR libraries are
 * not available. Based on the old OpenPSA mailer code.
 * @package org.openpsa.mail
 */
class org_openpsa_mail extends midcom_baseclasses_components_purecode
{
    var $subject;     //string, simpler access to headers['Subject']
    var $body;        //text
    var $headers;     //array, key is header name, value is header data
    var $from;        //string, simpler access to headers['From']
    var $to;          //string, simpler access to headers['To']

    var $htmlBody;    //text, HTML body (of MIME/multipart message)  reference to below
    var $html_body;    //text, HTML body (of MIME/multipart message) 
    var $attachments; /* array, primary keys are int, secondary keys for decoded array are: 'name' (filename), 'content' (file contents) and 'mimetype'. 
                       Array for encoding may in stead of 'content' have 'file' which is path to the file to be attached */
    var $embeds;      //array, like attachments but used for inline images
    var $encoding;    //string, character encoding in which the texts etc are
    var $allow_only_html; //Allow to send only HTML body
    var $embed_css_url; //Try to embed also css url() files as inline images (seems not to work with most clients so defaults to false)

    //Internal, do not touch unless you know what you're doing
    var $_backend;    //The backend object
    var $__debug;     //bool, output debug information
    var $__mime;      //object, (Mail_mime / Mail_mimeDecode) holder
    var $__mail;      //object, (Mail) holder
    var $__mailErr;   //bool/object, send error status
    var $__iconv;     //bool, when decoding mails, try to convert to desired charset.
    var $__headConverted; //bool, used internally to determine if headers have already been converted
    var $__orig_encoding;  //string, original encoding of the message

    function org_openpsa_mail()
    {
        $this->_component = 'org.openpsa.mail';
        parent::midcom_baseclasses_components_purecode();
        $this->_initialize_pear();
        
        $this->attachments = array();
        $this->embeds = array();
        $this->headers = array();
        $this->headers['Subject'] = null;
        $this->subject =& $this->headers['Subject'];
        $this->headers['From'] = null;
        $this->from =& $this->headers['From'];
        $this->headers['To'] = null;
        $this->to =& $this->headers['To'];
        $this->headers['Cc'] = null;
        $this->cc =& $this->headers['Cc'];
        $this->headers['Bcc'] = null;
        $this->bcc =& $this->headers['Bcc'];
        $this->headers['User-Agent'] = "OpenPSA / Midgard " . mgd_version();
        $this->headers['X-Originating-Ip'] = $_SERVER['REMOTE_ADDR'];
        $this->__mailErr = false;
        $this->htmlBody =& $this->html_body;
          
        $this->encoding = $this->_i18n->get_current_charset();
          
        //Try to convert between charsets 
        $this->__iconv = true;
        $this->__orig_encoding = '';
        $this->__headConverted = false;

        //$this->__debug = true;
        $this->_backend = false;
        $this->allow_only_html = false;
        $this->embed_css_url = false;
        return true;
    }

    /**
     * Returns true/false depending on whether we can send attachments
     */
    function can_attach()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (class_exists('Mail_mime'))
        {
            debug_add('Mail_mime exists: returning true');
            debug_pop();
            return true;
        }
        debug_pop();
        return false;
    }

    /**
     * Returns true/false depending on whether we can send HTML mails
     *
     * In fact by manually setting the headers oone can always send
     * single part HTML emails but the purpose of this class was to make things simpler
     */
    function can_html()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (class_exists('Mail_mime'))
        {
            debug_add('Mail_mime exists: returning true');
            debug_pop();
            return true;
        }
        debug_pop();
        return false;
    }

    /**
     * Creates a Mail_mime instance and adds parts to it as neccessary
     */
    function initMail_mime()
    {
        return $this->initMail_mime();
    }
    function init_mail_mime()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (!class_exists('Mail_mime'))
        {
            debug_add('Mail_mime does not exist, aborting');
            debug_pop();
            return false;
        }
        $this->__mime = new Mail_mime("\n");
        $mime =& $this->__mime;
          
        $mime->_build_params['html_charset'] = strtoupper($this->encoding);
        $mime->_build_params['text_charset'] = strtoupper($this->encoding);
        $mime->_build_params['head_charset'] = strtoupper($this->encoding);
        $mime->_build_params['text_encoding'] = '8bit';
          
        //TODO: Convert to use MidCOM debugger
        if ($this->__debug)
        {
            echo "DEBUG: mime, before <pre>\n";
             print_r($mime);
             echo "</pre>\n"; reset ($mime);
        }

        if (   isset($this->html_body)
            && strlen($this->html_body)>0)
        {
           $mime->setHTMLBody($this->html_body);
        }
        if (   isset($this->body)
            && strlen($this->body)>0)
        {
           $mime->setTxtBody($this->body);
        }
        if (   is_array($this->attachments)
            && (count($this->attachments)>0))
        {
            reset($this->attachments);
            while (list ($k, $att) = each ($this->attachments))
            {
                if (!isset($att['mimetype']) || $att['mimetype'] == null)
                {
                    $att['mimetype'] = "application/octet-stream";
                }
                
                if (isset($att['file']) && strlen($att['file'])>0)
                {
                    $aRet = $mime->addAttachment($att['file'], $att['mimetype'], $att['name'], true);
                } else if (isset($att['content']) && strlen($att['content'])>0)
                {
                    $aRet = $mime->addAttachment($att['content'], $att['mimetype'], $att['name'], false);
                }
                 //TODO: Convert to use MidCOM debugger
                if ($this->__debug)
                {
                   if ($aRet === true)
                   {
                         echo "DEBUG: mail->initMail_mime(): attachment ".$att['name']." added<br>\n";
                   } else {
                         echo "DEBUG: mail->initMail_mime(): failed to add attachment ".$att['name']." PEAR output <pre>\n";
                         print_r($aRet);
                         echo "</pre>\n";
                   }
                }
            }
        }
        if (   is_array($this->embeds)
            && (count($this->embeds)>0))
        {
            reset($this->embeds);
            while (list ($k, $att) = each ($this->embeds))
            {
                if (!isset($att['mimetype']) || $att['mimetype'] == null)
                {
                    $att['mimetype'] = "application/octet-stream";
                }
                if (isset($att['file']) && strlen($att['file'])>0)
                {
                    $aRet = $mime->addHTMLImage($att['file'], $att['mimetype'], $att['name'], true);
                } else if (isset($att['content']) && strlen($att['content'])>0)
                {
                    $aRet = $mime->addHTMLImage($att['content'], $att['mimetype'], $att['name'], false);
                }
                 //TODO: Convert to use MidCOM debugger
                if ($this->__debug)
                {
                   if ($aRet === true)
                   {
                         echo "DEBUG: mail->initMail_mime(): attachment ".$att['name']." embedded<br>\n";
                   } else {
                         echo "DEBUG: mail->initMail_mime(): failed to embed attachment ".$att['name']." PEAR output <pre>\n";
                         print_r($aRet);
                         echo "</pre>\n";
                   }
                }
            }
        }

        if ($this->embed_css_url)
        {
            $this->_fix_mime_css_embeds();
        }

        //TODO: Convert to use MidCOM debugger
        if ($this->__debug)
        {
             echo "DEBUG: mime, after <pre>\n";
             print_r($mime);
             echo "</pre>\n"; reset ($mime);
        }

        debug_pop();
        return $this->__mime;
    }

    function _fix_mime_css_embeds()
    {
        //Hacked support for inline CSS url() type images
        if (   is_array($this->__mime->_html_images)
            && !empty($this->__mime->_html_images))
        {
            reset($this->__mime->_html_images);
            foreach($this->__mime->_html_images as $image)
            {
                $regex = "#url\s*\(([\"'«])?" . preg_quote($image['name'], '#') . "\\1?\)#i";
                //debug_add("regex={$regex}");
                $rep = 'url(\1cid:' . $image['cid'] .'\1)';
                //debug_add("rep={$rep}");
                $this->__mime->_htmlbody = preg_replace($regex, $rep, $this->__mime->_htmlbody);
            }
            reset($this->__mime->_html_images);
        }
    }

    /**
     * Decodes HTML entities to their respective characters
     */
    function html_entity_decode( $given_html, $quote_style = ENT_QUOTES )
    {
        $trans_table = array_flip(get_html_translation_table( HTML_SPECIALCHARS, $quote_style ));
        $trans_table['&#39;'] = "'";
        $trans_table['&nbsp;'] = ' ';
        return ( strtr( $given_html, $trans_table ) );
    }

    /**
     * Tries to convert HTML to plaintext
     */
    function html2text($html)
    {
        if (class_exists('Html2Text'))
        {
            $html = preg_replace("/<!DOCTYPE[^>]*>\n?/", '', $html);
            $decoder = new Html2Text($html, 72);
            $text = $decoder->convert();
            $text = $this->html_entity_decode($text);
        }
        else
        {
            //Convert various newlines to unix ones
            $text = preg_replace('/\x0a\x0d|\x0d\x0a|\x0d/', "\n", $html);
            //convert <br/> tags to newlines
            $text = preg_replace("/<br\s*\\/?>/i","\n", $text);
            //strip all STYLE and SCRIPT tags, including their content
            $text = preg_replace('/(<style[^>]*>.*?<\\/style>)/si', '', $text);
            $text = preg_replace('/(<script[^>]*>.*?<\\/script>)/si', '', $text);
            //strip comments
            $text = preg_replace('/<!--.*?-->/s', '', $text);
            //strip all remaining tags, just the tags
            $text = preg_replace('/(<[^>]*>)/', '', $text);
            
            //Decode entities
            $text = $this->html_entity_decode($text);
            
            //Trim whitespace from end of lines
            $text = preg_replace("/[ \t\f]+$/m", '', $text);
            //Trim whitespace from beginning of lines
            $text = preg_replace("/^[ \t\f]+/m", '', $text);
            //Convert multiple concurrent spaces to one
            $text = preg_replace("/[ \t\f]+/", ' ', $text);
            //Strip extra linebreaks
            $text = preg_replace("/\n{3,}/", "\n\n", $text);
            //Wrap to RFC width
            $text = wordwrap($text, 72, "\n");
        }

        return trim($text);
    }


    /**
     * Decodes a Mail_mime part (recursive)
     */
    function partDecode(&$part)
    {
        return $this->part_decode($part);
    }
    function part_decode(&$part)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        //Check for subparts and process them if they exist
        if (   isset($part->parts)
            && is_array($part->parts)
            && count($part->parts) > 0)
        {
            reset ($part->parts);
            while (list ($k, $subPart) = each ($part->parts))
            {
                //We might recurse quite deep so pop here.
                debug_pop();
                $this->part_decode($part->parts[$k]);
            }
            return;
        }
        
        //Check attachment vs body
        if (   !isset($part->disposition)
            || (   $part->disposition == 'inline'
                && (   isset($part->ctype_primary)
                    && strtolower($part->ctype_primary) == 'text'
                    )
                )
            )
        {
            //part is (likely) body
            if (   isset($part->ctype_parameters['charset'])
                && !$this->__orig_encoding)
            {
                $this->__orig_encoding = $part->ctype_parameters['charset'];
            }
            switch (strtolower($part->ctype_secondary))
            {
                default:
                case "plain": //Always use plaintext body if found
                    $this->body =& $part->body;
                    $this->__textBodyFound = true;
                    //Convert character encodings
                    if (   isset($part->ctype_parameters['charset'])
                        && (strtolower($part->ctype_parameters['charset']) != strtolower($this->encoding))
                        && function_exists('iconv')
                        && $this->__iconv)
                    {
                        $temp = iconv($part->ctype_parameters['charset'], $this->encoding, $part->body);
                        if ($temp !== false)
                        {
                              $part->body = $temp;
                        }
                    }
                    break;
                case "html": 
                    if (!$this->__textBodyFound)
                    {
                        //Try to translate HTML body only if plaintext alternative is not available
                        $this->body = $this->html2text($part->body);
                        //Convert character encodings
                        //TODO: refactor (all conversions) to a method
                        if (   isset($part->ctype_parameters['charset'])
                            && (strtolower($part->ctype_parameters['charset']) != strtolower($this->encoding))
                            && function_exists('iconv')
                            && $this->__iconv)
                        {
                            $temp = iconv($part->ctype_parameters['charset'], $this->encoding, $this->body);
                            if ($temp !== false)
                            {
                                $this->body = $temp;
                            }
                        }
                    }
                    $this->html_body =& $part->body;
                    //Convert character encodings
                    //TODO: refactor (all conversions) to a method
                    if (   isset($part->ctype_parameters['charset'])
                        && (strtolower($part->ctype_parameters['charset']) != strtolower($this->encoding))
                        && function_exists('iconv')
                        && $this->__iconv)
                    {
                        $temp = iconv($part->ctype_parameters['charset'], $this->encoding, $part->body);
                        if ($temp !== false)
                        {
                            $part->body = $temp;
                        }
                    }
                    break;
            }
        }
        else
        {
            //part is (likely) attachment
            /* PONDER: Should we distinguish between attachments and embeds (NOTE: adds complexity to applications
             * using this library since they need the check both arrays and those that actually need to distinguish between
             * the two can also check the attachment['part'] object for details).
             */
            $dataArr = array();
            $dataArr['part'] =& $part;
            $dataArr['mimetype'] = $part->ctype_primary."/".$part->ctype_secondary;
            $dataArr['content'] =& $dataArr['part']->body;
            if ($part->d_parameters['filename'])
            {
                $dataArr['name'] = $part->d_parameters['filename'];
            } else if ($part->ctype_parameters['name'])
            {
                $dataArr['name'] = $part->ctype_parameters['name'];
            }
            else
            {
                $dataArr['name'] = "unnamed";
            }
            $this->attachments[] = $dataArr;
        }
        debug_pop();
    }

    function _compatibility_checks()
    {
        //Some (notably US based) mail providers report a charset without regard for the actual data, we skip conversions if we see them
        $stupid_domains = $this->_config->get('no_iconv_domains');
        if (!is_array($stupid_domains))
        {
            return;
        }
        foreach ($stupid_domains as $domain)
        {
            if (stristr($this->from, "@{$domain}"))
            {
                $this->__iconv = false;
                return;
            }
        }
        return;
    }


     /**
      * Decodes MIME content from $this->body
      */
    function mime_decode()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (!class_exists('Mail_mimeDecode'))
        {
            debug_add('Cannot decode without Mail_mimeDecode, aborting', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        // Make sure we only have NL linebreaks
        $this->body = preg_replace("/\n\r|\r\n|\r/","\n", $this->body);

        /* Check if we have mime boundary, in that case we need to make sure it does not exhibit certain
        corner cases which choke mail_mimedecode */
        if (preg_match("/Content-Type: multipart\/mixed;\n?\s+boundary=([\"']?)(.*?)(\\1)\n/", $this->body, $boundary_matches))
        {
            $boundary = $boundary_matches[2];
            if (   strpos($boundary, '"')
                || strpos($boundary, "'"))
            {
                // Any quote inside the boudary value will choke mail_mimedecode
                debug_add('"corrupt" (as in will choke mail_mimedecode) boundary detected, trying to fix', MIDCOM_LOG_WARN);
                // Se we replace them with dashes
                $new_boundary = str_replace(array('"', "'"), array('-', '-'), $boundary);
                debug_add("new_boundary=\"{$new_boundary}\"");
                // Check if our new boundary exists inside the body (unlikely but I would *hate* to debug such issue)
                while (strpos($this->body, $new_boundary))
                {
                    debug_add('Our fixed new_boundary already exists inside the body, making two random changes to it', MIDCOM_LOG_WARN);
                    //Replace two characters randomly in the boundary
                    $a = chr(rand(97,122));
                    $b = chr(rand(97,122));
                    $new_boundary  = substr_replace($new_boundary, $a, rand(0, strlen($new_boundary)), 1);
                    $new_boundary  = substr_replace($new_boundary, $b, rand(0, strlen($new_boundary)), 1);
                    debug_add("new_boundary=\"{$new_boundary}\"");
                }
                // Finally we have a new workable boundary, replace all instances of the old one with the new
                $this->body = str_replace($boundary, $new_boundary, $this->body);
            }
        }
        
        $args = array();
        $args['include_bodies'] = true;
        $args['decode_bodies'] = true;
        $args['decode_headers'] = true;
        $args['crlf'] = "\n";
        $args['input'] = $this->body;

        $decoder = new Mail_mimeDecode($this->body);
        $this->__mime = $decoder->decode($args);
        $mime =& $this->__mime;

        if (is_a($mime, 'pear_error'))
        {
            $this->__mailErr =& $this->__mime;
            return false;
        }

        if (is_array($mime->headers))
        {
            reset ($mime->headers);
            foreach ($mime->headers as $k => $v)
            {
                $this->headers[str_replace(" ","-",ucwords(str_replace("-"," ",$k)))] =& $mime->headers[$k];
            }
        }
        $this->subject =& $this->headers['Subject'];                  
        $this->from =& $this->headers['From'];
        $this->to =& $this->headers['To'];

        $this->_compatibility_checks();

        if (   isset ($mime->parts)
            && is_array($mime->parts)
            && count ($mime->parts)>0)
        {
            reset ($mime->parts);
            while (list ($k, $part) = each ($mime->parts))
            {
                $this->part_decode($mime->parts[$k]);
            }
        }
        else
        { //No parts, just body
            switch (strtolower($mime->ctype_secondary))
            {
                default:
                case "plain":
                   $this->body =& $mime->body;
                break;
                case "html":
                   $this->html_body =& $mime->body;
                   $this->body = $this->html2text($mime->body);         
                break;
            }
            if (   isset($mime->ctype_parameters['charset'])
                && !$this->__orig_encoding)
            {
                $this->__orig_encoding = $mime->ctype_parameters['charset'];
            }
            //Convert character encoding on the fly to DB encoding (if different)
            //TODO: refactor (all conversions) to a method
            if (   $this->__orig_encoding
                && ($this->__orig_encoding != strtolower($this->encoding))
                && function_exists('iconv')
                && $this->__iconv)
            {
                if ($this->html_body)
                {
                    $temp = iconv($this->__orig_encoding, $this->encoding, $this->html_body);
                    if ($temp !== false)
                    {
                        $this->html_body = $temp;
                    }
                }
                $temp = iconv($this->__orig_encoding, $this->encoding, $this->body);
                if ($temp !== false)
                {
                    $this->body = $temp;
                }
            }
        }

        //Convert certain headers and attchement names to correct charset
        if ($this->__orig_encoding && (strtolower($this->__orig_encoding) != strtolower($this->encoding)) && function_exists('iconv') && $this->__iconv && !$this->__headConverted) {
           //Supposedly subject and from use same encoding as bodies (they might not, though...)
           if (isset($this->headers['Subject'])) {
               $temp = iconv($this->__orig_encoding, $this->encoding, $this->headers['Subject']);
               if ($temp !== false) {
                  $this->headers['Subject'] = $temp;
               }
           }
           if (isset($this->headers['From'])) {
               $temp = iconv($this->__orig_encoding, $this->encoding, $this->headers['From']);
               if ($temp !== false) {
                  $this->headers['From'] = $temp;
               }
           }
           if (isset($this->headers['To'])) {
               $temp = iconv($this->__orig_encoding, $this->encoding, $this->headers['To']);
               if ($temp !== false) {
                  $this->headers['To'] = $temp;
               }
           }
           if (isset($this->headers['Cc'])) {
               $temp = iconv($this->__orig_encoding, $this->encoding, $this->headers['Cc']);
               if ($temp !== false) {
                  $this->headers['Cc'] = $temp;
               }
           }
           if (isset($this->attachments) && is_array($this->attachments)) {
                reset($this->attachments);
                while (list ($k, $attArr) = each ($this->attachments)) {
                   if (isset($attArr['name'])) {
                        $temp = iconv($this->__orig_encoding, $this->encoding, $attArr['name']);
                       if ($temp !== false) { //Safeguard against iconv errors
                           $this->attachments[$k]['name'] = $temp;
                       }
                   }
                    //PONDER: Should we check attchement type and if it's text then decode the body as well ?
              } reset($this->attachments);
           }

           $this->__headConverted = true;
        }

        //Strip whitespace around bodies
        $this->body = ltrim(rtrim($this->body));
        $this->html_body = ltrim(rtrim($this->html_body));

        //TODO Figure if decode was successfull or not and return true/false in stead
        debug_pop();
        return $mime;
    }

    /**
     * Quoted-Printable encoding for message subject if neccessary
     */
    function encode_subject()
    {
        preg_match_all("/[^\x21-\x7e]/", $this->subject, $matches);
        if (count ($matches[0])>0) {
            $cache = array();
            $newSubj = $this->subject;
            while (list ($k, $char) = each ($matches[0]))
            {
                $code = "=".dechex(ord($char));
                $hex = str_pad(strtoupper(dechex(ord($char))),2,"0", STR_PAD_LEFT);
                if (isset($cache[$hex]))
                {
                    continue;
                }
                $newSubj = str_replace($char, '=' . $hex, $newSubj);
                $cache[$hex] = true;
            }
            $this->subject = '=?' . strtoupper($this->encoding) . '?Q?' . $newSubj . '?=';
        }
    }

     /**
      * Prepares message for sending
      *
      * Calls MIME etc encodings as neccessary.
      */
     function prepare()
     {
        debug_push_class(__CLASS__, __FUNCTION__);
        //Translate newlines
        $this->body = preg_replace("/\n\r|\r\n|\r/","\n", $this->body);
        $this->html_body = preg_replace("/\n\r|\r\n|\r/","\n", $this->html_body);

        //Try to translate HTML-only body to plaintext as well
        if (   strlen($this->body) == 0
            && strlen($this->html_body) > 0
            && !$this->allow_only_html)
        {
           $this->body = $this->html2text($this->html_body);
        }

        //Check whether it's neccessary to initialize MIME
        if (    (
                    (   is_array($this->embeds)
                     && (count($this->embeds)>0)
                    )
                ||  (   is_array($this->attachments)
                     && (count($this->attachments)>0)
                    )
                || $this->html_body
                )
                && !is_object($this->__mime)
            )
        {
            debug_add('Initializing Mail_mime');
            $this->init_mail_mime();
        }

        //If MIME has been initialized create body and headers
        if (is_object($this->__mime))
        {
            //Create mime body and headers
            debug_add('Mail_mime object found, generating body and headers');
            $mime =& $this->__mime;
            $this->body = $mime->get();
            $this->headers = $mime->headers($this->headers);
        }

        // Encode subject (if neccessary) and set Content-Type (if not set already)
        $this->encode_subject();
        if (   !isset($this->headers['Content-Type'])
            || $this->headers['Content-Type'] == null)
        {
            $this->headers['Content-Type'] = "text/plain; charset={$this->encoding}";
        }
        // Set Mime-version if not set already
        if (   !isset($this->headers['Mime-version'])
            || $this->headers['Mime-version'] == null)
        {
            $this->headers['Mime-version'] = '1.0';
        }
        
        //Make sure we don't send any empty headers
        reset ($this->headers);
        foreach ($this->headers as $header => $value)
        {
            if (empty($value))
            {
                unset ($this->headers[$header]);
            }
        }
        
        //TODO: Encode from, cc and to if neccessary
        
        debug_pop();
        return true;
    }
    
    /**
     * Tries to load a send backend
     */
    function _load_backend($backend)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $classname = "org_openpsa_mail_backend_{$backend}";
        if (class_exists($classname))
        {
            $this->_backend = new $classname();
            debug_add("backend is now\n===\n" . sprint_r($this->_backend) . "===\n");
            debug_pop();
            return true;
        }
        debug_add("backend class {$classname} is not available", MIDCOM_LOG_WARN);
        return false;
    }

    /**
     * Sends the email
     */
    function send($backend = 'try_default', $backend_params = array())
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        switch ($backend)
        {
            case 'try_default':
                $try_backends = $this->_config->get('default_try_backends');
                if (!is_array($try_backends))
                {
                    return false;
                }
                //Use first available backend in list
                foreach ($try_backends as $backend)
                {
                    debug_add("Trying backend {$backend}");
                    
                    if (   $this->_load_backend($backend)
                        && $this->_backend->is_available())
                    {
                        debug_add('OK');
                        break;
                    }
                    debug_add("backend {$backend} is not available");
                }
                break;
            default:
                $this->_load_backend($backend);
                break;
        }

        if (!is_object($this->_backend))
        {
            debug_add('no backend object available, aborting');
            debug_pop();
            return false;
        }

        //Prepare mail for sending
        $this->prepare();


        $this->headers['X-org.openpsa.mail-backend-class'] = get_class($this->_backend);
        $ret = $this->_backend->send($this, $backend_params);
        debug_pop();
        return $ret;
    }

    /**
     * Get errormessage from mail class
     *
     * Handles also the PEAR errors from libraries used.
     */
    function getErrorMessage()
    {
        return $this->get_error_message();
    }
    function get_error_message()
    {
        if (is_object($this->_backend))
        {
            return $this->_backend->get_error_message();
        }
        if (   is_object($this->__mailErr)
            && is_a($this->__mailErr, 'pear_error'))
        {
            return $this->__mailErr->getMessage();
        }
        return 'Unknown error';
    }

    /**
     * Determine correct mimetype for file we have only content
     * (and perhaps filename) for.
     */
    function _get_mimetype($content, $name = 'unknown')
    {
        if (!function_exists('mime_content_type'))
        {
            return false;
        }
        $filename = tempnam(null, 'org_openpsa_mail_') . "_{$name}";
        $fp = fopen($filename, 'w');
        if (!$fp)
        {
            //Could not open file for writing
            unlink($filename);
            return false;
        }
        //fwrite($fp, $content, strlen($content));
        fwrite($fp, $content);
        fclose($fp);
        $mimetype = mime_content_type($filename);
        unlink($filename);
        
        return $mimetype;
    }
    
    /**
     * Whether given file definition is already in embeds
     */
    function _exists_in_embeds($input, $embeds)
    {
        reset($embeds);
        foreach ($embeds as $file_arr)
        {
            //PONDER: Check other values as well ?
            if ($input['name'] === $file_arr['name'])
            {
                return true;
            }
        }
        return false;
    }

    function _html_get_embeds_loop(&$obj, $html, $search, $embeds, $type)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (!isset($_SERVER)) { //Make sure we have this information (even on older PHPs)
            global $HTTP_SERVER_VARS;
            $_SERVER = $HTTP_SERVER_VARS;
        }
        
        //Cache for embeds data
        if (!array_key_exists('org_openpsa_mail_embeds_data_cache', $GLOBALS))
        {
            $GLOBALS['org_openpsa_mail_embeds_data_cache'] = array();
        }
        $embeds_data_cache =& $GLOBALS['org_openpsa_mail_embeds_data_cache'];
        
        
        reset($search);
        while (list ($k, $dummy) = each ($search['whole']))
        {
            $regExp_file = "/(.*\/|^)(.+?)$/";
            preg_match($regExp_file, $search['location'][$k], $match_file);
            debug_add("match_file:\n===\n".sprint_r($match_file)."===\n");
            $search['filename'][$k] = $match_file[2];

            if (array_key_exists($search['location'][$k], $embeds_data_cache))
            {
                $mode = 'cached';
            }
            else if ($search['proto'][$k])
            { //URI is fully qualified
               $mode = 'fullUri';
               $uri = $search['uri'][$k];
            }
            else if (preg_match('/^\//', $search['location'][$k]))
            { //URI is relative
               $mode = 'relUri';
            }
            else if ($search['uri'][$k] === $search['filename'][$k])
            { //URI is just the filename
               $mode = 'objFile';
            } else { //We cannot decide what to do
               $mode = false; 
            }

            debug_add('mode: '.$mode);
            switch ($mode)
            {
                case 'cached':
                        //Avoid multiple copies of same file in embeds
                        if (!$this->_exists_in_embeds($embeds_data_cache[$search['location'][$k]], $embeds))
                        {
                            $embeds[] = $embeds_data_cache[$search['location'][$k]];
                        }
                        switch ($type)
                        {
                            case 'src':
                                $html = str_replace($search['whole'][$k], 'src="' . $search['filename'][$k] . '"', $html);
                                break;
                            case 'url':
                                $html = str_replace($search['whole'][$k], 'url("' . $search['filename'][$k] . '")', $html);
                                break;
                        }
                    break;
                case 'relUri':
                    switch ($_SERVER['SERVER_PORT']) {
                        case 443:
                            $uri = 'https://' . $_SERVER['SERVER_NAME'] . $search['location'][$k];
                            break;
                        case 80:
                            $uri = 'http://' . $_SERVER['SERVER_NAME'] . $search['location'][$k];
                            break;
                        default:
                            $uri = 'http://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $search['location'][$k];
                            break;
                   }
                    //NOTE: Fall-trough intentional
                case 'fullUri':
                    debug_add('Trying to fetch file: '.$uri);
                    $cont = @file_get_contents($uri); //Suppress errors, the url might be invalid but if so then we just silentry drop it
                    if (  $cont 
                          && $cont != 'FAILED REDIRECT TO ERROR find does not point to valid object MGD_ERR_OK') //Aegir attachment server error
                    {
                        debug_add('Success!');
                        $tmpArr2 = array();    
                        $tmpArr2['name'] = $search['filename'][$k];
                        $tmpArr2['content'] = $cont;
                        if ($mimetype = $this->_get_mimetype($tmpArr2['content'], $tmpArr2['name']))
                        {
                            $tmpArr2['mimetype'] = $mimetype;
                        }
                        $embeds_data_cache[$search['location'][$k]] = $tmpArr2;
                        $embeds[] = $tmpArr2;
                        switch ($type)
                        {
                            case 'src':
                                $html = str_replace($search['whole'][$k], 'src="' . $search['filename'][$k] . '"', $html);
                                break;
                            case 'url':
                                $html = str_replace($search['whole'][$k], 'url("' . $search['filename'][$k] . '")', $html);
                                break;
                        }
                        unset($tmpArr2, $cont);
                    }
                    else
                    {
                        debug_add('FAILURE');
                    }
                    break;
                    case 'objFile':
                        if (is_object($obj))
                        {
                            $attObj = $obj->getattachment($search['filename'][$k]);
                            if ($attObj)
                            {
                                $fp = mgd_open_attachment($attObj->id, 'r');
                                if ($fp)
                                {
                                    $tmpArr2 = array();    
                                    $tmpArr2['mimetype'] = $attObj->mimetype;
                                    $tmpArr2['name'] = $search['filename'][$k];
                                    while (!feof($fp))
                                    {
                                        $tmpArr2['content'] .= fread($fp, 4096);
                                    }
                                    fclose($fp);
                                    $embeds_data_cache[$search['location'][$k]] = $tmpArr2;
                                    $embeds[] = $tmpArr2;
                                    unset ($tmpArr2);
                                }
                                unset($attObj);
                            }
                        }
                        break;
                 default:
                 break;
            }
        }
        debug_pop();
        return array($html, $embeds);
    }

    /**
     * Find emebeds from source HTML, intentionally does NOT use $this->html_body
     */
    function html_get_embeds($obj = false, $html = null, $embeds = null)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        /* This is a dangerous way to default (also the references seem to go all over the place), thus we won't
        if ($html === null) {
            $html =& $this->html_body;
        }
        if ($embeds === null)
        {
            $embeds =& $this->embeds;
        }
        */

        if (!is_array($embeds))
        {
            $embeds = array();
        }

        //Make sure we have this function
        if (!function_exists('file_get_contents'))
        {
            include_once('Compat/Function/file_get_contents.php');
        }
        if (!function_exists('file_get_contents'))
        {
            debug_add('Function file_get_contents() missing', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        //TODO: support for CSS images (NOTE: requires major work with _html_get_embeds_loop as well)
        //Anything with SRC = "" something in it (images etc)
        $regExp_src = "/src=([\"'«])(((https?|ftp):\/\/)?(.*?))\\1/i";
        preg_match_all($regExp_src, $html, $matches_src);
        debug_add("matches_src:\n===\n" . sprint_r($matches_src) . "===\n");
        $tmpArr = array();    
        $tmpArr['whole']    = $matches_src[0];
        $tmpArr['uri']      = $matches_src[2];
        $tmpArr['proto']    = $matches_src[3];
        $tmpArr['location'] = $matches_src[5];
        list ($html, $embeds) = $this->_html_get_embeds_loop($obj, $html, $tmpArr, $embeds, 'src');

        if ($this->embed_css_url)
        {
            //Anything with url() something in it (images etc)
            $regExp_url = "/url\s*\(([\"'«])?(((https?|ftp):\/\/)?(.*?))\\1?\)/i";
            preg_match_all($regExp_url, $html, $matches_url);
            debug_add("matches_url:\n===\n" . sprint_r($matches_url) . "===\n");
            $tmpArr = array();    
            $tmpArr['whole']    = $matches_url[0];
            $tmpArr['uri']      = $matches_url[2];
            $tmpArr['proto']    = $matches_url[3];
            $tmpArr['location'] = $matches_url[5];
            debug_add("tmpArr:\n===\n" . sprint_r($tmpArr) . "===\n");
            list ($html, $embeds) = $this->_html_get_embeds_loop($obj, $html, $tmpArr, $embeds, 'url');
        }
        
        //return array('html' => $html, 'embeds' => $embeds, 'debug' => $tmpArr);
        debug_pop();
        return array($html, $embeds);
    }

    function merge_address_headers()
    {
        $addresses = '';
        //TODO: Support array of addresses as well
        $addresses .= $this->to;
        if (   array_key_exists('Cc', $this->headers)
            && !empty($this->headers['Cc']))
        {
            //TODO: Support array of addresses as well
            $addresses .= ', '.$this->headers['Cc'];
        }
        if (   array_key_exists('Bcc', $this->headers)
            && !empty($this->headers['Bcc']))
        {
            //TODO: Support array of addresses as well
            $addresses .= ', '.$this->headers['Bcc'];
        }
        return $addresses;
    }

    /**
     * Initialize PEAR Mail/MIME classes if available
     *
     * The main mailer class can work without them, gracefully degrading
     * in functionality.
     */
    function _initialize_pear()
    {
        if (!class_exists('Mail')) 
        {
           @include_once('Mail.php');
        }
        if (class_exists('Mail'))
        {
           if (!class_exists('Mail_mime'))
           {
              @include_once('Mail/mime.php');
           }
           if (!class_exists('Mail_mimeDecode'))
           {
              @include_once('Mail/mimeDecode.php');
           }
        }
        return true;
    }

}
?>