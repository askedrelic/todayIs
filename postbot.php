<?php

//electroly's thread parsing
require_once 'include/Global.php';

require_once 'post.php';
require_once 'birthdayPost.php';
require_once 'infPost.php';
require_once 'lolPost.php';
require_once 'tagPost.php';
require_once 'unfPost.php';
require_once 'awardPost.php';


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

    public function __construct($username, $password, $sleeptime=120, $group=NULL) {
        $this->username = $username;
        $this->password = $password;

        //default sleeptime between posts
        $this->sleeptime = $sleeptime;

        //post to specific story id
        if($group === NULL) {
            $this->groupId = self::getLatestChattyId();
        } else {
            $this->groupId = $group;
        }

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

        $body .= system("curl -Is slashdot.org | egrep '^X-(F|B|L)' | sed s/^X-//");

        //TODO create quote database to use here
        $body .= "\n\n";
        $body .= "This is the Best Of shacknews:";

        $p->body = $body;
        //make first post
        self::post($p);

        //get the latestchatty page and parse for the last post by my username...
        $parent_id = self::getIdFromChatty($this->username, $this->groupId);

        //if no parent id is set, stop posting and exit
        if($parent_id !== -1) {
            $this->parentId = $parent_id;
            print "root post: http://www.shacknews.com/laryn.x?id={$parent_id}\n";
        } else {
            print "bad parentid: {$parent_id}\n";
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

    private function getIdFromChatty($username, $id) {
        $parser = new ChattyParser();
        $names = $parser->getStory($id,0);
        //check all latestchatty threads
        for($i = 0; $i < count($names); $i++) {
            $thread_author = $names['threads'][$i]['author'];
            $thread_id = $names['threads'][$i]['id'];

            //return for the the first matching username thread
            if(strcasecmp($username, $thread_author) == 0) {
                return $thread_id;
            }
        }
        //if username can't be found in latestchatty
        return -1;
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
