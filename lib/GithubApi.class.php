<?php

class Balloon_GithubApi {

    protected $api_url = 'https://api.github.com';

    protected $is_logged = false;

    protected $username = null;

    protected $password = null;

    public function listIssues()
    {
        
    }

    public function getIssues($user, $repo, $params = array())
    {
        foreach ($params as $param => $value) {
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

    private function get($url = '', $method = 'HTTP_BASIC')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->api_url.$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        if ('HTTP_BASIC' == $method && null !== $this->username) {
            curl_setopt($ch, CURLOPT_USERPWD, $this->username.':'.$this->password);
        }

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}