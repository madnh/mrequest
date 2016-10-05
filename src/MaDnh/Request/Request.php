<?php
namespace MaDnh\Request;

class Request
{
    const AUTH_MODE_BASIC = CURLAUTH_BASIC;
    const AUTH_MODE_DIGEST = CURLAUTH_DIGEST;
    const AUTH_MODE_GSSNEGOTIATE = CURLAUTH_GSSNEGOTIATE;
    const AUTH_MODE_NTLM = CURLAUTH_NTLM;
    const AUTH_MODE_ANY = CURLAUTH_ANY;
    const AUTH_MODE_ANYSAFE = CURLAUTH_ANYSAFE;

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
        'method' => 'GET',
        'data' => '',
        'cookie_send' => '',
        'cookie_save' => '',
        'cookie' => [],
        'use_cookie' => true,
        'fail_on_error' => true,
        'follow_location' => true,
        'return_transfer' => true,
        'header' => array(),
        'return_header' => true,
        'verifying_ssl' => false,
        'use_proxy' => false,
        'proxy' => '',
        'is_http_proxy' => true,
        'referer' => true,
        'timeout' => 3600,
        'useragent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.130 Safari/537.36',
        'raw' => array()
    );

    /**
     * Initialize cURL handle and options
     * @param array $config
     */
    public function __construct(array $config = array())
    {
        $this->config = array_merge(array(),
            self::$_global_config,
            !empty($config) ? $config : array());
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function config($name, $value)
    {
        $this->config[$name] = $value;
        return $this;
    }

    /**
     * Return cloned of config
     * @return array
     */
    public function getConfig()
    {
        return array_merge(array(), $this->config);
    }

    /**
     * @param array [$header = array()]
     * @return $this
     */
    public function setHeader($headers = array())
    {
        $this->config['header'] = $headers;
        return $this;
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function addHeader($name, $value)
    {
        $this->config['header'][$name] = $value;
        return $this;
    }

    /**
     * @param array [$cookie = array()]
     * @return $this
     */
    public function setCookie($cookies = array())
    {
        $this->config['cookie'] = $cookies;
        return $this;
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function addCookie($name, $value)
    {
        $this->config['use_cookie'] = true;
        $this->config['cookie'][$name] = $value;
        return $this;
    }

    /**
     * @param bool [$use_cookie = true]
     * @return $this
     */
    public function useCookie($use_cookie = true)
    {
        $this->config['use_cookie'] = $use_cookie;
        return $this;
    }

    /**
     * @param array [$raw_config = true]
     * @return $this
     */
    public function setRawConfig($raw_config = array())
    {
        $this->config['raw'] = $raw_config;
        return $this;
    }

    /**
     * @param $option
     * @param $value
     * @return $this
     */
    public function rawConfig($option, $value)
    {
        $this->config['raw'][$option] = $value;
        return $this;
    }

    /**
     * @param $username
     * @param $password
     * @return $this
     */
    public function setBasicAuthentication($username, $password)
    {
        $this->setHttpAuthMode(self::AUTH_MODE_BASIC);
        $this->rawConfig(CURLOPT_USERPWD, $username . ':' . $password);
        return $this;
    }

    /**
     * @param $mode
     * @return $this
     */
    public function setHttpAuthMode($mode)
    {
        $this->rawConfig(CURLOPT_HTTPAUTH, $mode);
        return $this;
    }

    /**
     * @param bool [$is_ajax_request = true]
     * @return $this
     */
    public function ajaxRequest($is_ajax_request = true)
    {
        if ($is_ajax_request) {
            $this->addHeader('X-Requested-With', 'XMLHttpRequest');
        } else {
            unset($this->config['header']['X-Requested-With']);
        }

        return $this;
    }

    /**
     * @param array [$data = array()]
     * @return $this
     */
    public function setData($data = array())
    {
        $this->config['data'] = $data;
        return $this;
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function addData($name, $value)
    {
        $this->config['data'][$name] = $value;
        return $this;
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

        curl_setopt($curl_instance, CURLOPT_SSL_VERIFYPEER, $config['verifying_ssl']);

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

        if (empty($config['cookie']) && empty($config['cookie_send']) && empty($config['cookie_save'])) {
            $config['use_cookie'] = false;
        }
        if ($config['use_cookie']) {
            curl_setopt($curl_instance, CURLOPT_COOKIE, http_build_query($config['cookie'], '', '; '));

            if (!empty($config['cookie_send'])) {
                curl_setopt($curl_instance, CURLOPT_COOKIEFILE, $config['cookie_send']);
            }
            if (!empty($config['cookie_save'])) {
                curl_setopt($curl_instance, CURLOPT_COOKIEJAR, $config['cookie_save']);
            }
        }

        if (!empty($config['header'])) {
            $headers = array();
            foreach ($config['header'] as $key => $value) {
                $headers[] = $key . ': ' . $value;
            }
            curl_setopt($curl_instance, CURLOPT_HTTPHEADER, $headers);
        }
        foreach ($config['raw'] as $key => $value) {
            curl_setopt($curl_instance, $key, $value);
        }


        return array(
            'ch' => $curl_instance,
            'config' => $config
        );
    }

    /**
     * Do Request
     * @param $ch
     * @param $config
     * @return Response
     * @throws \Exception
     */
    protected function _doRequest($ch, $config)
    {
        $result = new Response();
        if (empty($config['url'])) {
            throw new \Exception('URL is missing');
        }
        $url = $config['url'];
        curl_setopt($ch, CURLOPT_URL, $url);
        $result->start = time();
        $response = curl_exec($ch);
        $result->end = time();
        if ($response === false) {
            $result->error = array(
                'code' => curl_errno($ch),
                'message' => curl_error($ch)
            );
        }
        curl_close($ch);
        if ($result->error === false && $config['return_transfer']) {
            if ($config['return_header']) {
                $header_end_pos = strpos($response, "\r\n\r\n");
                if ($header_end_pos !== false) {
                    $result->headers = ltrim(substr($response, 0, $header_end_pos));
                    $result->response = substr($response, $header_end_pos + 4);
                } else {
                    $result->response = $response;
                }
            } else {
                $result->response = $response;
            }
        }

        return $result;
    }

    /**
     * Execute a request with config
     * @param array [$config = array()]
     * @return Response
     * @throws \Exception
     */
    public function request($config = array())
    {
        $config = array_merge($this->config, $config);
        $config_result = $this->_doConfig($config);
        $ch = $config_result['ch'];
        $config = $config_result['config'];
        if (empty($config['method'])) {
            $config['method'] = 'GET';
        }
        $config['method'] = strtoupper($config['method']);

        if ($config['method'] === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if (is_array($config['data']) || is_object($config['data'])) {
                $config['data'] = http_build_query($config['data']);
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $config['data']);
        } else {
            $config['url'] = $this->_addDataToUrl($config['url'], $config['data']);

            if ($config['method'] !== 'GET') {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $config['method']);
            }
        }

        return $this->_doRequest($ch, $config);
    }

    /**
     * @param $url
     * @param array $data
     * @return string
     */
    protected function _addDataToUrl($url, $data = array())
    {
        if (!empty($data)) {
            $url_query = parse_url($url, PHP_URL_QUERY);
            if (function_exists('http_build_url')) {
                $url = http_build_url($url, array(
                    'query' => implode('&', array(
                        $url_query,
                        http_build_query($data)
                    ))
                ));
            } else {
                $url .= empty($url_query) ? '?' : '&';
                $url .= http_build_query($data);
            }
        }

        return $url;
    }

    /**
     * Execute a GET request
     * @param string|array $data
     * @return Response
     */
    public function get($data = array())
    {
        $new_config = array(
            'method' => 'GET'
        );
        if (!empty($data)) {
            $new_config['data'] = $data;
        }

        return $this->request($new_config);
    }

    /**
     * Execute a POST request
     * @param array $data
     * @return Response
     */
    public function post($data = array())
    {
        $new_config = array(
            'method' => 'POST'
        );
        if (!empty($data)) {
            $new_config['data'] = $data;
        }

        return $this->request($new_config);
    }

    /**
     * Execute a PUT request
     * @param array $data
     * @return Response
     */
    public function put($data = array())
    {
        $new_config = array(
            'method' => 'PUT'
        );
        if (!empty($data)) {
            $new_config['data'] = $data;
        }

        return $this->request($new_config);
    }

    /**
     * Execute a DELETE request
     * @param array $data
     * @return Response
     */
    public function delete($data = array())
    {
        $new_config = array(
            'method' => 'DELETE'
        );
        if (!empty($data)) {
            $new_config['data'] = $data;
        }

        return $this->request($new_config);
    }


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

    public static function url($url)
    {
        $instance = new self();
        $instance->config['url'] = $url;

        return $instance;
    }
}
