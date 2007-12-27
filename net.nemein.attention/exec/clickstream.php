<?php
$_MIDCOM->auth->require_valid_user();

$content = file_get_contents("php://input");
//$content = file_get_contents('/tmp/attention_log.xml');
$clickstream = simplexml_load_string($content);

$attrs = $clickstream->attributes();
if (!isset($attrs['recorderGUID']))
{
    $this->generate_error(MIDCOM_ERRCRIT, 'No recorder GUID defined');
}

if (!isset($clickstream->httpTransactions->httpTransaction))
{
    $this->generate_error(MIDCOM_ERRCRIT, 'No transactions found');
}

$transaction = $clickstream->httpTransactions->httpTransaction;
if (!isset($transaction->url))
{
    $this->generate_error(MIDCOM_ERRCRIT, 'No click URL defined');
}

$recordguid = (string) $attrs['recorderGUID'] . '_' . md5((string) $transaction->url) . '_' . md5((string) $transaction->date);
$recordguid = str_replace('{', '', $recordguid);
$recordguid = str_replace('}', '', $recordguid);
$qb = net_nemein_attention_click_dba::new_query_builder();
$qb->add_constraint('person', '=', $_MIDGARD['user']);
$qb->add_constraint('name', '=', $recordguid);
$clickstreams = $qb->execute();
if (count($clickstreams) == 0)
{
    $click = new net_nemein_attention_click_dba();
    $click->person = $_MIDGARD['user'];
    $click->name = $recordguid;
    if (!$click->create())
    {
        $this->generate_error(MIDCOM_ERRCRIT, 'Failed creating click: ' . mgd_errstr());
    }
}
else
{
    $click = $clickstreams[0];
}

// Split the request URL into more usable parts
$url_parts = parse_url((string) $transaction->url);            
foreach ($url_parts as $key => $value) 
{
    switch ($key)
    {
        case 'scheme':
            $click->protocol = $value;
            break;
        case 'user':
            $click->username = $value;
            break;
        case 'pass':
            $click->password = $value;
            break;
        case 'host':
            $click->hostname = $value;
            break;
        case 'port':
            $click->port = (int) $value;
            break;
        case 'path':
            $click->path = $value;
            break;
        case 'query':
            $click->query = $value;
            break;
        case 'fragment':
            $click->fragment = $value;
            break;
    }
}

// Store the rest of the data
$click->title = (string) $transaction->title;
$click->cookie = (boolean) $transaction->cookie;
$click->setcookie = (boolean) $transaction->setCookie;
$click->responsecode = (int) $transaction->responseCode;
$click->method = (string) $transaction->method;
$click->metadata->published = strtotime((string) $transaction->date);

// Some metadata
$click->useragent = $_SERVER['HTTP_USER_AGENT'];
$click->ip = $_SERVER['REMOTE_ADDR'];

if (!$click->update())
{
    $this->generate_error(MIDCOM_ERRCRIT, 'Failed storing click: ' . mgd_errstr());
}
?>