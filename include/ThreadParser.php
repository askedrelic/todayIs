<?php
class ThreadParser extends Parser
{
   public function getThread($threadID)
   {
      # $threadID might be a reply ID instead of a root thread ID.
      # get_thread_tree() can handle it.
      $tree = $this->getThreadTree($threadID);

      # From $tree we can grab the real root thread ID.
      $threadID = $tree['replies'][0]['id'];
      $bodies   = $this->getThreadBodies($threadID);
      
      # Build a hashtable from the bodies.
      $bodies_table = array();
      foreach ($bodies['replies'] as $body)
         $bodies_table[$body['id']] = array('body' => $body['body'], 'date' => $body['date']);
      
      # Add the bodies to the tree.
      foreach ($tree['replies'] as $i => $reply)
      {
         if (isset($bodies_table[$reply['id']]))
         {
            $reply['body'] = $bodies_table[$reply['id']]['body'];
            $reply['date'] = $bodies_table[$reply['id']]['date'];
            $tree['replies'][$i] = $reply;
         }
      }
      
      return $tree;
   }
   
   public function getThreadBodies($threadID)
   {
      $threadID = intval($threadID);
      $url      = "http://shacknews.com/frame_laryn.x?root=$threadID";
      $html     = $this->download($url, true);

      $this->init($html);

      $o = array( # Output
         'replies'      => array());
      
      while ($this->peek(1, '<div id="item_') !== false)
      {
         $reply = array(
            'category' => false,
            'id'       => false,
            'author'   => false,
            'body'     => false,
            'date'     => false);
            
         $reply['id'] = $this->clip(
            array('<div id="item_', '_'),
            '">');
         $reply['category'] = $this->clip(
            array('<div class="fullpost fpmod_', '_'),
            ' ');
         $reply['author'] = trim(html_entity_decode($this->clip(
            array('<span class="author">', '<a href="/profile', '">', '>'),
            '</a>')));
         $reply['body'] = $this->makeSpoilersClickable($this->clip(
            array('<div class="postbody">', '>'),
            '</div>'));
         $reply['date'] = $this->clip(
            array('<div class="postdate">', '>'),
            '</div');
         
         $o['replies'][] = $reply;
      }
      
      return $o;
   }
   
   public function getThreadTree($threadID)
   {
      $threadID = intval($threadID);
      $url      = "http://shacknews.com/laryn.x?id=$threadID";
      $html     = $this->download($url);

      $this->init($html);
      
      $story_id = $this->clip(
         array('<div class="story">', 'onearticle.x/', '/'),
         '"');
      $story_name = $this->clip(
         '>', '</a>');

      $thread = $this->parseThreadTree($this);
      $thread['story_id'] = $story_id;
      $thread['story_name'] = $story_name;
      
      return $thread;
   }
   
   public function parseThreadTree(&$p)
   {
      $thread = array(
         'body'          => false,
         'category'      => false,
         'id'            => false,
         'author'        => false,
         'date'          => false,
         'preview'       => false,
         'reply_count'   => false,
         'last_reply_id' => false,
         'replies'       => array());
      
      #
      # Post metadata
      #
      # <div class="fullpost fpmod_ontopic fpauthor_203312"> 
      # <div class="postnumber"><a title="Permalink" href="laryn.x?id=21464158#itemanchor_21464158">#176</a></div>
      # <div class="refresh"><a title="Refresh Thread" target="dom_iframe" href="/frame_laryn.x?root=21464158&id=21464158&mode=refresh">Refresh Thread</a></div>
      # <div class="postmeta">
      #    <span class="author">
      #       By: 
      #       <a href="/profile/Mr.+Goodwrench">
      #          Mr. Goodwrench      </a>
      #
      $thread['category'] = $p->clip(
         array('<div class="fullpost', 'fpmod_', '_'),
         ' ');
      $thread['id'] = $p->clip(
         array('<a title="Permalink" href=', 'id=', '='),
         '#');
      $thread['author'] = html_entity_decode(trim($p->clip(
         array('<span class="author">', '<a href="/profile/', '>'),
         '</a>')));
      $thread['body'] = $this->makeSpoilersClickable(trim($p->clip(
         array('<div class="postbody">', '>'),
         '</div>')));
      $thread['date'] = $p->clip(
         array('<div class="postdate">', '>'),
         '</div');

      # Read the rest of the replies in this thread.
      $depth = 0;
      $last_reply_id = intval($thread['id']);
      $next_thread = $p->peek(1, '<div class="fullpost');
      if ($next_thread === false)
         $next_thread = $p->len;
      
      while (true)
      {
         $next_reply = $p->peek(1, '<div class="oneline');
         if ($next_reply === false || $next_reply > $next_thread)
            break;
         
         $reply = array(
            'category' => false,
            'id'       => false,
            'author'   => false,
            'preview'  => false,
            'depth'    => $depth);
         
         if (count($thread['replies']) == 0)
            $reply['date'] = $thread['date'];
         #
         # Reply
         #
         # <div class="oneline oneline9 hidden olmod_ontopic olauthor_203312">
         #          <div class="treecollapse">
         #             <a class="open" href="#" onClick="toggle_collapse(21464158); ...
         #          </div>
         # <a href="laryn.x?id=21464158" onClick="return clickItem( 21464158, ...
         #    <span class="oneline_body">
         #       These two opening themes to Ghost in the Shell always give me the chills; 
         # http://www.youtube.com/watch...	</span> : 
         #    <a href="/profile/Mr.+Goodwrench" class="oneline_user ">
         #       Mr. Goodwrench   </a>
         #
         $reply['category'] = $p->clip(
            array('<div class="oneline', 'olmod_', '_'),
            ' ');
         $reply['id'] = $p->clip(
            array('<a href="laryn.x?id=', 'id=', '='),
            '"');
         $reply['preview'] = trim(collapse_whitespace($p->clip(
            array('<span class="oneline_body">', '>'),
            '<a href="/profile/')));
         $reply['preview'] = self::removeSpoilers(substr($reply['preview'], 0, -1 * strlen('</span> :')));
         $reply['author'] = html_entity_decode(trim($p->clip(
            array('<a href="/profile/', 'class="oneline_user', '>'),
            '</a>')));
            
         if (intval($reply['id']) > $last_reply_id)
            $last_reply_id = intval($reply['id']);
         
         # Determine the next level of depth.
         while (true)
         {
            $next_li = $p->peek(1, '<li ');
            $next_ul = $p->peek(1, '<ul>');
            $next_end_ul = $p->peek(1, '</ul>');
            
            if ($next_li === false)
               $next_li = $next_thread;
            if ($next_ul === false)
               $next_ul = $next_thread;
            if ($next_end_ul === false)
               $next_end_ul = $next_thread;
               
            $next = min($next_li, $next_ul, $next_end_ul);

            if ($next == $next_thread)
            {
               # This thread has no more replies.
               break;
            }
            else if ($next == $next_li)
            {
               # Next reply is on the same depth level.
               break;
            }
            else if ($next == $next_ul)
            {
               # Next reply is underneath this one.
               $depth++;
            }
            else if ($next == $next_end_ul)
            {
               # Next reply is above this one.
               $depth--;
            }
            
            $p->cursors[1] = $next + 1;
         }

         $thread['replies'][] = $reply;
      }
      
      $thread['replies'][0]['body'] = $thread['body'];
      $thread['preview']       = $thread['replies'][0]['preview'];
      $thread['last_reply_id'] = $last_reply_id;
      $thread['reply_count']   = count($thread['replies']);
      return $thread;
   }
   
   private static function removeSpoilers($text)
   {
      $spoilerSpan    = 'span class="jt_spoiler" onClick="return doSpoiler( event );">';
      $spoilerSpanLen = strlen($spoilerSpan);
      $span           = 'span ';
      $spanLen        = strlen($span);
      $endSpan        = '/span>';
      $endSpanLen     = strlen($endSpan);
      $replaceStr     = "_______";
      $out            = '';
      $inSpoiler      = false;
      $depth          = 0;
      
      # Split by < to get all the tags separated out.
      foreach (explode('<', $text) as $i => $chunk)
      {
         if ($i == 0)
         {
            # The first chunk does not start with or contain a <, so we can
            # just copy it directly to the output.
            $out .= $chunk;
         }
         else if ($inSpoiler)
         {
            if (strncmp($chunk, $span, $spanLen) == 0)
            {
               # Nested Shacktag.
               $depth++;
            }
            else if (strncmp($chunk, $endSpan, $endSpanLen) == 0)
            {
               # End of a Shacktag.
               $depth--;

               # If the depth has dropped back to zero, then we found the end
               # of the spoilered text.
               if ($depth == 0)
               {
                  $out      .= substr($chunk, $endSpanLen);
                  $inSpoiler = false;
               }
            }
         }
         else
         {
            if (strncmp($chunk, $spoilerSpan, $spoilerSpanLen) == 0)
            {
               # Beginning of a spoiler.
               $inSpoiler = true;
               $depth     = 1;
               $out      .= $replaceStr;
            }
            else
            {
               $out .= '<' . $chunk;
            }
         }
      }
      
      return $out;
   }
   
   private function makeSpoilersClickable($text)
   {
      return str_replace('return doSpoiler( event );', "this.className = '';", $text);
   }
}

function ThreadParser()
{
   return new ThreadParser();
}
