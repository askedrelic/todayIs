<?php
class AwardPost extends Post {
    private $awardWinner;

    public function __construct($posts) {
        $this->awardWinner = False;

        $body = "_[l[Daily Awards:]l]_ \n \n";

        $lols = array();
        $tags = array();
        $unfs = array();
        $infs = array();
        $loltag = "_[n[LOL]n]_";
        $tagtag = "_[g{TAG}g]_";
        $unftag = "_[r{UNF}r]_";
        $inftag = "_[b{INF}b]_";

        //setup authors names
        foreach($posts as $p) {
            if($p instanceof LolPost) {
                //check against multiple lolers
                $lols = array_unique($p->getAuthors());
            }
            if($p instanceof TagPost) {
                $tags = array_unique($p->getAuthors());
            }
            if($p instanceof UnfPost) {
                $unfs = $p->getAuthors();
            }
            if($p instanceof InfPost) {
                $infs = $p->getAuthors();
            }
            //debug code
            // print_r($p->getAuthors());
        }

        //check all lol/*combinations
        foreach($lols as $lolAuthor) {
            $awardWinner = False;
            $award = "";
            //append each award to the string
            $award .= $loltag;
            foreach($tags as $tagAuthor) {
                if($lolAuthor == $tagAuthor) {
                    $awardWinner = True;
                    $award .= $tagtag;
                }
            }
            if($lolAuthor == $unfs[0]) {
                $awardWinner = True;
                $award .= $unftag;
            }
            if($lolAuthor == $infs[0]) {
                $awardWinner = True;
                $award .= $inftag;
            }
            if($awardWinner) {
                 $this->awardWinner = True;
                 $body .= $lolAuthor." is a {$award} winner!\n";
            }
        }

        //check all tag/* combinations
        foreach($tags as $tagAuthor) {
            $awardWinner = False;
            $award = $tagtag;
            if($tagAuthor == $unfs[0]) {
                $awardWinner = True;
                $award .= $unftag;
            }
            if($tagAuthor == $infs[0]) {
                $awardWinner = True;
                $award .= $inftag;
            }
            if($awardWinner) {
                $this->awardWinner = True;
                $body .= $tagAuthor." is a {$award} winner!\n";
            } 
        }

        //final unf/inf combo
        if($unfs[0] == $infs[0]) {
            $this->awardWinner = True;
            $body .= $unfs[0]." is a {$unftag}{$inftag} winner!\n";
        }

        $body .= "\n\n";

        parent::__construct($body);
    }

    public function checkAwardWinner() {
        return $this->awardWinner;
    }
}
?>
