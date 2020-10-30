<?php
namespace Simotel;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;

class API
{
    protected $username;

    protected $password;

    protected $baseUrl;

    protected $client;

    private $request;

    private $response;

    const VERSION = 'api/v2';

    /**
     * API constructor.
     * @param string $baseUrl
     * @param string $username
     * @param string $password
     * @param $client
     */
    public function __construct($baseUrl, $username, $password, $client = null)
    {
        $this->baseUrl = $baseUrl;
        $this->username = $username;
        $this->password = $password;
        $this->client = $client ? $client : new Client();
    }

    /**
     * @param string $to
     * @param string $trunkName
     * @param string $file
     * @param string $text
     * @param string $description
     * @param string $sender_id
     * @return
     */
    public function addFax($to, $ext, $trunkName, $file, $description, $sender_id)
    {
        return $this->_call('GET', 'pbx/faxes/add', [
            'to' => $to,
            'ext' => $ext,
            'trunk_name' => $trunkName,
            'file' => $file,
            'description' => $description,
            'sender_id' => $sender_id
        ]);
    }
	/**
     * @param array $conditions
     * @param bool $alike
     * @param array $date_range
     * @param array $pagination
     * @return data
     */
    public function searchFax($conditions,$alike,$date_range,$pagination)
    {
        return $this->_call('GET', 'pbx/faxes/search', [
            'conditions' => json_encode($conditions),
            'alike' => $alike,
            'date_range' => json_encode($date_range),
            'pagination' => json_encode($pagination)
        ]);
    }
    /**
     * @param string $methodName
     * @return string
     */
    public function uploadFaxFile($blob, $filename)
    {
        try {
            $url = $this->getPath('/pbx/faxes/upload');
            $this->response = $this->client->request('POST', $url, [
                'auth'      => [ $this->username, $this->password ],
                'multipart' => [
                    [
                        'name'     => 'file',
                        'contents' => $blob,
                        'filename' => $filename
                    ]
                ]
            ]);
        } catch (RequestException $e) {
            $this->response = $e->getResponse();
            throw new \RuntimeException($e->getMessage(), $e->getCode());
        }

        return json_decode($this->response->getBody());
    }

    /**
     * @param string $methodName
     * @return string
     */
    protected function getPath($methodName)
    {
        return $this->baseUrl . '/' . self::VERSION . '/' . $methodName;
    }

    /**
     * @param $requestType
     * @param $methodName
     * @param null $data
     * @return \stdClass
     */
    private function _call($requestType, $methodName, $data = null)
    {
        try {
            $url = $this->getPath($methodName);
            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => ' Basic ' . base64_encode($this->username . ':' . $this->password)
            ];
            $body = null;
            if (!empty($data)) {
                if (strtolower($requestType) == 'get') {
                    $url .= '?' . http_build_query($data);
                } else {
                    $body = json_encode($data);
                }
            }
            $this->request = new Request($requestType, $url, $headers, $body);
            $this->response = $this->client->send($this->request);
        } catch (RequestException $e) {
            $this->response = $e->getResponse();
            throw new \RuntimeException($e->getMessage(), $e->getCode());
        }

        return json_decode($this->response->getBody());
    }


}
