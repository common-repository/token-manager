<?php

// Add paging class
include_once("paging.php");

class tokentypes
{
  // Variables
  public $siteid;
  public $blogid;
  public $js;
  public $icon;
  public $table_pages;
  public $table_types;
  public $table_tokens;
  public $table_users;

  // Construct the token add
  public function __construct()
  {
    global $_POST, $wpdb, $current_site, $blog_id;

    $this->js = '';
    $this->icon = plugin_dir_url( __FILE__ ) . 'icons/icon1_32.png';
    $this->table_pages = $wpdb->base_prefix . 'tokenmanagerpages';
    $this->table_types = $wpdb->base_prefix . 'tokenmanagertypes'; 
    $this->table_tokens = $wpdb->base_prefix . 'tokenmanager'; 
    $this->table_users = $wpdb->base_prefix . 'users';
    $this->siteid = (isset($current_site) && isset($current_site->id)) ? $current_site->id : '0';
    $this->blogid = $blog_id;     

    $this->postback();
  }

  // Handles the postback info.
  private function postback()
  {
    global $_POST, $wpdb;

    if(isset($_POST['typeidforce']) && !empty($_POST['typeidforce']) && is_numeric($_POST['typeidforce']))
    {
      $tid = $wpdb->escape($_POST['typeidforce']);
      //$tablename1 = $wpdb->base_prefix . 'tokenmanager';
      //$tablename2 = $wpdb->base_prefix . 'tokenmanagertypes';
      //$tablename3 = $wpdb->base_prefix . 'tokenmanagerpages';
      $wpdb->query("DELETE a.* FROM $this->table_pages a LEFT JOIN $this->table_tokens b on a.tokenid = b.id 
                    WHERE b.typeid = '$tid' AND a.siteid='$this->siteid' AND a.blogid='$this->blogid';");
      $wpdb->query("UPDATE $this->table_tokens SET active = 0 WHERE typeid = '$tid' AND siteid='$this->siteid' AND blogid='$this->blogid';");
      $wpdb->query("UPDATE $this->table_types SET active = 0 WHERE id = '$tid' AND siteid='$this->siteid' AND blogid='$this->blogid';");
    }

    // Process Form
    if(isset($_POST['typeid']) && !empty($_POST['typeid']) && is_numeric($_POST['typeid']))
    {
      $tid = $wpdb->escape($_POST['typeid']);
      //$tablename1 = $wpdb->base_prefix . 'tokenmanager';
      //$tablename2 = $wpdb->base_prefix . 'tokenmanagertypes';

      // Check if type still exists
      $type = $wpdb->get_var("SELECT count(*) FROM $this->table_tokens 
                              WHERE active = 1 AND typeid = '$tid' AND siteid='$this->siteid' AND blogid='$this->blogid';");

      $typename = $wpdb->get_var("SELECT tokentype FROM $this->table_types 
                              WHERE active = 1 AND id = '$tid' AND siteid='$this->siteid' AND blogid='$this->blogid';");

      if($type==0)
      {
        $wpdb->query("UPDATE $this->table_types SET active = 0 
                      WHERE id = '$tid' AND siteid='$this->siteid' AND blogid='$this->blogid';");
      }
      else
      {
        
        $this->js = "<script type=\"text/javascript\">
             function confirmdeleteforce()
             {
                 var answer = confirm(\"Token Type ($typename) has $type associated token(s). Force remove all associated tokens, also?!\")
                 if (answer)
                 {
                   document.getElementById('typeidforce').value=$tid; 
                   document.getElementById('myfrom').submit();
                 }
             }
             confirmdeleteforce();
             </script>";
      }
    }
  }

  // Initializes the page.
  function build_grid()
  {
    global $_POST, $wpdb;
   
    //$tablename1 = $this->get_tablenametypes();
    //$tablename2 = $wpdb->base_prefix . 'users';

    // Get the filter add %% to escape or wont work with prepare
    $filt = '';
    if(isset($_GET['filt']) && !empty($_GET['filt']))
    {
      if(is_numeric($_GET['filt']))
      {
        $filt = "AND a.id ='" . $wpdb->escape($_GET['filt']) . "'";
      }
      else
      {
        $filt = "AND a.tokentype like '%" . trim($wpdb->escape($_GET['filt']), '{}') . "%'";
      }
    }

    //get the number of records in the database table
    $pagination_count = 0;
    if(!empty($filt))
    {
      $pagination_count = $wpdb->get_var("SELECT count(*)      
                                 FROM $this->table_types a
                                 LEFT JOIN $this->table_users b ON a.authorid = b.ID 
                                 WHERE a.active = 1 $filt");
    }
    else
    {
      $pagination_count = $wpdb->get_var("SELECT count(*)      
                                 FROM $this->table_types a
                                 LEFT JOIN $this->table_users b ON a.authorid = b.ID 
                                 WHERE a.active = 1");
    }

    $pge = (isset($_GET['pge']) && !empty($_GET['pge'])) ? $_GET['pge'] : 1;
    $paging = new paging($pge, 25, intval($pagination_count), paging::pageurl());

    // Get the order
    $order = 'ORDER BY a.datecreated DESC';
    $linka = 'admin.php?page=tokenmanagertypes';
    $linka .= (isset($_GET['filt']) && !empty($_GET['filt']))? '&filt=' . $_GET['filt'] : '';
    $linka .= (isset($_GET['pge']) && !empty($_GET['pge']))? "&pge=$pge" : '';
    $linka .= (isset($_GET['per']) && !empty($_GET['per']))? '&per=' . $_GET['per'] : '';
    $linka .= (isset($_GET['per']) && !empty($_GET['total']))? '&total=' . $_GET['total'] : '';

    $l0 = $linka . '&sort=0a';
    $l1 = $linka . '&sort=1a';
    $i0 = '';
    $i1 = '';
    if(isset($_GET['sort']) && !empty($_GET['sort']))
    {
      switch($_GET['sort'])
      {
        case '0a' : 
          $order = 'ORDER BY a.id';
          $l0 = str_replace('&sort=0a', '&sort=0d', $l0);
          $i0 = '<img class="sortarrow" src="' . plugin_dir_url( __FILE__ )  . 'icons/arrowup.png" />'; 
          break;
	case '0d' : 
          $order = 'ORDER BY a.id DESC';
          $l0 = str_replace('&sort=0a', '', $l0); 
          $i0 = '<img class="sortarrow" src="' . plugin_dir_url( __FILE__ )  . 'icons/arrowdown.png" />'; 
          break;
	case '1a' : 
          $order = 'ORDER BY a.tokentype'; 
          $l1 = str_replace('&sort=1a', '&sort=1d', $l1);
          $i1 = '<img class="sortarrow" src="' . plugin_dir_url( __FILE__ )  . 'icons/arrowup.png" />'; 
          break;
	case '1d' : 
          $order = 'ORDER BY a.tokentype DESC';
          $l1 = str_replace('&sort=1a', '', $l1); 
          $i1 = '<img class="sortarrow" src="' . plugin_dir_url( __FILE__ )  . 'icons/arrowdown.png" />'; 
          break;
      }
    }

    $list_end = 25;
    $list_start = $paging->get_rangestart()-1;

    // Escape data
    $results = $results = $paging->render('Find Token Type') . 
                          '<table class="widefat">
                             <thead>
                               <tr>
                                 <th></th>
                                 <th></th>
                                 <th></th>
                                 <th></th>
                                 <th><a href="' . $l0 . '">Type ID</a>' . $i0 . '</th>
                                 <th><a href="' . $l1 . '">Token Type</a>' . $i1 . '</th>
                                 <th></th>
                                 <th></th>
                               </tr>
                             </thead>
                             <tfoot>
                               <tr>
                                 <th></th>
                                 <th></th>
                                 <th></th>
                                 <th></th>
                                 <th><a href="' . $l0 . '">Type ID</a>' . $i0 . '</th>
                                 <th><a href="' . $l1 . '">Token Type</a>' . $i1 . '</th>
                                 <th></th>
                                 <th></th>
                               </tr>
                             </tfoot>
                           <tbody>';
    
    if(!empty($filt))
    {
      $items = $wpdb->get_results("SELECT a.id, a.tokentype, FROM_UNIXTIME(a.datecreated) as datecreated, 
                                   FROM_UNIXTIME(a.lastupdated) as lastupdated, a.tokendescription as description, 
                                   b.user_login as author, a.authorid as authorid, a.version as version
                                   FROM $this->table_types a
                                   LEFT JOIN $this->table_users b ON a.authorid = b.ID 
                                   WHERE a.active = 1 AND a.siteid = '$this->siteid' AND a.blogid = '$this->blogid' $filt
                                   $order LIMIT $list_start, $list_end");
    }
    else
    {
      $items = $wpdb->get_results("SELECT a.id, a.tokentype, FROM_UNIXTIME(a.datecreated) as datecreated, 
                                   FROM_UNIXTIME(a.lastupdated) as lastupdated, a.tokendescription as description, 
                                   b.user_login as author, a.authorid as authorid, a.version as version
                                   FROM $this->table_types a
                                   LEFT JOIN $this->table_users b ON a.authorid = b.ID 
                                   WHERE a.active = 1 AND a.siteid = '$this->siteid' AND a.blogid = '$this->blogid'
                                   $order LIMIT $list_start, $list_end");
    }

    $alt = true;

    foreach ($items as $item) 
    {
      $alt = ($alt)? false : true;
      $alttext = ($alt) ? 'alternate' : '';
      $id = $item->id;
      $tokentype = $item->tokentype;
      $datecreated = strtotime($item->datecreated);
      $datecreated = date('D, M j, Y \a\t g:i A', $datecreated);
      $lastupdated = strtotime($item->lastupdated);
      $lastupdated = date('D, M j, Y \a\t g:i A', $lastupdated);
      $description = $item->description;
      $author = $item->author;
      $authorid = $item->authorid;
      $version = $item->version;
      $results .= "<tr class=\"" . $alttext . "\">
                   <td style=\"width: 16px; height:27px; cursor: pointer;\"><a href=\"admin.php?page=tokenmanagertypeedit&tid=$id\" class=\"tm_helpmenu\"><img src=\"" . plugin_dir_url( __FILE__ )  . "icons/edit.png\" /><span class=\"tm_round tm_helpmenutext\">Edit Token Type ($tokentype)</span></a></td>
                   <td style=\"width: 16px; height:27px;\"><a href=\"javascript:void(0)\" class=\"tm_helpmenu\" style=\"cursor: default;\"><img src=\"" . plugin_dir_url( __FILE__ )  . "icons/description.png\" /><span class=\"tm_round tm_helpmenutext\">$description</span></a></td><td style=\"width: 16px; height:27px;\"><a href=\"javascript:void(0)\" class=\"tm_helpmenu\" style=\"cursor: default;\"><img src=\"" . plugin_dir_url( __FILE__ )  . "icons/stats.png\" /><span class=\"tm_round tm_helpmenutext\">Date Created: $datecreated<br/>Last Updated: $lastupdated</span></a></td>
                   <td style=\"width: 16px; height:27px; cursor: pointer;\"><a href=\"user-edit.php?user_id=$authorid\" class=\"tm_helpmenu\"><img src=\"" . plugin_dir_url( __FILE__ )  . "icons/user.png\" /><span class=\"tm_round tm_helpmenutext\">Created by $author (ID: $authorid)</span></a></td>
                   <td style=\"width: 120px;\">$id</td>
                   <td>$tokentype</td>
                   <td style=\"width: 16px; height:27px; cursor: pointer;\"><a href=\"admin.php?page=tokenmanagerpro\" class=\"tm_helpmenu\"><img src=\"" . plugin_dir_url( __FILE__ )  . "icons/history.png\" /><span class=\"tm_round tm_helpmenutext3\">View History (VER: $version)</span></a></td>
                   <td style=\"width: 16px; height:27px; cursor: pointer;\"><a href=\"javascript:void(0);\" onclick=\"confirmdelete2($id, '$tokentype');\" class=\"tm_helpmenu\"><img src=\"" . plugin_dir_url( __FILE__ )  . "icons/delete.png\" /><span class=\"tm_round tm_helpmenutext2\">Delete Token Type ($tokentype)</span></a></td>
                   </tr>";
    }

    $results .= '</tbody></table>' . $paging->render('Find Token Type');

    return $results;
  }
}

// Build class
$tokentypes = new tokentypes();
  
?>

<script type="text/javascript" src="<?php echo plugin_dir_url( __FILE__ ); ?>tm.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo plugin_dir_url( __FILE__ ); ?>tm.css" />

<div class="wrap">
  <div class="icon32 icon32-posts-post" id="icon-edit" style="background: transparent url(<?php echo $tokentypes->icon; ?>) 0px 0px no-repeat"><br></div>
  <h2>Token Manager - Token Types <a class="add-new-h2" href="admin.php?page=tokenmanageraddtype">Add New Type</a> </h2>
  <p>A list of token types currently listed in the token manager.</p>

<form method="POST" id="myfrom" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
<?php echo $tokentypes->build_grid($_GET['page']); ?>
<input type="hidden" id="typeid" name="typeid" value="" />
<input type="hidden" id="typeidforce" name="typeidforce" value="" />
<?php echo $tokentypes->js; ?>
</form>
</div>
