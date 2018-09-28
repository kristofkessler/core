$(document).ready(function() {
	$('.daia-availability').each(function(){
		var $this = $(this);
        $.ajax({
			url:'/vufind/PAIA/availability?ppn='+jQuery(this).attr('data-ppn')+'&marcField951aValue='+jQuery(this).attr('data-marcField951aValue')+'&list='+jQuery(this).data('list')+'&mediatype='+jQuery(this).data('mediatype'),
			success:function(data) {
				$this.html(jQuery(data).find('#paia-availability'));
			}
		})
    });
	$('.electronic-availability').each(function(){
		var $this = $(this);
        $.ajax({
			url:'/vufind/PAIA/electronicavailability?ppn='+jQuery(this).attr('data-ppn')+'&site='+jQuery(this).attr('data-site')+'&openurl='+jQuery(this).data('openurl')+'&url-access='+jQuery(this).attr('data-url-access')+'&url_access_level='+jQuery(this).attr('data-url_access_level')+'&gvklink='+jQuery(this).attr('data-gvklink')+'&doi='+jQuery(this).attr('data-doi')+'&list='+jQuery(this).data('list')+'&mediatype='+jQuery(this).data('mediatype'),
			success:function(data) {
				$this.html(jQuery(data).find('#electronic-availability'));
			}
		})
    });




 });