<?php
//Author: Matt Behrens <askedrelic@gmail.com>
//PHP library to post birthdays, daily info, and awards on shacknews.com forums.

require 'postbot.php';
set_time_limit(3600);

//Create new Postbot with your Shacknews username and password
$a = new PostBot('USERNAME','PASSWORD');

//Add whichever posts you want
$a->addPost(new BirthdayPost());
$a->addPost(new LolPost());
$a->addPost(new TagPost());
$a->addPost(new InfPost());
$a->addPost(new ShackmeetsPost());

//Post them!
$a->makePosts();
