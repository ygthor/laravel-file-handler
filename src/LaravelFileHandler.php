<?php

namespace YGThor\LaravelFileHandler;

use Caritech\LaravelFileHandler\Models\FileHandle;
use Storage;

class LaravelFileHandler
{
    private $disk;
    public $filename;
    public $content;
    public $model_name;
    public $model_primary_key; //should be id
    public $model_column; //column used to store fid
    public $uploader_name; //column used to store fid

    // if true, when same name appear, override
    // if false, add timestamp to filename
    public $replace; //TODO

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
    public function set($arr)
    {
        $this->filename = $arr['filename'] ?? null;
        $this->content = $arr['content'] ?? null;
        $this->model_name = $arr['model_name'] ?? null;
        $this->model_primary_key = $arr['model_primary_key'] ?? null;
        $this->model_column = $arr['model_column'] ?? null;
        $this->uploader_name = $arr['uploader_name'] ?? null;
        return $this;
    }

    public function upload($filename = null, $content = null)
    {
        if ($filename == null) $filename = $this->filename;
        if ($content == null) $content = $this->content;

        // PARAMETER IS INCOMPLETE
        if ($filename == null || $content == null) return false;

        $filename_arr = explode('/', $filename); //last array
        $real_filename = end($filename_arr);
        $real_file_ext_arr = explode('.', $real_filename);
        $real_file_ext = end($real_file_ext_arr);

        $status = Storage::disk('s3')->put($filename, $content);

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
                'disk' => 's3',
                'status' => 'permanent',
                'model_name' => $this->model_name,
                'model_primary_key' => $this->model_primary_key,
                'model_column' => $this->model_column,
                'uploader_name' => $this->uploader_name,
                'created_by' => auth()->check() ? auth()->user()->id : null,
            ];
            $f = FileHandle::create($arr);

            $mine_type = $this->getMineType($filename);
            $size = $this->getSize($filename);

            $file_url = Storage::disk('s3')->url($filename);

            FileHandle::find($f->fid)->update([
                'filemine' => $mine_type,
                'filesize' => $size,
                'url' => $file_url,
            ]);

            return $f;
        } else {
            return false;
        }
    }

    public function getMineType($filename = null)
    {
        if ($filename == null)  $filename = $this->filename;
        if (Storage::disk('s3')->exists($filename)) {
            $type = Storage::disk('s3')->mimeType($filename);
            return $type;
        } else {
            return false;
        }
    }
    public function getSize($filename = null)
    {
        if ($filename == null)  $filename = $this->filename;
        if (Storage::disk('s3')->exists($filename)) {
            $type = Storage::disk('s3')->size($filename);
            return $type;
        } else {
            return false;
        }
    }

    //get content
    public function get($filename = null)
    {
        if ($filename == null)  $filename = $this->filename;
        if (Storage::disk('s3')->exists($filename)) {
            return Storage::disk('s3')->get($filename);
        } else {
            return false;
        }
    }
}
