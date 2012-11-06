<?php

//electroly's thread parsing
require_once 'include/Global.php';
require_once 'shackapi.php';

require_once 'post.php';
require_once 'birthdayPost.php';
require_once 'infPost.php';
require_once 'lolPost.php';
require_once 'tagPost.php';
require_once 'unfPost.php';
require_once 'awardPost.php';

//set timezone to work with DateTime objects
date_default_timezone_set('America/New_York');
function date_diff($date1, $date2) { 
    $current = $date1; 
    $datetime2 = date_create($date2); 
    $count = 0; 
    while(date_create($current) < $datetime2){ 
        $current = gmdate("Y-m-d", strtotime("+1 day", strtotime($current))); 
        $count++; 
    } 
    return $count; 
} 

class PostBot {
    public $username;
    public $password;

    public $parentId;

    private $posts = array();
    private $sleeptime;
    private $debugMode;

    public function __construct($username, $password, $sleeptime=120, $parentId = null) {
        $this->username = $username;
        $this->password = $password;

        //default sleeptime between posts
        $this->sleeptime = $sleeptime;

        //override parentId if testing
        $this->parentId = $parentId;

        $this->setRootPost();
    }

    public function setRootPost() {
        $p = new Post('');
        $dayth = $p->ord_suf(date('z')+1);
        $body = "*[y{Today is ".date('l\, \t\h\e jS \o\f F').", the {$dayth} day of ".date('Y').".}y]*\n";

        print $body;

        //$body .= system("curl -Is slashdot.org | egrep '^X-(F|B|L)' | sed s/^X-//");

        //TODO create quote database to use here
        //$body .= "\n\n";
        // $body .= "This is the Best Of shacknews:";
        //$body .= $this->insertShackconRelease();
        
        $body .= $this->insertBrownies();

        $p->body = $body;
        //make first post and override parentId
        $this->parentId = $this->post($p);
    }

    public function makePosts() {
        $this->addAwardPost();

        //post all posts in the pool
        foreach($this->posts as $p) {
            print "sleeping for {$this->sleeptime} seconds\n";
            sleep($this->sleeptime);
            print "posting {$p}\n";
            print "http://www.shacknews.com/chatty?id=" . $this->post($p);
            print "\n";
        }
    }

    public function addAwardPost() {
        $awardPost = new AwardPost($this->posts);

        if($awardPost->checkAwardWinner()) {
            print "THERE ARE AWARDS!\n";
            $this->addPost($awardPost);
        }
    }

    public function addPost($post) {
        //add a post to the pool
        array_push($this->posts, $post);
    }

    private function post($post) {
        try {
            return ShackApi::post($this->username, $this->password, $post->body, $this->parentId);
        } catch (Exception $e) {
            while (true) {
                print "sleeping 300 secs\n";
                sleep(300);
                return ShackApi::post($this->username, $this->password, $post->body, $this->parentId);
            }
        }
    }

    private function insertBrownies() {
        $today = new DateTime("now");
        $day = $today->format("Y-m-d");                                                                                                                                                                                                                                                 

        $ret = "\n";
        $ret .= "HEY p[MULTISYNC]p buy some delicious ";

        if ($day == "2012-10-17") {
            $ret .= "Today only, r{Chocolate Overload Espresso Brownies}r";
        } elseif ($day == "2012-10-18") {
            $ret .= "Today only, r{Salted Caramel Chocolate Cookies}r";
        } elseif ($day == "2012-10-19") {
            $ret .= "r{Kiss of Chocolate Cherry Cookies}r";
        } elseif ($day == "2012-10-22") {
            $ret .= "r{Chewy Lemon Snowball Cookies}r";
        } elseif ($day == "2012-10-23") {
            $ret .= "r{Butterfinger Blast Crunchy Cookies}r";
        } elseif ($day == "2012-10-24") {
            $ret .= "r{Pumpkin Chocolate Chip Cake Bars}r";
        } elseif ($day == "2012-10-25") {
            $ret .= "r{Apple Cider Caramel Cookies}r";
        } elseif ($day == "2012-10-30") {
            $ret .= "r{Puff Power Cinnamon Snickerdoodles}r";
        } elseif ($day == "2012-10-31") {
            $ret .= "r{Banana Bread Chocolate Chip Cookies}r";
        } elseif ($day == "2012-11-01") {
            $ret .= "r{Cinnamon Hazelnut Biscotti}r";
        } else {
            return "";
        }
        $ret .= ", today only at http://www.defconyum.com/\n";
        return $ret;
    }

    private function insertShackconRelease() {
        $launch_date = new DateTime('2012-07-08');
        $today = new DateTime("now");
        $interval = date_diff($launch_date, $today);
        if ($interval->d > 1) {
            return "There are ". $interval->d ." days until Shackcon 2012!\n";
        } elseif ($interval->d == 1) {
            return "There is ". $interval->d ." day until Shackcon 2012!\n";
        } elseif ($interval->d == 0) {
            return "ZOMG VEGAS SHACKCON!!!\n";
        }
    }
}
?>
