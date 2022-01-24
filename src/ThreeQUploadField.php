<?php

namespace Level51\ThreeQ;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Forms\FormField;
use SilverStripe\Security\Security;
use SilverStripe\View\Requirements;

/**
 * @todo
 *   - check allowed file types
 *   - check folder support
 *   - cleanup, rearrange, add return types...
 */
class ThreeQUploadField extends FormField
{
    private static $allowed_actions = ['search'];

    /**
     * @var int Max file size in MB, overrides the config value if set
     */
    protected $maxFileSize;

    /**
     * @var int Min amount of characters needed to execute the search callback
     */
    private $minSearchChars = 3;

    /**
     * @var int Max duration in seconds until the XHR request is canceled, , overrides the config value if set.
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

        if (!($file = ThreeQFile::byThreeQId($value))) {
            $file = ThreeQFile::createForThreeQId($value);
        }

        return $file->ID;
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
                    'acceptedFiles' => 'mp4', // TODO check
                    'timeout'       => $this->getTimeout(),
                ],
                'config'          => [
                    'minSearchChars' => $this->minSearchChars,
                    'searchEndpoint' => $this->Link('search'),
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
     * Get the file record according to the value if set.
     *
     * @return null|\SilverStripe\ORM\DataObject|ThreeQFile
     */
    public function getFile()
    {
        if ($this->Value()) {
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

        $response = HTTPResponse::create();
        $response->addHeader('Access-Control-Allow-Origin', '*');
        $response->addHeader('Content-Type', 'application/json');

        return $response->setBody(json_encode($payload));
    }
}
