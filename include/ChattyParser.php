<?
class ChattyParser extends Parser
{
   public function getStory($storyID, $page)
   {
      $url = ($storyID == 0) ? "http://shacknews.com/latestchatty.x"
                             : "http://shacknews.com/laryn.x?story=$storyID&page=$page";
   
      return $this->parseStory($this->download($url));
   }
   
   public function parseStory($html)
   {
      $this->init($html);

      $o = array(
         'threads'      => array(),
         'story_id'     => false,
         'story_name'   => false,
         'story_text'   => false,
         'story_author' => false,
         'story_date'   => false,
         'current_page' => false,
         'last_page'    => false);
      
      #
      # Story text
      #
      # <div class="story">
      #    <h1><a href="/onearticle.x/61342">Evening Reading: Weekend Confirmed</a></h1>
      #
      $o['story_id'] = $this->clip(
         array('<div class="story">', 'onearticle.x/', '/'),
         '"');
      $o['story_name'] = $this->clip(
         '>', '</a>');
      
      #
      # Story metadata
      #
      # <div class="meta">
      #    <span class="byline">by <span class="author">Garnett Lee</span></span>
      #    <span class="date">Nov 20, 2009 8:35pm CST</span>
      # </div>
      #
      $o['story_author'] = $this->clip( 
         array('<div class="meta">', '<span class="author">', '>'),
         '</span>');   
      $o['story_date'] = $this->clip(
         array('<span class="date">', '>'),
         '</span>');
      
      #
      # Story body
      #
      # <div class="body">
      #    ...
      # </div>
      #
      $o['story_text'] = trim($this->clip(
         array('<div class="body">', '>'),
         "\t\t</div>"));
      
      #
      # Page navigation
      #
      # <div class="pagenavigation">
      #    <span  class="nextprev">&laquo; Previous</span>	
      #    <a class="selected_page" href="/laryn.x?story=61342&page=1">1</a>
      #    <a href="/laryn.x?story=61342&page=2">2</a>
      #    <a href="/laryn.x?story=61342&page=3">3</a>
      #    <A href="/laryn.x?story=61342&page=2" class="nextprev">Next &raquo;</A>	
      # </div> <!-- class="pagenavigation" -->
      #
      $this->seek(1, array('<div class="pagenavigation">', '>'));

      # May not be present if there's only 1 page.
      if ($this->peek(1, '<a class="selected_page"') === false)
      {
         $o['current_page'] = 1;
         $o['last_page'] = 1;
      }
      else
      {
         $is_last_page = ($this->peek(1, 'class="nextprev">Next &raquo;</A>') === false);
         
         if (!$is_last_page)
         {
            $navdiv = trim($this->clip('&laquo; Previous', '<A href'));
      
            $navdiv = rtrim($navdiv, '</a>');
            $temp = strrpos($navdiv, '>');
            $o['last_page'] = substr($navdiv, $temp + 1);
         }
         
         $o['current_page'] = $this->clip(
            array('<a class="selected_page"', '>'), 
            '</a>');
      
         if ($is_last_page)
            $o['last_page'] = $o['current_page'];
      }

      #
      # Threads
      #
      while ($this->peek(1, '<div class="fullpost') !== false)
      {
         $thread = ThreadParser()->parseThreadTree($this);
         $o['threads'][] = $thread;
         
         if (count($o['threads']) > 50)
            throw new Exception('Too many threads.  Something is wrong.' . print_r($o, true));
      }   
      
      return $o;
   }
   
   public function locatePost($post_id, $story_id)
   {
      $cachefile = locatecache_data_directory . intval($post_id) . '.data';
      
      # We might have this cached.
      if (file_exists($cachefile))
      {
         $data = unserialize(file_get_contents($cachefile));
         
         # Check that this page contains the thread.
         $chatty = $this->getStory($data['story'], $data['page']);
         
         foreach ($chatty['threads'] as $thread)
            foreach ($thread['replies'] as $reply)
               if ($reply['id'] == $post_id)
                  return $data;
      }

      # Find the page and thread containing this post.  We will have to 
      # search page-by-page through the specified chatty to find it.
      $last_page = 1;
      
      for ($page = 1; $page <= $last_page; $page++)
      {
         $chatty = $this->getStory($story_id, $page);
         $last_page = $chatty['last_page'];
         
         foreach ($chatty['threads'] as $thread)
         {
            foreach ($thread['replies'] as $reply)
            {
               if ($reply['id'] == $post_id)
               {
                  $data = array(
                     'story' => $story_id,
                     'page' => $page, 
                     'thread' => $thread['id']);
                  file_put_contents($cachefile, serialize($data));
                  return $data;
               }
            }
         }
      }
      
      return false;
   }
}

function ChattyParser()
{
   return new ChattyParser();
}