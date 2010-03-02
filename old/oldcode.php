<?php
require 'simple_html_dom.php';
require 'post.php';
require 'birthdayPost.php';
require 'infPost.php';
require 'lolPost.php';
require 'randomPost.php';

// $a = new BirthdayPost();
//print_r($a);
//echo "\n\n";
// echo $a->body;

$url = "http://shackchatty.com/search.xml?author=Steve+Gibson";
$dom = file_get_dom($url);
$postFirst = $dom->find("comment[author]",0);



$str = $postFirst->date;

if (($timestamp = strtotime($str)) === false) {
        echo "The string ($str) is bogus";
} else {
        echo "$str == " . date('l dS \o\f F Y h:i:s A', $timestamp);
}

$end = date('Y-m-d');
if( $diff=@get_time_difference($str, $end) )
{
       echo sprintf( 'days : %02d', $diff['days']);
}
else
{
  echo "Hours: Error";
}

// print $postFirst->date;
//
print "\n";

function get_time_difference( $start, $end )
{
    $uts['start']      =    strtotime( $start );
    $uts['end']        =    strtotime( $end );
    if( $uts['start']!==-1 && $uts['end']!==-1 )
    {
        if( $uts['end'] >= $uts['start'] )
        {
            $diff    =    $uts['end'] - $uts['start'];
            if( $days=intval((floor($diff/86400))) )
                $diff = $diff % 86400;
            if( $hours=intval((floor($diff/3600))) )
                $diff = $diff % 3600;
            if( $minutes=intval((floor($diff/60))) )
                $diff = $diff % 60;
            $diff    =    intval( $diff );            
            return( array('days'=>$days, 'hours'=>$hours, 'minutes'=>$minutes, 'seconds'=>$diff) );
        }
        else
        {
            trigger_error( "Ending date/time is earlier than the start date/time", E_USER_WARNING );
        }
    }
    else
    {
        trigger_error( "Invalid date/time data detected", E_USER_WARNING );
    }
    return( false );
}


?>
