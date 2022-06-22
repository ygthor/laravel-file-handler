# Very short description of the package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ygthor/laravel-file-handler.svg?style=flat-square)](https://packagist.org/packages/ygthor/laravel-file-handler)
[![Total Downloads](https://img.shields.io/packagist/dt/ygthor/laravel-file-handler.svg?style=flat-square)](https://packagist.org/packages/ygthor/laravel-file-handler)
![GitHub Actions](https://github.com/ygthor/laravel-file-handler/actions/workflows/main.yml/badge.svg)

- Tested on Laravel 8
- Hard-coded to use storage 's3' only

## Installation

You can install the package via composer:

```bash
composer require ygthor/laravel-file-handler
```

### Table to add

```sql
CREATE TABLE `file_handles` (
  `fid` bigint(20) UNSIGNED NOT NULL,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `filepath` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_ext` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `filemine` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `filesize` int(11) DEFAULT NULL,
  `disk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_primary_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_column` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uploader_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
ALTER TABLE `file_handles` ADD PRIMARY KEY (`fid`);
ALTER TABLE `file_handles` MODIFY `fid` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
```

### .env to set
```
AWS_ACCESS_KEY_ID="GET_FROM_OBJECT_STORAGE"
AWS_SECRET_ACCESS_KEY="GET_FROM_OBJECT_STORAGE"
AWS_DEFAULT_REGION="ap-south-1"
AWS_BUCKET="my-bucket"
AWS_ENDPOINT="https://ap-south-1.linodeobjects.com"
AWS_USE_PATH_STYLE_ENDPOINT=false
```

### update config/filesystems.php if not match
```
's3' => [
    'driver' => 's3',
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION'),
    'bucket' => env('AWS_BUCKET'),
    'url' => env('AWS_URL'),
    'endpoint' => env('AWS_ENDPOINT'),
    'bucket_endpoint' => false,
    'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
],
```


## Usage

* Need to add column such as fid_photo, fid_photo_2 in source table

```php
...
use LaravelFileHandler;
...

function save(){
    $profile_image = $request->file('profile_image');
    $user_id = auth()->id();

    $file_extension = getInputFileExtension('profile_image');
    $file_handle = LaravelFileHandler::set([
        'filename' => 'optional_image_path/' . $user_id . '/profile',
        'content' => $profile_image,
        'model_name' => '\App\Models\User',
        'model_primary_key' => 'id',
        'model_column' => 'profile_photo_fid',
        'uploader_name' => auth()->user()->name,
    ])->upload();

    User::find($user_id)->fill(['photo_fid' => $file_handle->fid])->save();
}

```

## Some Helper functions for S3
```php
function getS3Path($fid)
{
    $file = FileHandle::find($fid);
    if ($file == null) {
        return null;
    } else {
        return $file->filepath;
    }
}

// get s3 content url by fid
// can use to view images
function getS3Url($fid)
{
    $file = FileHandle::find($fid);
    if ($file == null) {
        return null;
    } else {
        return Storage::disk('s3')->temporaryUrl($file->filepath, now()->addMinutes(5));
    }
}

//get s3 content url by s3 path
function getS3UrlByPath($path)
{
    return Storage::disk('s3')->temporaryUrl($path, now()->addMinutes(5));
}
```


### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email yuguan@caritech.com instead of using the issue tracker.

## Credits

-   [YG Thor](https://github.com/caritech)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
