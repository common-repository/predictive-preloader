<?php
/**
 * Preloader Site
 */

 class Preloader_Admin {
	/**
	 * Instance of this class.
	 *
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 */

	protected $plugin_screen_hook_suffix = null;

	private function __construct() {
		add_action('init', array( $this, 'enqueue_register' ));
		add_action('init', array( $this, 'enqueue_style' ));
		add_action('admin_menu', array( $this, 'preloader_settings'));
		add_action('admin_notices', array( $this, 'activation_notices' ) ) ;
		add_action( 'wp_ajax_change_range_data', array( $this, 'change_range_data'));
		add_action( 'wp_ajax_nopriv_change_range_data', array( $this, 'change_range_data') );
		add_action( 'wp_ajax_validate_license_key', array( $this, 'validate_license_key'));
		add_action( 'wp_ajax_nopriv_validate_license_key', array( $this, 'validate_license_key') );
	}


	/**
	 * Return an instance of this class.
	 *
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is register script.
	 *
	 */

	public function enqueue_register(){
		wp_register_style( 'preloader', plugin_dir_url( __FILE__ ) .'assets/css/preloader.css', false, '1.0.0', 'all');
		wp_register_style( 'daterangepicker', plugin_dir_url( __FILE__ ) .'assets/css/daterangepicker.css', false, '1.0.0', 'all');
	}

	/**
	 * Fired when the plugin is load script.
	 *
	 */

	public function enqueue_style(){
	   wp_enqueue_style( 'preloader', plugin_dir_url( __FILE__ ) .'assets/css/preloader.css' );
	   wp_enqueue_style( 'daterangepicker', plugin_dir_url( __FILE__ ) .'assets/css/daterangepicker.css' );
	   wp_enqueue_script( 'preloader', plugin_dir_url( __FILE__ ) .'assets/js/preloader.js' );
	   wp_enqueue_script( 'predictive-dashboard.min.js', plugin_dir_url( __FILE__ ) .'assets/js/predictive-dashboard.min.js' );
	   wp_enqueue_script( 'daterangepicker.min', plugin_dir_url( __FILE__ ) .'assets/js/daterangepicker.min.js' );
	   wp_enqueue_script( 'Chart.min', plugin_dir_url( __FILE__ ) .'assets/js/Chart.min.js' );
	}

	/**
	 * Fired when the plugin is add menus.
	 *
	 */

	public function preloader_settings(){
		add_menu_page("Predictive Preloader", "Predictive Preloader", "manage_options", "preloader", array( $this, 'performanceDashboard'),plugin_dir_url( __FILE__ ) .'assets/images/preload-logo.png');
		add_submenu_page( "preloader", "Dashboard", "Dashboard", "manage_options", "dashboard", array( $this, 'performanceDashboard'));
		add_submenu_page( "preloader", "Settings", "Settings", "manage_options", "settings", array( $this, 'preloaderSettings'));
		remove_submenu_page('preloader','preloader');
	}

	/**
	 * Fired when the plugin is load setting page.
	 *
	 */

	public function preloaderSettings(){
		include( plugin_dir_path( __FILE__ ) . 'settings.php');
	}

	/**
	 * Fired when the plugin is load performance metrix page.
	 *
	 */

	public function performanceDashboard(){
		include( plugin_dir_path( __FILE__ ) . 'dashboard.php');
	}

	public static function activation_notices() {
		if(get_option("predictive_license_key")==''){
			echo '<div class="updated notice is-dismissible"><p>Upgrade to our PRO version and speed up your pages by 4x their original speed…! CLICK <a href="https://predictivepreloader.com/" target="_blank">HERE</a> TO UPGRADE</p></div>';
		}
	}

	public function change_range_data(){
		if($_POST['type']=="custom"){
			$range = sanitize_text_field($_POST['range']);
			$rangeArray = explode("-", $range);
			$startDate = date("d-m-Y", strtotime($rangeArray[0]));
			$endDate = date("d-m-Y", strtotime($rangeArray[1]));
		}else{
			$range = sanitize_text_field($_POST['range']);
			$rangeArray = explode(",", $range);
			$startDate = $rangeArray[0];
			$endDate = $rangeArray[1];
		}

		$siteURl = get_site_url();
		//$siteURl = "https://predictivepreloader.com";
		$body = array(
		    'full_path' => $siteURl,
		    'start_date' => $startDate,
		    'end_date' => $endDate
		);
		$response = $this->predictive_pre_loader_api_admin('https://admin.predictivepreloader.com/api/analytics/tti',$body);
		$count1 = 0;
		//$data[$count1]["data"][]=array(1578182400000,5000);
		/*echo "<pre>";
		print_r($response);*/
		if(!empty($response->data->avgReport)){
			$responseReport = array();
			$responseReport['msg']= "success";
			$data['type'] = 'line';
			$dateArray = array();
			foreach ($response->data->avgReport as $key => $value) {
				foreach ($value as $k => $v) {
					if(!in_array($k, $dateArray)){
						$dateArray[] = $k;
					}
				}
			}
			$count = 0;
			$data['data']['labels'] = $dateArray;
			$color = array('rgb(255,0,0)','rgb(0,0,255)','rgb(50,205,50)','rgb(255,255,0))','rgb(255,192,203)','rgb(0,0,0)','rgb(128,128,128)','rgb(135,206,235)','rgb(255, 99, 132)','rgb(164, 185, 129)','rgb(47, 64, 20)','rgb(108, 167, 154)','rgb(64, 98, 170)','rgb(152, 64, 170)','rgb(90, 31, 54)','rgb(140, 8, 20)','rgb(124, 106, 3)','rgb(255, 99, 132)','rgb(255, 99, 132)','rgb(255, 99, 132)');
			foreach ($response->data->avgReport as $key => $value) {
				$data['data']['datasets'][$count]['label'] = $key;
				$loadTime = array();
				foreach ($value as $k => $v) {
					$keyDate = array_search($k,$dateArray);
					$loadTime[$keyDate] = intval($v->avg_value);
				}
				$data['data']['datasets'][$count]['data'] = $loadTime;
				$data['data']['datasets'][$count]['borderColor'] = $color[$count];
				$data['data']['datasets'][$count]['backgroundColor'] = 'rgba(0, 0, 0, 0)';
				$data['data']['datasets'][$count]['fill'] = false;
				$data['data']['datasets'][$count]['cubicInterpolationMode'] = 'monotone';
				$count++;
			}
			$data['options']['title']['display'] = true;
			$data['options']['test'] = 'Page Load Time';

			$data['options']['legend']['display'] = true;
			$data['options']['legend']['position'] = 'right';
			$data['options']['legend']['labels']['fontStyle'] = 'bold';

			$data['options']['scales']['yAxes'][0]['scaleLabel']['display'] = true;
			$data['options']['scales']['yAxes'][0]['scaleLabel']['labelString'] = "Avg. Page Load Time (Millisecond)";
			$data['options']['scales']['xAxes'][0]['scaleLabel']['display'] = true;
			$data['options']['scales']['xAxes'][0]['scaleLabel']['labelString'] = "Date";
			$responseReport['data'] = $data;
			$boxReport = '';
			foreach ($response->data->boxReport as $key => $value) {
				if($key=="total_enb_avg"){
					$boxReport .= "<h3>".$value." <small>millisecs</small> <span>Overall Ave Enable Page Speed</span></h3>";
				}
				if($key=="total_disb_avg"){
					$boxReport .= "<h3>".$value." <small>millisecs</small> <span>Overall Ave Disable Page Speed</span></h3>";
				}
				if($key=="avg_speed_improvement"){
					$boxReport .= "<h3>".$value." <small>millisecs</small> <span>Overall Page Speed Improvement</span></h3>";
				}
				if($key=="perc_speed_improvement"){
					$boxReport .= "<h3>".$value." % <span>Percentage Page Speed Improvement</span></h3>";
				}
			}
			$responseReport['boxReport'] = $boxReport;
			$count = 1;
			$html = '';
			foreach ($response->data->enbReport as $key => $value) {
				$html .= "<tr><td>".$count."</td><td>".$key."</td>";
				$views = 0;
				$avg = 0;
				foreach ($value->enable as $k => $v) { 
					if($k=="avg_value"){
						$avg = $v;
					}
					if($k=="total_events"){
						$views = $v;
					}
				}
				$html .= "<td>".$views."</td><td>".$avg."</td>";
				foreach ($value->disable as $k => $v) { 
					if($k=="avg_value"){
						$avg = $v;
					}
					if($k=="total_events"){
						$views = $v;
					}
				}
				$html .= "<td>".$views."</td><td>".$avg."</td></tr>";
			}
			$responseReport['html'] = $html;
		}else{
			$responseReport = array();
			$responseReport['msg']= "Empty";
			$responseReport['data'] = array();
			$responseReport['html'] = "";
			$responseReport['boxReport'] = "";
		}
		echo json_encode($responseReport);
		die;
	}
	public function validate_license_key(){
		if(isset($_POST['predictive_license_key'])){
			$siteURl = get_site_url();
			$license_key = sanitize_text_field($_POST['predictive_license_key']);
			$body = array(
			    'shop_url' => $siteURl,
			    'license_key' => $license_key
			);
			$resulttArray = $this->predictive_pre_loader_api_admin('https://admin.predictivepreloader.com/api/validate/licensekey',$body);
			if($resulttArray->success){
				update_option("predictive_license_key",$license_key);
				$response['msg']="success";
			}else{
				$response['msg']="error";
			}
			echo json_encode($response); die;
		}
	}

	function predictive_pre_loader_api_admin($url,$body){ 
		$args = array(
			'method' => 'POST',
			'timeout' => 30,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'cookies' => array(),
		    'body' => $body,
		    'headers' => array("Content-Type:application/json'")
		);
		//$response = $this->predictive_pre_loader_api()
		$result = wp_remote_post($url, $args );
		return json_decode($result['body']);
	}
}