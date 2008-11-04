<?php
/**
 * @package org.openpsa.mail
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
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
    var $__debug;     //boolean, output debug information
    var $__mime;      //object, (Mail_mime / Mail_mimeDecode) holder
    var $__mail;      //object, (Mail) holder
    var $__mailErr;   //boolean/object, send error status
    var $__iconv;     //boolean, when decoding mails, try to convert to desired charset.
    var $__orig_encoding;  //string, original encoding of the message
    var $__textBodyFound; //boolean, used in part_decode
    var $__htmlBodyFound; // --''--

    function __construct()
    {
        $this->_component = 'org.openpsa.mail';
        parent::__construct();
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
        $this->headers['User-Agent'] = 'Midgard/' . substr(mgd_version(), 0, 4);
        $this->headers['X-Originating-Ip'] = $_SERVER['REMOTE_ADDR'];
        $this->__mailErr = false;
        $this->htmlBody =& $this->html_body;

        $this->encoding = $this->_i18n->get_current_charset();

        //Try to convert between charsets
        $this->__iconv = true;
        $this->__orig_encoding = '';

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
     * In fact by manually setting the headers one can always send
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
     * Creates a Mail_mime instance and adds parts to it as necessary
     */
    function initMail_mime()
    {
        return $this->init_mail_mime();
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
                $regex = "#url\s*\(([\"'�])?" . preg_quote($image['name'], '#') . "\\1?\)#i";
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
     * Old name for part_decode(), compatibility wrapper.
     */
    function partDecode(&$part)
    {
        return $this->part_decode($part);
    }
    /**
     * Decodes a Mail_mime part (recursive)
     */
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

        // PONDER: How to handle multiple text bodies better (like in bounce messages)

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
                    $this->body .= $part->body;
                    $this->__textBodyFound = true;
                    break;
                case "html":
                    if (!$this->__textBodyFound)
                    {
                        //Try to translate HTML body only if plaintext alternative is not available
                        $this->body .= $this->html2text($part->body);
                    }
                    $this->html_body =& $part->body;
                    $this->__htmlBodyFound = true;
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
            if (   isset($part->d_parameters['filename'])
                && !empty($part->d_parameters['filename']))
            {
                $dataArr['name'] = $part->d_parameters['filename'];
            }
            elseif (   isset($part->ctype_parameters['name'])
                    && !empty($part->ctype_parameters['name']))
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

    /**
     * No-op for now
     */
    function _compatibility_checks()
    {
        return;
    }

    /**
     * Converts given string to $this->encoding
     *
     * @param string to be converted
     * @param string encoding from header or such, used as default in case mb_detect_endoding is not available
     * @return string converted string (or original string in case we cannot convert for some reason)
     */
    function charset_convert($data, $given_encoding = false)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        // Some headers are multi-dimensional, recurse if needed
        if (is_array($data))
        {
            debug_add('Given data is an array, iterating trough it');
            foreach($data as $k => $v)
            {
                debug_add("Recursing key {$k}");
                $data[$k] = $this->charset_convert($v, $given_encoding);
            }
            debug_add('Done');
            debug_pop();
            return $data;
        }
        if ($this->__iconv === false)
        {
            debug_add('Conversions disabled ($this->__iconv is false), returning data as is',  MIDCOM_LOG_WARN);
            debug_pop();
            return $data;
        }
        if (empty($data))
        {
            debug_add('Data is empty, returning as is',  MIDCOM_LOG_WARN);
            debug_pop();
            return $data;
        }
        if (!function_exists('iconv'))
        {
            debug_add('Function \'iconv()\' not available, returning data as is',  MIDCOM_LOG_WARN);
            debug_pop();
            return $data;
        }
        $encoding = false;
        if (   !function_exists('mb_detect_encoding')
            && !empty($given_encoding))
        {
            $stupid_domains = $this->_config->get('incorrect_charset_domains');
            if (!is_array($stupid_domains))
            {
                return;
            }
            foreach ($stupid_domains as $domain)
            {
                if (stristr($this->from, "@{$domain}"))
                {
                    debug_add("Detected incorrect_charset_domain '{$domain}' and 'mb_detect_encoding()' not available, aborting convert", MIDCOM_LOG_WARN);
                    debug_pop();
                    return $data;
                }
            }
            $encoding =& $given_encoding;
        }
        else
        {
            $encoding = mb_detect_encoding($data, $this->_config->get('mb_detect_encoding_list'));
        }
        if (empty($encoding))
        {
            debug('Given/Detected encoding is empty, cannot convert, aborting', MIDCOM_LOG_WARN);
            debug_pop();
            return $data;
        }
        $encoding_lower = strtolower($encoding);
        $this_encoding_lower = strtolower($this->encoding);
        if (   $encoding_lower == $this_encoding_lower
            || (   $encoding_lower == 'ascii'
                /* ASCII is a subset of the following encodings, and thus requires no conversion to them */
                && (   $this_encoding_lower == 'utf-8'
                    || $this_encoding_lower == 'iso-8859-1'
                    || $this_encoding_lower == 'iso-8859-15')
                )
            )
        {
            debug_add("Given/Detected encoding '{$encoding}' and desired encoding '{$this->encoding}' require no conversion between them", MIDCOM_LOG_INFO);
            debug_pop();
            return $data;
        }
        $append_target = $this->_config->get('iconv_append_target');
        debug_add("Calling iconv('{$encoding_lower}', '{$this_encoding_lower}{$append_target}', \$data)");
        $stat = @iconv($encoding_lower, $this_encoding_lower . $append_target, $data);
        if (empty($stat))
        {
            debug_add("Failed to convert from '{$encoding}' to '{$this->encoding}'", MIDCOM_LOG_WARN);
            debug_pop();
            return $data;
        }
        debug_add("Converted from '{$encoding}' to '{$this->encoding}'", MIDCOM_LOG_INFO);
        debug_pop();
        return $stat;
    }

    /**
     * Wrapper for old style compatibility
     */
    function mimeDecode()
    {
        return $this->mime_decode();
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
        $this->body = preg_replace("/\n\r|\r\n|\r/", "\n", $this->body);

        /* Check if we have mime boundary, in that case we need to make sure it does not exhibit certain
        corner cases which choke mail_mimedecode */
        if (preg_match("/Content-Type: multipart\/mixed;\n?\s+boundary=([\"']?)(.*?)(\\1)\n/", $this->body, $boundary_matches))
        {
            $boundary = $boundary_matches[2];
            if (   strpos($boundary, '"')
                || strpos($boundary, "'"))
            {
                // Any quote inside the boundary value will choke mail_mimedecode
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

        // ucwords all header keys
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
            // Start with empty body and append all text parts to it
            //$this->body = '';
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
        }

        // Charset conversions
        debug_add('calling $this->charset_convert($this->body, $this->__orig_encoding);');
        $this->body  = $this->charset_convert($this->body, $this->__orig_encoding);
        debug_add('calling $this->charset_convert($this->html_body, $this->__orig_encoding);');
        $this->html_body  = $this->charset_convert($this->html_body, $this->__orig_encoding);
        foreach($this->headers as $header => $value)
        {
            debug_add("calling charset_convert for header '{$header}'");
            $this->headers[$header] = $this->charset_convert($value, $this->__orig_encoding);
        }
        foreach ($this->attachments as $key => $data)
        {
            debug_add("calling charset_convert for attachment '{$data['name']}'");
            $this->attachments[$key]['name'] = $this->charset_convert($data['name'], $this->__orig_encoding);
        }

        //Strip whitespace around bodies
        $this->body = ltrim(rtrim($this->body));
        $this->html_body = ltrim(rtrim($this->html_body));

        //TODO Figure if decode was successful or not and return true/false in stead
        debug_pop();
        return $mime;
    }

    function _code_for_sort_encode_subject()
    {
        return <<<EOF
        if (\$a == '=')
        {
            return -1;
        }
        if (\$b == '=')
        {
            return 1;
        }
        \$aord = ord(\$a);
        \$bord = ord(\$b);
        if (\$aord < \$bord)
        {
            return -1;
        }
        if (\$aord > \$bord)
        {
            return 1;
        }
        return 0;
EOF;
    }

    /**
     * Quoted-Printable encoding for message subject if necessary
     */
    function encode_subject()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        preg_match_all("/[^\x21-\x39\x41-\x7e]/", $this->subject, $matches);
        if (   count ($matches[0])>0
            && !stristr($this->subject, '=?' . strtoupper($this->encoding) . '?Q?')
            )
        {
            // Sort the results to make sure '=' gets encoded first (otherwise there will be double-encodes...)
            usort($matches[0], create_function('$a,$b', $this->_code_for_sort_encode_subject()));
            debug_add("matches[0]\n===\n" . org_openpsa_helpers::sprint_r($matches) . "===\n");
            $cache = array();
            $newSubj = $this->subject;
            while (list ($k, $char) = each ($matches[0]))
            {
                $hex = str_pad(strtoupper(dechex(ord($char))), 2, '0', STR_PAD_LEFT);
                if (isset($cache[$hex]))
                {
                    continue;
                }
                $code = '=' . $hex;
                debug_add("encoding  '{$char}' to '{$code}'");
                $newSubj = str_replace($char, $code, $newSubj);
                $cache[$hex] = true;
            }
            $this->subject = '=?' . strtoupper($this->encoding) . '?Q?' . $newSubj . '?=';
        }
        debug_pop();
    }

     /**
      * Prepares message for sending
      *
      * Calls MIME etc encodings as necessary.
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

        //Check whether it's necessary to initialize MIME
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
            // mime->headers() has some corner cases with UTF-8 so we encode at least the subject ourselves
            $this->encode_subject();
            debug_add("Headers before mime->headers\n===\n" . org_openpsa_helpers::sprint_r($this->headers) . "===\n");
            $this->headers = $mime->headers($this->headers);
            debug_add("Headers after mime->headers\n===\n" . org_openpsa_helpers::sprint_r($this->headers) . "===\n");
            // some MTAs manage to mangle multiline headers (RFC "folded"), here we make sure at least the content type is in single line
            $this->headers['Content-Type'] = preg_replace('/\s+/', ' ', $this->headers['Content-Type']);
            debug_add("Headers after multiline fix\n===\n" . org_openpsa_helpers::sprint_r($this->headers) . "===\n");
        }

        // Encode subject (if necessary) and set Content-Type (if not set already)
        $this->encode_subject();
        if (   !isset($this->headers['Content-Type'])
            || $this->headers['Content-Type'] == null)
        {
            $this->headers['Content-Type'] = "text/plain; charset={$this->encoding}";
        }
        // Set Mime-version if not set already (done this way to accommodate for various typings
        $mime_header = false;
        foreach ($this->headers as $k => $v)
        {
            if (strtolower($k) == 'mime-version')
            {
                $mime_header = $k;
                break;
            }
        }
        if (   $mime_header === false
            || $this->headers[$mime_header] == null
            )
        {
            if ($mime_header === false)
            {
                $this->headers['Mime-version'] = '1.0';
            }
            else
            {
                $this->headers[$mime_header] = '1.0';
            }
        }

        //Make sure we don't send any empty headers
        reset ($this->headers);
        foreach ($this->headers as $header => $value)
        {
            if (empty($value))
            {
                debug_add("Header '{$header}' has empty value, removing");
                unset ($this->headers[$header]);
            }
            $value_trimmed = trim($value);
            if ($value_trimmed != $value)
            {
                debug_add("Header '{$header}' has whitespace around its value, rewriting from\n===\n{$value}\n===\nto\n===\n{$value_trimmed}\n===\n");
                $this->headers[$header] = $value_trimmed;
            }
        }

        //TODO: Encode from, cc and to if necessary

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
            debug_add("backend is now\n===\n" . org_openpsa_helpers::sprint_r($this->_backend) . "===\n");
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
                        debug_add("Backend {$backend} loaded OK");
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
        $type_backup = $type;

        //Cache for embeds data
        if (!isset($GLOBALS['org_openpsa_mail_embeds_data_cache']))
        {
            $GLOBALS['org_openpsa_mail_embeds_data_cache'] = array();
        }
        $embeds_data_cache =& $GLOBALS['org_openpsa_mail_embeds_data_cache'];


        reset($search);
        while (list ($k, $dummy) = each ($search['whole']))
        {
            if ($type_backup == 'special:fromarray')
            {
                $type = $search['type'][$k];
            }
            debug_add("k: {$k}, type: {$type}, type_backup: {$type_backup}");

            $regExp_file = "/(.*\/|^)(.+?)$/";
            preg_match($regExp_file, $search['location'][$k], $match_file);
            debug_add("match_file:\n===\n".org_openpsa_helpers::sprint_r($match_file)."===\n");
            $search['filename'][$k] = $match_file[2];

            if (isset($embeds_data_cache[$search['location'][$k]]))
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
                        switch (strtolower($type))
                        {
                            case 'url':
                                $html = str_replace($search['whole'][$k], 'url("' . $search['filename'][$k] . '")', $html);
                                break;
                            default:
                                $html = str_replace($search['whole'][$k], $type . '="' . $search['filename'][$k] . '"', $html);
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
                    // NOTE: Fall-trough intentional
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
                        switch (strtolower($type))
                        {
                            case 'url':
                                $html = str_replace($search['whole'][$k], 'url("' . $search['filename'][$k] . '")', $html);
                                break;
                            default:
                                $html = str_replace($search['whole'][$k], $type . '="' . $search['filename'][$k] . '"', $html);
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
                            $attObj = $obj->get_attachment($search['filename'][$k]);
                            if ($attObj)
                            {
                                $fp = $attObj->open('r');
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
     * Find embeds from source HTML, intentionally does NOT use $this->html_body
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

        //Anything with SRC = "" something in it (images etc)
        $regExp_src = "/(src|background)=([\"'�])(((https?|ftp):\/\/)?(.*?))\\2/i";
        preg_match_all($regExp_src, $html, $matches_src);
        debug_add("matches_src:\n===\n" . org_openpsa_helpers::sprint_r($matches_src) . "===\n");
        $tmpArr = array();
        $tmpArr['whole']    = $matches_src[0];
        $tmpArr['uri']      = $matches_src[3];
        $tmpArr['proto']    = $matches_src[4];
        $tmpArr['location'] = $matches_src[6];
        $tmpArr['type']     = $matches_src[1];

        list ($html, $embeds) = $this->_html_get_embeds_loop($obj, $html, $tmpArr, $embeds, 'special:fromarray');

        if ($this->embed_css_url)
        {
            //Anything with url() something in it (images etc)
            $regExp_url = "/url\s*\(([\"'�])?(((https?|ftp):\/\/)?(.*?))\\1?\)/i";
            preg_match_all($regExp_url, $html, $matches_url);
            debug_add("matches_url:\n===\n" . org_openpsa_helpers::sprint_r($matches_url) . "===\n");
            $tmpArr = array();
            $tmpArr['whole']    = $matches_url[0];
            $tmpArr['uri']      = $matches_url[2];
            $tmpArr['proto']    = $matches_url[3];
            $tmpArr['location'] = $matches_url[5];
            debug_add("tmpArr:\n===\n" . org_openpsa_helpers::sprint_r($tmpArr) . "===\n");
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
        if (   isset($this->headers['Cc'])
            && !empty($this->headers['Cc']))
        {
            //TODO: Support array of addresses as well
            $addresses .= ', '.$this->headers['Cc'];
        }
        if (   isset($this->headers['Bcc'])
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

if (!function_exists('org_openpsa_helpers::sprint_r')) 
{
    function org_openpsa_helpers::sprint_r($var) 
    {
        ob_start();
        print_r($var);
        $ret = ob_get_contents();
        ob_end_clean();
        return $ret;
    }
}
?>