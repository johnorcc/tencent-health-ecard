<?php
/**
 * Created by PhpStorm.
 * User: johnor
 * Date: 2020/7/22
 * Time: 11:10
 */

namespace Tencent\ECard;


use ArrayAccess;

class Response implements ArrayAccess
{

    const CODE_SUCCESS = 0;
    const CODE_FAIL = 1;
    const INFO_FAIL = '请求失败默认消息';

    private $_data;
    private $commonOut = [];

    public function __construct($data = [])
    {
        $this->_data = $data['rsp'];
        $this->commonOut = $data['commonOut'];
    }

    public function offsetGet($offset)
    {
        return ($this->offsetExists($offset) ? $this->_data[$offset] : null);
    }

    public function offsetSet($offset, $value)
    {
        $this->_data[$offset] = $value;
    }

    public function offsetExists($offset)
    {
        return isset($this->_data[$offset]);
    }

    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            unset($this->_data[$offset]);
        }
    }

    public function __get($offset)
    {
        if ($offset === 'data') return $this->_data;
        return ($this->offsetExists($offset) ? $this->_data[$offset] : null);
    }

    public function __set($offset, $value)
    {
        $this->_data[$offset] = $value;
    }

    public function isSuccess()
    {
        return isset($this->commonOut['resultCode']) && $this->commonOut['resultCode'] === 0;
    }

    public function resultCode()
    {
        if (isset($this->commonOut)) return $this->commonOut['resultCode'];
        return 0;
    }

    public function errMsg()
    {
        if (isset($this->commonOut)) return $this->commonOut['errMsg'];
        return self::INFO_FAIL;
    }
}