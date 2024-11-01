<?php

class tokenadd
{
  // Variables
  public $siteid;
  public $blogid;
  public $error;
  public $errortokenname;
  public $errortokentype;
  public $errorhtmlvalue;
  public $errordescription;
  public $icon;
  public $table_pages;
  public $table_types;
  public $table_tokens;

  // Construct the token add
  public function __construct()
  {
    global $_POST, $wpdb, $current_site, $blog_id;

    $this->icon = plugin_dir_url( __FILE__ ) . 'icons/icon1_32.png';
    $this->table_pages = $wpdb->base_prefix . 'tokenmanagerpages'; 
    $this->table_types = $wpdb->base_prefix . 'tokenmanagertypes'; 
    $this->table_tokens = $wpdb->base_prefix . 'tokenmanager';   
    $this->siteid = (isset($current_site) && isset($current_site->id)) ? $current_site->id : '0';
    $this->blogid = $blog_id;  

    $this->postback();
  }

  // Handles the postback info.
  private function postback()
  {
    global $_POST, $wpdb, $_SERVER;  
    $haserror = false;

    // Process Form
    if(isset($_POST['addtoken']))
    {
      // Check required.
      if(!isset($_POST['tokentype']) || empty($_POST['tokentype']))
      { 
        $haserror = true; 
        $this->errortokentype = '<span style="color: Red; display: block;">Please add a token type first.</span>'; 
      }
      if(!isset($_POST['tokenname']) || empty($_POST['tokenname']))
      { 
        $haserror = true; 
        $this->errortokenname = '<span style="color: Red; display: block;">Token Name is a required field!</span>'; 
      }
      if(empty($_POST['htmlvalue']) && empty($_POST['cssvalue']) && empty($_POST['jsvalue']) && empty($_POST['phpvalue']))
      { 
        $haserror = true; 
        $this->errorhtmlvalue = '<span style="color: Red; display: block;">Token Value is a required field!</span>'; 
      }
      if(!isset($_POST['description']) || empty($_POST['description']))
      { 
        $haserror = true; 
        $this->errordescription = '<span style="color: Red; display: block;">Description is a required field!</span>'; 
      }

      if(!$haserror)
      {
        $tokenname = $wpdb->escape($_POST['tokenname']);
        $tokentype = $wpdb->escape($_POST['tokentype']);
        $htmlvalue = $wpdb->escape($_POST['htmlvalue']);
        $phpvalue = $wpdb->escape($_POST['phpvalue']);
        $cssvalue = $wpdb->escape($_POST['cssvalue']);
        $jsvalue = $wpdb->escape($_POST['jsvalue']);
        $description = $wpdb->escape($_POST['description']);

        $authorid = wp_get_current_user();
        $authorid = $authorid->ID;

        // Check if type already exists
        $toke = $wpdb->get_var("SELECT count(*) FROM $this->table_tokens
                                WHERE tokenname = '$tokenname' AND active='1' AND siteid='$this->siteid' AND blogid='$this->blogid' LIMIT 1");

        if($toke>0){ $haserror = true; $this->errortokenname = '<span style="color: Red; display: block;">Token Name already exists!</span>'; }

        if(!$haserror)
        {
          // Sets the history for the insert
          $remoteip = $_SERVER['REMOTE_ADDR'];
          $occurred = date("D, M j, Y G:i:s T");
          $history = $wpdb->escape("<?xml version=\"1.0\" encoding=\"utf-8\" ?>
<tokenmanager>
  <history>
    <event remoteip=\"$remoteip\" dateoccurred=\"$occurred\" author=\"$authorid\" type=\"create\" status=\"Created a new token in the token manager.\"  />
    <!-- NEXT -->
  </history>
</tokenmanager>");

          // Get process order id
          $processorder = 0;
          $processorder = $wpdb->get_var("SELECT processorder FROM $this->table_tokens order by processorder DESC LIMIT 1");
          $processorder++;

          // Insert into database
          $wpdb->query("INSERT INTO $this->table_tokens (id, datecreated, lastupdated, siteid, blogid, 
                        tokenname, htmlvalue, phpvalue, cssvalue, jsvalue, description, authorid, typeid, 
                        active, version, processorder, history) 
                        VALUES (null, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), '$this->siteid', '$this->blogid', 
                        '$tokenname', '$htmlvalue', '$phpvalue', '$cssvalue', '$jsvalue', '$description', 
                        '$authorid', '$tokentype', '1', '1', '$processorder', '$history')");

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
        $this->error = '<div id="tm_status" class="tm_noerror">New Token Created Successfully!</div>' .
                            '<script type="text/javascript">function hide_status(){' .
                            'document.getElementById("tm_status").style.display = "none";' .
                            '} setTimeout("hide_status()",4000);</script>';
 
        // Reset form.
        $_POST['tokenname'] = '';
        $_POST['htmlvalue'] = '';
        $_POST['phpvalue'] = '';
        $_POST['cssvalue'] = '';
        $_POST['jsvalue'] = '';
        $_POST['tokentype'] = '';
        $_POST['description'] = '';
      }
    }
  }

  // Builds the drop downlist
  function build_dropdownlist($_selected)
  {
    global $_POST, $wpdb;

    // Escape data
    $results = '<select id="tokentype" name="tokentype" style="min-width:220px;">';
    $droplistitems = $wpdb->get_results("SELECT id, tokentype FROM $this->table_types 
                                        WHERE active = 1 AND siteid='$this->siteid' AND blogid='$this->blogid' ORDER BY orderof");

    foreach ($droplistitems as $droplistitem) 
    {
      $results .= '<option value="' . $droplistitem->id . '"' . (($_selected==$droplistitem->id)? ' selected="selected"': '')  
               . '>' . $droplistitem->tokentype . '</option>';
    }

    $results .= '</select>';

    return $results;
  }
}

// Build class
$tokenadd = new tokenadd();

$_POST = array_map('stripslashes_deep', $_POST);
$_GET = array_map('stripslashes_deep', $_GET);
  
?>

<script type="text/javascript" src="<?php echo plugin_dir_url( __FILE__ ); ?>tm.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo plugin_dir_url( __FILE__ ); ?>tm.css" />
<link rel="stylesheet" href="<?php echo plugin_dir_url( __FILE__ ); ?>thirdparty/codemirror/lib/codemirror.css">
<link rel="stylesheet" href="<?php echo plugin_dir_url( __FILE__ ); ?>thirdparty/codemirror/lib/util/dialog.css">
<script src="<?php echo plugin_dir_url( __FILE__ ); ?>thirdparty/codemirror/lib/codemirror.js"></script>
<script src="<?php echo plugin_dir_url( __FILE__ ); ?>thirdparty/codemirror/lib/util/search.js"></script>
<script src="<?php echo plugin_dir_url( __FILE__ ); ?>thirdparty/codemirror/lib/util/searchcursor.js"></script>
<script src="<?php echo plugin_dir_url( __FILE__ ); ?>thirdparty/codemirror/lib/util/dialog.js"></script>
<script src="<?php echo plugin_dir_url( __FILE__ ); ?>thirdparty/codemirror/mode/xml/xml.js"></script>
<script src="<?php echo plugin_dir_url( __FILE__ ); ?>thirdparty/codemirror/mode/javascript/javascript.js"></script>
<script src="<?php echo plugin_dir_url( __FILE__ ); ?>thirdparty/codemirror/mode/css/css.js"></script>
<script src="<?php echo plugin_dir_url( __FILE__ ); ?>thirdparty/codemirror/mode/clike/clike.js"></script>
<script src="<?php echo plugin_dir_url( __FILE__ ); ?>thirdparty/codemirror/mode/php/php.js"></script>

<div class="wrap">
  <div class="icon32 icon32-posts-post" id="icon-edit" style="background: transparent url(<?php echo $tokenadd->icon; ?>) 0px 0px no-repeat"><br></div>
  <h2>Token Manager - Add New Token <a class="add-new-h2" href="admin.php?page=tokenmanager">View All Tokens</a></h2>
  <p>Create a new token in the wordpress token manager.</p>
  <form method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>">

<?php echo $tokenadd->error; ?>
<table class="form-table" style="table-layout:fixed;">
<tbody>
  <tr class="form-field form-required">
    <th scope="row"><label for="tokenname">Token Name <span class="description">(required)</span></label> <a href="#" class="tm_helpmenu">
            <img src="<?php echo plugin_dir_url( __FILE__ ); ?>icons/help.png" class="tm_iconhelp" />
            <span class="tm_round tm_helpmenutext">The unique token name to create.</span>
          </a></th>
      <td>
        <input style="width: 100%;" type="text" aria-required="true" id="tokenname" name="tokenname" value="<?php echo (isset($_POST['tokenname']) && !empty($_POST['tokenname']))?$_POST['tokenname']:''; ?>">
        <?php echo $tokenadd->errortokenname; ?>
      </td>
  </tr>
  <tr class="form-field form-required">
    <th scope="row"><label for="tokentype">Token Type <span class="description">(required)</span></label> <a href="#" class="tm_helpmenu">
            <img src="<?php echo plugin_dir_url( __FILE__ ); ?>icons/help.png" class="tm_iconhelp" />
            <span class="tm_round tm_helpmenutext">The assigned token type for the token.</span>
          </a></th>
      <td><?php echo $tokenadd->build_dropdownlist((isset($_POST['tokentype']) && !empty($_POST['tokentype']))?$_POST['tokentype']:''); ?> ( <a href="admin.php?page=tokenmanageraddtype">Add New Type</a> )
          <?php echo $tokenadd->errortokentype; ?></td>
    </tr>
    <tr class="form-field form-required">
      <th scope="row"><label for="description">Token Description <span class="description">(required)</span></label> <a href="#" class="tm_helpmenu">
            <img src="<?php echo plugin_dir_url( __FILE__ ); ?>icons/help.png" class="tm_iconhelp" />
            <span class="tm_round tm_helpmenutext">The description for what the token does.</span>
          </a></th>
        <td>
          <textarea style="width: 100%;" id="description" name="description" rows="6"><?php echo (isset($_POST['description']) && !empty($_POST['description']))?$_POST['description']:''; ?></textarea>
          <?php echo $tokenadd->errordescription; ?>
        </td>
    </tr>
    <tr class="form-field form-required">
      <th scope="row"><label for="tokenvalue">Token Value <span class="description">(required)</span></label> <a href="#" class="tm_helpmenu">
            <img src="<?php echo plugin_dir_url( __FILE__ ); ?>icons/help.png" class="tm_iconhelp" />
            <span class="tm_round tm_helpmenutext">The HTML, PHP, CSS and JS code to process and inject into the token name.</span>
          </a></th>
        <td>
          <div class="tm_tabholder">
          <span id="tab1" class="tm_tabon" onclick="showtab(1);">HTML</span><span id="tab2" class="tm_taboff" onclick="showtab(2);">PHP</span><span id="tab3" class="tm_taboff" onclick="showtab(3);">CSS</span><span id="tab4" class="tm_taboff" onclick="showtab(4);">JS</span>
          </div>
          <div id="tabdiv1" class="tm_tabdivon">
            <textarea id="htmlvalue" name="htmlvalue" rows="30"><?php echo (isset($_POST['htmlvalue']) && !empty($_POST['htmlvalue']))?$_POST['htmlvalue']:''; ?></textarea>
            <?php echo $tokenadd->errorhtmlvalue; ?>
          </div>
          <div id="tabdiv2" class="tm_tabdivon">
            <textarea id="phpvalue" name="phpvalue" rows="30"><?php echo (isset($_POST['phpvalue']) && !empty($_POST['phpvalue']))?$_POST['phpvalue']:''; ?></textarea>
          </div>
          <div id="tabdiv3" class="tm_tabdivon">
            <textarea id="cssvalue" name="cssvalue" rows="30"><?php echo (isset($_POST['cssvalue']) && !empty($_POST['cssvalue']))?$_POST['cssvalue']:''; ?></textarea>
          </div>
          <div id="tabdiv4" class="tm_tabdivon">
            <textarea id="jsvalue" name="jsvalue" rows="30"><?php echo (isset($_POST['jsvalue']) && !empty($_POST['jsvalue']))?$_POST['jsvalue']:''; ?></textarea>
          </div>

        </td>
    </tr>
    <tr>
      <th scope="row"></th>
        <td><input class="button-primary" type="submit" name="addtoken" value="Add Token" id="submitbutton" /></td>
    </tr>
</tbody></table>
</form>
</div>
<script type="text/javascript">

  var editor1 = CodeMirror.fromTextArea(document.getElementById("htmlvalue"), {
  lineNumbers : true,
  matchBrackets : true,
  mode : "application/x-httpd-php",
  enterMode : "keep",
  tabMode : "shift"
  });

  var editor2 = CodeMirror.fromTextArea(document.getElementById("phpvalue"), {
  lineNumbers : true,
  matchBrackets : true,
  mode : "application/x-httpd-php-open",
  enterMode : "keep",
  tabMode : "shift"
  });

  var editor3 = CodeMirror.fromTextArea(document.getElementById("cssvalue"), {
  lineNumbers : true,
  matchBrackets : true,
  mode : "text/css",
  enterMode : "keep",
  tabMode : "shift"
  });

  var editor4 = CodeMirror.fromTextArea(document.getElementById("jsvalue"), {
  lineNumbers : true,
  matchBrackets : true,
  mode : "text/javascript",
  enterMode : "keep",
  tabMode : "shift"
  });

  $cls('tabdiv2', 'tm_tabdivoff');
  $cls('tabdiv3', 'tm_tabdivoff');
  $cls('tabdiv4', 'tm_tabdivoff');

</script>

