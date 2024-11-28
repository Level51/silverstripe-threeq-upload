<?php

namespace Level51\ThreeQ;

use GuzzleHttp\Exception\GuzzleException;
use SilverStripe\Assets\File;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ValidationException;

/**
 * DataObject class for 3Q file records.
 *
 * @property int     $ThreeQId
 * @property string  $Title
 * @property string  $Name
 * @property string  $Thumbnail
 * @property boolean $IsFinished
 * @property int     $Size
 * @property float   $Length
 * @property string  $PlayoutId
 *
 * @package Level51\ThreeQ
 */
class ThreeQFile extends DataObject
{
    private static $table_name = 'ThreeQFile';

    private static $db = [
        'ThreeQId'   => 'Int',
        'Title'      => 'Text',
        'Name'       => 'Text',
        'Thumbnail'  => 'Varchar',
        'Size'       => 'Float',
        'Length'     => 'Float',
        'IsFinished' => 'Boolean',
        'PlayoutId'  => 'Text',
    ];

    // TODO on before delete handling?!

    /**
     * Get a flat version of this record.
     *
     * @param bool $autoSync
     * @return array
     *
     * @throws GuzzleException
     * @throws ValidationException
     */
    public function flat(bool $autoSync = true): array
    {
        if ($autoSync && !$this->IsFinished) {
            $this->syncWithApi();
        }

        // TODO maybe add (human readable) length/duration
        return [
            'id'         => $this->ThreeQId,
            'dbId'       => $this->ID,
            'title'      => $this->Title,
            'name'       => $this->Name,
            'thumbnail'  => $this->Thumbnail,
            'isFinished' => $this->IsFinished,
            'playoutId'  => $this->fetchPlayoutId(),
            'size'       => [
                'raw'       => $this->Size,
                'formatted' => File::format_size($this->Size),
            ],
        ];
    }

    /**
     * Sync meta data by calling the 3Q api.
     *
     * @return void
     *
     * @throws GuzzleException
     * @throws ValidationException
     */
    public function syncWithApi(): void
    {
        if (!$this->ThreeQId) {
            return;
        }

        $sanitizeText = fn($text) => $text ? str_replace('\'', '', $text) : null;

        $result = ThreeQApiService::singleton()->getFile($this->ThreeQId);
        if (!empty($result)) {
            $this->Name = $sanitizeText($result['Name'] ?? null);
            $this->Title = $sanitizeText($result['Metadata']['Title'] ?? null);
            $this->Length = $result['Properties']['Length'] ?? 0;
            $this->Size = $result['Properties']['Size'] ?? 0;
            $this->Thumbnail = $result['Metadata']['StandardFilePicture']['ThumbURI'] ?? null;
            $this->IsFinished = $result['IsFinished'] ?? false;
            $this->write();
        }
    }

    /**
     * Fetch the playout id for this file.
     *
     * Uses the one stored in the DB if already fetched and not forced via $forceApi = true.
     *
     * @param bool $forceApi
     * @return string|null
     * @throws GuzzleException
     * @throws ValidationException
     */
    public function fetchPlayoutId(bool $forceApi = false): ?string
    {
        if (!$this->PlayoutId || $forceApi) {
            $playoutIds = ThreeQApiService::singleton()->getPlayoutIds($this->ThreeQId);
            if (!empty($playoutIds)) {
                $this->PlayoutId = $playoutIds[0]['Id'];
                $this->write();
            }
        }

        return $this->PlayoutId;
    }

    /**
     * Create a new record for the given 3Q file id.
     *
     * @param string|int $id
     *
     * @return ThreeQFile
     *
     * @throws GuzzleException
     * @throws ValidationException
     */
    private static function createForThreeQId($id): ThreeQFile
    {
        $file = new self();
        $file->ThreeQId = $id;
        $file->write();
        $file->syncWithApi();

        return $file;
    }

    /**
     * Try to find a record for the given 3Q file id.
     *
     * @param string|int $id
     * @return ThreeQFile|DataObject|null
     */
    public static function byThreeQId($id): ?ThreeQFile
    {
        return self::get()->find('ThreeQId', $id);
    }

    /**
     * Get a record for the given id.
     *
     * This will either return an existing record or create a new one if necessary.
     *
     * @param string|int $id
     * @return ThreeQFile
     * @throws GuzzleException
     * @throws ValidationException
     */
    public static function requireForThreeQId($id): ThreeQFile
    {
        if ($file = self::byThreeQId($id)) {
            return $file;
        }

        return self::createForThreeQId($id);
    }
}
