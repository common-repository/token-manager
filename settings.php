<?php

class settings
{
  // Variables
  public $errormessage;
  public $displayerrors; 
  public $displaykeys; 
  public $injecturl;
  public $replacep;
  public $smartquotes;
  
  // Construct the token add
  public function __construct()
  {
    global $_POST, $wpdb;

    $this->init();
  }

  // Initializes the page.
  function init()
  {
    global $_POST, $wpdb, $_SERVER, $current_site, $blog_id;  
    $haserror = false;

    // Create custom vars
    $siteid = (isset($current_site) && isset($current_site->id)) ? $current_site->id : '0';
    $blogid = $blog_id;

    // Process Form
    if(isset($_POST['savesettings']))
    {
      $this->displayerrors = (isset($_POST['displayerrors']) && 
                             !empty($_POST['displayerrors']) && 
                             $_POST['displayerrors']=='1') ? intval($_POST['displayerrors']) : 0; 
      $this->displaykeys = (isset($_POST['displaykeys']) && 
                             !empty($_POST['displaykeys']) && 
                             $_POST['displaykeys']=='1') ? intval($_POST['displaykeys']) : 0;
      $this->injecturl = (isset($_POST['injecturl']) && 
                         !empty($_POST['injecturl']) && 
                         $_POST['injecturl']=='1') ? intval($_POST['injecturl']) : 0; 
      $this->replacep = (isset($_POST['replacep']) && 
                         !empty($_POST['replacep']) && 
                         $_POST['replacep']=='1') ? intval($_POST['replacep']) : 0; 
      $this->smartquotes = (isset($_POST['smartquotes']) && 
                         !empty($_POST['smartquotes']) && 
                         $_POST['smartquotes']=='1') ? intval($_POST['smartquotes']) : 0;
 
      // Update the options
      update_option('tokenmanager_displayerrors', $this->displayerrors);
      update_option('tokenmanager_displaykeys', $this->displaykeys);
      update_option('tokenmanager_injecturl', $this->injecturl);
      update_option('tokenmanager_replacep', $this->replacep);
      update_option('tokenmanager_smartquotes', $this->smartquotes);

      $this->errormessage = '<div id="tm_status" class="tm_noerror">Save Settings Completed</div>' .
                            '<script type="text/javascript">function hide_status(){' .
                            'document.getElementById("tm_status").style.display = "none";' .
                            '} setTimeout("hide_status()",4000);</script>';
    }
    else
    {
      // Display options because no post back
      $this->displayerrors = intval(get_option('tokenmanager_displayerrors', 0));
      $this->displaykeys = intval(get_option('tokenmanager_displaykeys', 0));
      $this->injecturl = intval(get_option('tokenmanager_injecturl', 0));
      $this->replacep = intval(get_option('tokenmanager_replacep', 0));
      $this->smartquotes = intval(get_option('tokenmanager_smartquotes', 0));
    }   
  }
}

// Build class
$settings = new settings();

$_POST = array_map('stripslashes_deep', $_POST);
$_GET = array_map('stripslashes_deep', $_GET);
  
?>

<link rel="stylesheet" type="text/css" href="<?php echo plugin_dir_url( __FILE__ ); ?>tm.css" />

<form method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
  <div class="tm_body">
    <div class="tm_mainfontlarge tm_icon32">Token Manager - Settings</div>
    <p class="tm_mainfont">
      Below are the main settings for controlling the Token Manager Wordpress plugin. 
      If you need more information about what a setting does, mouseover on 
      the help icon next to the title.
    </p>
    <div class="tm_form">
      <?php echo $settings->errormessage; ?>
      <div class="tm_formitem">
        <label class="tm_label" for="injecturl">
          Extra Token Info 
          <a href="#" class="tm_helpmenu">
            <img src="<?php echo plugin_dir_url( __FILE__ ); ?>icons/help.png" class="tm_iconhelp" />
            <span class="tm_round tm_helpmenutext">Adds default request tokens, that contain url and page information.</span>
          </a>
        </label>
        <input type="radio" <?php echo ($settings->injecturl==1)?' checked="checked" ':''; ?> value="1" name="injecturl" />
        <span class="tm_mainfont tm_label1">Yes</span>
        <input type="radio" <?php echo ($settings->injecturl==0)?' checked="checked" ':''; ?> value="0" name="injecturl" />
        <span class="tm_mainfont tm_label2">No</span>
      </div>
      <div class="tm_formitem">
        <label class="tm_label" for="displayerrors">
          Display Token Errors 
          <a href="#" class="tm_helpmenu">
            <img src="<?php echo plugin_dir_url( __FILE__ ); ?>icons/help.png" class="tm_iconhelp" />
            <span class="tm_round tm_helpmenutext">Turns on error handling and displays error messages on webpage.</span>
          </a>
        </label>
        <input type="radio" <?php echo ($settings->displayerrors==1)?' checked="checked" ':''; ?> value="1" name="displayerrors" />
        <span class="tm_mainfont tm_label1">Yes</span>
        <input type="radio" <?php echo ($settings->displayerrors==0)?' checked="checked" ':''; ?> value="0" name="displayerrors" />
        <span class="tm_mainfont tm_label2">No</span>
      </div>
      <div class="tm_formitem">
        <label class="tm_label" for="displaykeys">
          Display Token Key Pairs
          <a href="#" class="tm_helpmenu">
            <img src="<?php echo plugin_dir_url( __FILE__ ); ?>icons/help.png" class="tm_iconhelp" />
            <span class="tm_round tm_helpmenutext">Displays all token key pairs in a page. For testing only!</span>
          </a>
        </label>
        <input type="radio" <?php echo ($settings->displaykeys==1)? ' checked="checked" ' : ''; ?> value="1" name="displaykeys" />
        <span class="tm_mainfont tm_label1">Yes</span>
        <input type="radio" <?php echo ($settings->displaykeys==0)? ' checked="checked" ' : ''; ?> value="0" name="displaykeys" />
        <span class="tm_mainfont tm_label2">No</span>
      </div>
      <div class="tm_formitem">
        <label class="tm_label" for="replacep">
          Remove Auto &lt;p&gt; Tags
          <a href="#" class="tm_helpmenu">
            <img src="<?php echo plugin_dir_url( __FILE__ ); ?>icons/help.png" class="tm_iconhelp" />
            <span class="tm_round tm_helpmenutext">Turns off automatic HTML &lt;p&gt; tags from message posts.</span>
          </a>
        </label>
        <input type="radio" <?php echo ($settings->replacep==1)?' checked="checked" ':''; ?> value="1" name="replacep" />
        <span class="tm_mainfont tm_label1">Yes</span>
        <input type="radio" <?php echo ($settings->replacep==0)?' checked="checked" ':''; ?> value="0" name="replacep" />
        <span class="tm_mainfont tm_label2">No</span>
      </div>
      <div class="tm_formitem">
        <label class="tm_label" for="smartquotes">
          Remove Smart Quotes
          <a href="#" class="tm_helpmenu">
            <img src="<?php echo plugin_dir_url( __FILE__ ); ?>icons/help.png" class="tm_iconhelp" />
            <span class="tm_round tm_helpmenutext">Removes Wordpress automatic smart quotes from message posts.</span>
          </a>
        </label>
        <input type="radio" <?php echo ($settings->smartquotes==1)?' checked="checked" ':''; ?> value="1" name="smartquotes" />
        <span class="tm_mainfont tm_label1">Yes</span>
        <input type="radio" <?php echo ($settings->smartquotes==0)?' checked="checked" ':''; ?> value="0" name="smartquotes" />
        <span class="tm_mainfont tm_label2">No</span>
      </div>
      <div class="tm_formitem">
        <input class="button-primary" type="submit" name="savesettings" value="Save Settings" id="savesettings" />
      </div>
    </div>
  </div>
</form>

