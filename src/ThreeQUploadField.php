<?php

namespace Level51\ThreeQ;

use GuzzleHttp\Exception\GuzzleException;
use Psr\Container\NotFoundExceptionInterface;
use Psr\SimpleCache\InvalidArgumentException;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Forms\FormField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ValidationException;
use SilverStripe\Security\Security;
use SilverStripe\View\Requirements;

/**
 * CMS field for 3Q file handling.
 *
 * Includes a select field for existing files, an upload component (using dropzone) and a preview.
 *
 * @todo
 *   - check folder support
 *   - cleanup, rearrange, add return types...
 *
 * @package Level51\ThreeQ
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
     *
     * @throws GuzzleException
     * @throws ValidationException
     */
    public function getPayload(): string
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
                    'acceptedFiles' => $this->getAllowedFileTypesForFrontend()
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
     * @return int|null
     */
    public function getMaxFileSize(): ?int
    {
        return $this->maxFileSize ?: self::config()->get('maxFileSize');
    }

    /**
     * Get the list of allowed file types in the format needed for the frontend (dropzone).
     *
     * @return string
     */
    private function getAllowedFileTypesForFrontend(): string
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
     * @return null|DataObject|ThreeQFile
     */
    public function getFile(): ?ThreeQFile
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
     *
     * @return $this
     */
    public function setMinSearchChars(int $chars): ThreeQUploadField
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
    public function setMaxFileSize(int $maxFileSize): ThreeQUploadField
    {
        $this->maxFileSize = $maxFileSize;

        return $this;
    }

    /**
     * Helper function to get a new http response with proper header for json results.
     *
     * @param $body
     *
     * @return HTTPResponse
     */
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
     *
     * @return HTTPResponse
     *
     * @throws GuzzleException
     * @throws NotFoundExceptionInterface
     * @throws InvalidArgumentException
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

    /**
     * Endpoint after a file was selected.
     *
     * This will create the ThreeQFile record if necessary and return the flat version.
     *
     * @param HTTPRequest $request
     *
     * @return HTTPResponse
     *
     * @throws GuzzleException
     * @throws ValidationException
     */
    public function selectFile(HTTPRequest $request): HTTPResponse
    {
        $data = json_decode($request->getBody(), true);
        $response = $this->getJsonResponseObject();

        if (!isset($data['fileId'])) {
            return $response->setStatusCode(400)->setBody('missing_file_id');
        }

        return $response->setBody(json_encode(ThreeQFile::requireForThreeQId($data['fileId'])->flat()));
    }

    /**
     * Get a upload url for a new file.
     *
     * @param HTTPRequest $request
     * @return HTTPResponse
     * @throws GuzzleException
     */
    public function getUploadUrl(HTTPRequest $request): HTTPResponse
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

    /**
     * Callback endpoint after successful file upload.
     *
     * This will create the ThreeQFile record and return the flat version.
     *
     * @param HTTPRequest $request
     * @return HTTPResponse
     * @throws GuzzleException
     * @throws ValidationException
     */
    public function uploaded(HTTPRequest $request): HTTPResponse
    {
        $data = json_decode($request->getBody(), true);

        if (!isset($data['fileId'])) {
            return $this->getJsonResponseObject('missing_file_id')->setStatusCode(400);
        }

        return $this->getJsonResponseObject(json_encode(ThreeQFile::requireForThreeQId($data['fileId'])->flat()));
    }

    /**
     * Trigger a sync with the 3Q api for the linked file (if exists).
     *
     * @return HTTPResponse
     * @throws GuzzleException
     * @throws ValidationException
     */
    public function syncWithApi(): HTTPResponse
    {
        if ($this->getFile()) {
            $this->getFile()->syncWithApi();

            return $this->getJsonResponseObject(json_encode($this->getFile()->flat()));
        }

        return $this->getJsonResponseObject()->setStatusCode(204);
    }
}
