<?php

namespace Level51\ThreeQ;

use GuzzleHttp\Client;
use SilverStripe\Core\Injector\Injectable;

/**
 * Simple service class for parts of the 3Q API.
 *
 * @package T1\Subplatform
 *
 * @see https://sdn.3qsdn.com/api/doc
 */
class ThreeQApiService
{
    use Injectable;

    /** @var Client */
    protected $httpClient;

    public function __construct()
    {
        if (!($apiKey = Util::config()->get('api_key'))) {
            user_error('Missing 3Q Api Key');
        }

        if (!Util::config()->get('project_id')) {
            user_error('Missing 3Q project id');
        }

        $this->httpClient = new Client(
            [
                'base_uri' => 'https://sdn.3qsdn.com/api/v2/',
                'headers'  => [
                    'X-AUTH-APIKEY' => $apiKey
                ]
            ]
        );
    }

    public function getProjectId(): string
    {
        return Util::config()->get('project_id');
    }

    /**
     * Get a list of all files within a project.
     *
     * The results are cached for 5 minutes to reduce api requests.
     * This is mainly required for the select field, as there is no search option on 3Q side we
     * would just execute the same call on each query change.
     *
     * @return array
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @see https://sdn.3qsdn.com/api/doc#get--api-v2-projects-{ProjectId}-files
     */
    public function getFiles(): array
    {
        $cache = Util::getCache();
        $cacheKey = 'threeQFiles';
        if ($cache->has($cacheKey)) {
            return $cache->get($cacheKey);
        }

        try {
            $response = $this->httpClient
                ->get(
                    'projects/' . $this->getProjectId() . '/files',
                    [
                        'query' => [
                            'includeMetadata' => true
                        ]
                    ]
                );

            $result = $response->getBody()->getContents();
            $result = json_decode($result, true);
            $result = $result['Files'] ?? [];
            $cache->set($cacheKey, $result, 300);

            return $result;
        } catch (\Exception $e) {
            // TODO error handling
            return [];
        }
    }

    public function getFile($id)
    {
        $cache = Util::getCache();
        $cacheKey = 'threeQFile-' . $id;

        if ($cache->has($cacheKey)) {
            return $cache->get($cacheKey);
        }

        try {
            $response = $this->httpClient
                ->get('projects/' . $this->getProjectId() . '/files/' . $id);

            $result = $response->getBody()->getContents();
            $result = json_decode($result, true);
            $cache->set($cacheKey, $result, 300);

            return $result;
        } catch (\Exception $e) {
            // TODO error handling
            return null;
        }
    }

    /**
     * Get a list of all playout ids linked to the given file.
     *
     * @param $fileId
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @see https://sdn.3qsdn.com/api/doc#get--api-v2-projects-{ProjectId}-files-{FileId}-playouts
     */
    public function getPlayoutIds($fileId): array
    {
        try {
            $response = $this->httpClient
                ->get(
                    'projects/' . $this->getProjectId() . '/files/' . $fileId . '/playouts'
                );

            $result = $response->getBody()->getContents();
            $result = json_decode($result, true);

            return $result['FilePlayouts'] ?? [];
        } catch (\Exception $e) {
            // TODO error hanlding
            return [];
        }
    }
}
