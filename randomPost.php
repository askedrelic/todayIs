<?php
class RandomPost extends Post {

    public function __construct(){
        $post;
        $good = 0;
        $randId = mt_rand(1,18514293);
        $randUrl = "http://www.shackchatty.com/thread/{$randId}.xml";

        $dom = file_get_dom($randUrl);
        foreach($dom->find('comment') as $element) {
            if($element->reply_count >= $good) {
                $good = $element->reply_count;
                $post = $element;
            }
        }

        $shackUrl = "http://www.shacknews.com/laryn.x?id=".$post->id;

        $body = "_[g{Random Post:}g]_ \n";
        $body .= "_[By: y{".trim($post->author)."}y s[$shackUrl]s]_ \n";
        $body .= self::cleanText($post->children(0)->innertext)."\n";

        parent::__construct($body);
    }

    private function cleanText($text)
    {
        $tmp = htmlspecialchars_decode($text);
        //$tmp = html_entity_decode($tmp);

        //removes &13
        $tmp = preg_replace('~&#([0-9a-f]+);~ei','', $tmp);
        //converts <br/> to \n
        $tmp = preg_replace('/<br\\s*?\/??>/i', "\n", $tmp);
        //converts html tags to shacktags
        $tmp = parent::findtag($tmp);
        return $tmp;
    }
}
?>
