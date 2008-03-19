'default_ruleset' => 'default',
'rulesets' => array
(
    'default' => array
    (
        /* Schemadb and default schema name to use */
        'schemadb' => 'file:/net/nehmer/static/config/schemadb_default.inc',
        'schema_name' => 'default',
        /* Field mapping rules, first match wins */
        'field_map' => array
        (
            array
            (
                'type' => 'xpath',
                'path' => '//*/h1',
                'matches_key' => 0, // which key of the array returned from $simplexml->xpath() to use, -1 means all keys
                'field' => 'title',
                'purify' => false,
            ),
            array
            (
                'type' => 'preg_match',
                'regex' => '%<!-- BEGIN:content -->(.*?)<!-- END:content -->%ms',
                'matches_key' => 1, // as usual, 0 is entire match, 0+N is Nth group
                'field' => 'content',
                'purify' => true,
            ),
        ),
    ),
),
'mb_detect_encoding_list' => 'ASCII,JIS,UTF-8,ISO-8859-1,EUC-JP,SJIS',
'iconv_append_target' => '//TRANSLIT', //Transliterate by default (other valid options '//IGNORE' or null)
