<?php
$_MIDCOM->auth->require_valid_user('basic');
$config = $GLOBALS['midcom_component_data']['net.nemein.discussion']['config'];

$forum = new midcom_db_topic($config->get('venetsia_import_forum'));
if (   !$forum
    || !$forum->guid)
{
    $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Forum ' . $config->get('venetsia_import_forum') . ' not found.');
    // This will exit
}

$forum->require_do('midgard:create');

//"http://partner.mtv3.fi/nemein/xml_uk/{$datestring}.xml"
$url = str_replace('__DATE__', date('Ymd', time()), $config->get('venetsia_import_url');
$xml = file_get_contents($url);
$simplexml = simplexml_load_string($xml);

$featured_series = $config->get('venetsia_import_series');

foreach ($simplexml->PROGRAM as $program)
{
    if (!in_array($program->SARJANO, $featured_series))
    {
        continue;
    }
    
    $start = strtotime("{$program->STARTDATE}T{$program->STARTTIME}");
    if (   $start - 1800 > time()
        || $start < time())
    {
        // Ignore everything that is not starting within half hour
        continue;
    }   
    
    $end = strtotime("{$program->ENDDATE}T{$program->ENDTIME}");
    
    $thread = new net_nemein_discussion_thread_dba();
    $thread->node = $forum->id;
    $thread->title = (string) $program->NAME;
    $thread->create();
    $thread->parameter('net.nemein.discussion', 'thread_type', 'program');
    $thread->parameter('net.nemein.discussion', 'program', $program->SARJANO);
    
    $post = new net_nemein_discussion_post_dba();
    $post->subject = (string) $program->NAME;
    $post->content = (string) $program->DESCRIPTION;
    $post->thread = $thread->id;
    $post->sendername = 'AVA';
    $post->status = 5;
    $post->create();
    
    $thread->name = (string) $program->OHESNO;
    $thread->firstpost = $post->id;
    $thread->latestpost = $post->id;
    $thread->latestposttime = $post->metadata->published;
    $thread->update();
}
?>