# SilverStripe 3Q SDN file upload
Adds a data object and upload field for maintaining video files stored by [3Q](https://3q.video/).

## Features
- ThreeQUploadField with 
  - direct uploads from the browser to 3Q
  - selection of existing files via the 3Q api
  - select only mode if no uploads should be allowed
- ThreeQFile data object for relation handling, independent from Silverstripe file records

## Config
The credentials have to be maintained via environment config. Example using .env:

```
THREEQ_API_KEY=""
THREEQ_PROJECT_ID=""
```

## Requirements
- Silverstripe ^4.7
- PHP >= 7.3
- API access for 3Q

## Maintainer
- Level51 [hallo@lvl51.de](mailto:hallo@lvl51.de)
