<?php

namespace GithubApi_v3;

class Api {

    protected $api_url = 'https://api.github.com';

    protected $is_logged = false;

    protected $username = null;

    protected $password = null;

    public function addIssue($user, $repo, $params = array())
    {
        return $this->post('/repos/'.urlencode($user).'/'.urlencode($repo).'/issues', $params);
    }

    public function getIssues($user, $repo, $params_array = array())
    {
        $params = '';
        foreach ($params_array as $param => $value) {
            $params .= '&'.$param.'='.$value;
        }

        return $this->get('/repos/'.urlencode($user).'/'.urlencode($repo).'/issues?'.$params);
    }

    public function login($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
        $response = $this->get();

        if (isset($response['message']) && $response['message'] == 'Bad credentials') {
            return false;
        }

        $this->is_logged = true;
        return true;
    }

    private function post($url = '', $post_params = array(), $method = 'HTTP_BASIC')
    {
        return $this->get($url, $method, $post_params);
    }

    private function get($url = '', $method = 'HTTP_BASIC', $post_params = false)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->api_url.$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        if ('HTTP_BASIC' == $method && null !== $this->username) {
            curl_setopt($ch, CURLOPT_USERPWD, $this->username.':'.$this->password);
        }

        if (false !== $post_params) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_params));
        }

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}