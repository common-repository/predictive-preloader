<div class="preloder_head predictive_preloader">
	<h6>
		<img src="<?php echo plugin_dir_url( dirname( __FILE__ ) ) . 'admin/assets/images/preloder_icon.png'; ?>" alt="preloder_icon">Predictive Preloader
	</h6>
	<div class="statusPredict">
		<?php if(get_option("predictive_license_key")!=''){ ?>
			<p>Status: PRO Version Live</p>
		<?php }else{ ?>
			<p><strong>Status :</strong> FREE Version Live <a href="https://predictivepreloader.com/">UPDGRADE</a> 
		<?php } ?>
	</div>
	<div id="reportrange" style="background: #fff; cursor: pointer; ">
	    <i class="fa fa-calendar"></i>&nbsp;
	    <span></span> <i class="fa fa-caret-down"></i>
	</div>
</div>

<div class="perloader_settings Performance-Matrix-section">
	<div class="preloader_container">
		<?php 
			$siteURl = get_site_url();
			//$siteURl = "https://ndyc.org/";
			//$siteURl = "https://predictivepreloader.com";
			$body = array(
			    'full_path' => $siteURl,
			    'start_date' => date('Y-m-d', strtotime(date('Y-m-d')." -1 month")),
			    'end_date' => date("d-m-Y")
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
			 
			$result = wp_remote_post( 'https://admin.predictivepreloader.com/api/analytics/tti', $args );
			$response = json_decode($result["body"]);
			//echo "<pre>"; print_r($response);
			
			if($response->data->analysis_status=='completed'){
				$count1 = 0;
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
				$labelArray = array();
				foreach ($response->data->avgReport as $key => $value) {
					$data['data']['datasets'][$count]['label'] = $key;
					if(!in_array($key, $labelArray)){
						$labelArray[] = $key;
					}
					$loadTime = array();
					foreach ($value as $k => $v) {
						$keyDate = array_search($k,$dateArray);
						$loadTime[$keyDate] = intval($v->avg_value);
					}
					$arrayVal = array_values(array_filter($loadTime));
					
					$data['data']['datasets'][$count]['data'] = $arrayVal;
					$data['data']['datasets'][$count]['borderColor'] = $color[$count];
					$data['data']['datasets'][$count]['backgroundColor'] = 'rgba(0, 0, 0, 0)';
					$data['data']['datasets'][$count]['fill'] = false;
					$data['data']['datasets'][$count]['cubicInterpolationMode'] = 'monotone';
					$count++;
				}
				if(count($labelArray)>15){
					$data['options']['responsive'] = false;
				}
				$data['options']['title']['display'] = true;
				$data['options']['test'] = 'Page Load Time';

				$data['options']['legend']['display'] = true;
				$data['options']['legend']['position'] = "right";
				//$data['options']['legend']['maxHeight'] = 500;
				$data['options']['legend']['labels']['fontStyle'] = 'bold';

				$data['options']['scales']['yAxes'][0]['scaleLabel']['display'] = true;
				$data['options']['scales']['yAxes'][0]['scaleLabel']['labelString'] = "Avg. Page Load Time (Millisecond)";
				$data['options']['scales']['xAxes'][0]['scaleLabel']['display'] = true;
				$data['options']['scales']['xAxes'][0]['scaleLabel']['labelString'] = "Date";
				/*echo "<pre>";
				print_r($data);*/
				$report = json_encode($data);
			?>
			<div id="chartContainer" style="<?php if(count($labelArray)>15){ echo 'overflow: auto'; }?>">
				<canvas id="canvas" width="<?php if(count($labelArray)>15){?>1640<?php } ?>"></canvas> 
			</div>
				
				<input type="hidden" id="dataCollect" value='<?php echo $report; ?>'>
			
				<div class="finalStats">
					<?php foreach ($response->data->boxReport as $key => $value) { ?>
						<?php if($key=="total_enb_avg"){ ?>
							<h3><?php echo intval($value); ?> <small>millisecs</small> <span>Overall Ave Enable Page Speed</span></h3>
						<?php } ?>
						<?php if($key=="total_disb_avg"){ ?>
							<h3><?php echo intval($value); ?> <small>millisecs</small> <span>Overall Ave Disable Page Speed</span></h3>
						<?php } ?>
						<?php if($key=="avg_speed_improvement"){ ?>
							<h3><?php echo intval($value); ?> <small>millisecs</small> <span>Overall Page Speed Improvement</span></h3>
						<?php } ?>
						<?php if($key=="perc_speed_improvement"){ ?>
							<h3><?php echo intval($value); ?> % <span>Percentage Page Speed Improvement</span></h3>
						<?php } ?>
					<?php } ?>
				</div>
				<table class="Matrix-table-section">
					<thead>
						<tr>
							<th width="10%">Page Path</th> 
							<th>Page Requests Enabled</th>
							<th>Avg. Page Speed Enabled</th>
							<th>Page Requests Disabled</th>
							<th>Avg. Page Speed Disabled</th>
							<th>Page Speed Improvement</th>
							<th>% Change</th>
						</tr>
					</thead>
					<tbody class="rangeList">
						<?php 
						$count = 1;
						/*echo "<pre>";
						print_r($response->data);*/
						foreach ($response->data->enbReport as $key => $value) { ?>
							<tr>
								<td><?php echo $key;  //print_r($value->enable->count); ?></td>
								<?php 
									$totalEnable = 0;
									if(isset($value->enable)){
										foreach ($value->enable as $k => $v) { 
											if($k=="total_events"){
										?>		
												<td><?php echo $v; ?></td>
										<?php 	} 
											if($k=="avg_value"){
										?>		
												<td><?php echo $v; $totalEnable = $v; ?></td>
										<?php 	} ?>
									<?php }} ?>
								<?php 
									$totalDisable = 0;
									if(isset($value->disable)){
										foreach ($value->disable as $k => $v) { 
											if($k=="total_events"){
										?>		
												<td><?php echo $v; ?></td>
										<?php 	} 
											if($k=="avg_value"){
										?>		
												<td><?php echo $v; $totalDisable = $v; ?></td>
										<?php 	} ?>
									<?php }} ?>
									<td><?php if($totalDisable>0 && $totalEnable>0){ echo $totalDisable-$totalEnable; }else{ echo "0"; } ?></td>
									<td><?php if($totalDisable>0 && $totalEnable>0){ echo round((($totalDisable-$totalEnable)*100)/$totalDisable); }else{ echo "0"; } ?></td>
							</tr>
						<?php $count++; } ?>
					</tbody>
				</table>
			<?php }else{ ?>
				<div class="comingSoon">
					<p><b>SYSTEM LEARNING: Please wait.</b> Reports will show once we have enough data</p>
				</div>
			<?php } ?>
	</div>
</div>
<input type="hidden" id="Today" value="<?php echo date("d-m-Y").",".date("d-m-Y"); ?>">
<input type="hidden" id="Yesterday" value="<?php echo date("d-m-Y",strtotime("-1 days")).",".date("d-m-Y",strtotime("-1 days")) ?>">
<input type="hidden" id="Last_7_Days" value="<?php echo date("d-m-Y",strtotime("-7 days")).",".date("d-m-Y") ?>">
<input type="hidden" id="Last_30_Days" value="<?php echo date("d-m-Y",strtotime("-30 days")).",".date("d-m-Y"); ?>">
<input type="hidden" id="This_Month" value="<?php echo date("01-m-Y").",".date("t-m-Y") ?>">
<input type="hidden" id="Last_Month" value="<?php echo date('d-m-Y', strtotime('first day of last month')).",".date('d-m-Y', strtotime('last day of last month')); ?>">

<script>
	jQuery(function() {
	    var start = moment().subtract(29, 'days');
	    var end = moment();
	    function cb(start, end) {
	        jQuery('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
	    }

	    jQuery('#reportrange').daterangepicker({
	        startDate: start,
	        endDate: end,
	        ranges: {
	           'Last 7 Days': [moment().subtract(6, 'days'), moment()],
	           'Last 30 Days': [moment().subtract(29, 'days'), moment()],
	           'This Month': [moment().startOf('month'), moment().endOf('month')],
	           'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
	        }
	    }, cb);

	    cb(start, end);

	});
	var config = JSON.parse(document.getElementById("dataCollect").value);
	window.onload = function() {
		var ctx = document.getElementById('canvas').getContext('2d');
		window.myLine = new Chart(ctx, config);
	};
	jQuery(document).on("click",".ranges ul li",function(){
		var date = jQuery(this).text();
		if(date!="Custom Range"){
			var s = date.replace(/\ /g, '_');
			var range = jQuery("#"+s).val();
			get_range_data(range,"normal");
		}
	});
	jQuery(document).on("click",".drp-buttons .applyBtn",function(){
		var range = jQuery(".drp-selected").text();
		get_range_data(range,"custom")
	});
	function get_range_data(range,type){
		jQuery.ajax({
			type:"post",
			url:"<?php echo get_site_url(); ?>/wp-admin/admin-ajax.php",
			data:{action:"change_range_data",range:range,type:type},
			success:function(data){
				var reportData = jQuery.parseJSON(data);
				jQuery(".rangeList").html(reportData.html);
				jQuery(".finalStats").html(reportData.boxReport);
				var ctx = document.getElementById('canvas').getContext('2d');
				window.myLine = new Chart(ctx, reportData.data);
			}
		});
	}
</script>