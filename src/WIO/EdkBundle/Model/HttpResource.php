<?php

namespace WIO\EdkBundle\Model;

class HttpResource
{
    /** @var array */
    public $headers;

    /** @var string */
    private $content;

    /** @var int */
    private $statusCode;

    /**
     * Construct
     *
     * @param string $content    content
     * @param array  $headers    headers
     * @param int    $statusCode status code
     */
    public function __construct($content, array $headers = [], $statusCode = 200)
    {
        $this->headers = $headers;
        $this->content = $content;
        $this->statusCode = $statusCode;
    }

    /**
     * Get headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Get content
     *
     * @param string $format format
     *
     * @return mixed
     */
    public function getContent($format = 'string')
    {
        if ($format === 'json') {
            return json_decode($this->content);
        } elseif ($format === 'json-array') {
            return json_decode($this->content, true);
        }

        return $this->content;
    }

    /**
     * Get content from JSON
     *
     * @return mixed
     */
    public function getContentFromJson()
    {
        return $this->getContent('json');
    }

    /**
     * Get status code
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }
}
