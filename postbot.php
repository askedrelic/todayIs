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
require_once 'shackmeetsPost.php';
require_once 'shackbattlesPost.php';

//set timezone to work with DateTime objects
date_default_timezone_set('America/New_York');
function date_diff2($date1, $date2) {
    $current = $date1;
    $datetime2 = date_create($date2);
    $count = 0;
    while(date_create($current) < $datetime2){
        $current = gmdate("Y-m-d", strtotime("+1 day", strtotime($current)));
        $count++;
    }
    return $count;
}

function differenceInDays($firstDate, $secondDate){
    $firstDateTimeStamp = $firstDate->format("U");
    $secondDateTimeStamp = $secondDate->format("U");
    $rv = round ((($firstDateTimeStamp - $secondDateTimeStamp))/86400);
    return $rv;
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

        //$body .= system("curl -Is slashdot.org | egrep '^X-(F|B|L)' | sed s/^X-//");

        //TODO create quote database to use here
        //$body .= "\n\n";
        // $body .= "This is the Best Of shacknews:";

        //$body .= "\n";
        //$body .= $this->insertShackconRelease();

        //$snacks = $this->insertBrownies();
        //if (!empty($snacks)) {
        //    $body .= $snacks;
        //}

        $p->body = $body;
        //make first post and override parentId
        $this->parentId = $this->post($p);
    }

    public function makePosts() {
        $this->addAwardPost();

        //post all posts in the pool
        foreach($this->posts as $p) {
            print "--> on post {$p}\n";
            print "--> sleeping for {$this->sleeptime} seconds\n";
            sleep($this->sleeptime);
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
            print "\n---------\n";
            print $post->body;
            print "\n---------\n";
            return ShackApi::post($this->username, $this->password, $post->body, $this->parentId);
        } catch (Exception $e) {
            print "--> exception while posting {$e}\n";
            while (true) {
                print "--> exception caused sleep 120 secs\n";
                sleep(120);
                return ShackApi::post($this->username, $this->password, $post->body, $this->parentId);
            }
        }
    }

    private function insertBrownies() {
        //sigh unix > php
        //$snacks = system('curl -s http://www.defconyum.com/askedrelic.php');
        //if (empty($snacks) || strripos($snacks, "closed") !== false) {
        //    return "";
        //}

        $ret = "\n";
        /* $ret .= "HEY p[MULTISYNC]p buy some delicious "; */
        $ret .= "Rest In Crumbles Defcon YUM :(\n";
        $ret .+ "http://www.defconyum.com/goodbye.php\n";

        /* $ret .= ", today only at http://www.defconyum.com/\n"; */

        //if ($day == "2012-10-17") {
        //    $ret .= "Today only, r{Chocolate Overload Espresso Brownies}r";
        //} elseif ($day == "2012-10-25") {
        //    $ret .= "r{Apple Cider Caramel Cookies}r";
        //} else {
        //    return "";
        //}

        return $ret;
    }

    private function insertShackconRelease() {
        $launch_date = new DateTime('2013-07-04');
        $today = new DateTime("now");
        $interval = differenceInDays($launch_date, $today);
        if ($interval > 1) {
            return "There are ". $interval ." days until Shackcon 2013!\n";
        } elseif ($interval == 1) {
            return "There is ". $interval ." day until Shackcon 2013!\n";
        } elseif ($interval == 0) {
            return "ZOMG VEGAS SHACKCON!!!\n";
        }
    }
}
?>
