<?php
class BirthdayPost extends Post {

    public function __construct(){
        $body = "_[l[Shacker Birthdays:]l]_ \n";

        //TODO: refactor out... maybe?
        $dbh=mysql_connect ("localhost", "shack", "shack") or die ('I cannot connect to the database because: ' . mysql_error());
        mysql_select_db ("shack");

        $query = "SELECT * FROM birthdays where dob like '%".date('m-d')."'";
        $result = mysql_query($query) or die("I cannot query the database because: ".mysql_error());

        //see if we get a result or not
        $num = mysql_numrows($result);
        if($num > 0) {
            while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                  $body .= "y{".$row["username"]."}y". self::agestring($row["dob"], $row["username"]) ."\n";
            }
            $body .= "\nCongrats folks!\n";
        }
        else {
            $body .= "No birthdays today!\n";
        }
        $body .= "\ns[Want to add your birthday? http://www.asktherelic.com/shack/birthday.php]s";
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
        return "b[{$name}]b {$sdate} - {$edate}\n{$horoscope}\n";
    }

    private function agestring($age, $username) {
        $ret;
        if($age == "1900" || $age == "0000") {
            $ret = " is a year older";
        }

        $year = substr($age, 0, 4);
        $real_age = date('Y') - $year;
        $ord_real_age = parent::ord_suf($real_age);
        $firstchar = substr($username, 0, 1);

        $fun = array();
        array_push($fun, " better have fun turning {$real_age}");
        array_push($fun, " finally hits {$real_age} today");
        array_push($fun, " turns {$real_age}");
        array_push($fun, " is now {$real_age}");
        array_push($fun, " has can my birthday wishes for their {$ord_real_age}");
        array_push($fun, " IS IN UR BIRTHDAY CAKE FOR THIER {$ord_real_age}");
        array_push($fun, " is so so old at {$real_age}");
        array_push($fun, " better have a great time for their {$ord_real_age}");
        array_push($fun, " happy birthday for your {$ord_real_age}");
        array_push($fun, ": may you live long and prosper for your {$ord_real_age}");
        array_push($fun, " needs plenty of hookers and blow for their {$ord_real_age}");
        array_push($fun, " may have a present waiting for their {$ord_real_age}. Or not");
        array_push($fun, " has been a virgin for {$real_age} long years");
        array_push($fun, " better not have a lame {$ord_real_age}");
        array_push($fun, " HAPPY BURFDAY {$real_age}");
        array_push($fun, " OMGH{$firstchar}D :O");

        array_push($fun, ": Feliz Cumpleaños {$real_age}");
        array_push($fun, ": Alles Gute zum Geburtstag {$real_age}");
        array_push($fun, ": Buon Compleanno {$real_age}");
        array_push($fun, ": Yom Huledet Same'ach {$real_age}");
        array_push($fun, ": Joyeux Anniversaire Branleur");
        array_push($fun, ": Tillykke med fodselsdagen {$real_age}");
        array_push($fun, ": Suk San Wan Keut");
        array_push($fun, ": Hyvaa syntymapaivaa");
        array_push($fun, ": Van harte gefeliciteerd met je verjaardag");

        //important stuff
        switch(TRUE) {
        case ($real_age < 18):
          $ret = " is one of the youngest here at {$real_age}";
          break;
        case ($real_age === 18):
          $ret = " finally hits 18"; 
          break;
        case ($real_age === 21):
          $ret = " turns 21. Prepare the drunk tank"; 
          break;
        case ($real_age === 25):
          $ret = " can now rent a car at 25"; 
          break;
        case ($real_age === 30):
          $ret = " better get some good loot for for turning 30"; 
          break;
        case ($real_age === 35):
          $ret = " can now have a mid-life crisis at 35";
          break;
        case ($real_age === 40):
          $ret = " is over the hill at 40"; 
          break;
        case ($real_age === 50):
          $ret = " is old enough to be my dad at 50";
          break;
        case ($real_age > 50):
          $ret = " is really old, but we still love them.";
          break;
        default:
          $ret = $fun[mt_rand(0, (count($fun)-1))];
        }
        $ret .= " /[!]/ ";
        return $ret;
    }
}
?>
