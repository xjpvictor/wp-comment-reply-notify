<?php
/*
Send emails.
*/

if(!class_exists('wp_comment_reply_notify_mail')):
class wp_comment_reply_notify_mail{

  var $subject = '';
  var $body = '';
  var $double_opt_in_message = '';

  function wpcn_mailer($to,$type,$extra){
    global $wpdb, $wpcn_main;

    switch($type) {
    case 'reply':
      $this->subject = $wpcn_main->options['wpcn_mail_subject'];
      $this->body = $wpcn_main->options['wpcn_mail_body'];
      $this->double_opt_in_message = $wpcn_main->options['wpcn_mail_double_opt_in_message'];

      $comment_parent = $extra[0];
      $otp = $extra[1];
      $comment = $extra[2];

      if ($extra[1] == '-1' && $wpcn_main->options['wpcn_double_opt_in'])
        $doi = update_comment_meta($comment_parent['comment_ID'], 'wpcn_reply', '-'.($otp = rand(10000000, 99999999)));

      $output = $this->wpcn_str_replace(array($this->subject,$this->body,$this->double_opt_in_message), $comment_parent, $otp, $comment);

      wp_mail($to, html_entity_decode($output[0], ENT_QUOTES), html_entity_decode($output[1].(isset($doi) ? "\n".$output[2] : ''), ENT_QUOTES), array('Content-type: text/html', 'charset='.get_option('blog_charset'), 'From: '.html_entity_decode(get_bloginfo('name'), ENT_QUOTES).' <'.get_option('admin_email').'>'));
      break;

    case 'sub':
      $this->subject = $wpcn_main->options['wpcn_mail_subject_sub'];
      $this->body = $wpcn_main->options['wpcn_mail_body_sub'];

      $comment_parent = $extra[0];

      $output = $this->wpcn_str_replace(array($this->subject,$this->body), $comment_parent, $extra[1]);

      wp_mail($to, html_entity_decode($output[0], ENT_QUOTES), html_entity_decode($output[1], ENT_QUOTES), array('Content-type: text/html', 'charset='.get_option('blog_charset'), 'From: '.html_entity_decode(get_bloginfo('name'), ENT_QUOTES).' <'.get_option('admin_email').'>'));
      break;

    case 'unsub':
      $this->subject = $wpcn_main->options['wpcn_mail_subject_unsub'];
      $this->body = $wpcn_main->options['wpcn_mail_body_unsub'];

      $comment_parent = $extra[0];

      $output = $this->wpcn_str_replace(array($this->subject,$this->body), $comment_parent, $extra[1]);

      wp_mail($to, html_entity_decode($output[0], ENT_QUOTES), html_entity_decode($output[1], ENT_QUOTES), array('Content-type: text/html', 'charset='.get_option('blog_charset'), 'From: '.html_entity_decode(get_bloginfo('name'), ENT_QUOTES).' <'.get_option('admin_email').'>'));
      break;
    }

    return true;
  }

  function wpcn_str_replace($str, $comment_parent, $otp = null, $comment = null) {
    if (!isset($comment_parent) || !isset($str) || !$str)
      return false;

    $post = get_post($comment_parent['comment_post_ID'], ARRAY_A);
    return str_replace(array('##blogname##',
                      '##postname##',
                      '##cc_content##',
                      '##cc_author##',
                      '##cc_link##',
                      '##pc_content##',
                      '##pc_author##',
                      '##pc_link##',
                      '##unsub_url##',
                      '##sub_url##',
                      '##double_opt_in_url##'),
                array(get_bloginfo('name'),
                      $post['post_name'],
                      (isset($comment) ? $comment['comment_content'] : ''),
                      (isset($comment) ? $comment['comment_author'] : ''),
                      (isset($comment) ? get_comment_link((object) $comment) : ''),
                      $comment_parent['comment_content'],
                      $comment_parent['comment_author'],
                      get_comment_link((object) $comment_parent),
                      get_bloginfo('url').'/?wpcn_unsub='.$comment_parent['comment_ID'].'&wpcn_otp='.(isset($otp) ? $otp : ''),
                      get_bloginfo('url').'/?wpcn_sub='.$comment_parent['comment_ID'].'&wpcn_otp='.(isset($otp) ? $otp : ''),
                      get_bloginfo('url').'/?wpcn_doi_confirm='.$comment_parent['comment_ID'].'&wpcn_otp='.(isset($otp) ? $otp : '')),
                $str);
  }
}
endif;
?>
