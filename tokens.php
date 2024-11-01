<?php

// Add paging class
include_once("paging.php");

// Class for displaying the tokens in a grid.
class tokens
{
  // Variables
  public $siteid;
  public $blogid;
  public $icon;
  public $icon2;
  public $icon3;
  public $table_types;
  public $table_tokens;
  public $table_pages;
  public $table_users;

  // Construct for tokens.
  public function __construct()
  {
    global $wpdb, $current_site, $blog_id;

    $this->icon = plugin_dir_url( __FILE__ ) . 'icons/icon1_32.png';
    $this->icon2 = plugin_dir_url( __FILE__ ) . 'icons/close.png';
    $this->icon3 = plugin_dir_url( __FILE__ ) . 'icons/ajax-loader.gif';
    $this->table_types = $wpdb->base_prefix . 'tokenmanagertypes'; 
    $this->table_tokens = $wpdb->base_prefix . 'tokenmanager';
    $this->table_pages = $wpdb->base_prefix . 'tokenmanagerpages';
    $this->table_users = $wpdb->base_prefix . 'users';
    $this->siteid = (isset($current_site) && isset($current_site->id)) ? $current_site->id : '0';
    $this->blogid = $blog_id;

    // Handles post back.
    $this->postback();
  }

  // Deletes the token.
  private function postback()
  {
    global $_POST, $wpdb;

    // Process Form
    if(isset($_POST['tokenid']) && is_numeric($_POST['tokenid']))
    {
      $tid = $wpdb->escape($_POST['tokenid']);
      $wpdb->query("DELETE FROM $this->table_pages WHERE tokenid='$tid' AND siteid='$this->siteid' AND blogid='$this->blogid';");
      $wpdb->query("UPDATE $this->table_tokens SET active=0 WHERE id='$tid' AND siteid='$this->siteid' AND blogid='$this->blogid';");
    }
  }

  // Initializes the page.
  function build_grid()
  {
    global $_POST, $wpdb, $_GET;

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
        $filt = "AND a.tokenname like '%" . trim($wpdb->escape($_GET['filt']), '{}') . "%'";
      }
    }

    //get the number of records in the database table
    $pagination_count = 0;
    $startid = 0;
    $endid = 0;
    if(!empty($filt))
    {
      $startid = $wpdb->get_var("SELECT a.id      
      FROM $this->table_tokens a 
      LEFT JOIN $this->table_types b ON a.typeid = b.id 
      LEFT JOIN $this->table_users c ON a.authorid = c.ID 
      WHERE a.active = 1 AND b.active = 1 AND a.siteid='$this->siteid' AND a.blogid='$this->blogid'
      order by a.processorder DESC LIMIT 1;");

      $endid = $wpdb->get_var("SELECT a.id      
      FROM $this->table_tokens a 
      LEFT JOIN $this->table_types b ON a.typeid = b.id 
      LEFT JOIN $this->table_users c ON a.authorid = c.ID 
      WHERE a.active = 1 AND b.active = 1 AND a.siteid='$this->siteid' AND a.blogid='$this->blogid' 
      order by a.processorder ASC LIMIT 1;");

      $pagination_count = $wpdb->get_var("SELECT count(*)      
      FROM $this->table_tokens a 
      LEFT JOIN $this->table_types b ON a.typeid = b.id 
      LEFT JOIN $this->table_users c ON a.authorid = c.ID 
      WHERE a.active = 1 AND b.active = 1 AND a.siteid='$this->siteid' AND a.blogid='$this->blogid' $filt;");
    }
    else
    {
      $startid = $wpdb->get_var("SELECT a.id     
      FROM $this->table_tokens a 
      LEFT JOIN $this->table_types b ON a.typeid = b.id 
      LEFT JOIN $this->table_users c ON a.authorid = c.ID 
      WHERE a.active = 1 AND b.active = 1 AND a.siteid='$this->siteid' AND a.blogid='$this->blogid' 
      order by a.processorder DESC LIMIT 1;");

      $endid = $wpdb->get_var("SELECT a.id      
      FROM $this->table_tokens a 
      LEFT JOIN $this->table_types b ON a.typeid = b.id 
      LEFT JOIN $this->table_users c ON a.authorid = c.ID 
      WHERE a.active = 1 AND b.active = 1 AND a.siteid='$this->siteid' AND a.blogid='$this->blogid' 
      order by a.processorder ASC LIMIT 1;");

      $pagination_count = $wpdb->get_var("SELECT count(*)      
      FROM $this->table_tokens a 
      LEFT JOIN $this->table_types b ON a.typeid = b.id 
      LEFT JOIN $this->table_users c ON a.authorid = c.ID 
      WHERE a.active = 1 AND b.active = 1 AND a.siteid='$this->siteid' AND a.blogid='$this->blogid';");
    }

    $pge = (isset($_GET['pge']) && !empty($_GET['pge'])) ? $_GET['pge'] : 1;
    $paging = new paging($pge, 25, intval($pagination_count), paging::pageurl());

    // Get the order
    $order = 'ORDER BY a.processorder DESC';
    $linka = 'admin.php?page=tokenmanager';
    $linka .= (isset($_GET['filt']) && !empty($_GET['filt']))? '&filt=' . $_GET['filt'] : '';
    $linka .= (isset($_GET['pge']) && !empty($_GET['pge']))? "&pge=$pge" : '';
    $linka .= (isset($_GET['per']) && !empty($_GET['per']))? '&per=' . $_GET['per'] : '';
    $linka .= (isset($_GET['per']) && !empty($_GET['total']))? '&total=' . $_GET['total'] : '';

    $l0 = $linka . '&sort=0a';
    $l1 = $linka . '&sort=1a';
    $l2 = $linka . '&sort=2a';
    $i0 = '';
    $i1 = '';
    $i2 = '';
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
          $order = 'ORDER BY a.tokenname'; 
          $l1 = str_replace('&sort=1a', '&sort=1d', $l1);
          $i1 = '<img class="sortarrow" src="' . plugin_dir_url( __FILE__ )  . 'icons/arrowup.png" />'; 
          break;
	case '1d' : 
          $order = 'ORDER BY a.tokenname DESC';
          $l1 = str_replace('&sort=1a', '', $l1); 
          $i1 = '<img class="sortarrow" src="' . plugin_dir_url( __FILE__ )  . 'icons/arrowdown.png" />'; 
          break;
	case '2a' : 
          $order = 'ORDER BY b.tokentype'; 
          $l2 = str_replace('&sort=2a', '&sort=2d', $l2);
          $i2 = '<img class="sortarrow" src="' . plugin_dir_url( __FILE__ )  . 'icons/arrowup.png" />'; 
          break;
	case '2d' : 
          $order = 'ORDER BY b.tokentype DESC'; 
          $l2 = str_replace('&sort=2a', '', $l2);
          $i2 = '<img class="sortarrow" src="' . plugin_dir_url( __FILE__ )  . 'icons/arrowdown.png" />'; 
          break;
	default: break;
      }
    }
   
    // Escape data
    $list_end = 25;
    $list_start = $paging->get_rangestart()-1;
    $results = $paging->render('Find Token') . 
    '<table class="widefat">
      <thead>
        <tr>
          <th></th>
          <th></th>
          <th></th>
          <th></th>
          <th></th>
          <th></th>
          <th></th>
          <th><a href="' . $l0 . '">Token ID</a>' . $i0 . '</th>
          <th><a href="' . $l1 . '">Token Name</a>' . $i1 . '</th>
          <th><a href="' . $l2 . '">Token Type</a>' . $i2 . '</th>
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
          <th></th>
          <th></th>
          <th></th>
          <th><a href="' . $l0 . '">Token ID</a>' . $i0 . '</th>
          <th><a href="' . $l1 . '">Token Name</a>' . $i1 . '</th>
          <th><a href="' . $l2 . '">Token Type</a>' . $i2 . '</th>
          <th></th>
          <th></th>
        </tr>
      </tfoot>
    <tbody>';

    if(!empty($filt))
    {
      $items = $wpdb->get_results("
      SELECT a.id as id, a.processorder as processorder, a.tokenname as tokenname, a.frontpage as frontpage, 
      a.allpages as allpages, b.tokentype as tokentype, FROM_UNIXTIME(a.datecreated) as datecreated, 
      FROM_UNIXTIME(a.lastupdated) as lastupdated, a.description as description, c.user_login as author, 
      a.authorid as authorid, a.version as version      
      FROM $this->table_tokens a 
      LEFT JOIN $this->table_types b ON a.typeid = b.id 
      LEFT JOIN $this->table_users c ON a.authorid = c.ID
      WHERE a.active = 1 AND b.active = 1 AND a.siteid='$this->siteid' AND a.blogid='$this->blogid' $filt
      $order LIMIT $list_start, $list_end;");
    }
    else
    {
      $items = $wpdb->get_results("
      SELECT a.id as id, a.processorder as processorder, a.tokenname as tokenname, a.frontpage as frontpage, 
      a.allpages as allpages, b.tokentype as tokentype, FROM_UNIXTIME(a.datecreated) as datecreated, 
      FROM_UNIXTIME(a.lastupdated) as lastupdated, a.description as description, c.user_login as author, 
      a.authorid as authorid, a.version as version      
      FROM $this->table_tokens a 
      LEFT JOIN $this->table_types b ON a.typeid = b.id 
      LEFT JOIN $this->table_users c ON a.authorid = c.ID 
      WHERE a.active = 1 AND b.active = 1 AND a.siteid='$this->siteid' AND a.blogid='$this->blogid'
      $order LIMIT $list_start, $list_end;");
    }

     $alt = true;    

     foreach ($items as $item) 
     {
       $alt = ($alt)? false : true;
       $alttext = ($alt) ? 'alternate' : '';
       $id = $item->id;
       $processorder = $item->processorder; 
       $tokenname = '{' . $item->tokenname . '}';
       $tokentype = $item->tokentype;
       $datecreated = strtotime($item->datecreated);
       $datecreated = date('D, M j, Y \a\t g:i A', $datecreated);
       $lastupdated = strtotime($item->lastupdated);
       $lastupdated = date('D, M j, Y \a\t g:i A', $lastupdated);
       $description = $item->description;
       $author = $item->author;
       $authorid = $item->authorid;
       $version = $item->version;
       $moveup = ($id!=$startid) ? "<td style=\"width: 16px; height:27px; cursor: pointer;\"><a href=\"javascript:void(0);\" onclick=\"moveup($id);\" class=\"tm_helpmenu\"><img src=\"" . plugin_dir_url( __FILE__ )  . "icons/up.png\" /><span class=\"tm_round tm_helpmenutext\">Move Up - Process Order ID - $processorder</span></a></td>" : "<td style=\"width: 16px;\"></td>";
      $movedown = ($id!=$endid) ? "<td style=\"width: 16px; height:27px; cursor: pointer;\"><a href=\"javascript:void(0);\" onclick=\"movedown($id);\" class=\"tm_helpmenu\"><img src=\"" . plugin_dir_url( __FILE__ )  . "icons/down.png\" /><span class=\"tm_round tm_helpmenutext\">Move Down - Process Order ID - $processorder</span></a></td>" : "<td style=\"width: 16px;\"></td>";
      $results .= "<tr class=\"" . $alttext . "\">
                   $moveup
                   $movedown
                   <td style=\"width: 16px; height:27px; cursor: pointer;\"><a href=\"admin.php?page=tokenmanageredit&tid=$id\" class=\"tm_helpmenu\"><img src=\"" . plugin_dir_url( __FILE__ )  . "icons/edit.png\" /><span class=\"tm_round tm_helpmenutext\">Edit Token $tokenname</span></a></td>
                   <td style=\"width: 16px; height:27px;\"><a href=\"javascript:void(0)\" class=\"tm_helpmenu\" style=\"cursor: default;\"><img src=\"" . plugin_dir_url( __FILE__ )  . "icons/description.png\" /><span class=\"tm_round tm_helpmenutext\">$description</span></a></td>
                   <td style=\"width: 16px; height:27px;\"><a href=\"javascript:void(0)\" class=\"tm_helpmenu\" style=\"cursor: default;\"><img src=\"" . plugin_dir_url( __FILE__ )  . "icons/stats.png\" /><span class=\"tm_round tm_helpmenutext\">Date Created: $datecreated<br/>Last Updated: $lastupdated</span></a></td>
                   <td style=\"width: 16px; height:27px; cursor: pointer;\"><a href=\"user-edit.php?user_id=$authorid\" class=\"tm_helpmenu\"><img src=\"" . plugin_dir_url( __FILE__ )  . "icons/user.png\" /><span class=\"tm_round tm_helpmenutext\">Created by $author (ID: $authorid)</span></a></td>    
                   <td style=\"width: 16px; height:27px; cursor: pointer;\"><a href=\"javascript:void(0);\" onclick=\"loadpopup($id, '$tokenname');\" class=\"tm_helpmenu\"><img src=\"" . plugin_dir_url( __FILE__ )  . "icons/pages.png\" /><span class=\"tm_round tm_helpmenutext\">Attach Pages to $tokenname</span></a></td>
                   <td style=\"width: 120px; height:27px;\">$id</td>
                   <td>$tokenname</td>
                   <td>$tokentype</td>
                   <td style=\"width: 16px; height:27px; cursor: pointer;\"><a href=\"admin.php?page=tokenmanagerpro\" class=\"tm_helpmenu\"><img src=\"" . plugin_dir_url( __FILE__ )  . "icons/history.png\" /><span class=\"tm_round tm_helpmenutext3\">View History (VER: $version)</span></a></td>
                   <td style=\"width: 16px; height:27px; cursor: pointer;\"><a href=\"javascript:void(0);\" onclick=\"confirmdelete($id, '$tokenname');\" class=\"tm_helpmenu\"><img src=\"" . plugin_dir_url( __FILE__ )  . "icons/delete.png\" /><span class=\"tm_round tm_helpmenutext2\">Delete Token $tokenname</span></a></td>
                   </tr>";
    }

    $results .= '</tbody></table>' . $paging->render('Find Token');

    return $results;
  }
}

// Build class
$tokens = new tokens();
  
?>

<script type="text/javascript" src="<?php echo plugin_dir_url( __FILE__ ); ?>tm.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo plugin_dir_url( __FILE__ ); ?>tm.css" />

<div id="pagespopup" class="tm_pagespopup">
<form>
  <input type="hidden" id="tid" value="" />
  <input type="hidden" id="attachedpages" name="attachedpages" value="" />
  <div class="tm_pagespopup_top">
    Attach Token <span id="pagespopuptokinname"></span>
    <div style="float: right; height: 2em; line-height: 2em; margin-top: 5px;"><a href="javascript:void(0);" onclick="document.getElementById('pagespopup').style.display = 'none';"><img src="<?php echo $tokens->icon2; ?>" /></a></div>
  </div>
  <div style="padding: 10px;">
    <input type="text" id="pagespopupsearch" name="pagespopupsearch" value="" /> 
    <input type="button" value="Search for Page" class="button" onclick="search_results();" />
    <img src="<?php echo $tokens->icon3; ?>" id="loader" style="display: none; float: right;" />
  </div>
  <table cellpadding="0" cellspacing="0" border="0">
  <tr>
    <td style="width: 215px;">
      <span style="display: block; padding: 0px 0px 5px 5px;">Not Yet Added:</span>
      <select id="pagesnotadded" name="pagesnotadded" size="7" style="height: 160px; width: 215px; border: solid 1px #DDDDDD; background-color: #FFFFFF;"></select>
      </div>
    </td>
    <td style="width: 65px; text-align: center;">
      <input type="button" class="button" value="&gt;&gt;" id="pagespopupadd" name="pagespopupadd" onclick="pageadd();" />
      <input type="button" class="button" value="&lt;&lt;" id="pagespopupremove" name="pagespopupremove" onclick="pageremove();" />
    </td>
    <td style="width: 215px;">
      <span style="display: block; padding: 0px 0px 5px 5px">Attached Pages:</span>
      <select id="pagesadded" name="pagesadded" size="7" style="height: 160px; width: 215px; border: solid 1px #DDDDDD; background-color: #FFFFFF;"></select>
      </div>
    </td>
  </tr>
  </table>
  <div style="padding: 5px;">
    <input type="button" name="pagespopupclose" id="pagespopupclose" value="Close" class="button-primary alignright" onclick="document.getElementById('pagespopup').style.display = 'none';" />
  </div>
</form>
</div>

<div class="wrap">
  <div class="icon32 icon32-posts-post" id="icon-edit" style="background: transparent url(<?php echo $tokens->icon; ?>) 0px 0px no-repeat"><br></div>
  <h2>Token Manager <a class="add-new-h2" href="admin.php?page=tokenmanageradd">Add New Token</a> </h2>
  <p>Below is a list of tokens added to the Token Manager.</p>

<form method="POST" id="myfrom" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
<?php echo $tokens->build_grid($_GET['page']); ?>
<input type="hidden" id="tokenid" name="tokenid" value="" />
</form>

</div>
<script type="text/javascript">
document.body.appendChild(document.getElementById('pagespopup'));
</script>

