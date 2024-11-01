<?php

class tokentypeadd
{
  // Variables
  public $error;
  public $errortokentype;
  public $errortokendescription;
  public $icon;
  public $table_types;
  public $table_tokens;
  public $siteid;
  public $blogid;

  // Construct the token add
  public function __construct()
  {
    global $_POST, $wpdb, $current_site, $blog_id;

    $this->icon = plugin_dir_url( __FILE__ ) . 'icons/icon1_32.png';
    $this->table_types = $wpdb->base_prefix . 'tokenmanagertypes'; 
    $this->table_tokens = $wpdb->base_prefix . 'tokenmanager';  
    $this->siteid = (isset($current_site) && isset($current_site->id)) ? $current_site->id : '0';
    $this->blogid = $blog_id;
    
    $this->postback();
  }

  // Handles the postback info.
  private function postback()
  {
    global $_POST, $wpdb;
  
    $haserror = false;

    // Process Form
    if(isset($_POST['addtokentype']))
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
                               WHERE tokentype = '$tokentype' AND active='1' AND siteid='$this->siteid' AND blogid='$this->blogid' LIMIT 1");

        if($toke>0){ $haserror = true; $this->errortokentype = '<span style="color: Red; display: block;">Token Type already exists!</span>'; }

        if(!$haserror)
        {
          // Sets the history for the insert
          $remoteip = $_SERVER['REMOTE_ADDR'];
          $occurred = date("D, M j, Y G:i:s T");
          $history = $wpdb->escape("<?xml version=\"1.0\" encoding=\"utf-8\" ?>
<tokenmanagertypes>
  <history>
    <event remoteip=\"$remoteip\" dateoccurred=\"$occurred\" author=\"$authorid\" type=\"create\" status=\"Created a new token type in the token manager.\"  />
    <!-- NEXT -->
  </history>
</tokenmanagertypes>");

          // Insert into database
          $wpdb->query("INSERT INTO $this->table_types (id, datecreated, lastupdated, tokentype, tokendescription, 
                        siteid, blogid, authorid, orderof, active, version, history) 
                        VALUES (null, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), '$tokentype', '$tokendescription', 
                        '$this->siteid', '$this->blogid', '$authorid', '0', '1', '1', '$history')");

          // Get last insert id.
          $id = $wpdb->insert_id;
        }
      }

      if($haserror)
      {
        $this->error = '<span style="color: Red;">Your form had the following errors!</span>';
      }
      else
      {
        $this->error = '<div id="tm_status" class="tm_noerror">New Token Type Created Successfully!</div>' .
                            '<script type="text/javascript">function hide_status(){' .
                            'document.getElementById("tm_status").style.display = "none";' .
                            '} setTimeout("hide_status()",4000);</script>';

        // Reset form.
        $_POST['tokentype'] = '';
        $_POST['tokendescription'] = '';
      }
    }
  }
}

// Build class
$tokentypeadd = new tokentypeadd();

$_POST = array_map('stripslashes_deep', $_POST);
$_GET = array_map('stripslashes_deep', $_GET);
  
?>

<script type="text/javascript" src="<?php echo plugin_dir_url( __FILE__ ); ?>tm.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo plugin_dir_url( __FILE__ ); ?>tm.css" />

<div class="wrap">
  <div class="icon32 icon32-posts-post" id="icon-edit" style="background: transparent url(<?php echo $tokentypeadd->icon; ?>) 0px 0px no-repeat"><br></div>
  <h2>Token Manager - Add New Type <a class="add-new-h2" href="admin.php?page=tokenmanagertypes">View All Types</a></h2>
  <p>Create a new token type in the wordpress token manager.</p>
  <form method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>">

<?php echo $tokentypeadd->error; ?>
<table class="form-table">
<tbody>
  <tr class="form-field form-required">
    <th scope="row"><label for="tokentype">Token Type <span class="description">(required)</span></label> <a href="#" class="tm_helpmenu">
            <img src="<?php echo plugin_dir_url( __FILE__ ); ?>icons/help.png" class="tm_iconhelp" />
            <span class="tm_round tm_helpmenutext">The name for the token type.</span>
          </a></th>
      <td>
        <input style="width: 100%;" type="text" aria-required="true" id="tokentype" name="tokentype" value="<?php echo (isset($_POST['tokentype']) && !empty($_POST['tokentype']))?$_POST['tokentype']:''; ?>">
        <?php echo $tokentypeadd->errortokentype; ?>
      </td>
  </tr>
    <tr class="form-field form-required">
      <th scope="row"><label for="tokendescription">Type Description <span class="description">(required)</span></label> <a href="#" class="tm_helpmenu">
            <img src="<?php echo plugin_dir_url( __FILE__ ); ?>icons/help.png" class="tm_iconhelp" />
            <span class="tm_round tm_helpmenutext">The description for what the token type does.</span>
          </a></th>
        <td>
          <textarea style="width: 100%;" id="tokendescription" name="tokendescription" rows="12"><?php echo (isset($_POST['tokendescription']) && !empty($_POST['tokendescription']))?$_POST['tokendescription']:''; ?></textarea>
          <?php echo $tokentypeadd->errortokendescription; ?>
        </td>
    </tr>
    <tr>
      <th scope="row"></th>
        <td><input class="button-primary" type="submit" name="addtokentype" value="Add Type" id="submitbutton" /></td>
    </tr>
</tbody></table>
</form>
</div>
