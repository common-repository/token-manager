<?php

class tokenedit
{
  // Variables
  public $siteid;
  public $blogid;
  public $version;
  public $error;
  public $errortokenname;
  public $errorhtmlvalue;
  public $errordescription;
  public $icon;
  public $table_types;
  public $table_tokens;
  public $table_tokenversions;

  // Construct the token add
  public function __construct()
  {
    global $_POST, $wpdb, $current_site, $blog_id;

    $this->icon = plugin_dir_url( __FILE__ ) . 'icons/icon1_32.png';
    $this->table_types = $wpdb->base_prefix . 'tokenmanagertypes'; 
    $this->table_tokens = $wpdb->base_prefix . 'tokenmanager'; 
    $this->table_tokenversions = $wpdb->base_prefix . 'tokenmanagerversions';
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
    if(isset($_POST['edittoken']))
    {
      // Check required.
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
                                WHERE tokenname = '$tokenname' AND id!='$tid' AND siteid='$this->siteid' AND blogid='$this->blogid' 
                                AND active=1 LIMIT 1");

        if($toke>0){ $haserror = true; $this->errortokenname = '<span style="color: Red; display: block;">Token Name already exists!</span>'; }

        if(!$haserror)
        {
          // Sets the history for the insert
          $remoteip = $_SERVER['REMOTE_ADDR'];
          $occurred = date("D, M j, Y G:i:s T");
          $history = $wpdb->escape("<event remoteip=\"$remoteip\" dateoccurred=\"$occurred\" author=\"$authorid\" type=\"update\" status=\"Token was updated in the token manager.\"  />
    <!-- NEXT -->");

          // Setup version
          if($wpdb->query("INSERT INTO $this->table_tokenversions (id, tokenid, datecreated, lastupdated, siteid, blogid, tokenname, 
                        htmlvalue, phpvalue, cssvalue, jsvalue, description, authorid, typeid, active, version) 
                        SELECT null, id, datecreated, lastupdated, siteid, blogid, tokenname, htmlvalue, phpvalue, cssvalue, 
                        jsvalue, description, authorid, typeid, active, version FROM $this->table_tokens 
                        WHERE id='$tid' AND siteid='$this->siteid' AND blogid='$this->blogid' LIMIT 1;")==false)
	  {
	    echo $wpdb->last_error();
	  }

          // Insert into database
          if($wpdb->query("UPDATE $this->table_tokens SET lastupdated = UNIX_TIMESTAMP(), tokenname = '$tokenname', 
                        htmlvalue = '$htmlvalue', phpvalue = '$phpvalue', cssvalue = '$cssvalue', jsvalue = '$jsvalue',
                        description = '$description', typeid = '$tokentype',
                        history = (SELECT REPLACE(history, '<!-- NEXT -->', '$history')),
                        version = version + 1
                        WHERE id='$tid' AND siteid='$this->siteid' AND blogid='$this->blogid';")==false)
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
        $this->error = '<div id="tm_status" class="tm_noerror">Edited Token Successfully!</div>' .
                            '<script type="text/javascript">function hide_status(){' .
                            'document.getElementById("tm_status").style.display = "none";' .
                            '} setTimeout("hide_status()",4000);</script>';
      }
    }
    else
    {
      $items = $wpdb->get_results("SELECT tokenname, htmlvalue, phpvalue, cssvalue, jsvalue, typeid, description, version       
                                   FROM $this->table_tokens WHERE id='$tid' AND siteid='$this->siteid' AND blogid='$this->blogid' LIMIT 1;");

      foreach ($items as $item) 
      {
        // Get form fields
        $_POST['tokenname'] = $item->tokenname;
        $_POST['htmlvalue'] = $item->htmlvalue;
        $_POST['phpvalue'] = $item->phpvalue;
        $_POST['cssvalue'] = $item->cssvalue;
        $_POST['jsvalue'] = $item->jsvalue;
        $_POST['tokentype'] = $item->typeid;
        $_POST['description'] = $item->description;
      }
    }

    // Add Version to page
    $this->version = $wpdb->get_var("SELECT version FROM $this->table_tokens WHERE id='$tid' LIMIT 1;");
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
      $results .= '<option value="' . $droplistitem->id . '"' . (($_selected==$droplistitem->id)? ' selected="selected"': '')  . '>' . $droplistitem->tokentype . '</option>';
    }

    $results .= '</select>';

    return $results;
  }
}

// Build class
$tokenedit = new tokenedit();

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
  <div class="icon32 icon32-posts-post" id="icon-edit" style="background: transparent url(<?php echo $tokenedit->icon; ?>) 0px 0px no-repeat"><br></div>
  <h2>Token Manager - Edit Token (ID: <?php echo intval($_GET['tid']); ?>, VER: <?php echo $tokenedit->version; ?>) <a class="add-new-h2" href="admin.php?page=tokenmanager">View All Tokens</a></h2>
  <p>Edits the token in the token manager.</p>
  <form method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>">

<?php echo $tokenedit->error; ?>
<table class="form-table" style="table-layout:fixed;">
<tbody>
  <tr class="form-field form-required">
    <th scope="row"><label for="tokenname">Token Name <span class="description">(required)</span></label> <a href="#" class="tm_helpmenu">
            <img src="<?php echo plugin_dir_url( __FILE__ ); ?>icons/help.png" class="tm_iconhelp" />
            <span class="tm_round tm_helpmenutext">The unique token name to create.</span>
          </a></th>
      <td>
        <input type="text" style="width: 100%;" aria-required="true" id="tokenname" name="tokenname" value="<?php echo (isset($_POST['tokenname']) && !empty($_POST['tokenname']))?$_POST['tokenname']:''; ?>">
        <?php echo $tokenedit->errortokenname; ?>
      </td>
  </tr>
  <tr class="form-field form-required">
    <th scope="row"><label for="tokentype">Token Type <span class="description">(required)</span></label> <a href="#" class="tm_helpmenu">
            <img src="<?php echo plugin_dir_url( __FILE__ ); ?>icons/help.png" class="tm_iconhelp" />
            <span class="tm_round tm_helpmenutext">The assigned token type for the token.</span>
          </a></th>
      <td><?php echo $tokenedit->build_dropdownlist((isset($_POST['tokentype']) && !empty($_POST['tokentype']))?$_POST['tokentype']:''); ?> ( <a href="admin.php?page=tokenmanageraddtype">Add New Type</a> )</td>
    </tr>
    <tr class="form-field form-required">
      <th scope="row"><label for="description">Token Description <span class="description">(required)</span></label> <a href="#" class="tm_helpmenu">
            <img src="<?php echo plugin_dir_url( __FILE__ ); ?>icons/help.png" class="tm_iconhelp" />
            <span class="tm_round tm_helpmenutext">The description for what the token does.</span>
          </a></th>
        <td>
          <textarea style="width: 100%;" id="description" name="description" rows="6"><?php echo (isset($_POST['description']) && !empty($_POST['description']))?$_POST['description']:''; ?></textarea>
          <?php echo $tokenedit->errordescription; ?>
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
            <textarea id="htmlvalue" name="htmlvalue" rows="30"><?php echo (isset($_POST['htmlvalue']) && !empty($_POST['htmlvalue']))? htmlentities($_POST['htmlvalue']):''; ?></textarea>
            <?php echo $tokenedit->errorhtmlvalue; ?>
          </div>
          <div id="tabdiv2" class="tm_tabdivon">
            <textarea id="phpvalue" name="phpvalue" rows="30"><?php echo (isset($_POST['phpvalue']) && !empty($_POST['phpvalue']))? htmlentities($_POST['phpvalue']):''; ?></textarea>
          </div>
          <div id="tabdiv3" class="tm_tabdivon">
            <textarea id="cssvalue" name="cssvalue" rows="30"><?php echo (isset($_POST['cssvalue']) && !empty($_POST['cssvalue']))? htmlentities($_POST['cssvalue']):''; ?></textarea>
          </div>
          <div id="tabdiv4" class="tm_tabdivon">
            <textarea id="jsvalue" name="jsvalue" rows="30"><?php echo (isset($_POST['jsvalue']) && !empty($_POST['jsvalue']))? htmlentities($_POST['jsvalue']):''; ?></textarea>
          </div>
        </td>
    </tr>
    <tr>
      <th scope="row"></th>
        <td><input class="button-primary" type="submit" name="edittoken" value="Edit Token" id="submitbutton" /></td>
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
