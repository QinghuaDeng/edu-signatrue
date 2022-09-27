<?php

namespace Edu\Signatrue;

use App\Models\User;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SignatrueGuard implements Guard
{
    use GuardHelpers;

    protected $signatrue;
    protected $request;
    protected $provider;
    protected $user;

    public function __construct(Signatrue $signatrue, UserProvider $provider, Request $request)
    {
        $this->signatrue = $signatrue;
        $this->provider = $provider;
        $this->request = $request;
        $this->signatrue->setRequest($this->request);
        $this->signatrue->setProvider($this->provider);
    }


    public function user()
    {
        if ($this->user != null) {
            return $this->user;
        }

        $user = null;
        if ($this->signatrue->validated()) {
            $user = $this->provider->retrieveById($this->signatrue->getUserId());
        }

        return $this->user = $user;
    }

    public function validate(array $credentials = [])
    {
        return (bool)$this->attempt($credentials, false);
    }

    public function attempt($credentials, $login = true)
    {
        $user = $this->provider->retrieveByCredentials($credentials);
        if ($this->hasValidCredentials($user, $credentials)) {
            return $login ? $this->login($user) : true;
        }
        return false;
    }

    public function login($user)
    {
        $token = (!$user->api_token || $user->token_expired_at < time()) ? $this->refreshToken($user) : $user->api_token;

        $this->user = $user;

        return $token;
    }

    protected function refreshToken(User $user)
    {
        $user->api_token = Str::random(60);
        $user->token_expired_at = time() + 1800;
        $user->save();
        return $user->api_token;
    }

    protected function hasValidCredentials($user, $credentials)
    {
        return $user !== null && $this->provider->validateCredentials($user, $credentials);
    }

    public function __call($method, $parameters)
    {
        if (method_exists($this->signatrue, $method)) {
            return call_user_func_array([$this->signatrue, $method], $parameters);
        }
        throw new \BadMethodCallException("Method [$method] does not exist");
    }
}
