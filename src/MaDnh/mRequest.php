<?php
namespace MaDnh;
class mRequest
{
    /**
     * Instance's config
     * @var array
     */
    protected $config = array();

    /**
     * Static config
     * @var array
     */
    protected static $_global_config = array(
        'url' => '',
        'request_type' => 'GET',
        'data' => '',
        'cookie_send' => '',
        'cookie_save' => '',
        'use_cookie' => true,
        'fail_on_error' => true,
        'follow_location' => true,
        'return_transfer' => true,
        'return_header' => true,
        'verifying_ssl' => false,
        'use_proxy' => false,
        'proxy' => '',
        'is_http_proxy' => true,
        'referer' => true,
        'timeout' => 3600,
        'useragent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.130 Safari/537.36',
    );

    /**
     * Setup global config
     * @param string|array $config_name
     * @param null $value
     */
    public static function globalConfig($config_name, $value = null)
    {
        if (is_array($config_name)) {
            self::$_global_config = array_merge(self::$_global_config, $config_name);
        } else {
            self::$_global_config[$config_name] = $value;
        }
    }

    /**
     * Return static config
     * @return array
     */
    public static function getGlobalConfig()
    {
        return self::$_global_config;
    }

    /**
     * Initialize cURL handle and options
     * @param array $config
     */
    public function __construct(array $config = array())
    {
        $this->config = self::$_global_config;
        $config_result = $this->_doConfig($config);
        $this->config = $config_result['config'];
    }

    /**
     * Config instance
     * @param $config_name
     * @param null $value
     */
    public function config($config_name, $value = null)
    {
        if (!is_array($config_name)) {
            $config_name = array(
                $config_name => $value
            );
        }

        $config_result = $this->_doConfig($config_name);
        $this->config = $config_result['config'];
    }

    /**
     * Return config
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    protected function _doConfig($config)
    {
        $curl_instance = curl_init();

        $config = array_merge($this->config, $config);
        if ($config['url']) {
            curl_setopt($curl_instance, CURLOPT_URL, $config['url']);
        }

        // Set error in case http return code bigger than 400
        curl_setopt($curl_instance, CURLOPT_FAILONERROR, $config['fail_on_error']);
        // Allow redirects
        curl_setopt($curl_instance, CURLOPT_FOLLOWLOCATION, $config['follow_location']);
        // Return into a variable rather than displaying it
        curl_setopt($curl_instance, CURLOPT_RETURNTRANSFER, $config['return_transfer']);
        // Default connection timeout
        curl_setopt($curl_instance, CURLOPT_TIMEOUT, $config['timeout']);
        //Default useragent
        curl_setopt($curl_instance, CURLOPT_USERAGENT, $config['useragent']);

        if ($config['return_header']) {
            curl_setopt($curl_instance, CURLOPT_HEADER, true);
        }

        if ($config['use_proxy']) {
            curl_setopt($curl_instance, CURLOPT_PROXY, $config['proxy']);
            if ($config['is_http_proxy']) {
                curl_setopt($curl_instance, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
            } else {
                curl_setopt($curl_instance, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
            }
        }

        if ($config['referer'] === true) {
            $config['referer'] = $config['url'];
        }

        if (empty($config['cookie_send']) && empty($config['cookie_save'])) {
            $config['use_cookie'] = false;
        }
        if ($config['use_cookie']) {
            if ($config['cookie_send']) {
                curl_setopt($curl_instance, CURLOPT_COOKIEFILE, $config['cookie_send']);
            }
            if ($config['cookie_save']) {
                curl_setopt($curl_instance, CURLOPT_COOKIEJAR, $config['cookie_save']);
            }
        }
        return array(
            'ch' => $curl_instance,
            'config' => $config
        );
    }

    protected function _doRequest($ch, $config)
    {
        $result = array(
            'request' => $config,
            'header' => '',
            'response' => '',
            'error' => false,
            'start' => 0,
            'end' => 0
        );


        if (empty($config['url'])) {
            throw new \Exception('URL is invalid');
        }
        $url = $config['url'];

        if (strtoupper($config['request_type']) == 'GET') {
            if (empty($this->config['url'])) {
                $this->config['url'] = $url;
            }
            if (!empty($config['data'])) {
                if (is_string($config['data'])) {
                    if ($config['data'][0] != '?') {
                        $url .= '?';
                    }
                    $url .= $config['data'];

                } else {
                    $tmp_data = array();
                    foreach ($config['data'] as $key => $value) {
                        $tmp_data[] = $key . '=' . urlencode($value);
                    }
                    $url .= '?' . implode('&', $tmp_data);
                }
            }

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        } else if (strtoupper($config['request_type']) == 'POST') {
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $config['data']);


        }


        $result['start'] = time();
        $response = curl_exec($ch);
        $result['end'] = time();
        if ($response === false) {
            $result['error']['no'] = curl_errno($ch);
            $result['error']['message'] = curl_error($ch);
        }
        curl_close($ch);
        if ($result['error'] === false && $config['return_transfer']) {
            if ($config['return_header']) {
                $header_end_pos = strpos($response, "\r\n\r\n");
                if ($header_end_pos !== false) {
                    $result['header'] = ltrim(substr($response, 0, $header_end_pos));
                    $result['response'] = substr($response, $header_end_pos + 4);
                } else {
                    $result['response'] = $response;
                }
            } else {
                $result['response'] = $response;
            }
        }

        return $result;
    }

    /**
     * Execute a request with config
     * @param array $config
     * @return array
     * @throws \Exception
     */
    public function request($config = array())
    {
        $config_result = $this->_doConfig($config);
        return $this->_doRequest($config_result['ch'], $config_result['config']);
    }

    /**
     * Execute a GET request
     * @param $url
     * @param string|array $data
     * @param array $config
     * @return array
     */
    public function get($url, $data = array(), array $config = array())
    {
        return $this->request(array_merge($config, array(
            'url' => $url,
            'data' => $data,
            'request_type' => 'GET'
        )));
    }

    /**
     * Execute a POST request
     * @param string $url
     * @param string|array $data
     * @param array $config
     * @return array
     */
    public function post($url, $data, array $config = array())
    {
        return $this->request(array_merge($config, array(
            'url' => $url,
            'data' => $data,
            'request_type' => 'POST'
        )));
    }
}