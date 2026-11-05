<?php

namespace App\Helper;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait FileUpload
{
    private $_disk = 'public';

    public function uploadFile($file, $filePath)
    {
        $name = Str::random(4) . '_' . $file->getClientOriginalName();
        $folder = rtrim($filePath, '/') . '/';
        $path = ltrim($folder . $name, '/');

        // Use putFileAs to let the filesystem handle directories and visibility
        Storage::disk($this->_disk)->putFileAs($folder, $file, $name);

        return [
            'name' => $name,
            'path' => $path,
        ];
    }

    public function deleteFile($filePath)
    {
        return Storage::disk($this->_disk)->delete($filePath);
    }

    public function filePath($filePath)
    {
        return Storage::disk($this->_disk)->url(ltrim($filePath, '/'));
    }

    public function uploadMultipleFiles(array $files, string $filePath): array
    {
        $uploaded = [];

        foreach ($files as $file) {
            $name = Str::random(4) . '_' . $file->getClientOriginalName();
            $folder = rtrim($filePath, '/') . '/';
            $path = ltrim($folder . $name, '/');

            Storage::disk($this->_disk)->putFileAs($folder, $file, $name);

            $uploaded[] = [
                'file_name' => $name,
                'file_path' => $path,
                'file_type' => $file->getClientMimeType(),
            ];
        }

        return $uploaded;
    }

}
