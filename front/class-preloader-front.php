<?php
/**
 * Preloader Add script
 */

class Preloader_Front {
	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 */
	const VERSION = '1.0.0';

	/**
	 * Unique identifier for plugin.
	 *
	 */
	protected $plugin_slug = 'preloader';

	/**
	 * Instance of this class.
	 *
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 */
	private function __construct() {
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
		add_action('wpmu_new_blog', array( $this, 'activate_new_site' ) );
		add_action('wp_footer', array( $this, 'gtag_add_script' ));
		add_action('wp_footer', array( $this, 'preload_script_add' ));
		add_action('init', array( $this, 'enqueue_script' ));
	}

	/**
	 * Return the plugin slug.
	 *
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
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
	 * Fired when the plugin is activated.
	 *
	 */
	public static function activate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide  ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();
				}

				restore_current_blog();

			} else {
				self::single_activate();
			}

		} else {
			self::single_activate();
		}
		$siteTitle = get_bloginfo("name");
		$siteURl = get_site_url();
		$siteEmail = get_option('admin_email');
		$userDetails = get_user_by( 'email', $siteEmail );
		$shopBody = array("shop_id"=>'',"name"=>$siteTitle,"email"=>$siteEmail,"domain"=>$siteURl,"province"=>'',"country"=>'',"address1"=>"","zip"=>"","city"=>"","address2"=>"","country_code"=>"","country_name"=>"","currency"=>"","customer_email"=>$siteEmail,"iana_timezone"=>"","shop_owner"=>$userDetails->user_login,"myshopify_domain"=>$siteURl,"shop_created_at"=>"","shop_updated_at"=>"","type"=>"wordpress");
		
		$args = array(
			'method' => 'POST',
			'timeout' => 30,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'cookies' => array(),
		    'body' => $shopBody,
		    'headers' => array("Content-Type:application/json'")
		);

		$result = wp_remote_post("https://admin.predictivepreloader.com/api/create/shop", $args );
		$result2 = json_decode($result['body']);

		$checkBody = array("shop_url"=>$siteURl);
		$args = array(
			'method' => 'POST',
			'timeout' => 30,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'cookies' => array(),
		    'body' => $checkBody,
		    'headers' => array("Content-Type:application/json'")
		);
		$result = wp_remote_post("https://admin.predictivepreloader.com/api/check/", $args );
		$result3Aray = json_decode($result['body']);
		
		if(!empty($result3Aray->data->status)){
			$createBody = array("shop_url"=>$siteURl,"status"=>'active');

			$args = array(
				'method' => 'POST',
				'timeout' => 30,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'cookies' => array(),
			    'body' => $createBody,
			    'headers' => array("Content-Type:application/json'")
			);
			$result = wp_remote_post("https://admin.predictivepreloader.com/api/create/", $args );
		}

		$generateBody = array("shop_url"=>$siteURl,"shop_name"=>$siteTitle);
		$args = array(
				'method' => 'POST',
				'timeout' => 30,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'cookies' => array(),
			    'body' => $generateBody,
			    'headers' => array("Content-Type:application/json'")
			);
		$result = wp_remote_post("https://admin.predictivepreloader.com/api/generate/tracking", $args );
		$resultArray1 = json_decode($result['body']); 
		if($resultArray1->success){
			update_option("predictive_pre_loader_settings","active");
			update_option("gtag_key",$resultArray1->data);
		}
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 */
	public static function deactivate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_deactivate();

				}

				restore_current_blog();

			} else {
				self::single_deactivate();
			}

		} else {
			self::single_deactivate();
		}
		$siteURl = get_site_url();

		$body = array("shop_url"=>$siteURl,"status"=>'deleted');
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
		$result = wp_remote_post("https://admin.predictivepreloader.com/api/create/", $args );
	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 */
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col($sql);

	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();

	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 */
	private static function single_activate() {
		// No activation functionality needed... yet
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 */
	private static function single_deactivate() {
		// No deactivation functionality needed... yet
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 */

	public function enqueue_script(){
		wp_enqueue_script( 'tti-polyfill.js', plugin_dir_url( __FILE__ ) .'assets/js/tti-polyfill.js' );
	}

	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

	}
	public function gtag_add_script(){
		$gtag_key = get_option("gtag_key");
		if($gtag_key!=''){
			$siteURl = get_site_url();
			$body = array("full_path"=>$siteURl);	
			$response = $this->predictive_pre_loader_api('https://admin.predictivepreloader.com/api/get/shop/detail',$body);

	?>
			<!-- Global site tag (gtag.js) - Google Analytics -->
			<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $gtag_key; ?>"></script>
			<script>
			window.dataLayer = window.dataLayer || [];
			function gtag(){dataLayer.push(arguments);}
			gtag('js', new Date());

			gtag('config', '<?php echo $gtag_key; ?>');
			</script>
		  	<script>
			    ttiPolyfill.getFirstConsistentlyInteractive().then((tti) => {
			   		if(tti > 0 && tti <= 25000){
			      		var todayDate = new Date();
				      	gtag('event', '<?php echo $response->data->prefetch_status; ?>', {
					        'send_to': '<?php echo $gtag_key; ?>',
					        'event_category': window.location.pathname,
					        'event_label': todayDate.getDate() + '-' + (todayDate.getMonth()+1) + '-' + todayDate.getFullYear(),
					        'value': tti
					    });
				  	}
			    });
		  	</script>
	<?php
		}
	}

	function preload_script_add(){ 
		$gtag_key = get_option("gtag_key");
		if($gtag_key!=''){
	?>
			<script>
				jQuery(document).ready(function(){
				  var connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
				  var connection_effective_type = "4g";
				  if(typeof connection != 'undefined'){
				    connection_effective_type = connection.effectiveType;
				  }
				  var url = window.location.href;
				  var isMobile = {
				    Android: function() {
				      return navigator.userAgent.match(/Android/i);
				    },
				    BlackBerry: function() {
				      return navigator.userAgent.match(/BlackBerry/i);
				    },
				    iOS: function() {
				      return navigator.userAgent.match(/iPhone|iPad|iPod/i);
				    },
				    Opera: function() {
				      return navigator.userAgent.match(/Opera Mini/i);
				    },
				    Windows: function() {
				      return navigator.userAgent.match(/IEMobile/i);
				    },
				    any: function() {
				      return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows());
				    }
				  };
				  var deviceType = "Desktop";
				  if(isMobile.any()) {
				    deviceType = "Mobile";
				  }
				  jQuery.ajax({
				    type: 'post',
				    dataType: 'json',
				    url: 'https://admin.predictivepreloader.com/api/analytics/reports',
				    data: JSON.stringify({'full_path': url,'network_type': connection_effective_type,'device_type': deviceType }),
				    headers: {'Content-Type': 'application/json','Accept': 'application/json'},
				    success: function(data) {
				      if(data['success'] == true){
				        if(data["data"]["filteredReport"].length > 0 && data["data"]['prefetchStatus']=="enable"){
				       	  console.log('Predictions: ', data["data"]["filteredReport"]);
				          var next_pages = data["data"]["filteredReport"][0]["nextPages"];
				          next_pages.forEach(myFunction);
				        }
				      }
				    },
				    error: function (error) {
				      console.log(error['responseText']);
				    }
				  });
				});

				function myFunction(value, index, array) {
				  let domain = window.location.hostname;
				  let protocol = window.location.protocol;
				  jQuery('head').append("<link rel=\"prefetch\""+" href="+protocol+"//"+domain+value["pagePath"]+">");
				}
			</script>
	<?php }
	}
	function predictive_pre_loader_api($url,$body){ 
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