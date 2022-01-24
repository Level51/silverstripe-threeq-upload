<?php

namespace Level51\ThreeQ;

use SilverStripe\Assets\File;
use SilverStripe\ORM\DataObject;

/**
 * Class ThreeQFile.
 *
 * @property int    $ThreeQId
 * @property string $Title
 * @property string $Name
 * @property string $Thumbnail
 * @property int    $Size
 * @property float  $Length
 */
class ThreeQFile extends DataObject
{
    private static $table_name = 'ThreeQFile';

    private static $db = [
        'ThreeQId'  => 'Int',
        'Title'     => 'Text',
        'Name'      => 'Text',
        'Thumbnail' => 'Varchar',
        'Size'      => 'Float',
        'Length'    => 'Float'
        // TODO check further useful fields
    ];

    // TODO on before delete handling?!

    public function flat(): array
    {
        // TODO maybe add (human readable) length/duration
        return [
            'id'        => $this->ID,
            'threeQId'  => $this->ThreeQId,
            'title'     => $this->Title,
            'name'      => $this->Name,
            'thumbnail' => $this->Thumbnail,
            'size'      => [
                'raw'       => $this->Size,
                'formatted' => File::format_size($this->Size)
            ]
        ];
    }

    public function syncWithApi()
    {
        if (!$this->ThreeQId) {
            return;
        }

        $result = ThreeQApiService::singleton()->getFile($this->ThreeQId);
        if (!empty($result)) {
            $this->Name = $result['Name'] ?? null;
            $this->Title = $result['Metadata']['Title'] ?? null;
            $this->Length = $result['Properties']['Length'] ?? 0;
            $this->Size = $result['Properties']['Size'] ?? 0;
            $this->Thumbnail = $result['Metadata']['StandardFilePicture']['ThumbURI'] ?? null;
            $this->write();
        }
    }

    public static function createForThreeQId($id): ThreeQFile
    {
        $file = new self();
        $file->ThreeQId = $id;
        $file->write();
        $file->syncWithApi();

        return $file;
    }

    public static function byThreeQId($id): ?ThreeQFile
    {
        return self::get()->find('ThreeQId', $id);
    }
}
