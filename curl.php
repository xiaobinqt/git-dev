<?php
/**
 * Created by PhpStorm.
 * User: v_bivwei
 * Date: 2020/5/20
 * Time: 22:57
 */

class CurlWrapper
{
    const TIMEOUT = 15;
    const HTTP_VERSION = 'CURL_HTTP_VERSION_1_1';
    const USER_AGENT = 'jimu_http_curl_1.1';

    protected $_cofffffffffffffffffffffffffffffffffffffffffffnfig;
    protected $_cookies;

    private $refer = null;

    private $errMsg = null;
    private $charset = null;
    private $http_code = null;

    public function  __construct()
    {
        $this->_config = array(
            'timeout'     => self::TIMEOUT,
            'httpversion' => self::HTTP_VERSION,
            'useragent'   => self::USER_AGENT);
    }



    /**
     * 设置cookies信息
     * @param Array $cookieMap 参数关联数组
     * @return $this
     */
    public function setCookie($cookieMap)
    {
        $cookies = '';
        foreach ($cookieMap as $k => $v)
        {
            $cookies .= "$k=$v;";
        }
        $this->_cookies = $cookies;

        return $this;
    }

    /**
     * 配置client
     * timeout 设定超时以及curl的执行时间
     * @param array $config
     * @return $this
     */
    public function setConfig($config)
    {
        $this->_config = array_merge($this->_config, $config);

        return $this;
    }

    public function getConfig()
    {

        return  $this->_config;
    }

    public function getLastError()
    {
        return $this->errMsg;
    }

    /**
     * @return null
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * @param null $refer
     */
    public function setRefer($refer)
    {
        $this->refer = $refer;
    }

    /**
     * @return null
     */
    public function getRefer()
    {
        return $this->refer;
    }

    public function getHttpCode()
    {
        return $this->http_code;
    }

    /**
     * 获取请求返回的最后Location (不常用的单独给出)
     * @param $url
     * @return mixed
     */
    public function getLocation($url)
    {
        if (empty($url)) return false;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $this->_config['timeout'] * 1000);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $this->_config['timeout'] * 1000);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, $this->_config['httpversion']);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->_config['useragent']);
        curl_setopt($ch, CURLOPT_NOBODY, 1); //无需页面内容
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //不直接输出
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); //返回最后的Location
        curl_exec($ch);
        $info = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
        return $info;
    }

    public  function get($url, $timeout = self::TIMEOUT){

        if (empty($url))
        {
            return false;
        }
        $ch = curl_init();
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        }


        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPGET, 1);
        curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $timeout * 1000);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $timeout * 1000);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, self::HTTP_VERSION);
        curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); //返回最后的Location

        if ($this->getRefer() != null)
        {
            curl_setopt($ch, CURLOPT_REFERER, $this->getRefer());
        }

        $response = curl_exec($ch);
        $aStatus = curl_getinfo($ch);
        curl_close($ch);

        if(isset($aStatus["http_code"]))
        {
            $this->http_code=$aStatus["http_code"];
        }

        return $response;

    }




    /**
     * @param        $url
     * @param string $postData 需要post的数据，如果设置就用POST方式，否则用GET方式
     * @param string $ip
     * @param string $method
     * @return bool|mixed
     */
    public function request($url, $postData = '', $ip = '', $method = 'post')
    {
        if (empty($url))
        {
            return false;
        }

        $ch = curl_init(); //初始化CURL句柄

        if ($ip)
        {
            $info = parse_url($url);
            $host = $info['host'];
            $start = strpos($url, $host);
            $url = substr($url, 0, $start).$ip.substr($url, $start + strlen($host));

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Host: '.$host));
        }
        else
        {
            curl_setopt($ch, CURLOPT_URL, $url);
        }


        if ($this->getRefer() != null)
        {
            curl_setopt($ch, CURLOPT_REFERER, $this->getRefer());
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $this->_config['timeout'] * 1000);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $this->_config['timeout'] * 1000);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, $this->_config['httpversion']);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->_config['useragent']);

        if ($postData)
        {
            if ($method == 'put')
            {
                $postData['_method'] = 'PUT';
            }
            curl_setopt($ch, CURLOPT_POST, 1);

            //if (is_array($postData))
            //{
            //    $postData = http_build_query($postData);
            //}
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }
        else
        {
            curl_setopt($ch, CURLOPT_HTTPGET, 1);
        }

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        $this->http_code=$http_code;

        if (false == $response)
        {
            $this->errMsg = curl_error($ch);
        }

        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

        if (strpos($contentType, 'charset'))
        {
            $this->charset = $contentType;
        }

        curl_close($ch);

        return $response;
    }

    public function requestJson($url, $postData = '', $ip = '', $method = 'post')
    {
        if (empty($url))
        {
            return false;
        }

        $ch = curl_init(); //初始化CURL句柄
        $header = array();

        if ($ip)
        {
            $info = parse_url($url);
            $host = $info['host'];
            $start = strpos($url, $host);
            $url = substr($url, 0, $start).$ip.substr($url, $start + strlen($host));

            curl_setopt($ch, CURLOPT_URL, $url);
            $header[] = 'Host: '.$host;
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        else
        {
            curl_setopt($ch, CURLOPT_URL, $url);
        }

        if ($this->getRefer() != null)
        {
            curl_setopt($ch, CURLOPT_REFERER, $this->getRefer());
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $this->_config['timeout'] * 1000);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $this->_config['timeout'] * 1000);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, $this->_config['httpversion']);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->_config['useragent']);

        if ($postData)
        {
            if ($method == 'put')
            {
                $postData['_method'] = 'PUT';
            }
            curl_setopt($ch, CURLOPT_POST, 1);

            //if (is_array($postData))
            //{
            //    $postData = http_build_query($postData);
            //}
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            $header[] = 'Content-Type: application/json';
            $header[] = 'Content-Length: ' . strlen($postData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        else
        {
            curl_setopt($ch, CURLOPT_HTTPGET, 1);
        }

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        $this->http_code=$http_code;

        if (false == $response)
        {
            $this->errMsg = curl_error($ch);
        }

        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

        if (strpos($contentType, 'charset'))
        {
            $this->charset = $contentType;
        }

        curl_close($ch);

        return $response;
    }
}