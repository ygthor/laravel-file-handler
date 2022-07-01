<?php

namespace YGThor\LaravelFileHandler;

use YGThor\LaravelFileHandler\Models\FileHandle;
use YGThor\LaravelFileHandler\Extra\MapMineTypeToExtension;
use Storage;

class LaravelFileHandler
{
    private $disk;
    public $status = 'permanent'; // upload status: default permanent, can user defined
    public $filename;
    public $content;
    public $override_if_exists;
    public $model_name;
    public $model_primary_key; //should be id
    public $model_column; //column used to store fid
    public $uploader_name; //column used to store fid

    // if true, when same name appear, override
    // if false, add timestamp to filename
    public $replace; //TODO

    public function __construct()
    {
        $this->disk = 's3'; // default as s3
    }

    public static function test()
    {
        return 'TEST FUNCTION BY LaravelFileHandlerx ';
    }

    public function setDisk($disk)
    {
        $this->disk = $disk ?? 's3'; // default as s3
        return $this;
    }

    public function setFilename($value)
    {
        $this->filename = $value;
        return $this;
    }
    public function setContent($value)
    {
        $this->content = $value;
        return $this;
    }
    public function setOverrideIfExists($value)
    {
        $this->content = $value;
        return $this;
    }
    public function set($arr)
    {
        $this->disk = $arr['disk'] ?? 's3';
        $this->filename = $arr['filename'] ?? null;
        $this->content = $arr['content'] ?? null;
        $this->model_name = $arr['model_name'] ?? null;
        $this->model_primary_key = $arr['model_primary_key'] ?? null;
        $this->model_column = $arr['model_column'] ?? null;
        $this->uploader_name = $arr['uploader_name'] ?? null;
        $this->status = $arr['status'] ?? 'permanent';
        $this->override_if_exists = $arr['override_if_exists'] ?? true;

        return $this;
    }

    public function upload($filename = null, $content = null)
    {
        if ($filename == null) $filename = $this->filename;
        if ($content == null) $content = $this->content;

        if ($filename == null) {
            return [
                'error' => true,
                'message' => 'File name cannot be empty'
            ];
        }
        if ($content == null) {
            return [
                'error' => true,
                'message' => 'Content cannot be empty'
            ];
        }

        $filename_arr = explode('/', $filename); //last array
        $real_filename = end($filename_arr);
        $real_file_ext_arr = explode('.', $real_filename);
        if (count($real_file_ext_arr) > 1) {
            $real_file_ext = end($real_file_ext_arr);
        } else {
            $real_file_ext = null;
        }


        $status = Storage::disk($this->disk)->put($filename, $content);

        if ($status) {
            if ($filename != null && $status === true) { //if filename not found
                //use param file_name
            } else {
                $filename = $status; //if success, s3 will return file path and name
            }

            //Storage 
            $arr = [
                'filename' => $real_filename,
                'filepath' => $filename,
                'file_ext' => $real_file_ext,
                'filemine' => null, //WAIT FOR REAL UPLOAD
                'disk' => $this->disk,
                'status' => $this->status,
                'model_name' => $this->model_name,
                'model_primary_key' => $this->model_primary_key,
                'model_column' => $this->model_column,
                'uploader_name' => $this->uploader_name,
                'created_by' => auth()->check() ? auth()->user()->id : null,
            ];
            $f = FileHandle::create($arr);

            $update_arr = [];
            $mine_type = $this->getMineType($filename);
            $size = $this->getSize($filename);
            $file_url = Storage::disk($this->disk)->url($filename);

            $update_arr['filemine'] = $mine_type;
            $update_arr['filesize'] = $size;
            $update_arr['url'] = $file_url;

            if ($real_file_ext == null) {
                $update_arr['file_ext'] = MapMineTypeToExtension::getExtension($mine_type);
            }

            FileHandle::find($f->fid)->update($update_arr);


            return $f;
        } else {
            dump($status);
            return false;
        }
    }

    public function uploadTemporary($filename = null, $content = null)
    {
        $this->status = 'temporary';
        return $this->upload($filename, $content);
    }

    public function delete($fid){
        $path = $this->getS3Path($fid);
        if($path == null){
            return false;
        }
        \Storage::disk($this->disk)->delete($path);
        FileHandle::find($fid)->delete();
        return true;
    }


    public function getMineType($filename = null)
    {
        if ($filename == null)  $filename = $this->filename;
        if (Storage::disk($this->disk)->exists($filename)) {
            $type = Storage::disk($this->disk)->mimeType($filename);
            return $type;
        } else {
            return false;
        }
    }
    public function getSize($filename = null)
    {
        if ($filename == null)  $filename = $this->filename;
        if (Storage::disk($this->disk)->exists($filename)) {
            $type = Storage::disk($this->disk)->size($filename);
            return $type;
        } else {
            return false;
        }
    }

    //get content
    public function get($filename = null)
    {
        if ($filename == null)  $filename = $this->filename;
        if (Storage::disk($this->disk)->exists($filename)) {
            return Storage::disk($this->disk)->get($filename);
        } else {
            return false;
        }
    }


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
            return Storage::disk($this->disk)->temporaryUrl($file->filepath, now()->addMinutes(5));
        }
    }

    //get s3 content url by s3 path
    function getS3UrlByPath($path)
    {
        return Storage::disk($this->disk)->temporaryUrl($path, now()->addMinutes(5));
    }


    function exists($path){
        return Storage::disk($this->disk)->exists($path);
    }
}
