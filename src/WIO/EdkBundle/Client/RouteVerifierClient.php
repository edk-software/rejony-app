<?php

namespace WIO\EdkBundle\Client;

use Cantiga\Metamodel\FileRepositoryInterface;
use Exception;
use WIO\EdkBundle\Entity\EdkRoute;
use WIO\EdkBundle\Exception\HttpClientRuntimeException;
use WIO\EdkBundle\Exception\RouteVerifierResultException;
use WIO\EdkBundle\Model\RouteVerifierResult;

class RouteVerifierClient
{
    /** @var string */
    private $baseUrl;

    /** @var string[]|null */
    private $basicAuth;

    /** @var HttpClient */
    private $httpClient;

    /** @var FileRepositoryInterface */
    private $fileRepository;

    public function __construct(string $baseUrl, string $basicAuthUser = null, string $basicAuthPass = null)
    {
        $this->baseUrl = $baseUrl;
        $this->basicAuth = empty($basicAuthUser) || empty($basicAuthPass) ? null : [$basicAuthUser, $basicAuthPass];
    }

    public function setHttpClient(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function setFileRepository(FileRepositoryInterface $fileRepository)
    {
        $this->fileRepository = $fileRepository;
    }

    public function verify(EdkRoute $route): RouteVerifierResult
    {
        if (!isset($this->httpClient)) {
            throw new Exception('There is no HTTP client defined.');
        }
        if (!isset($this->fileRepository)) {
            throw new Exception('There is no file repository defined.');
        }

        try {
            $url = sprintf('%s/api/verify', $this->baseUrl);
            $response = $this->httpClient->makeRequest('POST', $url, [
                'kml' => $route->getGpsTrackContent($this->fileRepository),
            ], [
                'Content-Type: application/json',
            ], [
                'authBasic' => $this->basicAuth,
            ]);
            $result = new RouteVerifierResult($response->getContent('json-array'));
        } catch (RouteVerifierResultException $exception) {
            throw new Exception('Route verification response is invalid.', 0, $exception);
        } catch (HttpClientRuntimeException $exception) {
            throw new Exception('Third party error occurred.', 0, $exception);
        }

        return $result;
    }
}
