jQuery(document).ready(function(){
	jQuery(document).on("click",".google_analytics",function(){
		var check = jQuery(this).val();
		if(check==0){
			jQuery(".analytics_type").hide();
			jQuery(".preloder_store").show();
		}else{
			jQuery(".analytics_type").hide();
			jQuery(".no-preloader_container").show();
		}
	});
	jQuery(document).on("click",".saveLicense",function(){
		var predictive_license_key = jQuery(".predictive_license_key").val();
		if(predictive_license_key!=''){
			jQuery.ajax({
				type:"post",
				url:jQuery(".adminUrl").val(),
				data:{action:'validate_license_key',predictive_license_key:predictive_license_key},
				success:function(data){
					var result = JSON.parse(data);
					if(result.msg=='success'){
						jQuery(".licenseMsg").removeClass("errormsg");
						jQuery(".licenseMsg").addClass("successmsg");
						jQuery(".licenseMsg").html("License key is valid");
						jQuery(".saveLicense").hide();
						jQuery('.predictive_license_key').attr('readonly', true);
					}else{
						jQuery(".licenseMsg").removeClass("successmsg");
						jQuery(".licenseMsg").addClass("errormsg");
						jQuery(".licenseMsg").html("License key is not valid");
					}
				}
			});
		}
	});

});