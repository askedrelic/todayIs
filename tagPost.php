<?php
class TagPost extends Post {

    public function __construct(){
        $yesterday = date( 'm/d/Y', time() - 86400 );
        $url = "http://www.lmnopc.com/greasemonkey/shacklol/api.php?format=php&date={$yesterday}&tag=tag";
        $result = parent::curlData($url);
        $tag = unserialize($result);

        $body = "_[n{The Top Tags:}n]_ \nThis other thing that no one really uses!!!\n\n";
        for($i=0; $i < 3; $i++) {
           //cleanup text for findtag
           $bad = array("<div class=\"postbody\">" , "<br />", "<br/>"); 
           $good = array("", "\n", "\n");
           $post = str_ireplace($bad, $good, $tag[$i]["body"]);
           $post = html_entity_decode($post);
           $post = parent::findtag($post);
           $body .= "_[By: y{{$tag[$i]["author"]}}y with [{$tag[$i]["tag_count"]} tagz]]_ s[http://www.shacknews.com/laryn.x?id={$tag[$i]["id"]}]s \n";
           //If the post is tagged NWS or has nws literally in it, notify the public
           if(preg_match('/nws/i', $post) || parent::isNWS($tag[$i]["id"])) {
               $body .= "r{!!!          (Possible NWS Post detected!)          !!!}r \n";
           }
           $body .= $post;
           $body .= "\n\n";
        }
        $body .= "s[Want to tag too? http://www.lmnopc.com/greasemonkey/shacklol/]s\n";
        parent::__construct($body);
    }
}
?>
