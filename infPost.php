<?php
class InfPost extends Post {

    public function __construct(){
        $yesterday = date("m")."/".(date("d")-1)."/".date("Y");
        $url = "http://www.lmnopc.com/greasemonkey/shacklol/top5feed.php?date={$yesterday}&tag=inf&format=serialized";
        $result = parent::curlData($url);
        $inf = unserialize($result);

        //$lol has methods thread_id, cnt, who, post_author, post, post_date
        $body = "_[g{The Top INF:}g]_ \nThe b{smartest}b thing that was said yesterday. Get yourself to school. \n\n";
        for($i=0; $i < 1; $i++) {
           //cleanup text for findtag
           $bad = array("<div class=\"postbody\">" , "<br />"); 
           $good = array("", "\n");
           $post = str_ireplace($bad, $good, $inf[$i]->post);
           $post = html_entity_decode($post);
           $post = parent::findtag($post);
           $body .= "_[By: y{{$inf[$i]->post_author}}y with [{$inf[$i]->cnt} infz] s[http://www.shacknews.com/laryn.x?id={$inf[$i]->thread_id}]s]_ \n".$post."\n\n";
        }
        $body .= "s[Want to INF too? http://www.lmnopc.com/greasemonkey/shacklol/]s\n";
        parent::__construct($body);
    }
}
?>
