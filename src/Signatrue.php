<?php

namespace Edu\Signatrue;

class Signatrue
{
    const MUST_REQUEST_PARAMS = ['ts', 'user_id', 'sign'];

    protected $provider;
    protected $request;
    protected $validRequestPeriod = 180;
    protected $user;
    protected $errMessage;
    protected $errCode;


    public function setProvider($provider)
    {
        $this->provider = $provider;
        return $this;
    }

    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    public function validated()
    {
        $input = $this->request->all();
        $path = $this->request->path();
        $method = $this->request->method();
        $result = $this->validRequest($input)
            && $this->validRequestTime($input['ts'])
            && $this->validUser($input['user_id'])
            && $this->validLoginStatus()
            && $this->validSign($input, $method, $path);
        return $result;
    }


    public function validRequest($input)
    {
        foreach (self::MUST_REQUEST_PARAMS as $param) {
            if (!isset($input[$param])) {
                $this->errCode = 40301;
                $this->errMessage = "缺少必要参数：$param";
                return false;
            }
        }
        return true;
    }

    public function validRequestTime($ts)
    {
        if (time() - $ts > $this->validRequestPeriod) {
            $this->errCode = 40302;
            $this->errMessage = "请求已过期";
            return false;
        }

        return true;
    }

    public function validUser($userId)
    {
        if (!$this->getUser($userId)) {
            $this->errCode = 40303;
            $this->errMessage = '用户不存在';
            return false;
        }
        return true;
    }

    public function validLoginStatus()
    {
        if ($this->user->token_expired_at < time()) {
            $this->errCode = 40304;
            $this->errMessage = '登录已过期';
            return false;
        }
        return true;
    }

    public function validSign($input, $method, $path)
    {
        $sign = $this->makeSign($input, $method, $path);
        if ($input['sign'] != $sign) {
            $this->errCode = 40305;
            $this->errMessage = '签名验证失败' . $sign;
            return false;
        }
        return true;
    }

    protected function makeSign($input, $method, $path)
    {
        unset($input['sign']);
        ksort($input);
        $str = '';
        foreach ($input as $key => $value) {
            $str .= $key . $value;
        }
        $str .= $this->user->api_token;
        $str .= $method;
        $str .= $path;
        $str .= $input['ts'];
        return sha1($str);

    }

    protected function getUser($userId)
    {
        return $this->user = $this->provider->retrieveById($userId);
    }

    public function getUserId()
    {
        return $this->user->id();
    }

    public function getErrMessage()
    {
        return $this->errMessage;
    }

    public function getErrCode()
    {
        return $this->errCode;
    }


}
