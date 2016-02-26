<?php
/*
Option page.
*/

if(!class_exists('wp_comment_reply_notify_admin')):
class wp_comment_reply_notify_admin{

  function wp_comment_reply_notify_admin(){
    add_action('admin_menu', array(&$this,'wpcn_options_page'));
    register_deactivation_hook( __DIR__.'/wp-comment-reply-notify.php', array(&$this, 'wpcn_deactivate'));
  }

  function wpcn_deactivate(){
    global $wpcn_main, $wpdb;

    if(get_option($wpcn_main->options_key[1]) == 1) {
      $wpdb->query("DELETE FROM $wpdb->commentmeta WHERE meta_key='{$wpcn_main->options_key[0]}'");
    }
    foreach ($wpcn_main->options_key as $option) {
      delete_option($option);
    }
  }

  function wpcn_options_page(){
    global $wpcn_main;

    if (isset($_POST['wpcn_option_update']) && $_POST['wpcn_option_update'] == 'Update') {
      foreach($wpcn_main->options_key as $option) {
        if (isset($_POST[$option]) && stripslashes($_POST[$option]) !== $wpcn_main->options[$option]) {
          update_option($option, $_POST[$option]);
          $wpcn_main->options[$option] = stripslashes($_POST[$option]);
        }
      }
    }

    add_options_page('WP Comment Reply Notify Option', 'WP Comment Reply Notify', 'manage_options', __FILE__, array(&$this, 'options_page'));
  }

  function options_page(){
    global $wpcn_main;

?>
    <div class="wrap">
      <h2>WP Comment Reply Notify</h2>
      <p><strong>A wordpress plugin to provide email notification for comment reply.</strong></p>
    <fieldset name="wp_basic_options"  class="options">
      <form method="post" action="">
        <p>Send email notification to reader when his comment has been replied</p>
        <p>
          <label><input type="radio" name="wpcn_reply" value="0" <?php if ($wpcn_main->options['wpcn_reply'] == '0') { ?> checked="checked"<?php } ?>/> Disable</label><br/>
          <label><input type="radio" name="wpcn_reply" value="10" <?php if ($wpcn_main->options['wpcn_reply'] == '10') { ?> checked="checked"<?php } ?>/> Enable and Default as NOT Subscribe</label><br/>
          <label><input type="radio" name="wpcn_reply" value="11" <?php if ($wpcn_main->options['wpcn_reply'] == '11') { ?> checked="checked"<?php } ?>/> Enable Default as Subscribe</label><br/>
        </p>
        <p>
          <input type="hidden" name="wpcn_double_opt_in" value="0" /><label><input type="checkbox" name="wpcn_double_opt_in" value="1" <?php if ($wpcn_main->options['wpcn_double_opt_in'] == '1') { ?> checked="checked"<?php } ?>/> Require double-opt-in</label>
        </p>
          <br />
        <p>Checkbox message customization<p>
        <textarea rows="1" cols="50" class="large-text" name="wpcn_message"><?php echo htmlentities($wpcn_main->options['wpcn_message']); ?></textarea><br/>
          <br />
        <p>Email template customization<p>
        <p>Email Subject:<br/>
        <textarea rows="1" cols="50" class="large-text" name="wpcn_mail_subject"><?php echo htmlentities($wpcn_main->options['wpcn_mail_subject']); ?></textarea><br/><br/>
        Email Body:<br/>
        <textarea rows="10" cols="50" class="large-text" name="wpcn_mail_body"><?php echo htmlentities($wpcn_main->options['wpcn_mail_body']); ?></textarea><br/><br/>
        <p>Subscription nofity email Subject:<br/>
        <textarea rows="1" cols="50" class="large-text" name="wpcn_mail_subject_sub"><?php echo htmlentities($wpcn_main->options['wpcn_mail_subject_sub']); ?></textarea><br/><br/>
        Subscription nofity email Body:<br/>
        <textarea rows="10" cols="50" class="large-text" name="wpcn_mail_body_sub"><?php echo htmlentities($wpcn_main->options['wpcn_mail_body_sub']); ?></textarea><br/><br/>
        <p>Unsubscription nofity email Subject:<br/>
        <textarea rows="1" cols="50" class="large-text" name="wpcn_mail_subject_unsub"><?php echo htmlentities($wpcn_main->options['wpcn_mail_subject_unsub']); ?></textarea><br/><br/>
        Unsubscription nofity email Body:<br/>
        <textarea rows="10" cols="50" class="large-text" name="wpcn_mail_body_unsub"><?php echo htmlentities($wpcn_main->options['wpcn_mail_body_unsub']); ?></textarea><br/><br/>
        Prompt Message for Double-opt-in:<br/>
        <textarea rows="2" cols="50" class="large-text" name="wpcn_mail_double_opt_in_message"><?php echo htmlentities($wpcn_main->options['wpcn_mail_double_opt_in_message']); ?></textarea>
        </p>
        <p>* Available shortname: ##blogname## - Site title; ##postname## - Blog post title; ##pc_author## - Author of Original comment; ##pc_content## - Original comment; ##pc_link## - Link to Original comment; ##cc_author## - Author of New comment; ##cc_content## - New comment; ##cc_link## - Link to New comment; ##double_opt_in_url## - Link for double-opt-in; ##sub_url## - Subscribe link; ##unsub_url## - Unsubscribe link</p>
          <br />
        <p>
          <input type="hidden" name="wpcn_delete_data" value="0"><label><input type="checkbox" name="wpcn_delete_data" value="1" <?php if ($wpcn_main->options['wpcn_delete_data'] == '1') { ?> checked="checked" <?php } ?>/> Confirm to delete subscription data when deactivate the plugin</label><br/>
        </p>
        <p style="color:red;">Users will not receive any email even when you activate this plugin again. Use with care.<br/>Options will always be deleted from database when you deactivate the plugin.</p>
        <p class="submit">
          <input type="submit" class="button button-primary" name="wpcn_option_update" value="Update" />
        </p>
      </form>
    </fieldset>
    </div>
<?php
  }
}
endif;
?>
