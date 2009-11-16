<?php
require 'simple_html_dom.php';
require 'post.php';
require 'birthdayPost.php';
require 'infPost.php';
require 'lolPost.php';
require 'tagPost.php';
require 'unfPost.php';
require 'randomPost.php';


// $a = new BirthdayPost();
// print_r($a);
$a = new LolPost();
echo $a->body;
// print_r($a);
// $a = new InfPost();
// echo $a->body;
// print_r($a);
// $a = new TagPost();
// print_r($a);
// echo $a->body;

echo "\n\n";
?>
