<?php
require 'simple_html_dom.php';
require 'post.php';
require 'birthdayPost.php';
require 'infPost.php';
require 'lolPost.php';
require 'tagPost.php';
require 'unfPost.php';
require 'randomPost.php';
require 'awardPost.php';

class PostBot{ 
    public $username;
    public $password;

    public $parentId;
    public $groupId;

    private $posts = array();
    private $sleeptime;
    private $debugMode;
    private $latestUrl = 'http://www.shacknews.com/latestchatty.x';
    private $postUrl = 'http://www.shacknews.com/extras/post_laryn_iphone.x';

    public function __construct($username, $password) {
        $this->username = $username;
        $this->password = $password;

        //default sleeptime between posts
        $this->sleeptime = 90;

        //debug mode, set to post to specific chatty id
        $this->groupId = self::getLatestChattyId();

        self::setRootPost();
    }

    public function getLatestChattyId() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, "Firefox 5.0");
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_URL, $this->latestUrl);
        $result = curl_exec($ch);

        //pull last 5 digits of latest chatty URL
        $groupTemp = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

        curl_close($ch);

        //return the group on the post
        return substr($groupTemp, strlen($groupTemp)-5, strlen($groupTemp));
    }

    public function setRootPost() {
        $p = new Post('');
        $dayth = $p->ord_suf(date('z')+1);
        $body = "*[y{Today is ".date('l\, \t\h\e jS \o\f F').", the {$dayth} day of ".date('Y').".}y]*\n";

        //TODO create quote database to use here
        //$body .= "This is your life shackers, enjoy it.";
        $body .= system("curl -Is slashdot.org | egrep '^X-(F|B|L)' | sed s/^X-//");

        $p->body = $body;
        //make first post
        self::post($p);

        //get the latestchatty page and parse for the last post by my username...
        //$dom = file_get_dom("http://chatty.elrepositorio.com/{$this->groupId}.xml");
        $dom = file_get_dom("http://shackchatty.com/{$this->groupId}.xml");
        $v = $dom->find("comment[author={$this->username}]",0);

        #if no parent id is set or something crazy, stop posting and exit
        if($v->id !== 0 and $v->id >= 1) {
            $this->parentId = $v->id;
            print "root post: http://www.shacknews.com/laryn.x?id={$v->id}\n";
        } else {
            print "bad parentid: {$v->id}\n";
            exit(1);
        }

        //Post URL to API
        // if(!$this->debugMode) {
        //     shell_exec("echo {$v->id} > /home/askedrelic/public_html/asktherelic.com/public/shack/todayis.txt");
        // }
    }

    public function makePosts() {
        self::addAwardPost();

        //post all posts in the pool
        foreach($this->posts as $p) {
            print "sleeping for {$this->sleeptime} seconds\n";
            sleep($this->sleeptime);
            print "posting {$p}\n";
            self::post($p);
        }
    }

    public function addAwardPost() {
        $awardPost = new AwardPost($this->posts);

        if($awardPost->checkAwardWinner()) {
            print "THERE ARE AWARDS!\n";
            self::addPost($awardPost);
        }
    }

    public function addPost($post) {
        //add a post to the pool
        array_push($this->posts, $post);
    }

    private function post($post) {
        //    * iuser: username
        //    * ipass: password
        //    * parent: The ID of the post that is being replied to. Leave blank it its a new root post.
        //    * group: The story ID this post is getting attached to.
        //    * body: The text content of the comment.
        $body = $post->encodePost();
        $fields = 'iuser='.urlencode($this->username);
        $fields .= '&ipass='.urlencode($this->password);
        $fields .= '&parent='.urlencode($this->parentId);
        $fields .= '&group='.urlencode($this->groupId);
        $fields .= '&body='.$post->encodePost();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, "Firefox 5.0");
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_URL, $this->postUrl);
        $result = curl_exec($ch);

        print "result: \n\n".$result."\n";
        //check for PRL
        if(preg_match("/Please wait a few minutes/i", $result)){
            print "sleeping for 420 seconds";
            sleep(420);
            $result = curl_exec($ch);
        }

        curl_close($ch);
    }

    private function insertCustomItems() {
        //TODO add custom today is items
        $cdate = mktime(0, 0, 0, 8, 13, 2009, 0);
        $today = time();
        $difference = $cdate - $today;
        if ($difference > 0) {
            $body .= "There are /[OMG]/ ".floor($difference/60/60/24)." days until Quakecon!!!!\n";
        } elseif ($difference == 0) {
            $body .= "HOLY SHIT IT'S QUAKECON TIME";
        }
    }
}
?>
