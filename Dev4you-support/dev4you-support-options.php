<?php if(!defined('ABSPATH')){ exit; }
   
   //Enqueue Admin CSS on Job Board Settings page only
   if ( isset( $_GET['page'] ) && $_GET['page'] == 'dev4you_support' ) {
    // Enqueue Core Admin Styles
    wp_enqueue_style( 'boot_css', plugin_dir_url( __FILE__ ) . 'includes/style.min.css');
    }
    
   add_action('admin_menu', function(){
   	add_menu_page(
           'Pagalba', // page_title
           'Techninė Pagalba', // menu_title
           'manage_options', // capability
           'dev4you_support', //menu_slug
           'dev4you_support_plugin_page',
           'dashicons-cloud', // icon_url
           2 // position
   		);
   });
   
    function dev4you_support_plugin_page(){
   	global $lbapi;
   	global $lb_verify_res;
   	$lb_activate_res = null;
   	$lb_deactivate_res = null;
   	if(!empty($_POST['client_name'])&&!empty($_POST['license_code'])){
   		check_admin_referer('lb_update_license', 'lb_update_license_sec');
   		$lb_activate_res = $lbapi->activate_license(
   			strip_tags(trim($_POST['license_code'])), 
   			strip_tags(trim($_POST['client_name']))
   		);
   		$lb_verify_res = $lbapi->verify_license();
   	}
   	if(!empty($_POST['lb_deactivate'])){
   		check_admin_referer('lb_deactivate_license', 'lb_deactivate_license_sec');
   		$lb_deactivate_res = $lbapi->deactivate_license();
   		$lb_verify_res = $lbapi->verify_license();
   	}
   	$lb_update_data = $lbapi->check_update(); ?>
<div class="wrap">
   <h1>Dev4you.lt Pagalbos įskiepis - Nustatymai</h1>
   <?php if($lb_verify_res['status']){ ?> 
   <div class="notice notice-success">
      <p>Aktyvuota! Jūsų licenzija galiojanti.</p>
   </div>
   <?php }else{ ?> 
   <div class="notice notice-error">
      <p><?php echo (!empty($lb_activate_res['message']))?$lb_activate_res['message']:'Licenzija neaktyvuota arba pateikta licenzija nebegalioja.' ?></p>
   </div>
   <?php }?>
   <?php
      if($lb_verify_res['status']){
         //nieko 
         
         }else{
        ?>
   <style>div#wpfooter {display: none;}</style>     
   <div class="container" style="padding-top: 20px;">
      <div class="section">
         <div class="columns is-centered">
            <div class="column is-two-fifths">
               <center>
                  <h1 class="title" style="padding-top: 20px"><img style="max-width:70%;" src="https://i0.wp.com/dev4you.lt/wp-content/uploads/2020/09/naujas_5x.png"></h1>
                  <br>
               </center>
               <div class="box">
                  <form action="" method="post">
                     <?php wp_nonce_field('lb_update_license', 'lb_update_license_sec'); ?>
                     <div class="field">
                        <label class="label">Licenzijos raktas</label>
                        <div class="control">
                           <input class="input" type="text" placeholder="Įveskite licenzijos kodą" name="license_code" required>
                        </div>
                     </div>
                     <div class="field">
                        <label class="label">Elektroninis paštas</label>
                        <div class="control">
                           <input class="input" type="email" placeholder="Įveskite licenzijai priskirtą el.paštą" name="client_name" required>
                        </div>
                     </div>
                     <div style='text-align: right;'>
                        <button type="submit" class="button is-link is-rounded">Aktyvuoti administravimo paslaugą</button>
                     </div>
                  </form>
                  <?php 
                     } ?>
                  <?php if($lb_verify_res['status']){ ?>
                  <h2 class="title" style="padding-top:10px;">Pašalinti licenzija</h2>
                  <p style="max-width: 450px;">
                     Jei norite naudoti šią licenciją įskiepiui suaktyvinti kitame serveryje, pirmiausia pašalinkite licenciją iš šio serverio, aktyvinkitę ją toliau.
                  </p>
                  <?php if(empty($lb_deactivate_res)){ ?>
                  <form action="" method="post">
                     <?php wp_nonce_field('lb_deactivate_license', 'lb_deactivate_license_sec'); ?>
                     <input type="hidden" name="lb_deactivate" value="yes">
                     <input type="submit" value="Pašalinti" class="button">
                  </form>
                  <?php } ?>
                  <?php } ?>
                  <?php if($lb_verify_res['status']){ ?>
                  <h2 class="title" style="padding-top:10px;">Sistemos atnaujinimai</h2>
                  <p>
                     <strong><?php echo esc_html($lb_update_data['message']); ?></strong>
                  </p>
                  <?php if($lb_update_data['status']){ ?>
                  <p style="max-width: 700px;">Pakeitimai:: 
                     <?php echo strip_tags($lb_update_data['changelog'], '<ol><ul><li><i><b><strong><p><br><a><blockquote>'); ?>
                  </p>
                  <?php if(!empty($_POST['update_id'])){
                     check_admin_referer('lb_update_download', 'lb_update_download_sec');
                     $lbapi->download_update(
                     	strip_tags(trim($_POST['update_id'])), 
                     	strip_tags(trim($_POST['has_sql'])), 
                     	strip_tags(trim($_POST['version']))
                     );
                     if (false !== get_transient('licensebox_next_update_check')) {
                     	delete_transient('licensebox_next_update_check');
                     }
                     ?>
                  <?php }else{ ?>
                  <form action="" method="POST">
                     <?php wp_nonce_field('lb_update_download', 'lb_update_download_sec'); ?>
                     <input type="hidden" value="<?php echo esc_attr($lb_update_data['update_id']); ?>" name="update_id">
                     <input type="hidden" value="<?php echo esc_attr($lb_update_data['has_sql']); ?>" name="has_sql">
                     <input type="hidden" value="<?php echo esc_attr($lb_update_data['version']); ?>" name="version">
                     <div style="padding-top: 10px;">
                        <input type="submit" value="Download and Install Update" class="button button-secondary">
                     </div>
                  </form>
               </div>
            </div>
         </div>
      </div>
   </div>
   <?php } ?>
   <?php } ?>
   <?php } ?>
</div>
<?php }