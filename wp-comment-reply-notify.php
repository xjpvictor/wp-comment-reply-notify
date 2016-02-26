<?php
/*
Plugin Name: WP Comment Reply Notify
Plugin URI: https://github.com/xjpvictor/wp-comment-reply-notify
Version: 0.0.1
Author: xjpvictor Huang
Description: A wordpress plugin to provide email notification for comment reply.
*/

if(!class_exists('wp_comment_reply_notify_main')):
class wp_comment_reply_notify_main{

  var $options = array();
  var $options_key = array('wpcn_reply', 'wpcn_delete_data', 'wpcn_double_opt_in', 'wpcn_mail_subject', 'wpcn_mail_body', 'wpcn_mail_double_opt_in_message', 'wpcn_message', 'wpcn_mail_subject_unsub', 'wpcn_mail_body_unsub', 'wpcn_mail_subject_sub', 'wpcn_mail_body_sub');

  function wp_comment_reply_notify_main(){
    $this->wpcn_init();
    $this->wpcn_init_hook();
  }

  function wpcn_init(){
    global $wpdb;

    $options_default = array('0', '0', '1',
      '[##blogname##] New reply to "##postname##"',
      "<p>Hi ##pc_author##,</p>\n<p>New reply on your comment for post \"##postname##\"</p>\n<p>Original comment:<br />\n##pc_content##</p>\n<p>Reply from ##cc_author##:<br />\n##cc_content##</p>\n<p>You can see all reply to your comment here:<br />\n<a href=\"##pc_link##\">##pc_link##</a></p>\n<p>To unsubscribe, use the following url: <a href=\"##unsub_url##\" target=\"_blank\">##unsub_url##</a></p>",
      "<p>You need to confirm your email address before you can receive any further notification.<br/>Click <a href=\"##double_opt_in_url##\">##double_opt_in_url##</a> to confirm.</p>",
      'Email me with replies to my comment',
      '[##blogname##] Unsubscription Successful',
      "<p>Hi ##pc_author##,</p>\n<p>You have successfully unsubscribed from \"##postname##\" on ##blogname##</p>\n<p>To subscribe again, use the following url: <a href=\"##sub_url##\" target=\"_blank\">##sub_url##</a></p>",
      '[##blogname##] Subscription Successful',
      "<p>Hi ##pc_author##,</p>\n<p>You have successfully subscribed from \"##postname##\" on ##blogname##</p>\n<p>To unsubscribe, use the following url: <a href=\"##unsub_url##\" target=\"_blank\">##unsub_url##</a></p>");

    foreach($this->options_key as $key => $option_key) {
      $this->options[$option_key] = get_option($option_key);
      if (!$this->options[$option_key]) {
        update_option($option_key, $options_default[$key]);
        $this->options[$option_key] = $options_default[$key];
      } elseif (is_string($this->options[$option_key]))
        $this->options[$option_key] = stripslashes($this->options[$option_key]);
    }
  }

  function wpcn_init_hook(){
    add_action('wp_set_comment_status', array(&$this,'wpcn_email_reply'),9999,2);
    add_action('comment_post', array(&$this,'wpcn_email_reply'),9999,2);
    add_filter('query_vars', array(&$this,'wpcn_query_vars'), 10, 1);
    add_action('init', array(&$this,'wpcn_get'),1);
    if (!is_admin()) {
      include(__DIR__.'/wpcn-front.php');
      $wpcn_front = new wp_comment_reply_notify_front;
    } else {
      include(__DIR__.'/wpcn-admin.php');
      $wpcn_admin = new wp_comment_reply_notify_admin;
    }
  }

  function wpcn_email_reply($comment_id, $approved) {
    if ($approved !== 'approve' && $approved !== 1) {}
    elseif (null !== ($comment_parent_id = ($comment = get_comment($comment_id, ARRAY_A))['comment_parent']) && $comment_parent_id && !$comment['comment_type']) {
      if (is_numeric($send_mail = get_comment_meta($comment_parent_id, 'wpcn_reply', true)) && ($send_mail >= 10000000 || $send_mail == '-1')) {
        $comment_parent = get_comment($comment_parent_id, ARRAY_A);
        include(__DIR__.'/wpcn-mail.php');
        $wpcn_mail = new wp_comment_reply_notify_mail;
        $wpcn_mail->wpcn_mailer($comment_parent['comment_author_email'], 'reply', array($comment_parent, $send_mail, $comment));
      }
    }
  }

  function wpcn_query_vars($vars) {
    $vars[] = 'wpcn_doi_confirm';
    $vars[] = 'wpcn_otp';
    $vars[] = 'wpcn_unsub';
    $vars[] = 'wpcn_sub';

    return $vars;
  }

  function wpcn_get() {
    if (isset($_GET['wpcn_otp']) && ($otp = $_GET['wpcn_otp'])) {
      if (isset($_GET['wpcn_doi_confirm']) && ($comment_id = $_GET['wpcn_doi_confirm'])) {
        if ('-'.$otp == get_comment_meta($comment_id, 'wpcn_reply', true) && update_comment_meta($comment_id, 'wpcn_reply', $_GET['wpcn_otp'])) {
          include(__DIR__.'/wpcn-mail.php');
          $wpcn_mail = new wp_comment_reply_notify_mail;
          $comment_parent = get_comment($comment_id, ARRAY_A);
          $wpcn_mail->wpcn_mailer($comment_parent['comment_author_email'], 'sub', array($comment_parent, $otp));
          header('Location: '.get_comment_link((object) $comment_parent));
          exit;
        }
      } elseif (isset($_GET['wpcn_unsub']) && ($comment_id = $_GET['wpcn_unsub'])) {
        if ($otp == get_comment_meta($comment_id, 'wpcn_reply', true) && update_comment_meta($comment_id, 'wpcn_reply', '-'.$_GET['wpcn_otp'])) {
          include(__DIR__.'/wpcn-mail.php');
          $wpcn_mail = new wp_comment_reply_notify_mail;
          $comment_parent = get_comment($comment_id, ARRAY_A);
          $wpcn_mail->wpcn_mailer($comment_parent['comment_author_email'], 'unsub', array($comment_parent, $otp));
          header('Location: '.get_comment_link((object) $comment_parent));
          exit;
        }
      } elseif (isset($_GET['wpcn_sub']) && ($comment_id = $_GET['wpcn_sub'])) {
        if ('-'.$otp == get_comment_meta($comment_id, 'wpcn_reply', true) && update_comment_meta($comment_id, 'wpcn_reply', $_GET['wpcn_otp'])) {
          include(__DIR__.'/wpcn-mail.php');
          $wpcn_mail = new wp_comment_reply_notify_mail;
          $comment_parent = get_comment($comment_id, ARRAY_A);
          $wpcn_mail->wpcn_mailer($comment_parent['comment_author_email'], 'sub', array($comment_parent, $otp));
          header('Location: '.get_comment_link((object) $comment_parent));
          exit;
        }
      }
    }
  }
}
endif;

$wpcn_main = new wp_comment_reply_notify_main
?>
