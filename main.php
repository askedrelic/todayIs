<?php
require 'simple_html_dom.php';
require 'post.php';
require 'birthdayPost.php';
require 'infPost.php';
require 'lolPost.php';
require 'randomPost.php';

class PostBot{ 
    public $username;
    public $password;

    public $parentId;
    public $groupId;

    private $posts = array();
    private $sleeptime;
    private $latestUrl = 'http://www.shacknews.com/latestchatty.x';
    private $postUrl = 'http://www.shacknews.com/extras/post_laryn_iphone.x';

    public function __construct($username, $password, $sleep){
        $this->username = $username;
        $this->password = $password;
        $this->sleeptime = $sleep;
    }

    public function setLatestChattyUrl() {
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
        //set the group on the post
        $this->groupId = substr($groupTemp, strlen($groupTemp)-5, strlen($groupTemp));

        curl_close($ch);
        }

    public function setFirstPost() {
        $p = new Post('');
        $dayth = $p->ord_suf(date('z')+1);
        $body = "*[y{Today is ".date('l\, \t\h\e jS \o\f F').", the {$dayth} day of ".date('Y').".}y]*\n";

        //$body .= "This is your life shackers, enjoy it.";
        $body .= system("curl -Is slashdot.org | egrep '^X-(F|B|L)' | cut -d \- -f 2");
        
        $p->body = $body;
        //make first post
        self::post($p);

        //get the latest chatty and parse for the last post by my username...
        //$dom = file_get_dom("http://chatty.elrepositorio.com/{$this->groupId}.xml");
        $dom = file_get_dom("http://shackchatty.com/{$this->groupId}.xml");
        $v = $dom->find("comment[author={$this->username}]",0);

        $this->parentId = $v->id;

        //Post URL to API
        shell_exec("echo {$v->id} > /home/askedrelic/public_html/asktherelic.com/public/shack/todayis.txt");
        }

    public function addPost($post) {
        //add a post to the pool
        array_push($this->posts, $post);
        }

    public function makePosts() {
        //loop through all posts and post em!
        foreach($this->posts as $p) {
            sleep($this->sleeptime);
            $result = self::post($p);
            if(preg_match("/Please wait a few minutes/i", $result)){
                sleep(600);
                self::post($p);
            }
        }
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
        curl_close($ch);
        return $result;
        }
}

$a = new PostBot('askedrelic','xXxXxXxXxXx', 180);
$a->setLatestChattyUrl();
$a->setFirstPost();

$a->addPost(new BirthdayPost());
$a->addPost(new LolPost());
$a->addPost(new InfPost());
//$a->addPost(new RandomPost());

$a->makePosts();
?>
