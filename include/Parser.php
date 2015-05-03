<?php
class Parser
{
   protected $cursors;
   protected $html;
   protected $len;

   protected function init($html)
   {
      $this->cursors = array(null, 0, 0);
      $this->html    = $html;
      $this->len     = strlen($html);
   }

   protected function peek($which_cursor, $keyword)
   {
      return strpos($this->html, $keyword, $this->cursors[$which_cursor]);
   }

   protected function clip($before_keywords, $after_keyword)
   {
      $this->seek(1, $before_keywords);
      $this->incr(1);
      $this->seek(2, $after_keyword);
      return $this->read();
   }

   protected function incr($which_cursor)
   {
      $this->cursors[$which_cursor]++;

      if ($this->cursors[$which_cursor] >= $this->len)
         throw new Exception('Unexpected end of HTML data.');
   }

   protected function seek($which_cursor, $keywords)
   {
      # If $keyword is an array, then seek to each one in sequence.
      if (is_array($keywords))
      {
         foreach ($keywords as $keyword)
            $this->seek($which_cursor, $keyword);
      }
      else
      {
         $i = $this->cursors[1];
         $j = strpos($this->html, $keywords, $i);
         if ($j === false)
            throw new Exception("Did not find '$keywords' starting at index '$i'");
         else
            $this->cursors[$which_cursor] = $j;
      }
   }

   protected function read()
   {
      $c1 = $this->cursors[1];
      $c2 = $this->cursors[2];
      return substr($this->html, $c1, $c2 - $c1);
   }

   protected function download($url, $fast = false)
   {
      # This function will reuse a login over and over again until Shacknews kicks us off.
      # This cannot be used for user-specific pages, like Shackmessages.
      $cookiejar = 'Login.cookie';

      $curl = curl_init();
      curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_HEADER, false);
      curl_setopt($curl, CURLOPT_COOKIEFILE, $cookiejar);
      curl_setopt($curl, CURLOPT_COOKIEJAR, $cookiejar);

      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_POST, false);
      $html = curl_exec($curl);

      # We'll keep using the same session until Shacknews kicks us off.
      if ($fast == false && strpos($html, '/profile/latestchatty') === false)
      {
         # Need to log in, first.
         $fields = 'username=latestchatty&password=8675309&type=login';

         curl_setopt($curl, CURLOPT_URL, 'http://www.shacknews.com/login_laryn.x');
         curl_setopt($curl, CURLOPT_POST, true);
         curl_setopt($curl, CURLOPT_POSTFIELDS, $fields);
         $response = curl_exec($curl);

         if (strpos($response, 'do_iframe_login(') !== false)
         {
            # Successfully logged in.  Get the data again.
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, false);
            curl_setopt($curl, CURLOPT_POSTFIELDS, null);
            $html = curl_exec($curl);
            curl_close($curl);
            return $html;
         }
         else
         {
            curl_close($curl);
            throw new Exception('Unable to log into user account.');
         }
      }

      curl_close($curl);
      return $html;
   }

   protected function userDownload($url, $username, $password, $postArgs = null)
   {
      # This function does not reuse logins.  It will log in using $username and $password
      # on every call.
      $cookiejar = tempnam(sys_get_temp_dir(), 'WinChatty.CookieJar.');
      chmod($cookiejar, 0666);

      $curl = curl_init();
      curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_HEADER, false);
      curl_setopt($curl, CURLOPT_COOKIEFILE, $cookiejar);
      curl_setopt($curl, CURLOPT_COOKIEJAR, $cookiejar);

      # Log in first.
      $fields = 'username=' . urlencode($username) . '&password=' . urlencode($password) . '&type=login';

      curl_setopt($curl, CURLOPT_URL, 'http://www.shacknews.com/login_laryn.x');
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $fields);
      $response = curl_exec($curl);

      if (strpos($response, 'do_iframe_login(') === false)
      {
         curl_close($curl);
         unlink($cookiejar);
         throw new Exception('Unable to log into user account.');
      }

      # Download the requested page.
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_POST, false);
      curl_setopt($curl, CURLOPT_POSTFIELDS, null);

      if ($postArgs != null)
      {
         curl_setopt($curl, CURLOPT_POST, 1);
         curl_setopt($curl, CURLOPT_POSTFIELDS, $postArgs);
      }

      $html = curl_exec($curl);

      curl_close($curl);
      unlink($cookiejar);

      return $html;
   }
}
