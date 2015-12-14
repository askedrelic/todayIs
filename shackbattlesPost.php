<?php

// Posts upcoming ShackBattles information
class ShackBattlesPost extends Post 
{
  public function __construct()
  {
    $url = 'http://shackbattl.es/external/ShackBattlesPost.aspx';
    $shackBattlesText = parent::curlData($url);

    $body = "_[e[Upcoming ShackBattles:]e]_ \n\n";
    $body .= strlen($shackBattlesText) > 0 ? $shackBattlesText : 'None. :(';
      
    parent::__construct($body);
  }
}
