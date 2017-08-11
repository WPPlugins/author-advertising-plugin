<?php
/*
Plugin Name: Author Advertising
Plugin URI: http://www.harleyquine.com/code/author-advertising-plugin/
Description: Allows authors to specify an advertising ID and share in your blogs ad revenue.
Version: 2.9.2
Author: Harley
Author URI: http://www.harleyquine.com
*/

/*  Copyright 2010  Harley Quine  (email : support@harleyquine.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

function kd_aa_widget($args, $number=1){
extract($args);
$options = get_option('kd_aa_widget');
$title = $options[$number]['title'];
$google_values = get_option('kd_author_advertising');
if($google_values[19] == "YES"){ $widget_content = kd_get_ad_ready($google_values[20]); }
   echo $before_widget . $before_title . $title . $after_title . $widget_content . $after_widget;
}

function kd_aa_widget_control($number) {
   $options = $newoptions = get_option('kd_aa_widget');
   if ( !is_array($options) )
      $options = $newoptions = array();
   if ( $_POST["kd_aa_widget-$number"] ) {
      $newoptions[$number]['title'] = strip_tags(stripslashes($_POST["kd_aa_widget-$number"]));
      }
   if ( $options != $newoptions ) {
      $options = $newoptions;
      update_option('kd_aa_widget', $options);
   }
   $title = attribute_escape($options[$number]['title']);
?>
<p><label for="advertising_widget-title"><?php _e('Title:'); ?> <input style="width: 250px;" id="kd_aa_widget-<?php echo $number; ?>" name="kd_aa_widget-<?php echo $number; ?>" type="text" value="<?php echo $title; ?>" /></label></p>
<input type="hidden" id="kd_aa_widget-submit-<?php echo "$number"; ?>" name="kd_aa_widget-submit-<?php echo "$number"; ?>" value="1" />
<?php
}

function kd_aa_widget_setup() {
if (function_exists('register_sidebar_widget')){
   $options = $newoptions = get_option('kd_aa_widget');
   if ( isset($_POST['kd_aa_widget-submit']) ) {
      $number = (int) $_POST['kd_aa_widget-number'];
      if ( $number > 3 ) $number = 3;
      if ( $number < 1 ) $number = 1;
      $newoptions['number'] = $number;
   }
   if ( $options != $newoptions ) {
      $options = $newoptions;
      update_option('kd_aa_widget', $options);
      kd_aa_widget_register($options['number']);
   }
kd_aa_widget_register();
}
}

function kd_aa_widget_page() {
   $options = $newoptions = get_option('kd_aa_widget');
?>
   <div class="wrap">
      <form method="POST">
         <h2><?php _e('Author Advertising Widgets'); ?></h2>
         <p style="line-height: 30px;"><?php _e('How many Author Advertising widgets would you like?'); ?>
         <select id="kd_aa_widget-number" name="kd_aa_widget-number" value="<?php echo $options['number']; ?>">
<?php for ( $i = 1; $i < 4; ++$i ) echo "<option value='$i' ".($options['number']==$i ? "selected='selected'" : '').">$i</option>"; ?>
         </select>
         <span class="submit"><input type="submit" name="kd_aa_widget-submit" id="kd_aa_widget-submit" value="<?php echo attribute_escape(__('Save')); ?>" /></span></p>
      </form>
   </div>
<?php
}

function kd_aa_widget_register() {
   $options = get_option('kd_aa_widget');
   $number = $options['number'];
   if ( $number < 1 ) $number = 1;
   if ( $number > 3 ) $number = 3;
   $class = array('classname' => 'kd_aa_widget');
   for ($i = 1; $i <= 3; $i++) {
      $name = sprintf(__('Author Advertising %d'), $i);
      $id = "advertising-$i"; // Never never never translate an id
      wp_register_sidebar_widget($id, $name, $i <= $number ? 'kd_aa_widget' : /* unregister */ '', $class, $i);
      wp_register_widget_control($id, $name, $i <= $number ? 'kd_aa_widget_control' : /* unregister */ '', $dims, $i);
   }
   add_action('sidebar_admin_setup', 'kd_aa_widget_setup');
   add_action('sidebar_admin_page', 'kd_aa_widget_page');
}

function kd_install(){
   global $table_prefix, $wpdb, $user_level;

   $table_name = $table_prefix . "author_advertising";

   if($wpdb->get_var("show tables like '$table_name'") != $table_name){

   $sql = "CREATE TABLE ".$table_name." (
   id mediumint(9) NOT NULL auto_increment,
   author_id int(11) NOT NULL default '0',
   author_advertising text NOT NULL,
   author_custom1 text,
   author_custom2 text,
   PRIMARY KEY  (`id`)
   );";

   require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
   dbDelta($sql);
   }
   $google_values[0] = "ADMIN ID";
   $google_values[1] = "50";
   $google_values[2] = "50";
   $google_values[3] = "edit_posts";
   $google_values[4] = "0";
   $google_values[5] = "My Advertising";
   $google_values[6] = '<p><b>Warning:</b> Repeatedly clicking on your own ads will lead to a suspension of your advertising account by the friendly people at Google. For more information about Google\'s terms, take a look <a href="https://www.google.com/support/advertising/bin/answer.py?answer=23921&topic=8426" target="_blank">at what you should do to prevent suspension</a>.</p>
      <p>Insert your Google Advertising publishers ID below. If you don\'t have one you can get one <a href="http://www.google.com/advertising/" target="_blank">here</a>.</p>';
   $google_values[7] = "0";
   $google_values[8] = "Google Ad Slot";
   $google_values[9] = "my-slot-id";
   $google_values[10] = "0";
   $google_values[11] = "Author URL";
   $google_values[12] = "http://www.example.com";
   $google_values[13] = "NO";
   $google_values[14] = 'Example: %customfield2% <script type="text/javascript"><!--
google_ad_client = "%pubid%";
//Demo Ad
google_ad_slot = "%customfield1%";
google_ad_width = 468;
google_ad_height = 60;
//--></script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>';
   $google_values[15] = "NO";
   $google_values[16] = "";
   $google_values[17] = "NO";
   $google_values[18] = "";
   $google_values[19] = "NO";
   $google_values[20] = "";

   update_option("kd_author_advertising", $google_values);

}

function kd_authoredit(){
   global $table_prefix, $wpdb, $user_ID;
   $wpdb->show_errors();
   $google_values = get_option('kd_author_advertising');
   $table_name = $table_prefix . "author_advertising";

   if(isset($_POST['update_kd_googleauthor'])) {
      $user_advertising = $wpdb->escape($_POST['user_google']);
      $user_custom1 = $wpdb->escape($_POST['custom1']);
      $user_custom2 = $wpdb->escape($_POST['custom2']);
      $google_id = $wpdb->get_var("SELECT author_advertising FROM $table_name WHERE author_id=$user_ID");
              if(!$google_id) {
                  $user_exists = $wpdb->get_var("SELECT author_id FROM $table_name WHERE author_id=$user_ID");
                  if(!$user_exists){ $wpdb->query("INSERT INTO $table_name (author_id, author_advertising, author_custom1, author_custom2) VALUES ('$user_ID', '$user_advertising', '$user_custom1', '$user_custom2')"); }}

         $wpdb->query("UPDATE $table_name SET author_advertising='$user_advertising', author_custom1='$user_custom1', author_custom2='$user_custom2' WHERE author_id='$user_ID'");
   }
   $user_details = $wpdb->get_row("SELECT * FROM $table_name WHERE author_id = '$user_ID'");
   $google_id = $user_details->author_advertising;
   $user_custom1 = $user_details->author_custom1;
   $user_custom2 = $user_details->author_custom2;
   ?>
   <div class="wrap">
   <form method="post">
      <h2><?php echo $google_values[5]; ?></h2>
      <?php echo $google_values[6]; ?>

   <table class="form-table">

   <tr valign="top"><th scope="row">Your Advertising ID</th>
   <td><input type="text" name="user_google" value="<? echo $google_id; ?>"></td>
   </tr>

   <?php if($google_values[7] == 1){ echo '<tr valign="top"><th scope="row">' . $google_values[8] . '</th><td><input type="text" name="custom1" value="' . $user_custom1 . '"></td></tr>'; } ?>
   <?php if($google_values[10] == 1){ echo '<tr valign="top"><th scope="row">' . $google_values[11] . '</th><td><input type="text" name="custom2" value="' . $user_custom2 . '"></td></tr>'; } ?>

   </table>

   <input type="hidden" name="update_kd_googleauthor" value="1">
   <p class="submit"><input type="submit" name="info_update" value="Save Changes" /></p>
   </form>
   </div>
<?php
}

function kd_admin_menu() {
   $google_values = get_option('kd_author_advertising');
   $lowest_user = $google_values[3];
   add_submenu_page('options-general.php', 'Author Advertising Config', 'Author Advertising Config', 'manage_options', 'author-advertising-admin', 'kd_admin_options');
   add_submenu_page('users.php', 'Author Advertising', 'Author Advertising', 'manage_options', 'author-advertising-users', 'kd_admin_users');
   add_submenu_page('index.php', $google_values[5], $google_values[5], 'author_advertising', 'author-advertising', 'kd_authoredit');
   }

function kd_admin_users(){
   global $table_prefix, $wpdb;
   $table_name = $table_prefix . "author_advertising";
   $google_values = get_option('kd_author_advertising');
   echo "<div class=wrap>";
   $action = $_POST['action'];
   if($action == "delete"){
      $user_id = $_POST['user_id'];
      $wpdb->query("DELETE FROM $table_name WHERE author_id='$user_id'");
      }
   if($action == "edit"){
      $user_id = $_POST['user_id'];
   ?>
   <table class="form-table">
   <form method="post">
   <input type="hidden" name="action" value="edited">
   <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
   <tr valign="top"><th scope="row">Authors Publisher ID</th>
   <td><input type="text" name="edit_pubid" value="<? echo kd_get_google_id($user_id); ?>"></td>
   </tr>
   <tr valign="top"><th scope="row">Authors Custom 1</th>
   <td><input type="text" name="edit_custom1" value="<? echo $wpdb->get_var("SELECT author_custom1 FROM $table_name WHERE author_ID='$user_id'");  ?>"></td>
   </tr>
   <tr valign="top"><th scope="row">Authors Custom 2</th>
   <td><input type="text" name="edit_custom2" value="<? echo $wpdb->get_var("SELECT author_custom2 FROM $table_name WHERE author_ID='$user_id'");  ?>"></td>
   </tr>
   </table>
   <p class="submit"><input type="submit" name="submit" value="Edit Author"></p></form>
   <div class="tablenav">


<br class="clear" />

</div>
<?php
      }

   if($action == "edited"){
   $user_id = $_POST['user_id'];
   $edited_id = stripslashes($_POST['edit_pubid']);
   $edited_1 = stripslashes($_POST['edit_custom1']);
   $edited_2 = stripslashes($_POST['edit_custom2']);
   $wpdb->query("UPDATE $table_name SET author_advertising='$edited_id',author_custom1='$edited_1',author_custom2='$edited_2' WHERE author_id='$user_id'");
}
   $userresults = $wpdb->get_results("SELECT ID, author_id, author_advertising, author_custom1, author_custom2 FROM $table_name ORDER BY author_id ASC");

?>
<table class="widefat">
<thead>
<tr class="thead">
   <th>User ID</th>
   <th>Username</th>
   <th>Advertising ID</th>
   <th>Custom 1 (<?php echo $google_values[8]; ?>)</th>
   <th>Custom 2 (<?php echo $google_values[11]; ?>)</th>
   <th>Actions</th>
</tr>
</thead>
<tbody id="users" class="list:user user-list">
<?php foreach ($userresults as $userresult) { ?>
<tr id="<?php echo $userresult->author_id; ?>">
<td><?php echo $userresult->author_id; ?></td>
<td><?php $user_info = get_userdata($userresult->author_id); echo $user_info->user_login; ?></td>
<td><?php echo $userresult->author_advertising; ?></td>
<td><?php echo $userresult->author_custom1; ?></td>
<td><?php echo $userresult->author_custom2; ?></td>
<td><form method="post"><input type="hidden" name="action" value="delete"><input type="submit" class="button-secondary" name="submit" value="Delete"><input type="hidden" name="user_id" value="<?php echo $userresult->author_id; ?>"></form><form method="post"><input type="hidden" name="action" value="edit"><input type="hidden" name="user_id" value="<?php echo $userresult->author_id; ?>"><input type="submit" class="button-secondary" name="submit" value="Edit"></form>
</tr>
<?php } ?>
</tbody>
</table>
</div>
<?php
}

function kd_admin_options(){
   global $table_prefix, $wpdb;
   $table_name = $table_prefix . "author_advertising";
   $wp_roles = new WP_Roles;
   $existingroles = $wp_roles->get_names();
   $current_roles = $wp_roles->roles;
   
   if(isset($_POST['update_kd_google'])) {
   $google_values[0] = $_POST['user_google'];
   $google_values[1] = $_POST['admin_show'];
   $google_values[2] = 100 - $google_values[1];
   foreach($current_roles as $key => $role){
       $role = urlencode($role["name"]);
       if(isset($_POST[$role]) == $role){ $wp_roles->add_cap($key, 'author_advertising' ); }
       else { $wp_roles->remove_cap( $key, 'author_advertising' ); }
   }
   $current_roles = $wp_roles->roles;
   $google_values[3] = $_POST['level_google'];
   $google_values[4] = $_POST['random_home'];
   $google_values[5] = stripslashes($_POST['myadsense_title']);
   $google_values[6] = stripslashes($_POST['myadsense_text']);
   $google_values[7] = $_POST['customfield1'];
   $google_values[8] = stripslashes($_POST['customfield1_title']);
   $google_values[9] = stripslashes($_POST['customfield1_default']);
   $google_values[10] = $_POST['customfield2'];
   $google_values[11] = stripslashes($_POST['customfield2_title']);
   $google_values[12] = stripslashes($_POST['customfield2_default']);
   $google_values[13] = $_POST['adplace1'];
   $google_values[14] = stripslashes($_POST['adtext1']);
   $google_values[15] = $_POST['adplace2'];
   $google_values[16] = stripslashes($_POST['adtext2']);
   $google_values[17] = $_POST['adplace3'];
   $google_values[18] = stripslashes($_POST['adtext3']);
   $google_values[19] = $_POST['adplace4'];
   $google_values[20] = stripslashes($_POST['adtext4']);

   update_option("kd_author_advertising", $google_values);
   }


if($_POST['action'] == "resetall") { update_option("kd_author_advertising", ""); }
   echo "<div class=wrap>";
   echo '<div id="icon-options-general" class="icon32"><br /></div>';
   echo "<h2>Author Advertising Configuration</h2>";

$checkcustom1 = $wpdb->query("show columns from $table_name like 'author_custom1'");
$checkcustom2 = $wpdb->query("show columns from $table_name like 'author_custom2'");

if($wpdb->get_var("show tables like '$table_name'") != $table_name){
   $nodb = 1;
   echo "<div class='wrap'><p><b>Ah jist cannae do it captain.</b> The database table cannot be installed, check your database permissions and activate the plugin again or run the following code in phpMyAdmin.";
   echo " All this error message means is that the plugin doesn't have the rights to create a table for itself which means you'll have to create one for it.</p>";
   echo "<p>If you don't know what phpMyAdmin is or you're having trouble running the SQL statement, <a href='http://community.mybboard.net/thread-4720.html' target='_blank'>here</a> is a good tutorial.</p>";
   $sql = "CREATE TABLE ".$table_name." (
   id mediumint(9) NOT NULL auto_increment,
   author_id int(11) NOT NULL default '0',
   author_advertising text NOT NULL,
   author_custom1 text,
   author_custom2 text,
   PRIMARY KEY  (`id`)
   );";
   echo "<p style='background-color:#ccc; border:1px solid #666; font-family:courier new, courier; padding:5px;'><strong>CODE:</strong><br/>" . $sql . "<br/></p>";
   echo "</div>";
   }

   if($nodb != 1){
        if($checkcustom1 == 0){
        echo "Your Author Advertising table structure is out of date.... trying to update it now.<br/>";
        $wpdb->query("alter table $table_name add column author_custom1 text NOT NULL");
        }

        if($checkcustom2 == 0){
        echo "Your Author Advertising table structure is out of date.... trying to update it now.<br/>";
        $wpdb->query("alter table $table_name add column author_custom2 text NOT NULL");
        }
        if($checkcustom1 == 0 || $checkcustom2 == 0){
            $nodb = 1;
            echo "<p>You're getting this message because you previously used a very old version of Author Advertising. Refresh the page and if this message keeps coming up then your database does not have the correct permissions and you should run the following code in phpMyAdmin:</p>";
            echo "<p>If you don't know what phpMyAdmin is or you're having trouble running the SQL statement, <a href='http://community.mybboard.net/thread-4720.html' target='_blank'>here</a> is a good tutorial.</p>";
            echo "<p style='background-color:#ccc; border:1px solid #666; font-family:courier new, courier; padding:5px;'><strong>CODE:</strong><br/>ALTER table " . $table_name . " ADD COLUMN author_custom1_text NOT NULL; ALTER table " . $table_name . " ADD COLUMN author_custom2_text NOT NULL;<br/></p>";
            }
   $google_values = get_option('kd_author_advertising');
   if(empty($google_values)){ kd_install(); $google_values = get_option('kd_author_advertising'); }
}
if($nodb != 1){
   ?>
<script LANGUAGE="JavaScript">
<!--
function confirmSubmit()
{
var agree=confirm("Are you sure you wish to continue?");
if (agree)
	return true ;
else
	return false ;
}
// -->
</script>

   <form method="post">
      <h2>Update Author Advertising Options</h2>
      <p>At the moment your ads are showing at a ratio of (Admin) <?php echo $google_values[1] . ":" . $google_values[2]; ?> (User).</p>
<table class="form-table">

   <tr valign="top"><th scope="row">Admin Advertising ID</th>
   <td><input type="text" name="user_google" value="<?php echo $google_values[0]; ?>"><br />Enter your own advertising ID. If the author hasn't specified an ID, the admin id will be shown instead.</td>
   </tr>

   <tr valign="top"><th scope="row">Admin Percentage</th>
   <td><input type="text" name="admin_show" value="<?php echo $google_values[1]; ?>" size="2">%<br />Put in the percentage you want the above admin ID to show for example if you want your ads to show half of the time enter 50, if you want them to show three quarters of the time enter 75.</td>
   </tr>

   <tr valign="top"><th scope="row">Allowed Roles</th>
   <td>
   <?php
        foreach($current_roles as $key => $role){
            if($key == "administrator"){
                echo '<input type="hidden" name="Administrator" value="Administrator">';
                echo '<input type="checkbox" name="' .  urlencode($role["name"]) . '" value="' . urlencode($role["name"]) . '"';
                echo " checked disabled";
                echo '> ' . $role["name"] . ' (Always allowed)<br/>';
            }
            else {
            echo '<input type="checkbox" name="' .  urlencode($role["name"]) . '" value="' . urlencode($role["name"]) . '"';
            if($role["capabilities"]['author_advertising'] == 1){ echo " checked"; }
            echo '> ' . $role["name"] . '<br/>';
            }
        }
   ?>
   <br />Check all user roles that are allowed to add their advertising details.</td>
   </tr>

   <tr valign="top"><th scope="row">Random Home</th>
   <td><label for="random_home">
   <input name="random_home" type="checkbox" id="random_home" value="1" <?php if($google_values[4]=="1") echo "checked=\"checked \""; ?>/> Randomised home?</label><br />If checked, then the homepage will randomly rotate author ads (still at the ratio you've specified) on your homepage. If unchecked only admin ads will be shown on the homepage. This plugin only displays one ID on a page at a time.</td>
   </tr>
   </table>

   <h3>The Author Page</h3>
   <p>You can adjust the text that authors see in their (default) 'My Advertising' page and also change the title. This way you can tell the authors which publisher ID to put in there i.e Yahoo, Google or whichever advertising program you use.</p>

   <table class="form-table">
   <tr valign="top"><th scope="row">Title</th>
   <td><input type="text" name="myadsense_title" value="<?php echo $google_values[5]; ?>" size="25"><br/>e.g 'My Adsense', 'My Advertising'</td>
   </tr>
   <tr valign="top"><th scope="row">Page Content</th>
   <td><textarea rows="10" cols="50" name="myadsense_text"><?php echo $google_values[6]; ?></textarea><br />The text you'd like your authors to see on the page.</td>
   </tr>

   <tr valign="top"><th scope="row">Custom Fields</th>
   <td><label for="customfield1"><input name="customfield1" type="checkbox" id="customfield1" value="1" <?php if($google_values[7]=="1") echo "checked=\"checked \""; ?>/> Custom Field 1 enabled?</label><br />If checked, you may use a custom field to gather more information from your authors to use in the adverts.<br />
   <label for="customfield1_text"><input type="text" name="customfield1_title" value="<?php echo $google_values[8]; ?>" size="25"> Field 1 title e.g Yahoo ID, Amazon ID or even Author URL, Author Hometown.</label><br/>
   <label for="customfield1_default"><input type="text" name="customfield1_default" value="<?php echo $google_values[9]; ?>" size="25"> Field 1 default. If a user doesn't enter anything in this custom field what would you like shown instead?</label><br/>
   <label for="customfield2"><input name="customfield2" type="checkbox" id="customfield2" value="1" <?php if($google_values[10]=="1") echo "checked=\"checked \""; ?>/> Custom Field 2 enabled?</label><br />If checked, you may use a custom field to gather more information from your authors to use in the adverts.<br />
   <label for="customfield2_text"><input type="text" name="customfield2_title" value="<?php echo $google_values[11]; ?>" size="25"> Custom Field 2 title.</label><br/>
   <label for="customfield2_default"><input type="text" name="customfield2_default" value="<?php echo $google_values[12]; ?>" size="25"> Field 2 default. If a user doesn't enter anything in this custom field what would you like shown instead?</label><br/>
   </td>
   </table>

   <h3>Advertising Code</h3>
   <p>Below you can specify the advertising code for each of your ads. You can style your ads here too i.e centre them. The textarea fields allow html but not php code. The picture below shows the positioning of each advert. Bear in mind that this automatic positioning may not work with some themes (it's the themes fault not the plugin ;). If your ad's are not showing properly simply disable all the ads below and use the function below in your theme:
<code>
&lt;?php kd_template_ad($type); ?&gt;
</code>
Replacing $type with a number from 1-4 according to which of the ads below you'd like displayed.</p>
<a href="http://www.harleyquine.com/wp-downloads/php-scripts/authoradvertising/advertplaces.jpg" target="_blank"><img src="http://www.harleyquine.com/wp-downloads/php-scripts/authoradvertising/advertplaces_small.jpg" style="margin-bottom:20px;"></a>

   <table class="form-table">
   <tr valign="top"><th scope="row">Ad Place 1</th>
   <td><label for="adplace1_active"><input type="checkbox" name="adplace1" value="YES"<?php if($google_values[13]=="YES")echo " checked=\"checked\""; ?>> Active?</label><br />
   <textarea rows="10" cols="50" name="adtext1"><?php echo $google_values[14]; ?></textarea><br />Advert Place 1: Just before the posts start in the main content.<br /><p><b>Available tags:</b> %pubid% (original field), %custom1% (custom field 1) and %custom2% (custom field 2).</p></td>
   </tr>

   <tr valign="top"><th scope="row">Ad Place 2</th>
   <td><label for="adplace2_active"><input type="checkbox" name="adplace2" value="YES"<?php if($google_values[15]=="YES")echo " checked=\"checked\""; ?>> Active?</label><br />
   <textarea rows="10" cols="50" name="adtext2"><?php echo $google_values[16]; ?></textarea><br />Advert Place 2: At the end of the posts in the main content.<br /><p><b>Available tags:</b> %pubid% (original field), %custom1% (custom field 1) and %custom2% (custom field 2).</p></td>
   </tr>

   <tr valign="top"><th scope="row">Ad Place 3</th>
   <td><label for="adplace3_active"><input type="checkbox" name="adplace3" value="YES"<?php if($google_values[17]=="YES")echo " checked=\"checked\""; ?>> Active?</label><br />
   <textarea rows="10" cols="50" name="adtext3"><?php echo $google_values[18]; ?></textarea><br />Advert Place 3: At the very end of the page, in the footer.<br /><p><b>Available tags:</b> %pubid% (original field), %custom1% (custom field 1) and %custom2% (custom field 2).</p></td>
   </tr>

   <tr valign="top"><th scope="row">Author Advertising Widget</th>
   <td><label for="adplace4_active"><input type="checkbox" name="adplace4" value="YES"<?php if($google_values[19]=="YES")echo " checked=\"checked\""; ?>> Active?</label><br />
   <textarea rows="10" cols="50" name="adtext4"><?php echo $google_values[20]; ?></textarea><br />Widget: The Author Advertising widget.<br /><p><b>Available tags:</b> %pubid% (original field), %custom1% (custom field 1) and %custom2% (custom field 2).</p></td>
   </tr>
   </table>

   <input type="hidden" name="update_kd_google" value="1">
   <p class="submit"><input type="submit" name="info_update" value="Save Changes" /></p>
   </form>
<form method="post">
    <input type="hidden" name="action" value="resetall">
    <p class="submit"><input type="submit" name="settodefault" value="Reset All to Default" onClick="return confirmSubmit()"/></p>
</form>

   <div class="wrap">
   <p><b>Usage:</b></p>
   <p>For full usage instructions see the included <a href="../wp-content/plugins/author-advertising-plugin/AuthorAdvertisingPluginManual.pdf" target="_blank">plugin manual (PDF format)</a>. The script isn't just for advertising, the text boxes will take any html. This plugin is for <a href="http://www.wordpress.org">WordPress 2.9.2</a> and above. Enjoy the plugin! and if you have any extra dosh, why not <a href="http://www.harleyquine.com/code/" target="_blank">donate to the cause</a>? ;) For help/suggestions/bug reports please visit the >><a href="http://www.harleyquine.com/code/author-advertising-plugin/" target="_blank">Author Advertising Plugin page</a> or email support@harleyquine.com.</p>
   </div>
<?php
}
}

function kd_get_google_id($user_id){
   global $table_prefix, $wpdb;
   $table_name = $table_prefix . "author_advertising";
   $google_values = get_option('kd_author_advertising');

      $google_id = $wpdb->get_var("SELECT author_advertising FROM $table_name WHERE author_id='$user_id'");
      $google_values = get_option('kd_author_advertising');
      $admin_id = $google_values[0];
      if(!$google_id){ $google_id = $admin_id; }
   srand(time());
   $random = (rand()%101);
   if($random <= $google_values[1]){ return $admin_id; }
   else { return $google_id; }
}

function kd_get_random(){
   global $table_prefix, $wpdb;
   $table_name = $table_prefix . "author_advertising";

      $google_id = $wpdb->get_var("SELECT author_advertising FROM $table_name ORDER BY rand() LIMIT 1");
      $google_values = get_option('kd_author_advertising');
      $admin_id = $google_values[0];
      if(!$google_id){ $google_id = $admin_id; }
   srand(time());
   $random = (rand()%101);
   if($random <= $google_values[1]){ return $admin_id; }
   else { return $google_id; }
}

function kd_get_custom1(){
   global $table_prefix, $wpdb;
   $table_name = $table_prefix . "author_advertising";
   $google_values = get_option('kd_author_advertising');
   $kd_current_id = get_option('kd_current_id');
   $custom1 = $wpdb->get_var("SELECT author_custom1 FROM $table_name WHERE author_advertising='$kd_current_id'");
   if(!$custom1){ $custom1 = $google_values[9]; }
   return $custom1;
}

function kd_get_custom2(){
   global $table_prefix, $wpdb;
   $table_name = $table_prefix . "author_advertising";
   $google_values = get_option('kd_author_advertising');
   $kd_current_id = get_option('kd_current_id');
   $custom2 = $wpdb->get_var("SELECT author_custom2 FROM $table_name WHERE author_advertising='$kd_current_id'");
   if(!$custom2){ $custom2 = $google_values[12]; }
   return $custom2;
}

function kd_init_author_id(){
   global $post;
   $google_values = get_option('kd_author_advertising');
   $random_home = $google_values["4"];
   if(is_home()){ if($random_home == 1){ $kd_pub_id = kd_get_random(); } else { $kd_pub_id = kd_get_google_id('0'); }}
   if(is_single()){ $kdid = $post->post_author; $kd_pub_id = kd_get_google_id($kdid);}
   if(is_page()){ $kdid = $post->post_author; $kd_pub_id = kd_get_google_id($kdid);}
   update_option("kd_current_id", $kd_pub_id);
}


function kd_get_ad_ready($adtext){
   $kd_current_id = get_option('kd_current_id');
   if(!$kd_current_id){
   $kd_pub_id = kd_get_google_id('0');
   update_option("kd_current_id", $kd_pub_id);
   $kd_current_id = get_option('kd_current_id');
   }
   $custom1 = kd_get_custom1();
   $custom2 = kd_get_custom2();
   $adtext = str_replace("%pubid%", $kd_current_id, $adtext);
   $adtext = str_replace("%custom1%", $custom1, $adtext);
   $adtext = str_replace("%custom2%", $custom2, $adtext);
   return $adtext;
}

function kd_template_ad($type){
   $google_values = get_option('kd_author_advertising');
   if($type == 1){ echo kd_get_ad_ready($google_values[14]); }
   if($type == 2){ echo kd_get_ad_ready($google_values[16]); }
   if($type == 3){ echo kd_get_ad_ready($google_values[18]); }
   if($type == 4){ echo kd_get_ad_ready($google_values[20]); }
}

function kd_authoradvertisingparse($content) {
   $google_values = get_option('kd_author_advertising');
   if(strpos($content, "%authorad1%")){ $adtext = kd_get_ad_ready($google_values[14]); $content = str_replace("%authorad1%", $adtext, $content); }
   if(strpos($content, "%authorad2%")){ $adtext = kd_get_ad_ready($google_values[16]); $content = str_replace("%authorad2%", $adtext, $content); }
   if(strpos($content, "%authorad3%")){ $adtext = kd_get_ad_ready($google_values[18]); $content = str_replace("%authorad3%", $adtext, $content); }
   if(strpos($content, "%authorad4%")){ $adtext = kd_get_ad_ready($google_values[20]); $content = str_replace("%authorad4%", $adtext, $content); }

   return $content;
}

function kd_header_ad(){
   $google_values = get_option('kd_author_advertising');
   if($google_values[13] == "YES"){ echo kd_get_ad_ready($google_values[14]); }
}

function kd_footer_ad(){
   $google_values = get_option('kd_author_advertising');
   if($google_values[15] == "YES"){ echo kd_get_ad_ready($google_values[16]); }
}

function kd_footer_ad2(){
   $google_values = get_option('kd_author_advertising');
   if($google_values[17] == "YES"){ echo kd_get_ad_ready($google_values[18]); }
}

function kd_shutdown(){
   update_option("kd_current_id", '0');
}

$google_values = get_option('kd_author_advertising');

if($google_values[13] == "YES"){ add_action('loop_start','kd_header_ad'); }
if($google_values[15] == "YES"){ add_action('loop_end','kd_footer_ad'); }
if($google_values[17] == "YES"){ add_action('wp_footer','kd_footer_ad2'); }


add_action('admin_menu', 'kd_admin_menu');
add_action('activate_author-advertising.php','kd_install');
add_action('shutdown','kd_shutdown');
add_action('wp','kd_init_author_id');
add_action('plugins_loaded','kd_aa_widget_setup');
add_filter('the_content', 'kd_authoradvertisingparse');

