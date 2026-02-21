<?php

namespace Neoflow;

use RuntimeException;

class GitHubClient
{
    /**
     * @var string
     */
    protected $apiUrl;
    protected $repoPath;

    /**
     * @var int
     */
    protected $options = [
        'cacheLifetime' => 600, // 10*60 seconds = 600 seconds (10 minutes)
        'cacheDirectory' => 'cache',
    ];

    /**
     * Default cURL options.
     *
     * @var array
     */
    protected $curlOptions = [
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_FOLLOWLOCATION => 1,
    ];

    /**
     * Constructor.
     *
     * @param string $apiUrl GitHub API url
     * @param string $repoPath Repository URL path
     * @param array $options GitHub client options
     */
    public function __construct($apiUrl, $repoPath, $options = [])
    {
        $this->apiUrl = $apiUrl;
        $this->repoPath = $repoPath;

        $this->options = array_merge($this->options, $options);

        if (isset($options['curl'])) {
            $this->curlOptions = $this->curlOptions += $options['curl'];
        }
    }

    /**
     * Call GitHub API.
     *
     * @param string $urlPath Additional API url path
     * @param array $curlOptions Additional cURL options
     * @param bool $cache Set FALSE to prevent to cache the response content
     *
     * @return string
     */
    public function call($urlPath = '', $curlOptions = [], $cache = true)
    {
        return $this->send($this->apiUrl . $urlPath, $curlOptions, $cache);
    }

    /**
     * Build and send HTTP request.
     *
     * @param string $url Request url
     * @param array $curlOptions Additional cURL options
     * @param bool $cache Set FALSE to prevent to cache the response content
     *
     * @return string
     *
     * @throws RuntimeException
     */
    public function send($url = '', $curlOptions = [], $cache = true)
    {
        if (!$cache || !$this->isCached($url)) {
            // Get cURL resource
            $ch = curl_init();

            // Set destination url
            $this->curlOptions[CURLOPT_URL] = $url;

            // Merge and set cURL options
            curl_setopt_array($ch, $this->curlOptions + $curlOptions);

            // Send cURL request and get response and HTTP code
            $response = curl_exec($ch);

            // Check whether request was successful
            if (false === $response) {
                throw new RuntimeException('Connection error. ' . curl_error($ch));
            }

            // Set cache
            if ($cache) {
                $this->setCache($url, $response);
            }

            return $response;
        } else {
            return $this->getCache($url);
        }
    }

    /**
     * Create cache filename based on API url.
     *
     * @param string $url GitHub API url
     *
     * @return string
     */
    protected function createCacheFilename($url)
    {
        $urlParts = parse_url($url);
        $cacheFilename = str_replace([$this->repoPath, '/'], ['', '_'], $urlParts['path']);
        if (isset($urlParts['query'])) {
            $cacheFilename .= str_replace('=', '_', $urlParts['query']);
        }

        return $this->options['cacheDirectory'] . DIRECTORY_SEPARATOR . 'fraggy-backend-theme-' . trim($cacheFilename, '_') . '.json';
    }

    /**
     * Cache response based on API url.
     *
     * @param string $url GitHub API url
     * @param string $data GitHub API response
     *
     * @return self
     */
    protected function setCache($url, $data)
    {
        $cacheFilename = $this->createCacheFilename($url);
        file_put_contents($cacheFilename, $data);

        return $this;
    }

    /**
     * Get cache based on API url.
     *
     * @param string $url GitHub API url
     *
     * @return string
     */
    protected function getCache($url)
    {
        $cacheFilename = $this->createCacheFilename($url);
        if (is_file($cacheFilename)) {
            if (filemtime($cacheFilename) > (time() - $this->options['cacheLifetime'])) {
                return file_get_contents($cacheFilename);
            } else {
                unlink($cacheFilename);
            }
        }

        return '';
    }

    /**
     * Check whether a cache based on API url exists.
     *
     * @param string $url GitHub API url
     *
     * @return bool
     */
    protected function isCached($url)
    {
        return (bool)$this->getCache($url);
    }

    /**
     * Get info about latest release.
     *
     * @return array
     */
    public function getLatestRelease()
    {
        if ($this->options['prerelease']) {
            $releases = json_decode($this->call($this->repoPath . '/releases'), true);
            return $releases[0];
        } else {
            return json_decode($this->call($this->repoPath . '/releases/latest'), true);
        }

        return [];
    }

    /**
     * Get rate limit.
     *
     * @return array
     */
    public function getRateLimit()
    {
        $json = $this->call('/rate_limit');

        return json_decode($json, true);
    }
}
