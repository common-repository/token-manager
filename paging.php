<?php

// Class for paging recordsets
class paging 
{
  // Declare class variables
  private $rangestart;
  private $rangeend;
  private $current;
  private $per;
  private $totalrecords;
  private $totalpages;
  private $url;

  // Declare Properties of the class.
  public function set_rangestart($_value) { $this->rangestart = $_value; } 
  public function get_rangestart() { return $this->rangestart; }
  public function set_rangeend($_value) { $this->rangeend = $_value; } 
  public function get_rangeend() { return $this->rangeend; }
  public function set_current($_value) { $this->current = $_value; } 
  public function get_current() { return $this->current; }
  public function set_per($_value) { $this->per = $_value; } 
  public function get_per() { return $this->per; }
  public function set_totalrecords($_value) { $this->totalrecords = $_value; } 
  public function get_totalrecords() { return $this->totalrecords; }
  public function set_totalpages($_value) { $this->totalpages = $_value; } 
  public function get_totalpages() { return $this->totalpages; }
  public function set_url($_value) { $this->url = $_value; } 
  public function get_url() { return $this->url; }
  
  // Class constructor.
  public function __construct($_current, $_per, $_totalrecords, $_url) 
  {
    $_current = (isset($_current) && !empty($_current)) ? $_current : 1;
    $_per = (isset($_per) && !empty($_per)) ? $_per : 10;

    //$_totalrecords = ($_totalrecords>0) ? $_totalrecords : 10;
    $_url = (isset($_url) && !empty($_url)) ? $_url : '';

    // Set the class variables.
    $_url = (isset($_url) && !empty($_url)) ? $_url : paging::pageurl();
    $this->set_url($_url);
    $this->set_per($_per);
    $this->set_totalrecords($_totalrecords);

    // Get total pages.
    $totalpages = ($_totalrecords < $_per) ? 1 : ceil($_totalrecords / $_per);
    $this->set_totalpages($totalpages);

    // Fix Current page
    $_current = ($_current < 1) ? 1 : $_current;
    $_current = ($_current > $totalpages) ? $totalpages : $_current;
    $this->set_current($_current);

    // Set ranges
    $this->set_rangestart((($_current-1) * $_per)+1);
    $this->set_rangeend((($_current-1) * $_per)+$_per);
  }

  // Gets the current page url.
  public static function pageurl() 
  {
    global $_SERVER;
    return 'http' . 
           ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's' : '') . 
           '://' . 
           $_SERVER['SERVER_NAME'] . 
           (($_SERVER['SERVER_PORT'] != "80") ? ':' . $_SERVER['SERVER_PORT'] : '') . 
           $_SERVER['REQUEST_URI'];
  }

  // Build the url with the proper querystring.
  public function buildurl($_query)
  {
    // Get query as array
    $link = parse_url($this->url);
    $query = $link['query'];
    $pairs2 = array();
    parse_str($query, $pairs2);

    // Break apart query
    $pairs = array();
    parse_str($_query, $pairs);

    foreach($pairs as $key => $val)
    {
      $pairs2[$key] = $val;
    }

    $amp = '';
    $newquery = '';

    foreach($pairs2 as $key => $val)
    {
      switch(strtolower($key))
      {
        case 'pge' : $newquery .= "$amp$key=$val"; break;
        case 'per' : $newquery .= "$amp$key=$val"; break;
        case 'total' : $newquery .= "$amp$key=$val"; break;
        default : $newquery .= "$amp$key=$val"; break;
      }

      $amp = '&';
    }

    $link = explode('?',$this->url);
    $link = $link[0];

    return $link . ((isset($newquery) && !empty($newquery)) ? '?' . $newquery : '');
  }

  // Create and displays totals.
  public function createtotals()
  {
    $totalrows = $this->get_totalrecords();
    $startingrow = 0;
    $offset = 0;
    $endingrow = 0;

    if($totalrows>0)
    {
      $startingrow = ((($this->get_current()-1) * $this->get_per()) + 1);
      $offset = $startingrow + ($this->get_per()-1);
      $endingrow = ($offset > $totalrows)? $totalrows : $offset;
    }
    return "<span class=\"displaying-num\" style=\"margin: 0px 10px 0px 0px;\">Showing $startingrow to $endingrow of $totalrows</span>"; 
  }

  // Creates a search box.
  public function createsearch($_title)
  {
    global $_GET;
    $filt = (isset($_GET['filt']) && !empty($_GET['filt']))?$_GET['filt']:'';
    $page = $this->pageurl();

    if(strpos($page,'?')===false)
    {
      // No Query just give page
      $page = "$page?filt=";
    }
    else
    {
      $items = explode('?',$page);
      $page = $items[0] . '?'; 
      $items = explode('&', $items[1]);
      foreach($items as $item)
      {
        $pair = explode('=', $item);
        $name = $pair[0];
        $value = $pair[1];

        switch(strtolower($pair[0]))
        {
          case 'filt' : break;
          case 'total' : break;
          case 'pge' : break;
          case 'per' : break;
          case 'sort' : break;
          default : $page .= "$name=$value&"; break;
        }
      }
      $page = rtrim($page,'&');
      $page .= "&filt=";
    }

    return "<div style=\"float: left\"><input type=\"text\" value=\"$filt\" name=\"s\" id=\"post-search-input\">
            <input type=\"button\" value=\"$_title\" class=\"button\" id=\"search-submit\" name=\"\" onclick=\"window.location = '$page' + encodeURI(document.getElementById('post-search-input').value);\"></div>";
  }

  // Creates the next button.
  public function createnext()
  {
    $next = ($this->get_current() + 1);
    $per = $this->get_per();
    $total = $this->get_totalrecords();
    $link = $this->buildurl("pge=$next&per=$per&total=$total");

    if($next > $this->totalpages)
    { 
      return "<a style=\"font-weight: normal;\" class=\"disabled\" href=\"javascript: void(0)\">Next »</a>";
    }
    else
    {
      return "<a style=\"font-weight: normal;\" class=\"next\" href=\"$link\">Next »</a>";
    }
  }

  // Creates the previous button.
  public function createprevious()
  {
    $prev = ($this->get_current() - 1);
    $per = $this->get_per();
    $total = $this->get_totalrecords();
    $link = $this->buildurl("pge=$prev&per=$per&total=$total");

    if($prev < 1)
    { 
      return "<a style=\"font-weight: normal;\" class=\"disabled\" href=\"javascript: void(0)\">« Previous</a>";
    }
    else
    {
      return "<a style=\"font-weight: normal;\" class=\"next\" href=\"$link\">« Previous</a>";
    }
  }

  // Creates the paging system.
  public function createpager()
  {
    // Declare Variables
    $html = '';
    $count = 1;

    $start = ($this->get_current() > 5) ? $this->get_current()-4 : 1;
    $end = $start + 9;
 
    $start = ($end > $this->get_totalpages()) ? $this->get_totalpages() - 8 : $start;
    $start = ($this->get_totalpages() < 9) ? 1 : $start;

    // Build a list of indexes.
    for(; $start < $this->get_totalpages()+1; $start++)
    {
      //if($count > 1){ $html .= ' <span>|</span> '; }
      
      if($this->get_current() == $start)
      {
        $html .= "<a style=\"color:#333333; font-weight:normal;\" class=\"current\" href=\"javascript: void(0)\">$start</a>";
      }
      else
      {
        $total = $this->get_totalrecords();
        $per = $this->get_per();

        $link = $this->buildurl("pge=$start&per=$per&total=$total");
        
        $html .= "<a style=\"font-weight: normal;\" href=\"$link\">$start</a>";
      }

      // Break if count is greater than 10
      if($count < 9){ $count++; } else { break; }

    }

    return $html;
  }

  // Render the paging control.
  public function render($_title)
  {
    $html = '';

    $totals = $this->createtotals();
    $next = $this->createnext();
    $previous = $this->createprevious();
    $pager = $this->createpager();
    $search = $this->createsearch($_title);
    $html = "<div class=\"tablenav\">$search<div class=\"tablenav-pages\"><span class=\"pagination-links\">$totals$previous$pager$next</span></div></div>";

    return $html;
  }
}

//$paging = new paging($_GET['page'], $_GET['per'], $_GET['total'], "http://www.google.com");
//echo $paging->createtotals();
//echo $paging->createpager();

?>
