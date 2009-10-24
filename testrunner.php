<?php
require 'simple_html_dom.php';
require 'post.php';
require 'birthdayPost.php';
require 'infPost.php';
require 'lolPost.php';
require 'randomPost.php';

$a = new BirthdayPost();
print_r($a);
echo "\n\n";
echo $a->body;

?>
