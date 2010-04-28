<?php
class InfPost extends Post {
    public $tag = "_[b{INF}b]_";

    public function __construct(){
        $yesterday = date( 'm/d/Y', time() - 86400 );
        $url = "http://www.lmnopc.com/greasemonkey/shacklol/api.php?format=php&date={$yesterday}&tag=inf";
        $result = parent::curlData($url);
        $inf = unserialize($result);

        //set authors for awards
        $author = array($inf[0]["author"]);
        parent::setAuthors($author);

        //$lol has methods body, author, tag_count, id
        #$body = "_[g{The Top INF:}g]_ \nThe b{smartest}b thing that was said yesterday. Get yourself to school. \n\n";
        $body = "_[b{The Top INF:}b]_ \n\"The thing that was posted yesterday that compelled the most amount of people to hit the inf button.\"\n\n";
        for($i=0; $i < 1; $i++) {
           //cleanup text for findtag
           $bad = array("<div class=\"postbody\">" , "<br />", "<br/>"); 
           $good = array("", "\n", "\n");
           $post = str_ireplace($bad, $good, $inf[$i]["body"]);
           $post = html_entity_decode($post);
           $post = parent::findtag($post);
           $body .= "_[By: y{{$inf[$i]["author"]}}y with [{$inf[$i]["tag_count"]} infz]]_ s[http://www.shacknews.com/laryn.x?id={$inf[$i]["id"]}]s \n";
           $post_id = $inf[$i]["id"];
           //If the post is tagged NWS or has nws literally in it, notify the public
           if(preg_match('/nws/i', $post) || parent::isNWS($post_id)) {
               $body .= "r{!!!          (Possible NWS Post detected!)          !!!}r \n";
           }
           if(!parent::isNuked($post_id)) {
               $body .= $post;
           } else {
               $body .= Post::$NUKED_TEXT;
           }
           $body .= "\n\n";
        }
        $body .= "s[Want to INF too? http://www.lmnopc.com/greasemonkey/shacklol/]s\n";
        parent::__construct($body);
    }
}
?>
