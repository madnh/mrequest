<?php
namespace MaDnh;
class mRequest
{
    CONST METHOD_GET = 'GET';
    CONST METHOD_POST = 'POST';
    CONST METHOD_PUT = 'PUT';
    CONST METHOD_DELETE = 'DELETE';

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
     * Get default global config
     * @return array
     */
    public static function defaultConfig()
    {
        return array(
            'url' => '',
            'method' => 'GET',
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
    }

    /**
     * Reset global config to default
     */
    public static function resetGlobal()
    {
        self::$_global_config = self::defaultConfig();
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
        $this->config = array_merge(array(), self::$_global_config);
        $config_result = $this->_doConfig($config);
        $this->config = $config_result['config'];
    }

    /**
     * Reset config
     * @param bool $fresh If true then reset to default config, else use global config
     */
    public function reset($fresh = false)
    {
        if ($fresh) {
            $this->config = array_merge(array(), self::defaultConfig());
        } else {
            $this->config = array_merge(array(), self::$_global_config);
        }
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
            throw new \Exception('URL is invalid');
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
     * @param array $config
     * @return Response
     * @throws \Exception
     */
    public function request($config = array())
    {
        $config_result = $this->_doConfig($config);
        $ch = $config_result['ch'];
        $config = $config_result['config'];
        if (empty($config['method'])) {
            $config['method'] = 'GET';
        }
        $config['method'] = strtoupper($config['method']);
        switch ($config['method']) {
            case 'GET':
            case 'PUT':
            case 'DELETE':
                $config['url'] = $this->_addDataToUrl($config['url'], $config['data']);
                if ($config['method'] !== 'GET') {
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $config['method']);
                }
                break;

            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                if (is_array($config['data']) || is_object($config['data'])) {
                    $config['data'] = http_build_query($config['data']);
                }
                curl_setopt($ch, CURLOPT_POSTFIELDS, $config['data']);
                break;

            default:
                throw new \Exception('Invalid method');
        }

        return $this->_doRequest($ch, $config);
    }

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
     * @param $url
     * @param string|array $data
     * @param array $config
     * @return Response
     */
    public function get($url, $data = array(), array $config = array())
    {
        return $this->request(array_merge($config, array(
            'method' => 'GET',
            'data' => $data,
            'url' => $url
        )));
    }

    /**
     * Execute a POST request
     * @param string $url
     * @param string|array|object $data
     * @param array $config
     * @return Response
     */
    public function post($url, $data, array $config = array())
    {
        return $this->request(array_merge($config, array(
            'method' => 'POST',
            'data' => $data,
            'url' => $url
        )));
    }

    public function put($url, $data = array(), array $config = array())
    {
        return $this->request(array_merge($config, array(
            'method' => 'PUT',
            'data' => $data,
            'url' => $url
        )));
    }

    public function delete($url, $data = array(), array $config = array())
    {
        return $this->request(array_merge($config, array(
            'method' => 'DELETE',
            'data' => $data,
            'url' => $url
        )));
    }

}