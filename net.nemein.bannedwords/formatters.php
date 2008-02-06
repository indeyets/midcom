<?php
/**
 * @package net.nemein.bannedwords
 */

/**
 * These are custom Midgard formatter functions for
 * filttering banned words
 */
function net_nemein_bannedwords_html_formatter($content)
{
    // TODO: should propably encode as html like the original :h

    $banned = new net_nemein_bannedwords_handler();
    $processed_content = $banned->search_and_replace_html($content);

    echo $processed_content;
}

function net_nemein_bannedwords_plain_formatter($content)
{
    $banned = new net_nemein_bannedwords_handler();
    $processed_content = $banned->search_and_replace_plain($content);

    echo $processed_content;
}

/**
 * Registering the custom formatters to Midgard's
 * formatting engine
 */
mgd_register_filter('nnbwh', 'net_nemein_bannedwords_html_formatter');
mgd_register_filter('nnbwf', 'net_nemein_bannedwords_plain_formatter');

?>