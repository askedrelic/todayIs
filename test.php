<?php
require 'simple_html_dom.php';
require 'post.php';
require 'birthdayPost.php';
require 'infPost.php';
require 'lolPost.php';
require 'randomPost.php';

//$a = new LolPost();
//print_r($a);
//echo "\n\n";
//echo $a->body;

$url = "http://www.shackchatty.com/thread/19741626.xml";
$dom = file_get_dom($url);
print $dom;


exit();
?>
