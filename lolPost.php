<?php
class LolPost extends Post {

    public function __construct(){
        $yesterday = date( 'm/d/Y', time() - 86400 );
        $url = "http://www.lmnopc.com/greasemonkey/shacklol/api.php?format=php&date={$yesterday}&tag=lol";
        $result = parent::curlData($url);
        $lol = unserialize($result);

        $body = "_[g{This Old LOL Pile:}g]_ \nThe top 5 y{LOL'D}y posts from yesterday. Don't miss the funny, if there was any to begin with! \n\n";

        //multiplier bonus section
        //This whole section is terrible I must say
        $single = array();
        $double = array();
        for($i=0; $i < count($lol); $i++) {
            $author = $lol[$i]["author"];
            array_push($single, $author);
        }
        for($i=0; $i < count($single); $i++) {
            if(count(array_keys($single, $single[$i])) > 1) {
                array_push($double, $single[$i]);
            }
        }
        $double = array_unique($double);
        foreach($double as $key) {
            $body .= "*[Multiloller bonus for y{{$key}}y!!!!]*\n\n";
        }

        //$lol has methods body, author, tag_count, id
        for($i=0; $i < count($lol); $i++) {
           //cleanup text for findtag
            $bad = array("<div class=\"postbody\">" , "<br />"); 
            $good = array("", "\n");
            $post = str_ireplace($bad, $good, $lol[$i]["body"]);
            $post = html_entity_decode($post);
            $post = parent::findtag($post);
            $star = "";
            if($i == 0) {
                $star = html_entity_decode("&#9733;", ENT_NOQUOTES, 'UTF-8');
            }
            $body .= "_[{$star} By: y{{$lol[$i]["author"]}}y with [{$lol[$i]["tag_count"]} lolz {$star}]]_ s[http://www.shacknews.com/laryn.x?id={$lol[$i]["id"]}]s \n";
            if(preg_match('/nws/i', $post) || parent::isNWS($lol[$i]["id"])) {
                $body .= "r{!!!          (Possible NWS Post detected!)          !!!}r \n";
            }
            $body .= $post;
            $body .= "\n\n";
        }
        $body .= "s[Want to LOL too? http://www.lmnopc.com/greasemonkey/shacklol/]s\n";
        parent::__construct($body);
    }
}
?>
