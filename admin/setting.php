<?php
global $wpdb;
$result = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kcaptcha_setting`");
  foreach ($result as $page) {
      $login_form          = $page->login_form;
      $register_form       = $page->register_form;
      $lost_password_form  = $page->lost_password_form;
      $comments_form       = $page->comments_form;
      $hide_register       = $page->hide_register;
      $id                  = $page->id;
  }
    if (isset($_POST['save'])) {
        $login_form          = ( $_POST["login_form"] == "on" ) ? 1 : 0;
        $register_form       = ( $_POST["register_form"] == "on" ) ? 1 : 0;
        $lost_password_form  = ( $_POST["lost_password_form"] == "on" ) ? 1 : 0;
        $comments_form       = ( $_POST["comments_form"] == "on" ) ? 1 : 0;
        $hide_register       = ( $_POST["hide_register"] == "on" ) ? 1 : 0;
      
        $data = array(
            'login_form'           => $login_form,
            'register_form'        => $register_form,
            'lost_password_form'   => $lost_password_form,
            'comments_form'        => $comments_form,
            'hide_register'        => $hide_register,
        );
      
      $table_name      = $wpdb->prefix . 'kcaptcha_setting';
      
      if ( $result ) {          
          $rowid = $wpdb->update($table_name, $data, array('id' => $id));
      } else {
          $wpdb->insert($table_name, $data);
          $rowid = $wpdb->insert_id;
      }
      if ( $rowid > 0 ) {
          ?>
          <script>
              alert('Successfully Updated!');
          </script>
          <?php
      }
  } 
  //echo $_POST["login_form"].'<br>'.$_POST["register_form"].'<br>'.$lost_password_form.'<br>'.$comments_form.'<br>'.$hide_register.'<br>';
  
?>
<div class="container" ><h1>Enable CAPTCHA for: </h1><br>
    <form action="<?php esc_url($_SERVER['REQUEST_URI']) ?>" method="post">
        <label><input type="checkbox" <?php if($login_form){ echo "checked"; }else{ echo "";};?>  name="login_form"> Login form</label><br>
        <label><input type="checkbox" <?php if($register_form){ echo "checked"; }else{ echo "";};?>  name="register_form"> Registration form</label><br>
        <label><input type="checkbox" <?php if($lost_password_form){ echo "checked"; }else{ echo "";};?>  name="lost_password_form"> Reset Password form</label><br>
        <label><input type="checkbox" <?php if($comments_form){ echo "checked"; }else{ echo "";};?>  name="comments_form"> Comments form</label><br>
        <label><input type="checkbox" <?php if($hide_register){ echo "checked"; }else{ echo "";};?>  name="hide_register"> Hide CAPTCHA in Comments form for registered users</label><br>
        <input type="submit" name="save" value="Save">
    </form>
</div>