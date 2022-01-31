<?php

namespace Level51\ThreeQ;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Container\NotFoundExceptionInterface;
use Psr\SimpleCache\InvalidArgumentException;
use SilverStripe\Core\Environment;
use SilverStripe\Core\Injector\Injectable;

/**
 * Service class for the api communication with 3Q.
 *
 * @package Level51\ThreeQ
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
        if (!($apiKey = Environment::getEnv('THREEQ_API_KEY'))) {
            user_error('Missing 3Q Api Key');
        }

        if (!Environment::getEnv('THREEQ_PROJECT_ID')) {
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
        return Environment::getEnv('THREEQ_PROJECT_ID');
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
     * @throws GuzzleException
     * @throws NotFoundExceptionInterface
     * @throws InvalidArgumentException
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

    /**
     * Get detailed information for a specific file from 3Q.
     *
     * @param string|int $id
     *
     * @return array|null
     *
     * @throws GuzzleException
     */
    public function getFile($id): ?array
    {
        try {
            $response = $this->httpClient
                ->get('projects/' . $this->getProjectId() . '/files/' . $id);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            // TODO error handling
            return null;
        }
    }

    /**
     * Request an upload url from 3Q for the given file.
     *
     * The actual file upload has to be done against the url returned by this function.
     *
     * @param string $filename
     * @param string $filetype
     *
     * @return string|null
     *
     * @throws GuzzleException
     *
     * @see https://sdn.3qsdn.com/api/doc#post--api-v2-projects-{ProjectId}-files
     */
    public function getUploadUrl(string $filename, string $filetype): ?string
    {
        $response = $this->httpClient
            ->post(
                'projects/' . $this->getProjectId() . '/files',
                [
                    'json' => [
                        'FileName' => $filename,
                        'FileFormat' => $filetype
                    ]
                ]
            );

        return $response->getHeader('Location')[0];
    }

    /**
     * Get a list of all playout ids linked to the given file.
     *
     * @param string|int $fileId
     *
     * @return array
     *
     * @throws GuzzleException
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
            // TODO error handling
            return [];
        }
    }
}
