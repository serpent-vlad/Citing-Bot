<?php

namespace models\components\wiki;

use Yii;
use OAuth;
use OAuthException;

class wikiTools
{
    const API_ROOT = 'https://ru.wikipedia.org/w/api.php';
    const WIKI_ROOT = 'https://ru.wikipedia.org/wiki';

    protected $oauth;
    protected $ch;

    /**
     * wikiTools constructor.
     * @throws \OAuthException
     */
    public function __construct()
    {
        $oauth_consumer_token = Yii::$app->params['OAUTH_CONSUMER_TOKEN'];
        $oauth_consumer_secret = Yii::$app->params['OAUTH_CONSUMER_SECRET'];
        $oauth_access_token = Yii::$app->params['OAUTH_ACCESS_TOKEN'];
        $oauth_access_secret = Yii::$app->params['OAUTH_ACCESS_SECRET'];

        $this->oauth = new OAuth($oauth_consumer_token, $oauth_consumer_secret,
            OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_AUTHORIZATION);
        $this->oauth->setToken($oauth_access_token, $oauth_access_secret);
        $this->oauth->enableDebug();
        $this->oauth->setSSLChecks(0);
        $this->oauth->setRequestEngine(OAUTH_REQENGINE_CURL);
    }

    /**
     * @return bool
     */
    public function login()
    {
        $wp_username = Yii::$app->params['WP_USERNAME'];
        $wp_password = Yii::$app->params['WP_PASSWORD'];

        $response = $this->fetch([
            'action' => 'query',
            'meta'   => 'tokens',
            'type'   => 'login'
        ]);

        if (!isset($response->batchcomplete)) return false;
        if (!isset($response->query->tokens->logintoken)) return false;

        $loginVars = [
            'action'     => 'login',
            'lgname'     => $wp_username,
            'lgpassword' => $wp_password,
            'lgtoken'    => $response->query->tokens->logintoken,
        ];

        $response = $this->fetch($loginVars, 'POST');
        if (!isset($response->login->result)) return false;
        if ($response->login->result == 'Success') return true;

        Yii::warning($response->login->reason);

        return false;
    }

    /**
     * @param        $params
     * @param string $method
     * @return bool|mixed|null
     */
    public function fetch($params, $method = 'GET')
    {
        if (!$this->resetCurl()) {
            curl_close($this->ch);
            Yii::warning('Could not initialize CURL resource: ' . htmlspecialchars(curl_error($this->ch)));
            return false;
        }

        $check_logged_in = ((isset($params['type']) && $params['type'] == 'login')
            || (isset($params['action']) && $params['action'] == 'login')
            || (isset($params['meta']) && $params['meta'] == 'userinfo')) ? false : true;
        if ($check_logged_in) $params['assert'] = 'user';
        $params['format'] = 'json';

        try {
            switch (strtolower($method)) {

                case 'get':
                    $url = self::API_ROOT . '?' . http_build_query($params);
                    $header = 'Authentication: ' .
                        $this->oauth->getRequestHeader(OAUTH_HTTP_METHOD_POST, $url);

                    curl_setopt_array($this->ch, [
                        CURLOPT_URL        => $url,
                        CURLOPT_HTTPHEADER => [$header],
                    ]);

                    $response = @json_decode($data = curl_exec($this->ch));
                    if (!$data) {
                        Yii::warning('Curl error: ' . htmlspecialchars(curl_error($this->ch)));
                        return false;
                    }

                    if (isset($response->error->code) && $response->error->code == 'assertuserfailed') {
                        $this->login();
                        return $this->fetch($params, $method);
                    }

                    return ($this->returnedIsOkay($response)) ? $response : false;

                case 'post':

                    $header = 'Authentication: ' . $this->oauth->getRequestHeader(
                            OAUTH_HTTP_METHOD_POST, self::API_ROOT, http_build_query($params));
                    curl_setopt_array($this->ch, [
                        CURLOPT_POST       => true,
                        CURLOPT_POSTFIELDS => http_build_query($params),
                        CURLOPT_HTTPHEADER => [$header],
                    ]);

                    $response = @json_decode($data = curl_exec($this->ch));
                    if (!$data) {
                        echo "\n ! Curl error: " . htmlspecialchars(curl_error($this->ch));
                        Yii::$app->end(0);
                    }

                    if (isset($response->error) && $response->error->code == 'assertuserfailed') {
                        $this->login();
                        return $this->fetch($params, $method);
                    }

                    return ($this->returnedIsOkay($response)) ? $response : false;

                    echo ' ! Unrecognized method.'; // @codecov ignore - will only be hit if error in our code
                    return null;
            }
        } catch (OAuthException $E) {
            echo " ! Exception caught!\n";
            echo '   Response: ' . $E->lastResponse . "\n";
        }
    }

    /**
     * @return bool
     */
    private function resetCurl()
    {
        if (!$this->ch) {
            $this->ch = curl_init();
            if (!$this->login()) {
                curl_close($this->ch);
                Yii::warning('Could not login to Wikipedia');
            }
        }

        return curl_setopt_array($this->ch, [
            CURLOPT_FAILONERROR    => true, // #TODO Remove this line once debugging complete
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_HEADER         => false,
            CURLOPT_HTTPGET        => true,
            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_CONNECTTIMEOUT_MS => 1200,

            CURLOPT_COOKIESESSION => true,
            CURLOPT_COOKIEFILE    => 'cookie.txt',
            CURLOPT_COOKIEJAR     => 'cookiejar.txt',
            CURLOPT_URL           => self::API_ROOT,
            CURLOPT_USERAGENT     => 'Citing Bot',
        ]);
    }

    /**
     * @param $response
     * @return bool
     */
    private function returnedIsOkay($response)
    {
        if ($response === CURLE_HTTP_RETURNED_ERROR) {
            Yii::warning('Curl encountered HTTP response error');
        }
        if (isset($response->error)) {
            if ($response->error->code == 'blocked') {
                Yii::warning('Account "' . $this->username() . '" or this IP is blocked from editing.');
            } else {
                Yii::warning('API call failed: ' . $response->error->info);
            }
            return false;
        }
        return true;
    }

    /**
     * @return bool
     */
    private function username()
    {
        $userQuery = $this->fetch(['action' => 'query', 'meta' => 'userinfo']);
        return (isset($userQuery->query->userinfo->name)) ? $userQuery->query->userinfo->name : false;
    }

    /**
     * @param string $page
     * @param string $newText
     * @param string $summary
     * @return bool
     */
    public function writePage($page = 'Участник:Citing Bot/Черновик', $newText = 'Тест №1', $summary = 'Тест')
    {
        $response = $this->fetch([
            'action' => 'query',
            'prop'   => 'info|revisions',
            'rvprop' => 'timestamp',
            'meta'   => 'tokens',
            'titles' => $page,
        ]);

        if (!$response) return false;
        if (isset($response->warnings)) {
            if (isset($response->warnings->prop)) {
                Yii::warning((string)$response->warnings->prop->{'*'});
            }
            if (isset($response->warnings->info)) {
                Yii::warning((string)$response->warnings->info->{'*'});
            }
        }
        if (!isset($response->batchcomplete)) {
            Yii::warning('Write request triggered no response from server');
            return false;
        }

        $myPage = reset($response->query->pages);

        if (!isset($myPage->lastrevid)) {
            Yii::warning('Page seems not to exist. Aborting.');
            return false;
        }

        $submit_vars = [
            'action'    => 'edit',
            'title'     => $page,
            'text'      => $newText,
            'summary'   => $summary,
            'minor'     => 1,
            'bot'       => 1,
            'watchlist' => 'nochange',
            'format'    => 'json',
            'token'     => $response->query->tokens->csrftoken,
        ];
        $result = $this->fetch($submit_vars, 'POST');

        if (isset($result->error)) {
            Yii::warning('Write error: ' .
                htmlspecialchars(strtoupper($result->error->code)) . ': ' . str_replace(['You ', ' have '], ['This bot ', ' has '], htmlspecialchars($result->error->info)));
            return false;
        } elseif (isset($result->edit)) {
            if (isset($result->edit->captcha)) {
                Yii::warning("Write error: We encountered a captcha, so can't be properly logged in.");
                return false;
            } elseif ($result->edit->result == 'Success') {
                // Need to check for this string whereever our behaviour is dependant on the success or failure of the write operation
                if (true == true) { // TODO: переделать
                    //
                } else echo "\n Written to " . htmlspecialchars($myPage->title) . '.  ';
                return true;
            } elseif (isset($result->edit->result)) {
                echo "\n ! " . htmlspecialchars($result->edit->result);
                return false;
            }
        } else {
            Yii::warning('Unhandled write error.  Please copy this output and report a bug.');
            return false;
        }

        return false;
    }
}