<?php 

// Handles the json feeds.
class tokenmanagerjson
{
  // Construct the tokens class
  public function __construct()
  {
    global $_POST;

    if(!isset($_POST['json']) || empty($_POST['json'])) { $this->nofeed(); return; }

    switch($_POST['json']) 
    {
      case 'searchpages' : $this->searchpages(); break;
      case 'attachedpages' : $this->attachedpages(); break;
      case 'attachedpagesadd' : $this->attachedpagesadd(); break;
      case 'attachedpagesremove' : $this->attachedpagesremove(); break;
      case 'moveup' : $this->moveup(); break;
      case 'movedown' : $this->movedown(); break;
      default : echo 'No feed with that name!'; break; 
    }
  }

  // Processes the page and spits out an admin page.
  public function nofeed()
  {
    echo '<h2>Admin Console</h2>';
  }

  // Handles the search for pages to attach to (Tokens).
  public function searchpages()
  {
    global $wpdb, $_POST, $current_site, $blog_id;

    // Create custom vars
    $siteid = (isset($current_site) && isset($current_site->id)) ? $current_site->id : '0';
    $blogid = $blog_id;
    
    if(isset($_POST['keyword']) && !empty($_POST['keyword']))
    {
      $search = $wpdb->escape($_POST['keyword']);
      $search2 = (is_numeric($search)) ? "ID = $search" : "post_title like '%$search%'";

      $attachedpages = $wpdb->escape($_POST['attachedpages']);

      $results = '';

      $wherein = '';
      $extra = '';
      $tablename = $wpdb->prefix . 'posts';
      if(!empty($attachedpages))
      {
        $attachedpages = explode(',', $attachedpages);
        array_pop($attachedpages);

        if(array_search('-1', $attachedpages)===false)
        {
          $extra = "(SELECT -1 as ID, 'All Pages' as post_title FROM $tablename WHERE 'All Pages' like '%$search%' LIMIT 1) UNION ";
        }

        if(array_search('0', $attachedpages)===false)
        {
          $extra .= "(SELECT 0 as ID, 'Frontpage' as post_title FROM $tablename WHERE 'Frontpage' like '%$search%' LIMIT 1) UNION ";
        }

        $attachedpages = implode(',',$attachedpages);
        $wherein = "ID NOT IN ($attachedpages) AND";
      }
      else
      {

          $extra = "(SELECT -1 as ID, 'All Pages' as post_title FROM $tablename WHERE 'All Pages' like '%$search%' LIMIT 1) UNION 
                    (SELECT 0 as ID, 'Frontpage' as post_title FROM $tablename WHERE 'Frontpage' like '%$search%' LIMIT 1) UNION ";
      }

      $items = $wpdb->get_results("$extra(SELECT ID, post_title
                                   FROM $tablename 
                                   WHERE $wherein post_status = 'publish' AND post_type = 'page' AND $search2 ORDER BY post_title)");  

      // Add in all pages and frontpage
      for($i = 0; $i < count($items); $i++)
      {
        $item = $items[$i];
        $pid = intval($item->ID);
        $ptitle = $item->post_title;
        switch($pid)
        {
          case 0 : $items[$i]->post_title = 'Frontpage'; break;
          case -1 : $items[$i]->post_title = 'All Pages'; break;
          default : $items[$i]->post_title = "$ptitle (id=$pid)"; break;
        }
      }

    $results = json_encode($items);

    echo "<jsonobject>$results</jsonobject>";
    }
    else
    {
      echo 'Keyword not set!';
    } 
  }
  
  // Gets a list of current attached pages.
  public function attachedpages()
  {
    global $wpdb, $_POST, $current_site, $blog_id;

    // Create custom vars
    $siteid = (isset($current_site) && isset($current_site->id)) ? $current_site->id : '0';
    $blogid = $blog_id;
    
    if(isset($_POST['tokenid']) && !empty($_POST['tokenid']))
    {
      $id = $wpdb->escape($_POST['tokenid']);

      $results = '';

      $tablename = $wpdb->base_prefix . 'tokenmanagerpages';
      $tablename2 = $wpdb->prefix . 'posts';
      $items = $wpdb->get_results("SELECT DISTINCT a.pageid as pid, b.post_title as ptitle
                                   FROM $tablename a
                                   LEFT JOIN $tablename2 b ON a.pageid = b.ID
                                   WHERE b.post_status = 'publish' AND b.post_type = 'page' AND a.tokenid = '$id' AND a.siteid='$siteid' AND a.blogid='$blogid' and a.pageid > 0 
                                   OR a.tokenid = '$id' AND a.siteid='$siteid' AND a.blogid='$blogid' and a.pageid < 1 AND b.ID is NULL
                                   ORDER BY b.post_title, a.pageid;");  

      // Add in all pages and frontpage
      for($i = 0; $i < count($items); $i++)
      {
        $item = $items[$i];
        $pid = intval($item->pid);
        $ptitle = $item->ptitle;
        switch($pid)
        {
          case 0 : $items[$i]->ptitle = 'Frontpage'; break;
          case -1 : $items[$i]->ptitle = 'All Pages'; break;
          default : $items[$i]->ptitle = "$ptitle (id=$pid)"; break;
        }
      }

      $results = json_encode($items);

      echo "<jsonobject>$results</jsonobject>";
    }
    else
    {
      echo 'Token ID not set!';
    } 
  }

  // Adds a page.
  public function attachedpagesadd()
  {
    global $wpdb, $_POST, $current_site, $blog_id;

    // Create custom vars
    $siteid = (isset($current_site) && isset($current_site->id)) ? $current_site->id : '0';
    $blogid = $blog_id;
    
    if(isset($_POST['tokenid']) && !empty($_POST['tokenid']) && isset($_POST['pageid']))
    {
      $tid = $wpdb->escape($_POST['tokenid']);
      $pid = $wpdb->escape($_POST['pageid']);

      $tablename = $wpdb->base_prefix . 'tokenmanagerpages';
      $count = $wpdb->get_var("SELECT count(*) FROM $tablename WHERE tokenid = '$tid' AND pageid = '$pid' AND siteid='$siteid' AND blogid='$blogid' LIMIT 1");

      if($count==0)
      {
        $wpdb->query("INSERT INTO $tablename (id, datecreated, lastupdated, siteid, blogid, pageid, tokenid) VALUES (null, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), '$siteid', '$blogid', '$pid', '$tid');");
        echo "<jsonobject>Page Added Successfully!</jsonobject>";
      }     
    }
    else
    {
      echo 'Token ID or Page ID not set!';
    } 
  }

  // Adds a page.
  public function attachedpagesremove()
  {
    global $wpdb, $_POST, $current_site, $blog_id;

    // Create custom vars
    $siteid = (isset($current_site) && isset($current_site->id)) ? $current_site->id : '0';
    $blogid = $blog_id;
    
    if(isset($_POST['tokenid']) && !empty($_POST['tokenid']) && isset($_POST['pageid']))
    {
      $tid = $wpdb->escape($_POST['tokenid']);
      $pid = $wpdb->escape($_POST['pageid']);

      $tablename = $wpdb->base_prefix . 'tokenmanagerpages';
      $count = $wpdb->get_var("SELECT count(*) FROM $tablename WHERE tokenid = '$tid' AND pageid = '$pid' AND siteid='$siteid' AND blogid='$blogid' LIMIT 1");

      if($count > 0)
      {
        $wpdb->query("DELETE FROM $tablename WHERE pageid = '$pid' AND tokenid = '$tid' AND siteid='$siteid' AND blogid='$blogid';");
        echo "<jsonobject>Page Removed Successfully!</jsonobject>";
      }     
    }
    else
    {
      echo 'Token ID or Page ID not set!';
    } 
  }

  public function moveup()
  {
    global $wpdb, $_POST, $current_site, $blog_id;

    if(isset($_POST['tid']) && !empty($_POST['tid']))
    {
      $siteid = (isset($current_site) && isset($current_site->id)) ? $current_site->id : '0';
      $blogid = $blog_id;

      $tid = $wpdb->escape($_POST['tid']);

      $tablename = $wpdb->base_prefix . 'tokenmanager';

      $corder = $wpdb->get_var("SELECT processorder FROM $tablename 
                                WHERE id = $tid AND active = 1 AND siteid='$siteid' AND blogid='$blogid';"); 
      $ntid = $wpdb->get_var("SELECT id FROM $tablename 
                              WHERE processorder > $corder AND active = 1 AND siteid='$siteid' AND blogid='$blogid' 
                              ORDER BY processorder ASC LIMIT 1;");
      $norder = $wpdb->get_var("SELECT processorder FROM $tablename 
                                WHERE id = $ntid AND active = 1 AND siteid='$siteid' AND blogid='$blogid';");

      if(isset($ntid) && isset($corder) && isset($norder) && $norder > $corder)
      {
        $wpdb->query("UPDATE $tablename SET processorder = $norder, lastupdated = UNIX_TIMESTAMP() 
                      WHERE id = '$tid' AND active = 1 AND siteid='$siteid' AND blogid='$blogid';"); 
        $wpdb->query("UPDATE $tablename SET processorder = $corder, lastupdated = UNIX_TIMESTAMP() 
                      WHERE id = '$ntid' AND active = 1 AND siteid='$siteid' AND blogid='$blogid';");  

        echo "<jsonobject>Successfully moved token order.</jsonobject>";   
      }
      else
      {
        echo "<jsonobject>Cannot move token order.</jsonobject>";
      }
    }
  }

  public function movedown()
  {
    global $wpdb, $_POST, $current_site, $blog_id;

    if(isset($_POST['tid']) && !empty($_POST['tid']))
    {
      $tid = $wpdb->escape($_POST['tid']);
      $siteid = (isset($current_site) && isset($current_site->id)) ? $current_site->id : '0';
      $blogid = $blog_id;

      $tablename = $wpdb->base_prefix . 'tokenmanager';

      $corder = $wpdb->get_var("SELECT processorder FROM $tablename 
                                WHERE id = $tid AND active = 1 AND siteid='$siteid' AND blogid='$blogid';"); 
      $ntid = $wpdb->get_var("SELECT id FROM $tablename 
                              WHERE processorder < $corder AND active = 1 AND siteid='$siteid' AND blogid='$blogid'
                              ORDER BY processorder DESC LIMIT 1;");
      $norder = $wpdb->get_var("SELECT processorder FROM $tablename 
                                WHERE id = $ntid AND active = 1 AND siteid='$siteid' AND blogid='$blogid';");

      if(isset($ntid) && isset($corder) && isset($norder) && $norder < $corder)
      {
        $wpdb->query("UPDATE $tablename SET processorder = $norder, lastupdated = UNIX_TIMESTAMP() 
                      WHERE id = '$tid' AND active = 1 AND siteid='$siteid' AND blogid='$blogid';"); 
        $wpdb->query("UPDATE $tablename SET processorder = $corder, lastupdated = UNIX_TIMESTAMP() 
                      WHERE id = '$ntid' AND active = 1 AND siteid='$siteid' AND blogid='$blogid';");  

        echo "<jsonobject>Successfully moved token order.</jsonobject>";   
      }
      else
      {
        echo "<jsonobject>Cannot move token order.</jsonobject>";
      }
    }
  }
} 

$tokenmanagerjson = new tokenmanagerjson();

?>
