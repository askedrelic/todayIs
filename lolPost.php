<?php
class LolPost extends Post {

    public function __construct(){
        $yesterday = date("m")."/".(date("d")-1)."/".date("Y");
        $url = "http://www.lmnopc.com/greasemonkey/shacklol/top5feed.php?date={$yesterday}&format=serialized";
        $result = parent::curlData($url);
        $lol = unserialize($result);

        //multiplier bonus section


        //$lol has methods thread_id, cnt, who, post_author, post, post_date
        $body = "_[g{This Old LOL Pile:}g]_ \nThe top 5 y{LOL'D}y posts from yesterday. Don't miss the funny, if there was any to begin with! \n\n";
        for($i=0; $i < count($lol); $i++) {
           //cleanup text for findtag
            $bad = array("<div class=\"postbody\">" , "<br />"); 
            $good = array("", "\n");
            $post = str_ireplace($bad, $good, $lol[$i]->post);
            $post = html_entity_decode($post);
            $post = parent::findtag($post);
            $body .= "_[By: y{{$lol[$i]->post_author}}y with [{$lol[$i]->cnt} lolz] s[http://www.shacknews.com/laryn.x?id={$lol[$i]->thread_id}]s]_ \n";

            //TODO: pull down post and see if it is marked NWS
            if(preg_match('/nws/i', $post)) {
                $body .= "r{!!!!!          Possible NWS detected!          !!!!!}r \n";
            } 
            $body .= $post;

            //TODO: is this extra \n needed?
            if(preg_match('/nws/i', $post)) {
                $body .= "\n r{!!!!!!!!!!}r \n";
            } 
            $body .= "\n\n";
        }
        $body .= "s[Want to LOL too? http://www.lmnopc.com/greasemonkey/shacklol/]s\n";
        parent::__construct($body);
    }
}
?>
