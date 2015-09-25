Zepto(function($){
	// load widget initially
	$.get(anattadesign_abandonedcarts.url, function(data){
		if ( $.trim(data) != '' ) {
			$('#diagram_tab').append('<li><a class="tab-item-link" title="Abandoned Carts" id="diagram_tab_abandoned_carts" href="#"><span><span title="The information in this tab has been changed." class="changed"></span><span title="This tab contains invalid data. Please solve the problem before saving." class="error"></span>Abandoned Carts</span></a></li>');
			$('#diagram_tab_content').append('<div style="display: none;" id="diagram_tab_abandoned_carts_content"><div style="margin:20px;"><p style="padding:5px 10px;" class="switcher a-right">Select Range:<select id="order_abandoned_carts_period" name="period"><option value="24h">Last 24 Hours</option><option value="7d">Last 7 Days</option><option value="1m">Current Month</option><option value="1y">YTD</option><option value="2y">2YTD</option></select></p></div>'+data+'</div>');

			// activate the abandoned carts widget tab
			$('#diagram_tab > li > a').on('click', function(e){
				e.preventDefault();
				$('#diagram_tab > li > a.active').removeClass('active');
				$(this).addClass('active');
				$('#diagram_tab_content').children().hide();
				$('#' + this.id + '_content').show();
			});
		}
	});

	// bind click for browsing previous/next months
	$('#diagram_tab_content').on('change','#order_abandoned_carts_period', function(){
		var value = $(this).val();

		// fill up widget with the requested month-year stats
		$.get(anattadesign_abandonedcarts.url+'?period='+value, function(data){
			if ( $.trim(data) != '' ) {
				$('#ac-wrapper').parent().children().slice(1).remove();
				$('#diagram_tab_abandoned_carts_content').append(data);
			}
		});

		return false;
	});

});