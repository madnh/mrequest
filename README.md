A cURL wrapper

# Config
Request support 3 ways of config:
- Global config
- Instance's config
- Request's config

Inherit: Global config <- Instance's config <- Request's config

## Default config
- url: request's URL
- request_type: request method, default is *'GET'*
- data: request data, string or array, default is empty string
- use_cookie: use cookie? default is **true**
- cookie: custom cookie to request, default is **empty array**
- cookie_send: Path of cookie file, use when send request
- cookie_save: Path of cookie file, use to store cookie after every request
- fail_on_error: stop on error? default is **true**
- follow_location: follow any redirect? default is **true**
- return_transfer: return response instead of print it out, default is **true**
- header: request header, array, default is **empty array**
- return_header: return response's header
- verifying_ssl: default is **false**
- use_proxy: default is **false**
- proxy: proxy's info
- is_http_proxy: default is **true**
- referer: default is **true**
- timeout: request timout, default is **3600**
- useragent: useragent info of request, default is **'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.130 Safari/537.36'**

## Global config
```php
Request::globalConfig('cookie_send', __DIR__.'/cookie.txt');
Request::globalConfig('cookie_save', __DIR__.'/cookie.txt');
```


# Response
- request: request config, array
- header: response's header, string
- response: response, string
- error: False or array of
    + no: cURL's error number
    + message: error message

- start: UNIX timestamp when request start
- end: UNIX timestamp when request complete

# Constant

- AUTH_MODE_BASIC
- AUTH_MODE_DIGEST
- AUTH_MODE_GSSNEGOTIATE
- AUTH_MODE_NTLM
- AUTH_MODE_ANY
- AUTH_MODE_ANYSAFE

# How to use

1. Setup global config, if need
2. Create new instance, which config if need
3. Setup instance config
4. Make request, requets may include config if need

## Config methods
- globalConfig(config_name: string or array of configs, [value = null])
- getGlobalConfig()
- getConfig(): export config

## Request Methods
- request(config)
- get(url, [data, [config])
- post(url, [data, [config])
- put(url, [data, [config])
- delete(url, [data, [config])

## Other methods
- setHeader(headers), set request headers, return instance
- addHeader(name, value): add a request header, return instance
- setCookie(cookies), set request cookies, return instance
- addCookie(name, value), set a request cookie value, return instance
- setData(data): set request data, return instance
- addData(name, value): add a request data, return instance
- rawConfig(CURL Options instance, value), set config, return instance
- setBasicAuthentication(username, password), set basic auth info, return instance
- setHttpAuthMode(mode): set auth mode. Return instance. Support:
    - AUTH_MODE_BASIC
    - AUTH_MODE_DIGEST
    - AUTH_MODE_GSSNEGOTIATE
    - AUTH_MODE_NTLM
    - AUTH_MODE_ANY 
    - AUTH_MODE_ANYSAFE 

# Examples
Create new instance
```php
$obj = new Request(array(
    'timeout' => 10
));
$result = $obj->request(array(
    'url' => 'http://www.w3schools.com/'
));
print_r($result);
```

Make another request
```php
$result = Request::url($_SERVER['HTTP_HOST'] . '/test/target.php')
        ->addData('name', 'Tom')
        ->addData('old', 16)
        ->setCookie(array(
            'is_admin' => true
        ))
        ->ajaxRequest()
        ->post();
```
















