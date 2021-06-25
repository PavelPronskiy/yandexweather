<?php
/**
 * @package         pp.Module
 * @subpackage      mod_yandexweather
 * @copyright       Copyright (C) 2021 pp, Inc. All rights reserved.
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

abstract class modYandexWeatherHelper {

	private static $WEATHER = [
		'extra' => 'true',
		'lat' => '55.791102',
		'lon' => '37.634114',
		'lang' => 'ru'
	];

	private static $API_KEY;
	private static $GEO_LOCATION;
	private static $USERAGENT = 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.212 Safari/537.36';
	private static $API_URL = 'https://api.weather.yandex.ru/v2/';
	// private static $API_URL = 'https://api.weather.yandex.ru/v2/informers';
	private static $CONDITION = [
		'clear' => 'ясно',
		'partly-cloudy' => 'малооблачно',
		'cloudy' => 'облачно с прояснениями',
		'overcast' => 'пасмурно',
		'drizzle' => 'морось',
		'light-rain' => 'небольшой дождь',
		'rain' => 'дождь',
		'moderate-rain' => 'умеренно сильный дождь',
		'heavy-rain' => 'сильный дождь',
		'continuous-heavy-rain' => 'длительный сильный дождь',
		'showers' => 'ливень',
		'wet-snow' => 'дождь со снегом',
		'light-snow' => 'небольшой снег',
		'snow' => 'снег',
		'snow-showers' => 'снегопад',
		'hail' => 'град',
		'thunderstorm' => 'гроза',
		'thunderstorm-with-rain' => 'дождь с грозой',
		'thunderstorm-with-hail' => 'гроза с градом'
	];

	private static $CACHE_FILE = __DIR__ . '/weather.json';
	private static $YANDEX_ICON_URL = 'https://yastatic.net/weather/i/icons/blueye/color/svg/';
	public static $CACHE_ENABLED;
	public static $CACHE_LIFETIME;

	public static function getModuleParamsByName($name) {
		$db = \JFactory::getDbo();
		$q = $db->getQuery(true);
		$results = [];

		$q->select([
			'm.params'
		])->from('#__modules AS m')
		->where('m.module = ' . $db->quote($name));
		$db->setQuery($q);
		$r = $db->loadObject();
		return json_decode($r->params);
	}

	public static function getAjax() {

		$params = self::getModuleParamsByName('mod_yandexweather');
		$coords = explode(',', $params->coords);
		self::$WEATHER->lat = $coords[0];
		self::$WEATHER->lon = $coords[1];
		self::$API_KEY = $params->apikey;
		self::$GEO_LOCATION = $params->location;
		self::$CACHE_ENABLED = !empty($params->cache_enabled) ? (int) $params->cache_enabled : 1;
		self::$CACHE_LIFETIME = !empty($params->cache_lifetime) ? (int) $params->cache_lifetime : 1800;
		self::$API_URL = $params->mod === '0' ? self::$API_URL . 'forecast' : self::$API_URL . 'informers';

		$weather_array = self::getCachedWeather();
		return self::getWeatherParams($weather_array);
	}

	public static function getWeatherParams($array) {

		header('Content-Type: application/json');
		
		if (is_null($array)) {
			return (object) [
				'status' => 1
			];
		}

		$temp = ($array->fact->temp > 0) ? '+' . $array->fact->temp : '-' . $array->fact->temp;
		return (object) [
			'status' => 0,
			'location' => self::$GEO_LOCATION,
			'temp' => $temp . 'C',
			'icon' => self::$YANDEX_ICON_URL . $array->fact->icon . '.svg',
			'condition' => isset(self::$CONDITION[$array->fact->condition]) ? self::$CONDITION[$array->fact->condition] : 'нет данных'
		];
	}

	public static function getCachedWeather() {

		// cache enabled
		if (self::$CACHE_ENABLED == '1') {
			$time_ago = \time();
			$file_time_modified = \filemtime(self::$CACHE_FILE);
			$date_modified = $time_ago - $file_time_modified;
			
			if ($date_modified <= self::$CACHE_LIFETIME && is_file(self::$CACHE_FILE)) {
				$data = file_get_contents(self::$CACHE_FILE);

			} else {
				$data = self::getYandexPogodaArray();
				file_put_contents(self::$CACHE_FILE, $data);
			}

			if (count( (array) $data) == 0 || empty($data)) {
				$data = file_get_contents(self::$CACHE_FILE);
			}

		} else {
			$data = self::getYandexPogodaArray();
		}

		return json_decode($data);
	}

	public static function getYandexPogodaArray() {
		$ch = curl_init();
		$struct_url = self::$API_URL . '?' . http_build_query(self::$WEATHER);
		curl_setopt($ch, CURLOPT_USERAGENT, self::$USERAGENT);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [ 'X-Yandex-API-Key: ' . self::$API_KEY ]);
		curl_setopt($ch, CURLOPT_TCP_KEEPALIVE, false);
		curl_setopt($ch, CURLOPT_TCP_KEEPINTVL, 10);
		curl_setopt($ch, CURLOPT_TCP_KEEPIDLE, 10);
		curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
		curl_setopt($ch, CURLOPT_ENCODING,  "");
		curl_setopt($ch, CURLOPT_TCP_FASTOPEN,  true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10); //timeout in seconds
		curl_setopt($ch, CURLOPT_URL, $struct_url);

		$curl_result = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if ($http_code === 200) {
			return $curl_result;
		} else {
			return [];
		}
	}
}

