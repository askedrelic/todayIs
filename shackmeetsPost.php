<?php

// Posts upcoming shackmeets information
class ShackmeetsPost extends Post 
{
  public function __construct()
  {
    $url = 'http://www.shackmeets.com/api/shackmeetspost';
    $shackmeetsText = parent::curlData($url);

    $body = "_[p[Upcoming Shackmeets:]p]_ \n\n";
    $body .= strlen($shackmeetsText) > 0 ? $shackmeetsText : 'None. :(';
      
    parent::__construct($body);
  }
}
