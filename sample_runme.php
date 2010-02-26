<?php
require 'postbot.php';

$a = new PostBot('USERNAME','PASSWORD');

$a->addPost(new BirthdayPost());
$a->addPost(new LolPost());
$a->addPost(new TagPost());
$a->addPost(new UnfPost());
$a->addPost(new InfPost());

$a->makePosts();
?>
