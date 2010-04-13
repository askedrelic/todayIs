<?php
class UnfPost extends Post {

    public function __construct(){
        $yesterday = date( 'm/d/Y', time() - 86400 );
        $url = "http://www.lmnopc.com/greasemonkey/shacklol/api.php?format=php&date={$yesterday}&tag=unf";
        $result = parent::curlData($url);
        $unf = unserialize($result);

        //set authors for awards
        $author = array($unf[0]["author"]);
        parent::setAuthors($author);

        $body = "_[r{The Top Unfs:}r]_ \nThis is all probably r{NWS}r, seriously, look before you click!\n\n";
        for($i=0; $i < 1; $i++) {
           //cleanup text for findtag
           $bad = array("<div class=\"postbody\">" , "<br />", "<br/>"); 
           $good = array("", "\n", "\n");
           $post = str_ireplace($bad, $good, $unf[$i]["body"]);
           $post = html_entity_decode($post);
           $post = parent::findtag($post);
           $body .= "_[By: y{{$unf[$i]["author"]}}y with [{$unf[$i]["tag_count"]} unfz]]_ s[http://www.shacknews.com/laryn.x?id={$unf[$i]["id"]}]s \n";
           $post_id = $unf[$i]["id"];
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
        $body .= "s[Want to unf too? http://www.lmnopc.com/greasemonkey/shacklol/]s\n";
        parent::__construct($body);
    }
}
?>
