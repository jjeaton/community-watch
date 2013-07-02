(function ($) {
	"use strict";
	$(function () {
		// Handle reporting
		$('.cw-report-link').on('click', function(e){
			e.preventDefault();

			var $this  = $(this);
			var postid = $this.data('post-id');
			var user   = $this.data('user');
			var resp   = {};
			var data   = {
				action: 'cw_report_post',
				postid: postid,
				user: user,
				nonce: CWReportAJAX.nonce
			};

			// Make AJAX request
			$.post(CWReportAJAX.ajaxurl, data, function(response){
				try {
					resp = $.parseJSON(response);
				} catch(e) {
					console.log('Response parse error');
				}

				if ( resp.success ) {
					$('.cw-report-link').text('Reported').addClass('reported');
				} else {
					console.log( 'Failed: ' + resp.err_msg );
				}
			});
		});
	});
}(jQuery));
