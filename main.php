<?php
class Post{ 
    public $body;

    public function __construct($body){
        $this->body = $body;
    }

    public function encodePost(){
        return urlencode($this->body);
    }

    public function __toString(){
        return $this->body;
    }
}

class PostBot{ 
    public $username;
    public $password;

    public $body;
    public $parentId;
    public $groupId;

    private $posts = array();
    private $sleeptime = 200;
    private $latestUrl = 'http://www.shacknews.com/latestchatty.x';
    private $postUrl = 'http://www.shacknews.com/extras/post_laryn_iphone.x';

    public function __construct($username, $password){
        $this->username = $username;
        $this->password = $password;
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
        $body = "*[y{Today is ".date('l\, \\t\h\e jS \o\f F\, \\d\a\y z \o\f Y').".}y]*\n";
        $body .= "This is your life shackers, enjoy it.";
        
        //make first post
        self::post(new Post($body));

        //get the latest chatty and parse for the last post by my username...
        //$dom = file_get_dom("http://shackchatty.com/index.xml");
        //$v = $dom->find("comment[author={$username}]",0);

        //save parent id
        //$this->parentId = $v->id;
        $this->parentId = '232323';
        }

    public function addPost($post) {
        //add a post to the pool
        array_push($this->posts, $post);
    }

    public function makePosts() {
        //loop through all posts and post em!
        foreach($this->posts as $p) {
            sleep($this->sleeptime);
            self::post($p);
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
        $fields .= '&parent='.urlencode($this->parentId); //should fail first time?
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
        //$result = curl_exec($ch);

        echo $fields."\n";

        curl_close($ch);
        }
}

$a = new PostBot('askedrelic','behrens');
$a->setLatestChattyUrl();
$a->setFirstPost();

$b = new Post('lol1');
$c = new Post('lol5');

$a->addPost($b);
$a->addPost($c);

$a->makePosts();
?>
