<?php
class UnfPost extends Post {

    public function __construct(){
        $yesterday = date( 'm/d/Y', time() - 86400 );
        $url = "http://www.lmnopc.com/greasemonkey/shacklol/api.php?format=php&date={$yesterday}&tag=unf";
        $result = parent::curlData($url);
        $unf = unserialize($result);

        $body = "_[g{The Top Unfs:}g]_ \nThis is all probably r{NWS}r, seriously, look before you click!\n\n";
        for($i=0; $i < 1; $i++) {
           //cleanup text for findtag
           $bad = array("<div class=\"postbody\">" , "<br />", "<br/>"); 
           $good = array("", "\n", "\n");
           $post = str_ireplace($bad, $good, $unf[$i]["body"]);
           $post = html_entity_decode($post);
           $post = parent::findtag($post);
           $body .= "_[By: y{{$unf[$i]["author"]}}y with [{$unf[$i]["tag_count"]} unfz]]_ s[http://www.shacknews.com/laryn.x?id={$unf[$i]["id"]}]s\n".$post."\n\n";
        }
        $body .= "s[Want to unf too? http://www.lmnopc.com/greasemonkey/shacklol/]s\n";
        parent::__construct($body);
    }
}
?>
