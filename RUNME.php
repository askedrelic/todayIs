<?php
//Author: Matt Behrens <askedrelic@gmail.com>
//PHP library to post birthdays, daily info, and awards on shacknews.com forums.

require 'postbot.php';

//Create new Postbot with your Shacknews username and password
$a = new PostBot('USERNAME','PASSWORD');

//Add whichever posts you want
$a->addPost(new BirthdayPost());
$a->addPost(new LolPost());
$a->addPost(new TagPost());
$a->addPost(new UnfPost());
$a->addPost(new InfPost());

//Post them!
$a->makePosts();
?>
