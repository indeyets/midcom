<h2>Rambos playgound</h2>
<?php


 echo '<p>Current locale (LC_ALL): '.setlocale(LC_ALL,'0')."</p>\n";


$finder=new org_openpsa_event();
$finder->up=$GLOBALS['midcom_component_data']['org.openpsa.calendar']['calendar_root_event']->id;
$finder->find();
while ($finder->fetch())
{
    $ev = new org_openpsa_calendar_event($finder->id);

    //if (count($ev->participants)==0)
    //{
        //$ret=$ev->delete();
        echo "DELETED: $ev->id, ret=$ret, errstr=".mgd_errstr()."<br>\n";
        /*
    }
    else
    {
        echo "OK: $ev->id, title: $ev->title<br>\n";
    }
    */
}

/*
$ev = new org_openpsa_calendar_event(1999);
echo "ev=<pre>".sprint_r($ev)."</pre>\n";
*/
/*
unset($ev->participants[13]);
$ev->participants[3]=true;

$ev->_get_em('old_');
$ret=$ev->busy_em();
echo "ret=<pre>".sprint_r($ret)."</pre>, errstr=".mgd_errstr()."<br>\n";
$ret=$ev->_update_em();
echo "ret=<pre>".sprint_r($ret)."</pre>, errstr=".mgd_errstr()."<br>\n";
*/
/*
$ret=$ev->update();
echo "ret=<pre>".sprint_r($ret)."</pre>, errstr=".mgd_errstr()."<br>\n";

echo "ev=<pre>".sprint_r($ev)."</pre>\n";
*/


/*
$ev = new org_openpsa_calendar_event(4);
$ret=$ev->listparameters('midcom.helper.datamanager');
echo "ret=<pre>".sprint_r($ret)."</pre>, errstr=".mgd_errstr()."<br>\n";
while ($ret->fetch())
{
    echo "ret=<pre>".sprint_r($ret)."</pre>";
}
*/
/*
$ret = $ev->parameter('midcom.helper.datamanager', 'layout', '');
echo "ret=<pre>".sprint_r($ret)."</pre>, errstr=".mgd_errstr()."<br>\n";
*/
?>