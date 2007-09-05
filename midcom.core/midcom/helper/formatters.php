<?php

if (!function_exists('midcom_helper_formatters_links'))
{    
    function midcom_helper_formatters_links($content)
    {   
        // echo $content;
        // echo "\n<br />---------<br />\n";
        
        $length = strlen($content);
        $start = 0;
        $end = $length;
        $blocks = explode(" ",$content);

        foreach ($blocks as $block)
        {            
            $start = strpos($content, $block, $start);
                        
            if (eregi('(([[:alpha:]]+://)|^(www\.))+[^<>[:space:]]+[[:alnum:]/]',$block))
            {
                if (! eregi('([[:alpha:]]+=")+[^<>[:space:]]+[[:alnum:]/]',$block))
                {                    
                    $end = strpos($content, $block, $start);
                    $end += strlen($block);
                    
                    while ( ereg("[,\.]$", $block) )
                    {
                        $block = substr( $block, 0, -1 );
                        $end--;
                    }
                    
                    $new_block = $block;
                    //$new_block = ereg_replace("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]","<a href=\"\\0\">\\0</a>", $new_block);
                    
                    $new_block = eregi_replace('(((f|ht){1}tp://)[-a-zA-Z0-9@:%_\+.~#?&//=]+)', '<a href="\\1">\\1</a>', $new_block);
                    if ($new_block == $block)
                    {
                        $new_block = eregi_replace('(www.[-a-zA-Z0-9@:%_\+.~#?&//=]+)', '<a href="http://\\1">\\1</a>', $new_block);
                    }
                    
                    _midcom_helper_formatters_replace_content($content, $new_block, $start, $end);
                    
                    $start += strlen($new_block);
                }
            }
            else
            {
                $end = strpos($content, $block, $start);
            }
            
            $start += 1;
        }

        echo $content;
    }
    _midcom_helper_formatters_register_filter('links');
}

if (!function_exists('midcom_helper_formatters_maillinks'))
{
    function midcom_helper_formatters_maillinks($content)
    {
        $content = eregi_replace('([_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,3})', '<a href="mailto:\\1">\\1</a>', $content);        
        
        echo $content;
    }
    _midcom_helper_formatters_register_filter('maillinks');
}

if (!function_exists('midcom_helper_formatters_obfmaillinks'))
{
    function midcom_helper_formatters_obfmaillinks($content)
    {
        $regexp = '(?:(?:\r\n)?[ \t])*(?:(?:(?:[^()<>@,;:\\".\[\] \000-\031]+(?:(?:(?:\r\n)?[ \t]
        )+|\Z|(?=[\["()<>@,;:\\".\[\]]))|"(?:[^\"\r\\]|\\.|(?:(?:\r\n)?[ \t]))*"(?:(?:
        \r\n)?[ \t])*)(?:\.(?:(?:\r\n)?[ \t])*(?:[^()<>@,;:\\".\[\] \000-\031]+(?:(?:(
        ?:\r\n)?[ \t])+|\Z|(?=[\["()<>@,;:\\".\[\]]))|"(?:[^\"\r\\]|\\.|(?:(?:\r\n)?[ 
        \t]))*"(?:(?:\r\n)?[ \t])*))*@(?:(?:\r\n)?[ \t])*(?:[^()<>@,;:\\".\[\] \000-\0
        31]+(?:(?:(?:\r\n)?[ \t])+|\Z|(?=[\["()<>@,;:\\".\[\]]))|\[([^\[\]\r\\]|\\.)*\
        ](?:(?:\r\n)?[ \t])*)(?:\.(?:(?:\r\n)?[ \t])*(?:[^()<>@,;:\\".\[\] \000-\031]+
        (?:(?:(?:\r\n)?[ \t])+|\Z|(?=[\["()<>@,;:\\".\[\]]))|\[([^\[\]\r\\]|\\.)*\](?:
        (?:\r\n)?[ \t])*))*|(?:[^()<>@,;:\\".\[\] \000-\031]+(?:(?:(?:\r\n)?[ \t])+|\Z
        |(?=[\["()<>@,;:\\".\[\]]))|"(?:[^\"\r\\]|\\.|(?:(?:\r\n)?[ \t]))*"(?:(?:\r\n)
        ?[ \t])*)*\<(?:(?:\r\n)?[ \t])*(?:@(?:[^()<>@,;:\\".\[\] \000-\031]+(?:(?:(?:\
        r\n)?[ \t])+|\Z|(?=[\["()<>@,;:\\".\[\]]))|\[([^\[\]\r\\]|\\.)*\](?:(?:\r\n)?[
         \t])*)(?:\.(?:(?:\r\n)?[ \t])*(?:[^()<>@,;:\\".\[\] \000-\031]+(?:(?:(?:\r\n)
        ?[ \t])+|\Z|(?=[\["()<>@,;:\\".\[\]]))|\[([^\[\]\r\\]|\\.)*\](?:(?:\r\n)?[ \t]
        )*))*(?:,@(?:(?:\r\n)?[ \t])*(?:[^()<>@,;:\\".\[\] \000-\031]+(?:(?:(?:\r\n)?[
         \t])+|\Z|(?=[\["()<>@,;:\\".\[\]]))|\[([^\[\]\r\\]|\\.)*\](?:(?:\r\n)?[ \t])*
        )(?:\.(?:(?:\r\n)?[ \t])*(?:[^()<>@,;:\\".\[\] \000-\031]+(?:(?:(?:\r\n)?[ \t]
        )+|\Z|(?=[\["()<>@,;:\\".\[\]]))|\[([^\[\]\r\\]|\\.)*\](?:(?:\r\n)?[ \t])*))*)
        *:(?:(?:\r\n)?[ \t])*)?(?:[^()<>@,;:\\".\[\] \000-\031]+(?:(?:(?:\r\n)?[ \t])+
        |\Z|(?=[\["()<>@,;:\\".\[\]]))|"(?:[^\"\r\\]|\\.|(?:(?:\r\n)?[ \t]))*"(?:(?:\r
        \n)?[ \t])*)(?:\.(?:(?:\r\n)?[ \t])*(?:[^()<>@,;:\\".\[\] \000-\031]+(?:(?:(?:
        \r\n)?[ \t])+|\Z|(?=[\["()<>@,;:\\".\[\]]))|"(?:[^\"\r\\]|\\.|(?:(?:\r\n)?[ \t
        ]))*"(?:(?:\r\n)?[ \t])*))*@(?:(?:\r\n)?[ \t])*(?:[^()<>@,;:\\".\[\] \000-\031
        ]+(?:(?:(?:\r\n)?[ \t])+|\Z|(?=[\["()<>@,;:\\".\[\]]))|\[([^\[\]\r\\]|\\.)*\](
        ?:(?:\r\n)?[ \t])*)(?:\.(?:(?:\r\n)?[ \t])*(?:[^()<>@,;:\\".\[\] \000-\031]+(?
        :(?:(?:\r\n)?[ \t])+|\Z|(?=[\["()<>@,;:\\".\[\]]))|\[([^\[\]\r\\]|\\.)*\](?:(?
        :\r\n)?[ \t])*))*\>(?:(?:\r\n)?[ \t])*)|(?:[^()<>@,;:\\".\[\] \000-\031]+(?:(?
        :(?:\r\n)?[ \t])+|\Z|(?=[\["()<>@,;:\\".\[\]]))|"(?:[^\"\r\\]|\\.|(?:(?:\r\n)?
        [ \t]))*"(?:(?:\r\n)?[ \t])*)*:(?:(?:\r\n)?[ \t])*(?:(?:(?:[^()<>@,;:\\".\[\] 
        \000-\031]+(?:(?:(?:\r\n)?[ \t])+|\Z|(?=[\["()<>@,;:\\".\[\]]))|"(?:[^\"\r\\]|
        \\.|(?:(?:\r\n)?[ \t]))*"(?:(?:\r\n)?[ \t])*)(?:\.(?:(?:\r\n)?[ \t])*(?:[^()<>
        @,;:\\".\[\] \000-\031]+(?:(?:(?:\r\n)?[ \t])+|\Z|(?=[\["()<>@,;:\\".\[\]]))|"
        (?:[^\"\r\\]|\\.|(?:(?:\r\n)?[ \t]))*"(?:(?:\r\n)?[ \t])*))*@(?:(?:\r\n)?[ \t]
        )*(?:[^()<>@,;:\\".\[\] \000-\031]+(?:(?:(?:\r\n)?[ \t])+|\Z|(?=[\["()<>@,;:\\
        ".\[\]]))|\[([^\[\]\r\\]|\\.)*\](?:(?:\r\n)?[ \t])*)(?:\.(?:(?:\r\n)?[ \t])*(?
        :[^()<>@,;:\\".\[\] \000-\031]+(?:(?:(?:\r\n)?[ \t])+|\Z|(?=[\["()<>@,;:\\".\[
        \]]))|\[([^\[\]\r\\]|\\.)*\](?:(?:\r\n)?[ \t])*))*|(?:[^()<>@,;:\\".\[\] \000-
        \031]+(?:(?:(?:\r\n)?[ \t])+|\Z|(?=[\["()<>@,;:\\".\[\]]))|"(?:[^\"\r\\]|\\.|(
        ?:(?:\r\n)?[ \t]))*"(?:(?:\r\n)?[ \t])*)*\<(?:(?:\r\n)?[ \t])*(?:@(?:[^()<>@,;
        :\\".\[\] \000-\031]+(?:(?:(?:\r\n)?[ \t])+|\Z|(?=[\["()<>@,;:\\".\[\]]))|\[([
        ^\[\]\r\\]|\\.)*\](?:(?:\r\n)?[ \t])*)(?:\.(?:(?:\r\n)?[ \t])*(?:[^()<>@,;:\\"
        .\[\] \000-\031]+(?:(?:(?:\r\n)?[ \t])+|\Z|(?=[\["()<>@,;:\\".\[\]]))|\[([^\[\
        ]\r\\]|\\.)*\](?:(?:\r\n)?[ \t])*))*(?:,@(?:(?:\r\n)?[ \t])*(?:[^()<>@,;:\\".\
        [\] \000-\031]+(?:(?:(?:\r\n)?[ \t])+|\Z|(?=[\["()<>@,;:\\".\[\]]))|\[([^\[\]\
        r\\]|\\.)*\](?:(?:\r\n)?[ \t])*)(?:\.(?:(?:\r\n)?[ \t])*(?:[^()<>@,;:\\".\[\] 
        \000-\031]+(?:(?:(?:\r\n)?[ \t])+|\Z|(?=[\["()<>@,;:\\".\[\]]))|\[([^\[\]\r\\]
        |\\.)*\](?:(?:\r\n)?[ \t])*))*)*:(?:(?:\r\n)?[ \t])*)?(?:[^()<>@,;:\\".\[\] \0
        00-\031]+(?:(?:(?:\r\n)?[ \t])+|\Z|(?=[\["()<>@,;:\\".\[\]]))|"(?:[^\"\r\\]|\\
        .|(?:(?:\r\n)?[ \t]))*"(?:(?:\r\n)?[ \t])*)(?:\.(?:(?:\r\n)?[ \t])*(?:[^()<>@,
        ;:\\".\[\] \000-\031]+(?:(?:(?:\r\n)?[ \t])+|\Z|(?=[\["()<>@,;:\\".\[\]]))|"(?
        :[^\"\r\\]|\\.|(?:(?:\r\n)?[ \t]))*"(?:(?:\r\n)?[ \t])*))*@(?:(?:\r\n)?[ \t])*
        (?:[^()<>@,;:\\".\[\] \000-\031]+(?:(?:(?:\r\n)?[ \t])+|\Z|(?=[\["()<>@,;:\\".
        \[\]]))|\[([^\[\]\r\\]|\\.)*\](?:(?:\r\n)?[ \t])*)(?:\.(?:(?:\r\n)?[ \t])*(?:[
        ^()<>@,;:\\".\[\] \000-\031]+(?:(?:(?:\r\n)?[ \t])+|\Z|(?=[\["()<>@,;:\\".\[\]
        ]))|\[([^\[\]\r\\]|\\.)*\](?:(?:\r\n)?[ \t])*))*\>(?:(?:\r\n)?[ \t])*)(?:,\s*(
        ?:(?:[^()<>@,;:\\".\[\] \000-\031]+(?:(?:(?:\r\n)?[ \t])+|\Z|(?=[\["()<>@,;:\\
        ".\[\]]))|"(?:[^\"\r\\]|\\.|(?:(?:\r\n)?[ \t]))*"(?:(?:\r\n)?[ \t])*)(?:\.(?:(
        ?:\r\n)?[ \t])*(?:[^()<>@,;:\\".\[\] \000-\031]+(?:(?:(?:\r\n)?[ \t])+|\Z|(?=[
        \["()<>@,;:\\".\[\]]))|"(?:[^\"\r\\]|\\.|(?:(?:\r\n)?[ \t]))*"(?:(?:\r\n)?[ \t
        ])*))*@(?:(?:\r\n)?[ \t])*(?:[^()<>@,;:\\".\[\] \000-\031]+(?:(?:(?:\r\n)?[ \t
        ])+|\Z|(?=[\["()<>@,;:\\".\[\]]))|\[([^\[\]\r\\]|\\.)*\](?:(?:\r\n)?[ \t])*)(?
        :\.(?:(?:\r\n)?[ \t])*(?:[^()<>@,;:\\".\[\] \000-\031]+(?:(?:(?:\r\n)?[ \t])+|
        \Z|(?=[\["()<>@,;:\\".\[\]]))|\[([^\[\]\r\\]|\\.)*\](?:(?:\r\n)?[ \t])*))*|(?:
        [^()<>@,;:\\".\[\] \000-\031]+(?:(?:(?:\r\n)?[ \t])+|\Z|(?=[\["()<>@,;:\\".\[\
        ]]))|"(?:[^\"\r\\]|\\.|(?:(?:\r\n)?[ \t]))*"(?:(?:\r\n)?[ \t])*)*\<(?:(?:\r\n)
        ?[ \t])*(?:@(?:[^()<>@,;:\\".\[\] \000-\031]+(?:(?:(?:\r\n)?[ \t])+|\Z|(?=[\["
        ()<>@,;:\\".\[\]]))|\[([^\[\]\r\\]|\\.)*\](?:(?:\r\n)?[ \t])*)(?:\.(?:(?:\r\n)
        ?[ \t])*(?:[^()<>@,;:\\".\[\] \000-\031]+(?:(?:(?:\r\n)?[ \t])+|\Z|(?=[\["()<>
        @,;:\\".\[\]]))|\[([^\[\]\r\\]|\\.)*\](?:(?:\r\n)?[ \t])*))*(?:,@(?:(?:\r\n)?[
         \t])*(?:[^()<>@,;:\\".\[\] \000-\031]+(?:(?:(?:\r\n)?[ \t])+|\Z|(?=[\["()<>@,
        ;:\\".\[\]]))|\[([^\[\]\r\\]|\\.)*\](?:(?:\r\n)?[ \t])*)(?:\.(?:(?:\r\n)?[ \t]
        )*(?:[^()<>@,;:\\".\[\] \000-\031]+(?:(?:(?:\r\n)?[ \t])+|\Z|(?=[\["()<>@,;:\\
        ".\[\]]))|\[([^\[\]\r\\]|\\.)*\](?:(?:\r\n)?[ \t])*))*)*:(?:(?:\r\n)?[ \t])*)?
        (?:[^()<>@,;:\\".\[\] \000-\031]+(?:(?:(?:\r\n)?[ \t])+|\Z|(?=[\["()<>@,;:\\".
        \[\]]))|"(?:[^\"\r\\]|\\.|(?:(?:\r\n)?[ \t]))*"(?:(?:\r\n)?[ \t])*)(?:\.(?:(?:
        \r\n)?[ \t])*(?:[^()<>@,;:\\".\[\] \000-\031]+(?:(?:(?:\r\n)?[ \t])+|\Z|(?=[\[
        "()<>@,;:\\".\[\]]))|"(?:[^\"\r\\]|\\.|(?:(?:\r\n)?[ \t]))*"(?:(?:\r\n)?[ \t])
        *))*@(?:(?:\r\n)?[ \t])*(?:[^()<>@,;:\\".\[\] \000-\031]+(?:(?:(?:\r\n)?[ \t])
        +|\Z|(?=[\["()<>@,;:\\".\[\]]))|\[([^\[\]\r\\]|\\.)*\](?:(?:\r\n)?[ \t])*)(?:\
        .(?:(?:\r\n)?[ \t])*(?:[^()<>@,;:\\".\[\] \000-\031]+(?:(?:(?:\r\n)?[ \t])+|\Z
        |(?=[\["()<>@,;:\\".\[\]]))|\[([^\[\]\r\\]|\\.)*\](?:(?:\r\n)?[ \t])*))*\>(?:(
        ?:\r\n)?[ \t])*))*)?;\s*)';
        
        //$content = preg_replace($regexp, '', $content);
        $content = eregi_replace('([_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,3})', '<a href="mailto:\\1">\\1</a>', $content);        
        $content = preg_replace('/<a [^>]*href="mailto:([^"]+)"[^>]*>(.*?)<\/a>/ie', '_midcom_helper_formatters_obfuscate_email_link("\\1",false)', $content);
        
        echo $content;
    }
    _midcom_helper_formatters_register_filter('obfmaillinks');
    
    function _midcom_helper_formatters_obfuscate_email($email,$echo_results=true)
    {
        $obfuscated = '';
        
        $len = strlen($email);
        for ($i=0;$i<$len;$i++)
        {
            $obfuscated .= "&#" . ord($email[$i]);
        }
        
        if ($echo_results)
        {
            echo $obfuscated;
        }
        else
        {
            return $obfuscated;
        }
    }
    _midcom_helper_formatters_register_filter('obfmail','_midcom_helper_formatters_obfuscate_email');
    
    function _midcom_helper_formatters_obfuscate_email_link($email,$echo_results=true)
    {
        $obfuscated = _midcom_helper_formatters_obfuscate_email($email,false);
        
        $link = "<a href=\"mailto:{$obfuscated}\">{$obfuscated}</a>";
        
        if ($echo_results)
        {
            echo $link;
        }
        else
        {
            return $link;
        }
    }
    _midcom_helper_formatters_register_filter('obfmaillink','_midcom_helper_formatters_obfuscate_email_link');
}

if (!function_exists('midcom_helper_formatters_plaintext'))
{    
    function midcom_helper_formatters_plaintext($content)
    {
        echo $content;
        echo "\n<br />---------<br />\n";
        
        $search = array
        (
            "/\r/",                                  // Non-legal carriage return
            "/[\n\t]+/",                             // Newlines and tabs
            '/[ ]{2,}/',                             // Runs of spaces, pre-handling
            '/<script[^>]*>.*?<\/script>/i',         // <script>s -- which strip_tags supposedly has problems with
            '/<style[^>]*>.*?<\/style>/i',           // <style>s -- which strip_tags supposedly has problems with
            '/<h[123][^>]*>(.*?)<\/h[123]>/ie',      // H1 - H3
            '/<h[456][^>]*>(.*?)<\/h[456]>/ie',      // H4 - H6
            '/<p[^>]*>/i',                           // <P>
            '/<br[^>]*>/i',                          // <br>
            '/<b[^>]*>(.*?)<\/b>/ie',                // <b>
            '/<strong[^>]*>(.*?)<\/strong>/ie',      // <strong>
            '/<i[^>]*>(.*?)<\/i>/i',                 // <i>
            '/<em[^>]*>(.*?)<\/em>/i',               // <em>
            '/(<ul[^>]*>|<\/ul>)/i',                 // <ul> and </ul>
            '/(<ol[^>]*>|<\/ol>)/i',                 // <ol> and </ol>
            '/<li[^>]*>(.*?)<\/li>/i',               // <li> and </li>
            '/<li[^>]*>/i',                          // <li>
            '/<a [^>]*href="([^"]+)"[^>]*>(.*?)<\/a>/ie', // <a href="">
            '/<hr[^>]*>/i',                          // <hr>
            '/(<table[^>]*>|<\/table>)/i',           // <table> and </table>
            '/(<tr[^>]*>|<\/tr>)/i',                 // <tr> and </tr>
            '/<td[^>]*>(.*?)<\/td>/i',               // <td> and </td>
            '/<th[^>]*>(.*?)<\/th>/ie',              // <th> and </th>
            '/&(nbsp|#160);/i',                      // Non-breaking space
            '/&(quot|rdquo|ldquo|#8220|#8221|#147|#148);/i', // Double quotes
            '/&(apos|rsquo|lsquo|#8216|#8217);/i',   // Single quotes
            '/&gt;/i',                               // Greater-than
            '/&lt;/i',                               // Less-than
            '/&(amp|#38);/i',                        // Ampersand
            '/&(copy|#169);/i',                      // Copyright
            '/&(trade|#8482|#153);/i',               // Trademark
            '/&(reg|#174);/i',                       // Registered
            '/&(mdash|#151|#8212);/i',               // mdash
            '/&(ndash|minus|#8211|#8722);/i',        // ndash
            '/&(bull|#149|#8226);/i',                // Bullet
            '/&(pound|#163);/i',                     // Pound sign
            '/&(euro|#8364);/i',                     // Euro sign
            '/&[^&;]+;/i',                           // Unknown/unhandled entities
            '/[ ]{2,}/'                              // Runs of spaces, post-handling
        );
        
        $replace = array
        (
            '',                                     // Non-legal carriage return
            ' ',                                    // Newlines and tabs
            ' ',                                    // Runs of spaces, pre-handling
            '',                                     // <script>s -- which strip_tags supposedly has problems with
            '',                                     // <style>s -- which strip_tags supposedly has problems with
            "strtoupper(\"\n\n\\1\n\n\")",          // H1 - H3
            "ucwords(\"\n\n\\1\n\n\")",             // H4 - H6
            "\n\n\t",                               // <P>
            "\n",                                   // <br>
            'strtoupper("\\1")',                    // <b>
            'strtoupper("\\1")',                    // <strong>
            '_\\1_',                                // <i>
            '_\\1_',                                // <em>
            "\n\n",                                 // <ul> and </ul>
            "\n\n",                                 // <ol> and </ol>
            "\t* \\1\n",                            // <li> and </li>
            "\n\t* ",                               // <li>
            '"\\1"',                                // <a href="">
            "\n-------------------------\n",        // <hr>
            "\n\n",                                 // <table> and </table>
            "\n",                                   // <tr> and </tr>
            "\t\t\\1\n",                            // <td> and </td>
            "strtoupper(\"\t\t\\1\n\")",            // <th> and </th>
            ' ',                                    // Non-breaking space
            '"',                                    // Double quotes
            "'",                                    // Single quotes
            '>',                                    // Greater-than
            '<',                                    // Less-than
            '&',                                    // Ampersand
            '(c)',
            '(tm)',
            '(R)',
            '--',
            '-',
            '*',
            '£',
            'EUR',                                  // Euro sign. € ?
            '',                                     // Unknown/unhandled entities
            ' '                                     // Runs of spaces, post-handling
        );
        
        $formatted = trim(stripslashes($content));
        $formatted = preg_replace($search, $replace, $formatted);
        $formatted = strip_tags($formatted);
        
        $formatted = preg_replace("/\n\s+\n/", "\n\n", $formatted);
        $formatted = preg_replace("/[\n]{3,}/", "\n\n", $formatted);

        echo $formatted;
    }
    _midcom_helper_formatters_register_filter('plaintext');
}

/**
 * Helpers
**/

function _midcom_helper_formatters_register_filter($name, $method=null)
{
    if ($method === null)
    {
        $method = "midcom_helper_formatters_{$name}";
    }
    
    mgd_register_filter($name, $method);
}

function _midcom_helper_formatters_replace_content(&$in, $replace, $start, $end)
{
    $begin = substr($in, 0, $start);
    $end   = substr($in, $end, strlen($in)-$end);
    $in    = $begin.$replace.$end;
}



?>