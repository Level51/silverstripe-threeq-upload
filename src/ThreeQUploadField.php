<?php

namespace Level51\ThreeQ;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Forms\FormField;
use SilverStripe\Security\Security;
use SilverStripe\View\Requirements;

/**
 * @todo
 *   - check folder support
 *   - cleanup, rearrange, add return types...
 */
class ThreeQUploadField extends FormField
{
    private static $allowed_actions = ['search', 'getUploadUrl', 'uploaded', 'selectFile', 'syncWithApi'];

    /**
     * @var int Max file size in MB, overrides the config value if set
     */
    protected $maxFileSize;

    /**
     * @var int Min amount of characters needed to execute the search callback
     */
    private $minSearchChars = 3;

    /**
     * @var string[]
     */
    private $allowedFileTypes = ['mp4'];

    /**
     * @var int Max duration in seconds until the XHR request is canceled, overrides the config value if set.
     */
    protected $timeout;

    public function Field($properties = array())
    {
        Requirements::javascript('level51/silverstripe-threeq-upload: client/dist/threeqUpload.js');
        Requirements::css('level51/silverstripe-threeq-upload: client/dist/threeqUpload.css');

        return parent::Field($properties);
    }

    public function dataValue()
    {
        $value = $this->value;

        if ($value) {
            return ThreeQFile::requireForThreeQId($value)->ID;
        }

        return 0;
    }

    /**
     * Get frontend payload.
     *
     * @return string
     */
    public function getPayload()
    {
        return json_encode(
            [
                'id'              => $this->ID(),
                'name'            => $this->getName(),
                'value'           => $this->Value(),
                'file'            => ($file = $this->getFile()) ? $file->flat() : null,
                'title'           => $this->Title(),
                'lang'            => substr(Security::getCurrentUser()->Locale, 0, 2),
                'dropzoneOptions' => [
                    'maxFilesize'   => $this->getMaxFileSize(),
                    'acceptedFiles' => $this->getAllowedFileTypesForFrontend(),
                    'timeout'       => $this->getTimeout(),
                ],
                'config'          => [
                    'minSearchChars'          => $this->minSearchChars,
                    'searchEndpoint'          => $this->Link('search'),
                    'selectFileEndpoint'      => $this->Link('selectFile'),
                    'uploadUrlEndpoint'       => $this->Link('getUploadUrl'),
                    'successCallbackEndpoint' => $this->Link('uploaded'),
                    'syncWithApiEndpoint'     => $this->Link('syncWithApi')
                ]
            ]
        );
    }

    /**
     * The max allowed file size.
     *
     * @return int
     */
    public function getMaxFileSize()
    {
        return $this->maxFileSize ?: self::config()->get('maxFileSize');
    }

    /**
     * Get the timeout for the dropzone component.
     *
     * @return mixed
     */
    public function getTimeout()
    {
        return ($this->timeout ?: self::config()->get('timeout')) * 1000;
    }

    /**
     * Get the list of allowed file types in the format needed for the frontend (dropzone).
     *
     * @return string
     */
    private function getAllowedFileTypesForFrontend()
    {
        $types = [];
        foreach ($this->allowedFileTypes as $type) {
            $types[] = '.' . Util::sanitizeFileType($type);
        }

        return implode(',', $types);
    }

    /**
     * Get the file record according to the value if set.
     *
     * @return null|\SilverStripe\ORM\DataObject|ThreeQFile
     */
    public function getFile()
    {
        if (
            $this->Value() &&
            is_object($this->Value()) &&
            get_class($this->Value()) === ThreeQFile::class &&
            $this->Value()->exists()
        ) {
            return $this->Value();
        }

        return null;
    }

    /**
     * Define the min length of search terms needed to execute the search.
     *
     * @param int $chars
     * @return $this
     */
    public function setMinSearchChars($chars): ThreeQUploadField
    {
        $this->minSearchChars = $chars;

        return $this;
    }

    /**
     * Override the allowed max file size.
     *
     * @param int $maxFileSize In MB
     *
     * @return $this
     */
    public function setMaxFileSize($maxFileSize)
    {
        $this->maxFileSize = $maxFileSize;

        return $this;
    }

    /**
     * Override the config timeout value for this field.
     *
     * @param int $timeout Timeout in seconds
     *
     * @return $this
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    private function getJsonResponseObject($body = null): HTTPResponse
    {
        $response = HTTPResponse::create();
        $response->addHeader('Access-Control-Allow-Origin', '*');
        $response->addHeader('Content-Type', 'application/json');

        if ($body) {
            $response = $response->setBody($body);
        }

        return $response;
    }

    /**
     * Endpoint for search requests.
     *
     * @param HTTPRequest $request
     * @return HTTPResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function search(HTTPRequest $request): HTTPResponse
    {
        $query = $request->getVar('query') ? mb_strtolower($request->getVar('query')) : null;
        $files = ThreeQApiService::singleton()->getFiles();

        $payload = [];
        foreach ($files as $file) {
            $title = $file['Metadata']['Title'];
            if ($query && strpos(mb_strtolower($title), $query) === false) {
                continue;
            }

            $payload[] = [
                'id'    => $file['Id'],
                'title' => $title
            ];
        }

        return $this->getJsonResponseObject(json_encode($payload));
    }

    public function selectFile(HTTPRequest $request)
    {
        $data = json_decode($request->getBody(), true);
        $response = $this->getJsonResponseObject();

        if (!isset($data['fileId'])) {
            return $response->setStatusCode(400)->setBody('missing_file_id');
        }

        return $response->setBody(json_encode(ThreeQFile::requireForThreeQId($data['fileId'])->flat()));
    }

    public function getUploadUrl(HTTPRequest $request)
    {
        $data = json_decode($request->getBody(), true);
        $response = $this->getJsonResponseObject();

        if (!isset($data['name']) || !is_string($data['name']) || trim($data['name']) === '') {
            return $response->setStatusCode(400)->setBody('missing_name');
        }

        if (!isset($data['type']) || !is_string($data['type']) || trim($data['type']) === '') {
            return $response->setStatusCode(400)->setBody('missing_file_type');
        }

        $name = trim($data['name']);
        $type = Util::sanitizeFileType(trim($data['type']));

        try {
            return $response->setBody(ThreeQApiService::singleton()->getUploadUrl($name, $type));
        } catch (\Exception $e) {
            $response->setStatusCode(500);
            return $response->setBody($e->getMessage());
        }
    }

    public function uploaded(HTTPRequest $request)
    {
        $data = json_decode($request->getBody(), true);

        if (!isset($data['fileId'])) {
            return $this->getJsonResponseObject('missing_file_id')->setStatusCode(400);
        }

        return $this->getJsonResponseObject(json_encode(ThreeQFile::requireForThreeQId($data['fileId'])->flat()));
    }

    public function syncWithApi()
    {
        if ($this->getFile()) {
            $this->getFile()->syncWithApi();

            return $this->getJsonResponseObject(json_encode($this->getFile()->flat()));
        }

        return $this->getJsonResponseObject()->setStatusCode(204);
    }
}
