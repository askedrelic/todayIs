<?php
class BirthdayPost extends Post {

    public function __construct(){
        $body = "_[g{Shacker Birthdays:}g]_ \n";

        $dbh=mysql_connect ("localhost", "shack", "shack") or die ('I cannot connect to the database because: ' . mysql_error());
        mysql_select_db ("shack");

        $query = "SELECT * FROM birthdays where dob like '%".date('m-d')."'";
        $result = mysql_query($query) or die("I cannot query the database because: ".mysql_error());

        //see if we get a result or not
        $num = mysql_numrows($result);
        if($num > 0) {
            while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                  $body .= "y{".$row["username"]."}y ". self::agestring($row["dob"]) ."\n";
            }
            $body .= "\n";
            $body .= self::getHoroscope();
            $body .= "\nCongrats folks!\n";
        }
        else {
            $body .= "No birthdays today!\n";
        }

        $body .= "\ns[Want to add your birthday? http://www.asktherelic.com/shack/birthday.php]s\n";
        $body .= "s[Horoscopes via http://www.trynt.com/trynt-astrology-horoscope-api/]s";
        parent::__construct($body);
    }

    private function getHoroscope() {
        $m = date('m');
        $d = date('d');
        $url = "http://www.trynt.com/astrology-horoscope-api/v2/?m={$m}&d=${d}&s=&l=0&fo=php&f=0";

        $result = parent::curlData($url);

        $h = unserialize($result);
        $h = $h["trynt"]["astrology"];

        //name is name of the animal
        //start date of the horoscope
        //end date of the horoscope
        //horoscope itself (long string)
        $name = $h["name"];
        $sdate = $h["start-date"];
        $edate = $h["end-date"];
        $horoscope = trim($h["horoscope"]["horoscope"]);
        return "f[/[Now with horoscopes!]/]f\nb[{$name}]b {$sdate} - {$edate}\n{$horoscope}\n";
    }

    private function agestring($age) {
        $ret;
        if($age == "1900" || $age == "0000") {
            $ret = "is a year older";
        }

        $year = substr($age, 0, 4);
        $real_age = date('Y') - $year;
        $ord_real_age = parent::ord_suf($real_age);

        $fun = array(
            0 => "better have fun turning {$real_age}", 
            1 => "finally hits {$real_age} today", 
            2 => "turns {$real_age}", 
            3 => "is now {$real_age}", 
            4 => "has can my birthday wishes for their {$ord_real_age}", 
            5 => "IS IN UR BIRTHDAY CAKE FOR THIER {$ord_real_age}", 
            6 => "is so so old at {$real_age}", 
            7 => "better have a great time for their {$ord_real_age}", 
            8 => "Alles Gute zum Geburtstag {$real_age}", 
            9 => "happy birthday for your {$ord_real_age}", 
            10 => ": Feliz Cumplea-os {$real_age}!!!", 
            11 => ": may you live long and prosper for your {$ord_real_age}", 
            12 => "needs plenty of hookers and blow for their {$ord_real_age}", 
            13 => "may have a present waiting for their {$ord_real_age}. Or not",
            14 => "has been a virgin for {$real_age} long years",
            15 => "better not have a lame {$ord_real_age}"
        );

        //important stuff
        switch(TRUE) {
        case ($real_age < 18):
          $ret = "is one of the youngest here at {$real_age}";
          break;
        case ($real_age === 18):
          $ret = "finally hits 18"; 
          break;
        case ($real_age === 21):
          $ret = "turns 21. Prepare the drunk tank"; 
          break;
        case ($real_age === 25):
          $ret = "can now rent a car at 25"; 
          break;
        case ($real_age === 30):
          $ret = "better get some good loot for for turning 30"; 
          break;
        case ($real_age === 35):
          $ret = "can now have a mid-life crisis at 35";
          break;
        case ($real_age === 40):
          $ret = "is over the hill at 40"; 
          break;
        case ($real_age === 50):
          $ret = "is old enough to be my dad at 50";
          break;
        case ($real_age > 50):
          $ret = "is really old, but we still love them.";
          break;
        default:
          $ret = $fun[mt_rand(0, (count($fun)-1))];
        }
        $ret .= " /[!]/ ";
        return $ret;
    }
}
?>
