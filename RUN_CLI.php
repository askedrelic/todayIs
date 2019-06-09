<?php
// Author: Matt Behrens <askedrelic@gmail.com>
// PHP library to post birthdays, daily info, and awards on shacknews.com forums.
// Designed to be run from the CLI

require 'postbot.php';
set_time_limit(3600);

//Create new Postbot with your Shacknews username and password
$a = new PostBot($_SERVER['USER'],$_SERVER['PASS']);

//Add whichever posts you want
$a->addPost(new BirthdayPost($_SERVER['DB_HOST'],$_SERVER['DB_USER'],$_SERVER['DB_PASS']));
//$a->addPost(new LolPost());
//$a->addPost(new TagPost());
//$a->addPost(new InfPost());
//$a->addPost(new ShackBattlesPost());

//Post them!
$a->makePosts();
