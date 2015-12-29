Class mUrl dùng để thực hiện các request HTTP, sử dụng thư viện cURL của PHP.

Các request được thực hiện thông qua một cấu hình dạng mảng. mURL hỗ trợ cấu hình ở 3 mức độ:
    - Global config: cấu hình toàn class
    - Instance config: cấu hình cho object instance
    - Request config: cấu hình cho mỗi config

Config có thể kế thừa từ config ở cấp độ cao hơn, theo sơ đồ sau: Global <- Instance <- Request



Cấu hình mặc định của Global:
-------------------------------------------------------------------------------------
    - url => '', // Url truy vấn
    - request_type => 'GET', // Method
    - data => '', // Dữ liệu gửi kèm khi truy vấn, có thể là chuỗi hoặc array (với key là tên biến, value là giá trị), nếu request_type là GET sẽ append vào url
    - cookie_send => '', // File chứa cookie dùng để gửi đi khi truy vấn
    - cookie_save => '', // File chứa cookie dùng để lưu sau khi truy vấn
    - use_cookie => true, // Sử dụng cookie?
    - fail_on_error => true, // Dừng khi có lỗi xảy ra
    - follow_location => true, // Khi request mà target yêu cầu redirect tới một url khác thì vẫn follow
    - return_transfer => true, // Trả về kết quả truy vấn thay vì print ra
    - return_header => true, // Trả về header của response
    - verifying_ssl => false, // Sử dụng SSL, mặc định false để bỏ qua SSL khi muốn truy vấn bình thường như khi không dùng SSL
    - use_proxy => false, // Sử dụng proxy?
    - proxy => '', // Thông tin proxy
    - is_http_proxy => true, // Proxy là dạng HTTP?
    - referer => true, // Referer của request
    - timeout => 3600, // Request timeout
    - useragent => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.130 Safari/537.36',



Kết quả thực hiện request có dạng:
-------------------------------------------------------------------------------------
    - request: Mảng chứa cấu hình thực hiện request
    - header: Chuỗi chứa thông tin header của response
    - response: Chuỗi kết quả thực hiện request
    - error: Thông tin lỗi. False nếu không có lỗi, ngược lại sẽ là một mảng với 2 key:
        + no: Mã lỗi (do cURL quy định)
        + message: Chú thích lỗi (do cURL chú thích)

    - start: UNIX timestamp bắt đầu thực hiện request
    - end: UNIX timestamp kết thúc request

======================================================================================
CÁCH DÙNG
======================================================================================

<Setup Global config nếu cần thiết>
<Tạo instance mới của class (cùng với config nếu cần thiết)>
<Setup Instance config nếu cần thiết>
<Thực hiện các request, gán các thông số và request config nếu cần thiết>

Các cách thực hiện 1 request:
    - Method request(config): Thực hiện request với 1 config (tùy chọn)
    - Method get(url, data, config): Thực hiện request GET với URL, data (tùy chọn) và 1 config (tùy chọn)
    - Method post(url, data, config): Thực hiện request POST với URL, data và 1 config (tùy chọn)


======================================================================================
Ví dụ:
======================================================================================


Cấu hình Global
-----------------------------------------------------------------
define('CURDIR', dirname(__FILE__));
mUrl::globalConfig('cookie_send', CURDIR.'/cookie.txt');
mUrl::globalConfig('cookie_save', CURDIR.'/cookie.txt');



Tạo instance và cấu hình
-----------------------------------------------------------------
$obj = new mUrl(array(
    'timeout' => 10
));


Thực hiện 1 request bằng method request
-----------------------------------------------------------------
$result = $obj->request(array(
    'url' => 'http://www.w3schools.com/'
));
print_r($result);



Thực hiện 1 request bằng method get
-----------------------------------------------------------------
$result = $m->get('http://www.w3schools.com/');

















