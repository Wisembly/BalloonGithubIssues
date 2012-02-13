<?php

namespace GithubApi_v3;

class Api {

    protected $api_url = 'https://api.github.com';

    protected $is_logged = false;

    protected $username = null;

    protected $password = null;

    protected $userData = null;

    public function getMilestones($user, $repo, $params = array())
    {
        return $this->get('/repos/'.$user.'/'.$repo.'/milestones', $this->params($params));
    }

    public function loadUserData()
    {
        return $this->get('/user');
    }

    public function getUserData()
    {
        return $this->userData;
    }

    public function addIssue($user, $repo, $params = array())
    {
        return $this->post('/repos/'.urlencode($user).'/'.urlencode($repo).'/issues', $params);
    }

    public function closeIssue($user, $repo, $number)
    {
        return $this->post('/repos/'.urlencode($user).'/'.urlencode($repo).'/issues/'.urlencode($number), array('state' => 'closed'));
    }

    public function getIssues($user, $repo, $params = array())
    {
        return $this->get('/repos/'.urlencode($user).'/'.urlencode($repo).'/issues', $this->params($params));
    }

    public function getIssue($user, $repo, $number)
    {
        return $this->get('/repos/'.urlencode($user).'/'.urlencode($repo).'/issues/'.urlencode($number));
    }

    public function login($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
        $response = $this->get();

        if (isset($response['message']) && $response['message'] == 'Bad credentials') {
            return false;
        }

        $this->userData = $this->loadUserData();
        $this->is_logged = true;
        return true;
    }

    public function isLogged()
    {
        return $this->is_logged;
    }

    private function params($params_array = array())
    {
        $params = '';
        foreach ($params_array as $param => $value) {
            $params .= '&'.$param.'='.$value;
        }

        return $params;
    }

    private function post($url = '', $post_params = array(), $method = 'HTTP_BASIC')
    {
        return $this->get($url, '', $method, $post_params);
    }

    private function get($url = '', $params = '', $method = 'HTTP_BASIC', $post_params = false)
    {
        $ch = curl_init();
        $url = $this->api_url . $url . (!empty($params) ? $params : '');
        curl_setopt($ch, CURLOPT_URL, $url);
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
