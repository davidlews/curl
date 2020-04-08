<?php

class _curl
{

    protected $_ch = null;
    protected $_url = '';
    protected $_ssl = false;


    protected function __construct()
    {
    }




    private function _socket($url, $para, $return)
    {

        $this->_setUrl($url);


        if (false === isset($para['header'])) {
            $para['header'] = false;
        } else {
            $para['header'] = true;
        }
        curl_setopt($this->_ch, CURLOPT_HEADER, $para['header']);


        if (false === isset($para['location'])) {
            $para['location'] = false;
        } else {
            $para['location'] = true;
        }
        curl_setopt($this->_ch, CURLOPT_FOLLOWLOCATION, $para['location']);

        unset($para['location']);

        if (isset($para['proxy']) && false != $para['proxy']) {
            curl_setopt($this->_ch, CURLOPT_PROXY, $para['proxy']);
        }

        if (false === isset($para['cookieFile'])) {
            $para['cookieFile'] = array(0 => '');
            curl_setopt($this->_ch, CURLOPT_COOKIEFILE, $para['cookieFile'][0]);
            curl_setopt($this->_ch, CURLOPT_COOKIEJAR, $para['cookieFile'][0]);
        }

        if (true === $return) {
            curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, true);
        }

        if (isset($para['curl_httpheader']) && !empty($para['curl_httpheader'])) {
            if (is_array($para['curl_httpheader'])) {
                curl_setopt($this->_ch, CURLOPT_HTTPHEADER, $para['curl_httpheader']);
            } else {
                curl_setopt($this->_ch, CURLOPT_HTTPHEADER, array($para['curl_httpheader']));
            }
        }


        if (true === $this->_ssl) {
            curl_setopt($this->_ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($this->_ch, CURLOPT_SSL_VERIFYHOST, true);
        }

        curl_setopt($this->_ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);


        $timeout = (!isset($para['curl_timeout']) ? $CURL_TIMEOUT : intval($para['curl_timeout']));
        (!is_int($timeout) || $timeout <= 0) && ($timeout = 5);
        curl_setopt($this->_ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($this->_ch, CURLOPT_TIMEOUT, $timeout);


        $this->_cUrl($para);



        $CURL_TRY_NUM = 5;
        $tryNum = (!isset($para['curl_try_num']) ? $CURL_TRY_NUM : intval($para['curl_try_num']));
        (!is_int($tryNum) || $tryNum <= 0) && ($tryNum = 1);
        $return_result = array();
        while (1 <= $tryNum--) {
            $result = curl_exec($this->_ch);
            if (false === $result) {
                $log = array(
                    'errno' => 'errno:curl-' . curl_errno($this->_ch),
                    'error' => 'error:' . curl_error($this->_ch),
                    'api' => 'api:' . $url
                );
                $curl_errno = curl_errno($this->_ch);
                $curl_error = curl_error($this->_ch);
                error_log(
                    date('Y-m-d H:i:s') . " curl_errno:[$curl_errno] curl_error:[$curl_error] api:[$url]" . PHP_EOL,
                    3,
                    ROOT_PATH . 'logs/curl_log.txt'
                );
                $return_result = array('status' => -1, 'message' => $curl_error, 'data' => $log);
            } else {
                $return_result = array('status' => 1, 'message' => 'OK', 'data' => $result);
                break;
            }
        }

        curl_close($this->_ch);
        return $return_result;
    }


    private function _setUrl($url)
    {
        $this->_url = $url;

        if (false !== strstr($this->_url, 'https://')) {
            $this->_ssl = true;
        }

        curl_setopt($this->_ch, CURLOPT_URL, $this->_url);

        if (preg_match('/:([\d]+)/i', $url, $port)) {
            curl_setopt($this->_ch, CURLOPT_PORT, $port[1]);
        }
    }


	//调用入口
    public function curl($url, $para = array(), $return = true)
    {
        $this->_ch = curl_init();
        return $this->_socket($url, $para, $return);
    }
	
	protected function _cUrl($para = array()) {
		curl_setopt($this->_ch, CURLOPT_POST, true);
		curl_setopt($this->_ch, CURLOPT_POSTFIELDS, $para['data']);
	}	
}
