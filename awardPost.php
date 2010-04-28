<?php
class AwardPost extends Post {
    private $awardWinner = False;

    public function __construct($posts) {
        $body = "_[l[Daily Awards:]l]_ \n\n";

        //build a (name => tags) dictionary for all posts
        $winners = array();
        foreach($posts as $p) {
            foreach(array_unique($p->getAuthors()) as $name) {
                if(!array_key_exists($name, $winners)) {
                    $winners[$name] = array();
                }
                array_push($winners[$name], $p->tag) ;
            }
        }

        //check for a winner; someone with multiple tags
        foreach($winners as $name=>$tags) {
            if(count($tags) > 1) {
                $body .= $name." is a ".implode('',$tags)." winner!\n";
                $awardWinner = True;
            }
        }

        $body .= "\n";
        parent::__construct($body);
    }

    public function checkAwardWinner() {
        return $this->awardWinner;
    }
}
?>
