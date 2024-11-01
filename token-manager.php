<?php  
/* 
  Plugin Name: Token Manager
  Plugin URI: http://www.codevendor.com/product/tokenmanager/
  Description: The Token Manager allows web developers to program PHP, HTML, CSS and JavaScript into tokens that can be used throughout Wordpress.
  Author: Codevendor 
  Version: 1.0.4
  Author URI: http://www.codevendor.com 
*/  

// Globals
global $wpdb;
global $PLUGIN_TM_JSON_CALL;
global $PLUGIN_TM_DB_VERSION;
global $PLUGIN_TM_SITEID;
global $PLUGIN_TM_BLOGID;
global $PLUGIN_TM_PAGEID;
global $OPTION_TM_INJECTURL;
global $OPTION_TM_DISPLAYERRORS;
global $OPTION_TM_DISPLAYKEYS;
global $OPTION_TM_DBVERSION;
global $OPTION_TM_REPLACEP;
global $OPTION_TM_SMARTQUOTES;
global $TAB_TM;
global $TAB_TMV;
global $TAB_TMT;
global $TAB_TMTV;
global $TAB_TMP;
global $PLUGIN_ICON1;
global $PLUGIN_ICON2;

$PLUGIN_TM_DB_VERSION = '0.2';
$OPTION_TM_INJECTURL = (get_option('tokenmanager_injecturl', false)==false)?false:true; 
$OPTION_TM_DISPLAYERRORS = (get_option('tokenmanager_displayerrors', false)==false)?false:true;
$OPTION_TM_DISPLAYKEYS = (get_option('tokenmanager_displaykeys', false)==false)?false:true;
$OPTION_TM_DBVERSION = get_option('tokenmanager_db_version');
$OPTION_TM_REPLACEP = (get_option('tokenmanager_replacep', false)==false)?false:true;
$OPTION_TM_SMARTQUOTES = (get_option('tokenmanager_smartquotes', false)==false)?false:true;
$TAB_TM = $wpdb->base_prefix . 'tokenmanager';
$TAB_TMV = $wpdb->base_prefix . 'tokenmanagerversions';
$TAB_TMT = $wpdb->base_prefix . 'tokenmanagertypes';
$TAB_TMTV = $wpdb->base_prefix . 'tokenmanagertypesversions';
$TAB_TMP = $wpdb->base_prefix . 'tokenmanagerpages';
$PLUGIN_ICON1 = plugin_dir_url( __FILE__ ) . 'icons/icon1_32.png';
$PLUGIN_ICON2 = plugin_dir_url( __FILE__ ) . 'icons/icon1_16.png';
$PLUGIN_TM_JSON_CALL = false;

// Class for creating tokens
class tokenmanager
{
  // Variables
  private $buffer;
  private $tokens;

  // Properties
  public function set_buffer($_value) { $this->buffer = $_value; } 
  public function get_buffer() { return $this->buffer; }

  // Construct the tokens class
  public function __construct()
  {
    ob_start(array($this, "obcallback"));
  }

  // Gets all the tokens into results.
  private function get_tokens()
  {
    global $post, $blog_id, $current_site, $OPTION_TM_INJECTURL, $TAB_TM, $TAB_TMP, $PLUGIN_TM_SITEID, $PLUGIN_TM_BLOGID, $PLUGIN_TM_PAGEID, $wpdb, $_SERVER;

    $PLUGIN_TM_SITEID = (isset($current_site) && isset($current_site->id)) ? $current_site->id : '0';
    $PLUGIN_TM_BLOGID = (isset($blog_id) && isset($blog_id)) ? $blog_id : '0';
    $PLUGIN_TM_PAGEID = (isset($post) && isset($post->ID)) ? $post->ID : '0';

    // Check site id and blog main token
    $where = " a.siteid='$PLUGIN_TM_SITEID' AND a.blogid='$PLUGIN_TM_BLOGID' AND";

    // Check site id and blog id
    $where .= " b.siteid='$PLUGIN_TM_SITEID' AND b.blogid='$PLUGIN_TM_BLOGID' AND";

    // Check if file is frontpage
    if(is_front_page())
    {
      $where = "$where b.pageid='0' OR $where b.pageid='-1'";
    }
    else
    {
      $where = "$where b.pageid='-1' OR $where b.pageid='$PLUGIN_TM_PAGEID'"; 
    }

    $this->tokens = $wpdb->get_results("SELECT DISTINCT a.tokenname as tokenname, a.id as id, 
                                        a.htmlvalue as htmlvalue,
                                        a.phpvalue as phpvalue,
                                        a.cssvalue as cssvalue,
                                        a.jsvalue as jsvalue
                                        FROM $TAB_TM a 
                                        LEFT JOIN $TAB_TMP b ON a.id = b.tokenid
                                        WHERE $where ORDER BY processorder DESC;", OBJECT_K);

    if($OPTION_TM_INJECTURL)
    {
      $host = (isset($_SERVER['HTTP_HOST']) && !empty($_SERVER['HTTP_HOST']))? $_SERVER['HTTP_HOST']: '';
      $scheme = (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on')?'https':'http';
      $url = (isset($_SERVER['REQUEST_URI']) && !empty($_SERVER['REQUEST_URI']))? $_SERVER['REQUEST_URI'] : '';
      $uri = explode('?', $url);
      $uricount = count($uri);
      $query = ($uricount == 2 && isset($uri[1]) && !empty($uri[1])) ? $uri[1] : '';
      $siteurl = "$scheme://$host";
      $fullurl = $siteurl . $url;
      $urlpath = ($uricount > 0 && isset($uri[0]) && !empty($uri[0]))? $uri[0] : '';
      $items = explode('/', $urlpath);
      $itemscount = count($items);
      $filecheck = array_pop($items);
      $filenameparts = explode('.', $filecheck);
      $filenamepartscount = count($filenameparts);
      $filename = ($filenamepartscount == 2 && isset($filenameparts[0]) && !empty($filenameparts[0])) ? $filenameparts[0] : ''; 
      $fileext = ($filenamepartscount == 2 && isset($filenameparts[1]) && !empty($filenameparts[1])) ? $filenameparts[1] : '';
      $fullfilename = (isset($filename) && !empty($filename) && isset($fileext) && !empty($fileext))? "$filename.$fileext" : ''; 

      $req = new stdClass;
      $req->htmlvalue = false;
      $this->tokens['REQUEST_HASERRORS'] = $req;

      $req = new stdClass;
      $req->htmlvalue = $host;
      $this->tokens['REQUEST_HOST'] = $req;

      $req = new stdClass;
      $req->htmlvalue = $scheme;
      $this->tokens['REQUEST_SCHEME'] = $req;

      $req = new stdClass;
      $req->htmlvalue = $siteurl;
      $this->tokens['REQUEST_SITEURL'] = $req;

      $req = new stdClass;
      $req->htmlvalue = $fullurl;
      $this->tokens['REQUEST_FULLURL'] = $req;

      $req = new stdClass;
      $req->htmlvalue = $query;
      $this->tokens['REQUEST_QUERY'] = $req;

      $req = new stdClass;
      $req->htmlvalue = $urlpath;
      $this->tokens['REQUEST_URLPATH'] = $req;

      $req = new stdClass;
      $req->htmlvalue = $fullfilename;
      $this->tokens['REQUEST_FULLFILENAME'] = $req;

      $req = new stdClass;
      $req->htmlvalue = $filename;
      $this->tokens['REQUEST_FILENAME'] = $req;

      $req = new stdClass;
      $req->htmlvalue = $fileext;
      $this->tokens['REQUEST_FILEXT'] = $req;

      $req = new stdClass;
      $req->htmlvalue = get_template_directory();
      $this->tokens['REQUEST_TEMPLATESPATH'] = $req;

      $req = new stdClass;
      $req->htmlvalue = get_bloginfo('template_url');
      $this->tokens['REQUEST_TEMPLATESURL'] = $req;

      $req = new stdClass;
      $req->htmlvalue = $PLUGIN_TM_SITEID;
      $this->tokens['REQUEST_SITEID'] = $req;

      $req = new stdClass;
      $req->htmlvalue = $PLUGIN_TM_BLOGID;
      $this->tokens['REQUEST_BLOGID'] = $req;

      $req = new stdClass;
      $req->htmlvalue = $PLUGIN_TM_PAGEID;
      $this->tokens['REQUEST_PAGEID'] = $req;

      $req = new stdClass;
      $req->htmlvalue = is_front_page();
      $this->tokens['REQUEST_ISFRONTPAGE'] = $req;
    }
  }

  // Parses the arguments of a query.
  private function parse_args($args)
  {
    $arg = array(); 
    if(strstr($args, ','))
    {
      $arglist = explode(',', $args);
      $tot = count($arglist);
      for($i = 0; $i<$tot; $i++)
      {
        $arg[] = urldecode(stripslashes(trim($arglist[$i], '\'" ')));
      }
    }
    else
    {
      $arg[] = trim($args, '\'" ');
    }
    return $arg;
  }

  // Handles php processing errors.
  private function php_error_handler($errormessage, $tokenname, $tokentype)
  {
    // Variables
    global $PLUGIN_ICON1;
    $html = '';

    // Set haserror token
    $this->tokens['REQUEST_HASERRORS']->htmlvalue = true;

    return '<div style="color: #373737; font-family: arial; font-size: small; margin: 0px 0px 10px 0px; border: solid 1px gray; padding: 10px; background-color: #F4F4F4; max-width: 600px;">' . 
           '<span style="font-family: arial; font-size: small; color: #373737; display: block; border-bottom: solid 1px white; ' .
           'height: 32px; line-height: 33px;' .
           'background: transparent url(' . $PLUGIN_ICON1 . ') no-repeat; padding: 0px 0px 5px 40px; ' .
           'margin: 0px 0px 5px 0px; font-weight: bold;">' . 
           'Token Manager - Error Processing Token!</span>' . 
           '<strong style="font-family: arial; font-size: small; color: #3F8242;">Token:</strong> ' . $tokenname . 
           '<span style="margin: 0px 10px 0px 10px; ' .
           'color: white;">|</span><strong style="font-family: arial; font-size: small; color: #3F8242;">Code Type:</strong> ' . 
           $tokentype . '<br>' .
           '<strong style="font-family: arial; font-size: small; color: #3F8242;">Message:</strong> ' . htmlentities($errormessage) . '<br>' .
           '</div>';

  }

  // Processes the code.
  private function process_php($token, $args, &$errormes)
  {
    global $OPTION_TM_DISPLAYERRORS, $OPTION_TM_DISPLAYKEYS;

    // Fixup php to be processed in all scripts.
    $phpvalue = (!empty($token->phpvalue)) ? '<?php ' . stripslashes($token->phpvalue) . ' ?>' : '';
    $cssvalue = (!empty($token->cssvalue)) ? "\r\n<style type=\"text/css\">\r\n" . stripslashes($token->cssvalue) . "\r\n</style>\r\n" : '';
    $htmlvalue = (!empty($token->htmlvalue)) ? stripslashes($token->htmlvalue) : '';
    $jsvalue = (!empty($token->jsvalue)) ? "\r\n<script type=\"text/javascript\">\r\n" . stripslashes($token->jsvalue) . "\r\n</script>\r\n" : '';

    // Load up all scripts to be processed at same time in order.
    $scripts = $phpvalue . $cssvalue . $htmlvalue . $jsvalue;

    // Find all php to be processed.
    preg_match_all("/\<\?php(.*)\?\>/msU", $scripts, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) 
    {      
      $GLOBALS["ARGS"] = $args;
      $GLOBALS["currentbuffer"] = '';
            
      $match[1] = str_ireplace('echo', ' $GLOBALS["currentbuffer"] .= ', $match[1]);

      // Replace Echo with new buffer. Variable
      $return = eval($match[1]);

      if($return === false && ( $err = error_get_last() )) 
      { 
        if($OPTION_TM_DISPLAYERRORS)
        {
          // Handle error
          $tokentype = 'Unknown';
          if(strstr($phpvalue, $match[0])){ $tokentype = 'PHP Code'; }
          if(strstr($cssvalue, $match[0])){ $tokentype = 'CSS Code'; }
          if(strstr($htmlvalue, $match[0])){ $tokentype = 'HTML Code'; }
          if(strstr($jsvalue, $match[0])){ $tokentype = 'JS Code'; }
          $scripts = $this->php_error_handler($err['message'], $token->tokenname, $tokentype);
          $errormes = $err['message'];
          break;
        }
        else
        {
          $scripts = '';
        }
      }
      else
      {
        // Replace processed php with match.
        $scripts = str_ireplace($match[0], $GLOBALS["currentbuffer"], $scripts);
      }
    } 

    // Check if all php has been processed.
    if(strstr($scripts, '<?php') || strstr($scripts, '?>'))
    { 
      if($OPTION_TM_DISPLAYERRORS)
      {
        // Process error
        $scripts = $this->php_error_handler('Unprocessed PHP, make sure you have &#60;&#63;php and &#63;&#62; around your inline PHP code.', $token->tokenname, 'PHP, CSS, HTML, JS');
        $errormes = 'Unprocessed PHP, make sure you have <?php and ?> around your inline PHP code.';
      }
      else
      {
        $scripts = ($OPTION_TM_DISPLAYKEYS) ? 'Error' : '';
      }
    }

    return $scripts;
    
  }

  // Processes the tokens and replaces them in the page.
  public function process()
  {
    global $PLUGIN_TM_JSON_CALL, $OPTION_TM_DISPLAYKEYS, $OPTION_TM_DISPLAYERRORS, $PLUGIN_ICON1, $_SERVER, $_POST, $_GET, $wpdb;

    // Has json strip out surround admin console.
    if($PLUGIN_TM_JSON_CALL)
    {
      $buf =  explode('<jsonobject>', $this->get_buffer());
      if(count($buf) > 1)
      {
        $buf =  explode('</jsonobject>', $buf[1]);
        $this->set_buffer($buf[0]);
      }
      return;
    }

    // If admin just return no process needed.
    if(is_admin()){ return; }

    // Whats Been Processed
    $ptokens = array();

    // Get the tokens to process
    $this->get_tokens();

    foreach($this->tokens as $key => $value)
    {   
      // Match all tokens in the buffer. 
      preg_match_all("/\{(" . preg_quote($key, '/') . ".*)\}/msU", $this->buffer, $argmatches, PREG_SET_ORDER);

      // If no token mathches move to next.
      if(count($argmatches)<1){ continue; }

      foreach ($argmatches as $argmatch) 
      {
        // Rest Error Message
        $errormes = '';

        // Check if token has already been processed
        if(array_key_exists($argmatch[0], $ptokens)){ $this->buffer = str_ireplace($argmatch[0], $ptokens[$argmatch[0]], $this->buffer); continue; }

        $args = $this->parse_args($argmatch[1]);

        // If no args, then no match skip to next.  
        if(count($args)<1){ continue; }

        // Get token from all tokens.
        $token = $this->tokens[$args[0]];

        // Check if token has something
        if(!isset($token) || empty($token)){ continue; }

        // Remove the token name from the argu list.
        array_shift($args);

        $output = $this->process_php($token, $args, $errormes);

        // Add to processed token list.
        $ptokens[$argmatch[0]] = (isset($errormes) && !empty($errormes)) ? $errormes : $output;

        // Replace the actual token with the processed code.
        $this->buffer = str_ireplace($argmatch[0], $output, $this->buffer);
      }
    }

    if($OPTION_TM_DISPLAYKEYS)
    {
      if(count($ptokens) < 1){ return; }

      $html = '<div style="color: #373737; font-family: arial; font-size: medium; background-color: white; margin: 10px 0px 20px 0px; border: solid 1px black; padding: 20px;">' . 
              '<span style="color: #373737; font-family: arial; font-weight: bold; background: transparent url(' . $PLUGIN_ICON1 . ') no-repeat; ' .
              'display: block; line-height: 32px; font-size: medium; padding: 0px 0px 0px 40px; ' .
              'margin: 0px 0px 20px 0px">Token Manager - Token Key Pairs</span>';
      foreach($ptokens as $key => $value)
      {
        $code = (empty($value)) ? 'Not Set' : htmlentities($value);
        $code = '<pre style="background: #F4F4F4; border: solid 1px gray; margin: 0px 15px 10px 15px; font-family: verdana; font-size: x-small; color: #373737;">' . $code . '</pre>';
        $html .= "<strong style=\"color: #3f8242; margin: 5px 15px 5px 15px; display: block; font-family: arial; font-size: small;\">" . htmlentities($key) . "</strong>$code";
      }
      $html .= '</div>';

      $this->buffer = str_ireplace('</body>', "$html\n</body>", $this->buffer);
    }  
      
  }

  // Handles the ob_start callback.
  public function obcallback($_buffer)
  { 
    global $tokenmanager;
    $tokenmanager->set_buffer($_buffer);
    $tokenmanager->process();
    return $tokenmanager->get_buffer();
  }

  // Starts the token manager.
  public static function start()
  {
    global $tokenmanager;
    $tokenmanager = new tokenmanager();
  }

  // Ends the token manager.
  public static function stop()
  {
    //echo 'done';
  }

  public static function posts($_data)
  {
    // Protect input data from over riding tokens
    if(current_user_can('manage_options'))
    {
      return $_data;
    }
    else
    {
      $_data['post_content'] = str_ireplace('{', '&#123;', str_ireplace('}', '&#125;',  $_data['post_content']));
    }
  }

  public static function comments($_data)
  {
    // Protect input data from over riding tokens
    $_data['comment_content'] = str_ireplace('{', '&#123;', str_ireplace('}', '&#125;',  $_data['comment_content']));
    return $_data;
  }

  // Creates the tables.
  public static function create()
  {
    // Variables
    global $OPTION_TM_DBVERSION, $PLUGIN_TM_DB_VERSION, $wpdb, $TAB_TM, $TAB_TMV, $TAB_TMT, $TAB_TMTV, $TAB_TMP;

    // Needed to use dbdelta.
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    if($wpdb->get_var("SHOW TABLES LIKE '$TAB_TM'") != $TAB_TM || $OPTION_TM_DBVERSION != $PLUGIN_TM_DB_VERSION)
    {
      $sql1 = "CREATE TABLE $TAB_TM (
	  id int(10) unsigned NOT NULL AUTO_INCREMENT,
	  datecreated int(10) unsigned DEFAULT '0' NOT NULL,
          lastupdated int(10) unsigned DEFAULT '0' NOT NULL,
          siteid int(10) unsigned DEFAULT '0' NOT NULL,
          blogid int(10) unsigned DEFAULT '0' NOT NULL,
	  tokenname VARCHAR(38) NOT NULL,
          htmlvalue text NOT NULL,
          phpvalue text NOT NULL,
          cssvalue text NOT NULL,
          jsvalue text NOT NULL,
          description VARCHAR(250) NOT NULL,
	  authorid int(10) unsigned DEFAULT '0' NOT NULL,
          typeid int(10) unsigned DEFAULT '0' NOT NULL,
          frontpage TINYINT(1) DEFAULT '0' NOT NULL,
          allpages TINYINT(1) DEFAULT '0' NOT NULL,
          active TINYINT(1) DEFAULT '0' NOT NULL,
          version int(10) unsigned DEFAULT '0' NOT NULL,
          processorder int(0) unsigned DEFAULT '0' NOT NULL,
          history MEDIUMTEXT NOT NULL,
	  UNIQUE KEY id (id)
      );";

      dbDelta($sql1);

      $sql2 = "CREATE TABLE $TAB_TMV (
	  id int(10) unsigned NOT NULL AUTO_INCREMENT,
          tokenid int(10) unsigned DEFAULT '0' NOT NULL,
	  datecreated int(10) unsigned DEFAULT '0' NOT NULL,
          lastupdated int(10) unsigned DEFAULT '0' NOT NULL,
          siteid int(10) unsigned DEFAULT '0' NOT NULL,
          blogid int(10) unsigned DEFAULT '0' NOT NULL,
	  tokenname VARCHAR(38) NOT NULL,
          htmlvalue text NOT NULL,
          phpvalue text NOT NULL,
          cssvalue text NOT NULL,
          jsvalue text NOT NULL,
          description VARCHAR(250) NOT NULL,
	  authorid int(10) unsigned DEFAULT '0' NOT NULL,
          typeid int(10) unsigned DEFAULT '0' NOT NULL,
          active TINYINT(1) DEFAULT '0' NOT NULL,
          version int(10) unsigned DEFAULT '0' NOT NULL,
	  UNIQUE KEY id (id)
      );";

      dbDelta($sql2);

      $sql3 = "CREATE TABLE $TAB_TMT (
	  id int(10) unsigned NOT NULL AUTO_INCREMENT,
          datecreated int(10) unsigned DEFAULT '0' NOT NULL,
          lastupdated int(10) unsigned DEFAULT '0' NOT NULL,
	  tokentype VARCHAR(38) NOT NULL,
          tokendescription VARCHAR(250) NOT NULL,
          siteid int(10) unsigned DEFAULT '0' NOT NULL,
          blogid int(10) unsigned DEFAULT '0' NOT NULL,
          authorid int(10) unsigned DEFAULT '0' NOT NULL,
	  orderof int(10) unsigned DEFAULT '0' NOT NULL,
          active tinyint(1) DEFAULT '0' NOT NULL,
          version int(10) unsigned DEFAULT '0' NOT NULL,
          history MEDIUMTEXT NOT NULL,
	  UNIQUE KEY id (id)
      );";

      dbDelta($sql3);

      $sql4 = "CREATE TABLE $TAB_TMTV (
	  id int(10) unsigned NOT NULL AUTO_INCREMENT,
          tokentypeid int(10) unsigned DEFAULT '0' NOT NULL,
          datecreated int(10) unsigned DEFAULT '0' NOT NULL,
          lastupdated int(10) unsigned DEFAULT '0' NOT NULL,
	  tokentype VARCHAR(38) NOT NULL,
          tokendescription VARCHAR(250) NOT NULL,
          siteid int(10) unsigned DEFAULT '0' NOT NULL,
          blogid int(10) unsigned DEFAULT '0' NOT NULL,
          authorid int(10) unsigned DEFAULT '0' NOT NULL,
	  orderof int(10) unsigned DEFAULT '0' NOT NULL,
          active tinyint(1) DEFAULT '0' NOT NULL,
          version int(10) unsigned DEFAULT '0' NOT NULL,
	  UNIQUE KEY id (id)
      );";

      dbDelta($sql4);

      $sql5 = "CREATE TABLE $TAB_TMP (
	  id int(10) unsigned NOT NULL AUTO_INCREMENT,
          datecreated int(10) unsigned DEFAULT '0' NOT NULL,
          lastupdated int(10) unsigned DEFAULT '0' NOT NULL,
          siteid int(10) unsigned DEFAULT '0' NOT NULL,
          blogid int(10) unsigned DEFAULT '0' NOT NULL,
	  pageid int(10) signed DEFAULT '0' NOT NULL,
          tokenid int(10) unsigned DEFAULT '0' NOT NULL,
	  UNIQUE KEY id (id)
      );";
      
      dbDelta($sql5);

      update_option('tokenmanager_db_version', $PLUGIN_TM_DB_VERSION);
    }

    add_option('tokenmanager_db_version', $PLUGIN_TM_DB_VERSION);
  }

  // Creates the wordpress menus. Add null to parent slug if you dont want it to be attached.
  public static function adminmenus()
  {
    global $PLUGIN_ICON2;
    add_menu_page('Token Manager', 'Tokens', 'activate_plugins', 'tokenmanager', 'tokenmanager::tokens', $PLUGIN_ICON2);
    add_submenu_page('tokenmanager', 'Token Manager - Add New Token', 'Add New Token', 'activate_plugins', 'tokenmanageradd', 'tokenmanager::add');
    add_submenu_page('tokenmanager', 'Token Manager - Token Types', 'Token Types', 'activate_plugins', 'tokenmanagertypes', 'tokenmanager::types');
    add_submenu_page('tokenmanager', 'Token Manager - Token Types - Add New Type', 'Add New Type', 'activate_plugins', 'tokenmanageraddtype', 'tokenmanager::typeadd');
    add_submenu_page('tokenmanager', 'Token Manager - Professional', 'Backup System', 'activate_plugins', 'tokenmanagerpro', 'tokenmanager::pro');
    add_submenu_page('tokenmanager', 'Token Manager - Settings', 'Settings', 'activate_plugins', 'tokenmanagersettings', 'tokenmanager::settings');
    add_submenu_page('tokenmanager', 'Token Manager - Info', 'Information', 'activate_plugins', 'tokenmanagerinfo', 'tokenmanager::info');
    add_submenu_page(null, 'Token Manager - JSON', '', 'activate_plugins', 'tokenmanagerjson', 'tokenmanager::json');
    add_submenu_page(null, 'Token Manager - Edit Token', '', 'activate_plugins', 'tokenmanageredit', 'tokenmanager::edit');
    add_submenu_page(null, 'Token Manager - Edit Token Type', '', 'activate_plugins', 'tokenmanagertypeedit', 'tokenmanager::typeedit');
  }

  public static function tokens(){ tokenmanager::create(); include('tokens.php'); }
  public static function add(){ tokenmanager::create(); include('tokenadd.php'); }
  public static function edit(){ tokenmanager::create(); include('tokenedit.php'); }
  public static function types(){ tokenmanager::create(); include('types.php'); }
  public static function typeadd(){ tokenmanager::create(); include('typeadd.php'); }
  public static function typeedit(){ tokenmanager::create(); include('typeedit.php'); }
  public static function settings(){ tokenmanager::create(); include('settings.php'); }
  public static function pro(){ tokenmanager::create(); include('pro.php'); }
  public static function info(){ tokenmanager::create(); include('info.php'); }
  public static function json(){ global $PLUGIN_TM_JSON_CALL; $PLUGIN_TM_JSON_CALL=true; include('json.php'); }

}

/* Add the wordpress actions. */
register_activation_hook(__FILE__,'tokenmanager::create');
add_action('plugins_loaded', 'tokenmanager::start');
add_action('shutdown', 'tokenmanager::stop');
add_action('admin_menu', 'tokenmanager::adminmenus');
add_action('wp_insert_post_data', 'tokenmanager::posts', 1, 1);
add_action('preprocess_comment', 'tokenmanager::comments', 1, 1);

if($OPTION_TM_REPLACEP)
{
  /* Remove p tags */
  remove_filter('the_content', 'wpautop');
}

if($OPTION_TM_SMARTQUOTES)
{
  // Turn off smart quotes. 
  remove_filter('the_content', 'wptexturize');
  remove_filter('comment_text', 'wptexturize');
  remove_filter('the_excerpt', 'wptexturize');
}

?>
