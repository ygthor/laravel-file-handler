<?php

namespace YGThor\LaravelFileHandler\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileHandle extends Model
{
    use HasFactory;
    protected $primaryKey = "fid";
    public $table = "file_handles";

    protected $guarded = ['fid'];
}
