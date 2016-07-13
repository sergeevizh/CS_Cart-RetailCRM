<?php

/**
 * HTTP client
 */
class Client
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';

    protected $url;
    protected $defaultParameters;
    protected $retry;

    public function __construct($url, array $defaultParameters = array())
    {
        if (false === stripos($url, 'https://')) {
            throw new InvalidArgumentException('API schema requires HTTPS protocol');
        }

        $this->url = $url;
        $this->defaultParameters = $defaultParameters;
        $this->retry = 0;
    }

    /**
     * Make HTTP request
     *
     * @param string $path
     * @param string $method (default: 'GET')
     * @param array $parameters (default: array())
     * @param int $timeout
     * @param bool $verify
     * @param bool $debug
     * @return ApiResponse
     */
    public function makeRequest(
        $path,
        $method,
        array $parameters = array(),
        $timeout = 30,
        $verify = false,
        $debug = false
    ) {
        $allowedMethods = array(self::METHOD_GET, self::METHOD_POST);
        if (!in_array($method, $allowedMethods)) {
            throw new InvalidArgumentException(sprintf(
                'Method "%s" is not valid. Allowed methods are %s',
                $method,
                implode(', ', $allowedMethods)
            ));
        }

        $parameters = array_merge($this->defaultParameters, $parameters);

        $url = $this->url . $path;

        if (self::METHOD_GET === $method && sizeof($parameters)) {
            $url .= '?' . http_build_query($parameters);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $verify);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $verify);

        if (!$debug) {
            curl_setopt($ch, CURLOPT_TIMEOUT, (int) $timeout);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, (int) $timeout);
        } else {
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, (int) $timeout + ($this->retry * 2000));
        }

        if (self::METHOD_POST === $method) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
        }

        $responseBody = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errno = curl_errno($ch);
        $error = curl_error($ch);

        curl_close($ch);

        if ($errno && in_array($errno, array(6, 7, 28, 34, 35)) && $this->retry < 3) {
            $errno = null;
            $error = null;
            $this->retry += 1;
            $this->makeRequest(
                $path,
                $method,
                $parameters,
                $timeout,
                $verify,
                $debug
            );
        }

        if ($errno) {
            throw new CurlException($error, $errno);
        }

        return new ApiResponse($statusCode, $responseBody);
    }

    public function getRetry()
    {
        return $this->retry;
    }
}
