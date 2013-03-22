<?php

class MMT_TestingBot_WebDriverTestCase  extends PHPUnit_Extensions_Selenium2TestCase
{
    /**
     * An array containing the API key and secret for a testing bot account.
     *
     * @var array
     */
    private $_clientData = null;

    protected function tearDown()
    {
        $data = array(
            'session_id' => $this->getSessionId(),
            'client_key' => $this->getClientKey(),
            'client_secret' => $this->getClientSecret(),
            'status_message' => $this->getStatusMessage(),
            'success' => !$this->hasFailed(),
            'name' => $this->toString()
        );

        $this->_apiCall($data);

        parent::tearDown();
    }

    /**
     * Send the result of a test to testing bot.
     *
     * @param array $postData
     *
     * @return void
     */
    protected function _apiCall(array $postData)
    {
        $data = http_build_query($postData);
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, "http://testingbot.com/hq");
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        curl_exec($curl);
        curl_close($curl);
    }

    /**
     * @return array
     */
    private function _getClientData()
    {
        if (is_null($this->_clientData))
        {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
            {
                $homeDir = substr(__FILE__, 0, 2);
            }
            else
            {
                if (isset($_SERVER['HOME']))
                {
                    $homeDir = $_SERVER['HOME'];
                }
                else
                {
                    $homeDir = shell_exec('echo $HOME 2>&1');
                }
            }

            if (!file_exists($homeDir . DIRECTORY_SEPARATOR . '.testingbot'))
            {
                die('Please run testingbot configure "API_KEY:API_SECRET" first.');
            }

            $data = file_get_contents($homeDir . DIRECTORY_SEPARATOR . '.testingbot');
            list($client_key, $client_secret) = explode(':', $data);

            $this->_clientData['client_key'] = $client_key;
            $this->_clientData['client_secret'] = $client_secret;
        }

        return $this->_clientData;
    }

    /**
     * @return string
     */
    public function getClientSecret()
    {
        $data = $this->_getClientData();
        return rtrim($data['client_secret'], "\n");
    }

    /**
     * @return string
     */
    public function getClientKey()
    {
        $data = $this->_getClientData();
        return $data['client_key'];
    }

    /**
     * @param string $using
     * @param string $value
     *
     * @return PHPUnit_Extensions_Selenium2TestCase_ElementCriteria
     */
    public function getCriteria($using, $value)
    {
        $criteria = new PHPUnit_Extensions_Selenium2TestCase_ElementCriteria($using);
        $criteria->value($value);

        return $criteria;
    }
}
