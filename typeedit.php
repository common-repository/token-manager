<?php

class tokentypeedit
{
  // Variables
  public $version;
  public $error;
  public $errortokentype;
  public $errortokendescription;
  public $icon;
  public $table_types;
  public $table_typeversions;
  public $table_tokens;
  public $siteid;
  public $blogid;

  // Construct the token add
  public function __construct()
  {
    global $_POST, $wpdb, $current_site, $blog_id;

    $this->icon = plugin_dir_url( __FILE__ ) . 'icons/icon1_32.png';
    $this->table_types = $wpdb->base_prefix . 'tokenmanagertypes'; 
    $this->table_typeversions = $wpdb->base_prefix . 'tokenmanagertypesversions';
    $this->table_tokens = $wpdb->base_prefix . 'tokenmanager';   
    $this->siteid = (isset($current_site) && isset($current_site->id)) ? $current_site->id : '0';
    $this->blogid = $blog_id;   

    $this->postback();
  }

  // Handles the postback info.
  private function postback()
  {
    global $_POST, $wpdb, $_GET; 
  
    $haserror = false;
    $tid = '';
    if(!isset($_GET['tid']) || empty($_GET['tid']) || !is_numeric($_GET['tid'])){ return; } else { $tid = $_GET['tid']; };

    // Process Form
    if(isset($_POST['edittokentype']))
    {
      // Check required.
      if(!isset($_POST['tokentype']) || empty($_POST['tokentype']))
      { 
        $haserror = true; 
        $this->errortokentype = '<span style="color: Red; display: block;">Token Type is a required field!</span>'; 
      }
      if(!isset($_POST['tokendescription']) || empty($_POST['tokendescription']))
      { 
        $haserror = true; 
        $this->errortokendescription = '<span style="color: Red; display: block;">Token Description is a required field!</span>'; 
      }

      if(!$haserror)
      {
        $tokentype = $wpdb->escape($_POST['tokentype']);
        $tokendescription = $wpdb->escape($_POST['tokendescription']);

        $authorid = wp_get_current_user();
        $authorid = $authorid->ID;
        //$tablename = $this->get_tablenametypes();

        // Check if type already exists
        $toke = $wpdb->get_var("SELECT count(*) FROM $this->table_types 
                                WHERE tokentype = '$tokentype' AND id!='$tid' AND siteid = '$this->siteid' AND blogid='$this->blogid' 
                                AND active = 1 LIMIT 1");

        if($toke>0){ $haserror = true; $this->errortokentype = '<span style="color: Red; display: block;">Token Type already exists!</span>'; }

        if(!$haserror)
        {
          // Sets the history for the insert
          $remoteip = $_SERVER['REMOTE_ADDR'];
          $occurred = date("D, M j, Y G:i:s T");
          $history = $wpdb->escape("<event remoteip=\"$remoteip\" dateoccurred=\"$occurred\" author=\"$authorid\" type=\"update\" status=\"Token type was updated in the token manager.\"  />
    <!-- NEXT -->");

          // Setup version
          if($wpdb->query("INSERT INTO $this->table_typeversions (id, tokentypeid, datecreated, lastupdated, tokentype, 
                        tokendescription, siteid, blogid, authorid, orderof, active, version) 
                        SELECT null, id, datecreated, lastupdated, tokentype, tokendescription, siteid, blogid, authorid, 
                        orderof, active, version FROM $this->table_types 
                        WHERE id='$tid' AND siteid='$this->siteid' AND blogid='$this->blogid' LIMIT 1;")==false)
	  {
	    echo $wpdb->last_error();
          }


          // Insert into database
          if($wpdb->query("UPDATE $this->table_types SET lastupdated = UNIX_TIMESTAMP(), 
                        tokentype = '$tokentype', tokendescription = '$tokendescription',
                        history = (SELECT REPLACE(history, '<!-- NEXT -->', '$history')),
                        version = version + 1 
                        WHERE id = '$tid' AND siteid='$this->siteid' AND blogid='$this->blogid';")==false)
	  {
	    echo $wpdb->last_error();
          }
        }
      }

      if($haserror)
      {
        $this->error = '<span style="color: Red;">Your form had the following errors!</span>';
      }
      else
      {
        $this->error = '<div id="tm_status" class="tm_noerror">Edited Token Type Successfully!</div>' .
                            '<script type="text/javascript">function hide_status(){' .
                            'document.getElementById("tm_status").style.display = "none";' .
                            '} setTimeout("hide_status()",4000);</script>';
      }
    }
    else
    {
      //$tablename = $wpdb->base_prefix . 'tokenmanagertypes';
      $items = $wpdb->get_results("SELECT tokentype, tokendescription FROM $this->table_types 
                                  WHERE id='$tid' AND siteid='$this->siteid' AND blogid='$this->blogid' LIMIT 1;");

      foreach ($items as $item) 
      {
        // Get form fields
        $_POST['tokentype'] = $item->tokentype;
        $_POST['tokendescription'] = $item->tokendescription;
      }
    }
    // Add Version to page
    //$tablename = $this->get_tablenametypes();;
    $this->version = $wpdb->get_var("SELECT version FROM $this->table_types WHERE id='$tid' LIMIT 1;");
  }
}

// Build class
$tokentypeedit = new tokentypeedit();
  
$_POST = array_map('stripslashes_deep', $_POST);
$_GET = array_map('stripslashes_deep', $_GET);

?>

<script type="text/javascript" src="<?php echo plugin_dir_url( __FILE__ ); ?>tm.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo plugin_dir_url( __FILE__ ); ?>tm.css" />

<div class="wrap">
  <div class="icon32 icon32-posts-post" id="icon-edit" style="background: transparent url(<?php echo $tokentypeedit->icon; ?>) 0px 0px no-repeat"><br></div>
  <h2>Token Manager - Edit Token Type (ID: <?php echo intval($_GET['tid']); ?>, VER: <?php echo $tokentypeedit->version; ?>) <a class="add-new-h2" href="admin.php?page=tokenmanagertypes">View All Types</a></h2>
  <p>Edit a token type in the wordpress token manager.</p>
  <form method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>">

<?php echo $tokentypeedit->error; ?>
<table class="form-table">
<tbody>
  <tr class="form-field form-required">
    <th scope="row"><label for="tokentype">Token Type <span class="description">(required)</span></label> <a href="#" class="tm_helpmenu">
            <img src="<?php echo plugin_dir_url( __FILE__ ); ?>icons/help.png" class="tm_iconhelp" />
            <span class="tm_round tm_helpmenutext">The name for the token type.</span>
          </a></th>
      <td>
        <input style="width: 100%;" type="text" aria-required="true" id="tokentype" name="tokentype" value="<?php echo (isset($_POST['tokentype']) && !empty($_POST['tokentype']))?$_POST['tokentype']:''; ?>">
        <?php echo $tokentypeedit->errortokentype; ?>
      </td>
  </tr>
    <tr class="form-field form-required">
      <th scope="row"><label for="tokendescription">Type Description <span class="description">(required)</span></label> <a href="#" class="tm_helpmenu">
            <img src="<?php echo plugin_dir_url( __FILE__ ); ?>icons/help.png" class="tm_iconhelp" />
            <span class="tm_round tm_helpmenutext">The description for what the token type does.</span>
          </a></th>
        <td>
          <textarea style="width: 100%;" id="tokendescription" name="tokendescription" rows="12"><?php echo (isset($_POST['tokendescription']) && !empty($_POST['tokendescription']))?$_POST['tokendescription']:''; ?></textarea>
          <?php echo $tokentypeedit->errortokendescription; ?>
        </td>
    </tr>
    <tr>
      <th scope="row"></th>
        <td><input class="button-primary" type="submit" name="edittokentype" value="Edit Type" id="submitbutton" /></td>
    </tr>
</tbody></table>
</form>
</div>
