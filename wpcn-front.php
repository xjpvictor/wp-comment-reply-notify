<?php
/*
The subscription input area on the front pages and add subscription record.
*/

if(!class_exists('wp_comment_reply_notify_front')):
class wp_comment_reply_notify_front{
  var $double_opt;

  function wp_comment_reply_notify_front(){
    add_action('comment_post', array(&$this,'wpcn_comment_reply_submit'),9997);
    add_action('comment_form', array(&$this,'wpcn_add_form'), 9999);
  }

  function wpcn_add_form(){
    global $wpcn_main;

    if($wpcn_main->options['wpcn_reply'] == '0'){}
    elseif((isset($_SESSION['wpcn_reply']) && $_SESSION['wpcn_reply'] == '0') || ($wpcn_main->options['wpcn_reply'] == '10' && (!isset($_SESSION['wpcn_reply']) || $_SESSION['wpcn_reply'] == '0')))
      echo '<p><input type="hidden" name="wpcn_reply" value="0" /><label><input type="checkbox" name="wpcn_reply" value="1" /> '.$wpcn_main->options['wpcn_message'].'</label></p>';
    else
      echo '<p><input type="hidden" name="wpcn_reply" value="0" /><label><input type="checkbox" name="wpcn_reply" value="1" checked="checked" /> '.$wpcn_main->options['wpcn_message'].'</label></p>';
  }

  function wpcn_comment_reply_submit($id){
    global $wpcn_main;

    $this->double_opt = $wpcn_main->options['wpcn_double_opt_in'];
    $_SESSION['wpcn_reply'] = $_POST['wpcn_reply'];
    if(isset($_POST['wpcn_reply']) && $_POST['wpcn_reply']) {
      if($this->double_opt == 0) {
        add_comment_meta($id, 'wpcn_reply', rand(10000000, 99999999));
      } else {
        add_comment_meta($id, 'wpcn_reply', '-1');
      }
    } else {
      add_comment_meta($id, 'wpcn_reply', '0');
    }
  }
}
endif;

define('WP_USE_THEMES', false);
include_once(__DIR__.'/../../../wp-load.php');

session_set_cookie_params(0,'/','',is_ssl(),1);
session_name('_wp_comment_reply_notify');
if(session_status() !== PHP_SESSION_ACTIVE)
  session_start();
?>
