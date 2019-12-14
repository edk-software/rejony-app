<?php

namespace WIO\EdkBundle\Client;

use Cantiga\Metamodel\FileRepositoryInterface;
use Exception;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use WIO\EdkBundle\Entity\EdkRoute;
use WIO\EdkBundle\Exception\RouteVerifierResultException;
use WIO\EdkBundle\Model\RouteVerifierResult;

class RouteVerifierClient
{
    /** @var string */
    private $baseUrl;

    /** @var string[]|null */
    private $basicAuth;

    /** @var HttpClientInterface */
    private $httpClient;

    /** @var FileRepositoryInterface */
    private $fileRepository;

    public function __construct(string $baseUrl, string $basicAuthUser = null, string $basicAuthPass = null)
    {
        $this->baseUrl = $baseUrl;
        $this->basicAuth = empty($basicAuthUser) || empty($basicAuthPass) ? null : [$basicAuthUser, $basicAuthPass];
    }

    public function setHttpClient(HttpClientInterface $httpClient)
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
            $response = $this->httpClient->request('POST', $url, [
                'auth_basic' => $this->basicAuth,
                'json' => [
                    'kml' => $route->getGpsTrackContent($this->fileRepository),
                ],
            ]);
            $result = new RouteVerifierResult($response->toArray());
        } catch (RouteVerifierResultException $exception) {
            throw new Exception('Route verification response is invalid.', 0, $exception);
        } catch (ExceptionInterface $exception) {
            throw new Exception('Third party error occurred.', 0, $exception);
        }

        return $result;
    }
}
