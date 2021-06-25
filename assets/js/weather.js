(function ($, window, document) {
	$.fn.weatherHelper = function(options) {

		var defaults = {};
		var _this = $(this);
		options = $.extend(defaults, options);
		var refresh = options.refresh * 60;
		var request = {
			'option' : 'com_ajax',
			'module' : 'yandexweather',
			'format' : 'json'
		};

		var constructor = {
			getWeather: function() {
				return $.ajax({
					type: 'POST',
					data: request,
					success: function(data) {
						var res = data.data;

						if (res.status == 1) {
							return false;	
						}

						$(_this).html('');

						$('<div/>', {
							class: 'weather-item',
							html: '<div class="weather-temp-wrap">' +
									'<div class="weather-temp">' + res.temp + '</div>' +
									'<div class="weather-icon"><img src="' + res.icon + '" /></div>' +
								'</div>' +
								'<div class="weather-condition">' + res.condition + '</div>' +
								'<div class="weather-location">' + res.location + '</div>'
						}).appendTo(_this);
					}
				});
			},
			initialize: function() {
				setInterval(function() {
					constructor.getWeather();
				}, refresh);
				
				constructor.getWeather();
			}
		};

		return constructor.initialize();
	};


})(jQuery, window, document);
