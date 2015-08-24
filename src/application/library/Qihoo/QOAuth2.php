<?php
/**
 * Provides access to the QIHOO360 Platform.  This class provides
 * a majority of the functionality needed.
 */
class Qihoo_QOAuth2
{

	private $_clientId         = ""; // api key
	private $_clientSecret     = ""; // app secret
	private $_redirectUri      = ""; // call back
	// Set up the API root URL.
	private $_host             = 'https://openapi.360.cn';
	private $_authorizeURL     = 'https://openapi.360.cn/oauth2/authorize';
	private $_accessTokenURL   = 'https://openapi.360.cn/oauth2/access_token';

	public function __construct($clientId, $clientSecret, $accessToken)
	{
		$this->http = new QHttp();

		$this->_clientId       = $clientId;
		$this->_clientSecret   = $clientSecret;
		$this->_accessToken    = $accessToken;
	}


	/**
	 * Get API root URL
	 *
	 * @param string $name     url name.
	 *
	 * @return a string of url.
	 */
	public function getURL($name)
	{
		switch ($name)
		{
			case 'host':
				return $this->_host;
			case 'authorize':
				return $this->_authorizeURL;
			case 'accesstoken':
				return $this->_accessTokenURL;
		}
	}

	/**
	 * Make authorize URL for request authorize code
	 *
	 * @param string $response_type
	 *                 = 'code'     Get authorize code.
	 *                 = 'token'    It's Implicit Grant mode.
	 *
	 * @return a new access token and refresh token.
	 */
	public function getAuthorizeURL($responseType, $redirectUri, $scope=null, $state=null, $display=null)
	{
		$data = array(
				'client_id'         => $this->_clientId,
				'response_type'     => $responseType,
				'redirect_uri'      => $redirectUri,
		);
		if(!empty($scope))      $data['scope'] = $scope;
		if(!empty($state))      $data['state'] = $state;
		if(!empty($display))    $data['display'] = $display;
		$query = $this->http->buildHttpQuery($data);
		return $this->_authorizeURL . "?{$query}";
	}

	/**
	 * Get access token by refresh token
	 *
	 * @param string $code     Authorized Code get by send HTTP Authorize request.
	 *
	 * @return a new access token and refresh token.
	 */
	public function getAccessTokenByCode($code, $redirectUri)
	{
		$data = array(
				'grant_type'       => "authorization_code",
				'code'             => $code,
				'client_id'        => $this->_clientId,
				'client_secret'    => $this->_clientSecret,
				'redirect_uri'     => $redirectUri,
				'scope'            => 'basic'
		);

		$request = $this->call('get', $this->_accessTokenURL, $data);
		return $request;
	}


	/**
	 * Get access token by refresh token
	 *
	 * @param string $refresh_token     A string of refresh token.
	 * @param string $scope             Scope limit.
	 *
	 * @return a new access token and refresh token.
	 */
	function getAccessTokenByRefreshToken($refresh_token, $scope)
	{
		$data = array(
				'grant_type'    => "refresh_token",
				'refresh_token' => $refresh_token,
				'client_id'     => $this->_clientId,
				'client_secret' => $this->_clientSecret,
				'scope'         => $scope,
		);
		$request = $this->call('get', $this->_accessTokenURL, $data);
		return $request;
	}

	/**
	 * Make an POST/GET request
	 *
	 * @param string $method  It's "GET" or "POST".
	 * @param string $url     A request url like "https://example.com".
	 * @param array  $data    An array to make query string like "example1=&example2=" .
	 *
	 * @return API results.
	 */
	public function call($method, $url, $data = array())
	{
		return $this->http->$method($url, $data);
	}

}


/**
 * Provides http methods.
 */
class QHttp
{
	// Respons format.
	private $_format         = 'json';

	// Decode returned json data.
	private $_decodeJson     = TRUE;

	private $_connectTimeOut = 30;

	private $_timeOut        = 30;

	private $_userAgent      = 'QIHOO360 PHPSDK API v0.0.1';

	/**
	 * Make an POST request.
	 *
	 * @param string $url      A request url like "https://example.com".
	 * @param array  $data     An array to make query string like "example1=&example2=" .
	 *
	 * @return API results.
	 */
	public function post($url, $data = array())
	{
		$query = "";

		$query = $this->buildHttpQuery($data);


		$response = $this->makeRequest($url,'POST', $query);
		if ('json' === $this->_format && $this->_decodeJson) {
			return json_decode($response, true);
		}
		return $response;
	}

	/**
	 * Make an GET request.
	 *
	 * @param string $url     A request url like "https://example.com".
	 * @param array  $data     An array to make query string like "example1=&example2=" .
	 *
	 * @return API results.
	 */
	public function get($url, $data = array())
	{
		if (!empty($data)) {
			$url .= "?".$this->buildHttpQuery($data);
		}
		//$response = file_get_contents($url);
		$response = $this->makeRequest($url,'GET');
		if ('json' === $this->_format && $this->_decodeJson) {
			return json_decode($response, true);
		}
	}

	/**
	 * Make an HTTP request.
	 *
	 * @param string $url        A request url like "https://example.com/xx.json?example1=&example2=".
	 * @param string $method     Request method is "GET" or "POST".
	 * @param string $postfields A query string post to $url.
	 * @param bool    $multi.
	 *
	 * @return API results.
	 */
	public function makeRequest($url, $method, $postfields = NULL) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
		if ('POST' === $method) {
			curl_setopt($ch, CURLOPT_POST, 1);
			if (!empty($postfields)) {
				curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
			}
		}
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->_connectTimeOut);
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->_timeOut);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->_userAgent);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		$output = curl_exec($ch);
		curl_close($ch);
		return $output;
	}


	/**
	 * Build HTTP Query.
	 *
	 * @param array $params Name => value array of parameters.
	 *
	 * @return string HTTP query.
	 */
	public function buildHttpQuery(array $params)
	{
		if (empty($params)) {
			return '';
		}

		$keys   = $this->urlencode(array_keys($params));
		$values = $this->urlencode(array_values($params));

		$params = array_combine($keys, $values);

		uksort($params, 'strcmp');

		$pairs = array();
		foreach ($params as $key => $value)
		{
			$pairs[] =  $key . '=' . $value;
		}

		return implode('&', $pairs);
	}

	/**
	 * URL Encode.
	 *
	 * @param mixed $item string or array of items to url encode.
	 *
	 * @return mixed url encoded string or array of strings.
	 */
	public function urlencode($item)
	{
		static $search  = array('%7E');
		static $replace = array('~');

		if (is_array($item)) {
			return array_map(array(&$this, 'urlencode'), $item);
		}

		if (is_scalar($item) === false) {
			return $item;
		}

		return str_replace($search, $replace, rawurlencode($item));
	}

	/**
	 * URL Decode.
	 *
	 * @param mixed $item Item to url decode.
	 *
	 * @return string URL decoded string.
	 */
	public function urldecode($item)
	{
		if (is_array($item)) {
			return array_map(array(&$this, 'urldecode'), $item);
		}

		return urldecode($item);
	}

}