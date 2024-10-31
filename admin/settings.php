<div class="preloder_head">
	<h6><img src="<?php echo plugin_dir_url( dirname( __FILE__ ) ) . 'admin/assets/images/preloder_icon.png'; ?>" alt="preloder_icon">Predictive Preloader</h6>
</div>

<div class="perloader_settings">
	<div class="preloader_container">
		<h2 class="heading">License Key</h2>
		<div class="preloader_container">
			<div class="store_part">
				<?php if(get_option("predictive_license_key")!=''){ ?>
					<p class='meEnable'>Pro version is enabled on your site</p>
				<?php }else{ ?>
					<p class='meEnable'>Upgrade to our PRO version and speed up your <span>pages by 4x their original speed…! CLICK <a href="https://predictivepreloader.com" target="_blank">HERE</a> TO UPGRADE</span></p>
				<?php } ?>
				<ul class="license_key_section"> 
					<li>
						<label>Key</label><input type="text" class="predictive_license_key" name="predictive_license_key" value="<?php echo get_option("predictive_license_key"); ?>" placeholder="Enter license key here..." <?php if(get_option("predictive_license_key")!=''){ echo "readonly"; } ?>>
						<div class="licenseMsg"></div>
					</li>
					<?php if(get_option("predictive_license_key")==''){ ?>
						<li class="editKey"><a href="javascript:void(0)" class="saveLicense">Save</a></li>
					<?php } ?>
				</ul>
			</div>
		</div>
		<?php 
			if(isset($_POST['haveAccount'])){
				if($_POST['haveAccount']=="no"){ 
					$siteTitle = get_bloginfo("name");
					$siteURl = get_site_url();
					$body = array(
					    'shop_name' => $siteTitle,
					    'shop_url' => $siteURl
					);
					$args = array(
						'method' => 'POST',
						'timeout' => 30,
						'redirection' => 5,
						'httpversion' => '1.0',
						'blocking' => true,
						'cookies' => array(),
					    'body' => $body,
					    'headers' => array("'Content-Type:application/json'")
					);
					 
					$result = wp_remote_post( 'https://admin.predictivepreloader.com/api/generate/tracking', $args );
				}else{
					$siteURl = get_site_url();
					$body = array(
					    'shop_url' => $siteURl
					);
					$args = array(
						'method' => 'POST',
						'timeout' => 30,
						'redirection' => 5,
						'httpversion' => '1.0',
						'blocking' => true,
						'cookies' => array(),
					    'body' => $body,
					    'headers' => array("'Content-Type:application/json'")
					);
					 
					$result = wp_remote_post( 'https://admin.predictivepreloader.com/api/validate/tracking', $args );
				} 
				$resultArray = json_decode($result["body"]); 
				//print_r($resultArray);
				if($resultArray->success){
					update_option("predictive_pre_loader_settings","active");
					update_option("gtag_key",$resultArray->data);
				}else{
					echo "<p>".$resultArray->message."</p>"; 
				}
			}
			if(get_option("predictive_pre_loader_settings")!="active"){?>
				<div class="store_part">
					<div class="store_part">
						<label class="status">Do you already have Google Analytics in your store ?</label>
						<ul class="have_google_analytics">
							<li><input type="radio" class="predictive_status google_analytics" name="google_analytics" value="0"><label>Yes</label></li>
							<li><input type="radio" class="predictive_status google_analytics" name="google_analytics" value="1"><label>No</label></li>
						</ul>
					</div>
				</div>
				<div class="preloder_store analytics_type" style="display:none;">
					<form class="form-inline" method="post">
					  <div class="form-group">
					    <label class="sr-only">Email ID</label>
					    <p>(please add below email id in your Google Analytics Account)</p>
					    <div class="input-group">
					      <input type="text" class="form-control" id="exampleInputAmount" value="predictivetest1@predictive-262411.iam.gserviceaccount.com" readonly>
					      <input type="hidden" name="haveAccount" value="yes">
					      <button type="submit" class="btn btn-primary validate_btn valGenerate" flag="yes">Validate</button>
					    </div>
					  </div>
					</form>
					<div class="google_analytics">
						<h3>Steps to add user permission in Google Analytics</h3>
						<ul>
							<li>1. Sign in to <a href="https://analytics.google.com">Google Analytics.</a></li>
							<li>2. Click <a href="https://support.google.com/analytics/answer/6132368">Admin</a>,and navigate to the admin <a href="https://support.google.com/analytics/answer/6099198">dashboard.</a></li>
							<li>In the Property column click Property User Management.</li>
							<li>4. In the Account users list, click +, then click Add new users.</li>
							<li>5. Enter the above email address.</li>
							<li>6. Select check box “Notify new users by email”.</li>
							<li>8. Click Add.</li>
						</ul>
					</div>
				</div>	
				<div class="no-preloader_container analytics_type" style="display:none;">
					<div class="create_account">
						<p>Predictive Preloader will automatically create GA Account and Update Script Tags in your Wordpress Store .</p>
						<span>Please press Continue to authorise.</span>
					</div>
					<div class="continue_part">
						<form method="post">
							<input type="hidden" name="haveAccount" value="no">
							<button type="submit" class="validate_btn" flag="no">Continue</button>
						</form>
					</div>
				</div>
			<?php } ?>
	</div>
	<input type="hidden" class="adminUrl" value="<?php echo admin_url(); ?>admin-ajax.php">
</div>