<?php
class AwardPost extends Post {
    private $awardWinner = False;

    public function __construct($posts) {
        $body = "_[l[Daily Awards:]l]_ \n\n";

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
            //debug
            // print_r($p->getAuthors());
        }

        $winners = array();
        //check all lol/*combinations
        foreach($lols as $lolAuthor) {
            $award = "";
            foreach($tags as $tagAuthor) {
            if($lolAuthor == $tagAuthor) {
                $award .= $tagtag;
            }
            }
            if($lolAuthor == $unfs[0]) {
                $award .= $unftag;
            }
            if($lolAuthor == $infs[0]) {
                $award .= $inftag;
            }
            if($award !== "") {
                $body .= $lolAuthor." is a {$loltag}{$award} winner!\n";
                array_push($winners, $lolAuthor);
                //notify that someone won and this should be posted
                $this->awardWinner = True;
            }
        }

        //check all tag/* combinations
        foreach($tags as $tagAuthor) {
            $award = "";
            if($tagAuthor == $unfs[0]) {
                $award .= $unftag;
            }
            if($tagAuthor == $infs[0]) {
                $award .= $inftag;
            }
            //if there is an award and this person has not already won an award
            if($award !== "" and !in_array($lolAuthor, $winners)) {
                $body .= $tagAuthor." is a {$tagtag}{$award} winner!\n";
                array_push($winners, $lolAuthor);
                //notify that someone won and this should be posted
                $this->awardWinner = True;
            }
        }

        //final unf/inf combo
        if($unfs[0] == $infs[0] and !in_array($unfs[0], $winners)) {
            $body .= $unfs[0]." is a {$unftag}{$inftag} winner!\n";
            //notify that someone won and this should be posted
            $this->awardWinner = True;
        }

        print_r($winners);

        $body .= "\n";

        parent::__construct($body);
    }

    public function checkAwardWinner() {
        return $this->awardWinner;
    }
}
?>
