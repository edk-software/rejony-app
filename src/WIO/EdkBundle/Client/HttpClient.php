<?php

namespace WIO\EdkBundle\Client;

use WIO\EdkBundle\Exception\HttpClientRuntimeException;
use WIO\EdkBundle\Model\HttpResource;

class HttpClient
{
    /**
     * Make request
     *
     * @param string $type           type
     * @param string $url            URL
     * @param mixed  $data           data
     * @param array  $requestHeaders request headers
     * @param array  $options        options
     *
     * @return HttpResource
     *
     * @throws HttpClientRuntimeException
     */
    public function makeRequest($type, $url, $data = '', array $requestHeaders = [], array $options = [])
    {
        $type = strtoupper($type);
        $body = $type === 'GET' ? '' : (is_array($data) || is_object($data) ? json_encode($data) : (string) $data);
        $options = array_merge([
            'authBasic' => null,
            'ignoreHttpErrors' => false,
            'maxRedirs' => 0,
            'returnHeaders' => false,
            'timeout' => 30,
        ], $options);

        $connection = curl_init();
        curl_setopt($connection, CURLOPT_URL, $url);
        curl_setopt($connection, CURLOPT_CUSTOMREQUEST, $type);
        curl_setopt($connection, CURLOPT_POSTFIELDS, $body);
        curl_setopt($connection, CURLOPT_TIMEOUT, $options['timeout']);
        curl_setopt($connection, CURLOPT_MAXREDIRS, $options['maxRedirs']);
        curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($connection, CURLOPT_HTTPHEADER, $requestHeaders);
        curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, false);
        if (is_array($options['authBasic'])) {
            curl_setopt($connection, CURLOPT_USERPWD, implode(':', $options['authBasic']));
        }

        $headers = [];
        if ($options['returnHeaders'] === true) {
            curl_setopt($connection, CURLOPT_HEADERFUNCTION, function ($curl, $header) use (&$headers) {
                $length = strlen($header);
                $header = explode(':', $header, 2);
                if (count($header) < 2) {
                    return $length;
                }
                $name = strtolower(trim($header[0]));
                $value = trim($header[1]);
                if (array_key_exists($name, $headers)) {
                    if (!is_array($headers[$name])) {
                        $headers[$name] = (array) $headers[$name];
                    }
                    $headers[$name][] = $value;
                } else {
                    $headers[$name] = $value;
                }
                return $length;
            });
        }

        $content = curl_exec($connection);
        $errorNo = curl_errno($connection);
        $statusCode = curl_getinfo($connection, CURLINFO_HTTP_CODE);
        curl_close($connection);

        if ($errorNo === CURLE_HTTP_POST_ERROR) {
            throw new HttpClientRuntimeException('HTTP POST error occurred during content sending.');
        } elseif ($errorNo !== CURLE_OK && $errorNo !== CURLE_HTTP_NOT_FOUND) {
            throw new HttpClientRuntimeException('An error ' . $errorNo . ' occurred during content sending.');
        } elseif ($options['ignoreHttpErrors'] === false && ($statusCode < 200 || $statusCode >= 300)) {
            throw new HttpClientRuntimeException('An error received in HTTP response.');
        }

        return new HttpResource($content, $headers, $statusCode);
    }
}
