<?php

namespace App\Relationships;

use App\Helper\FileUpload;
use App\Models\File;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Storage;

trait FileRelationship
{
    use FileUpload;

    public function file(): MorphOne
    {
        return $this->morphOne(File::class, 'file');
    }

    public function fileUrl($type = 'profile'): string
    {
        $file = $this->files()->where('type', $type)->first();

        if ($file != null && !empty($file->path)) {
            try {
                $normalised = ltrim($file->path, '/');
                
                // Only validate paths that are likely user profiles, skip driver/vehicle/driver-mate images
                $skipValidation = preg_match('/^(drivers|vehicles|settings|driver-mate|driver-mates)\//', $normalised);
                
                if (!$skipValidation && preg_match('/[\x80-\xFF]/', $normalised)) {
                    // Path contains invalid UTF-8 characters, return default
                    \Log::warning("Corrupted file path detected for {$type}: " . $file->path);
                    return $this->getDefaultImage($type);
                }
                
                if (Storage::disk($this->_disk)->exists($normalised)) {
                    return $this->filePath($normalised);
                }
            } catch (\Exception $e) {
                // Log the error and return default image
                \Log::warning("File access error for {$type}: " . $e->getMessage());
                return $this->getDefaultImage($type);
            }
        }

        // Return default dummy image for all profile/image types
        return $this->getDefaultImage($type);
    }

    /**
     * Get default image based on type
     */
    private function getDefaultImage($type): string
    {
        return match($type) {
            'logo'    => asset('storage/settings/logo.png'),
            default   => asset('storage/9MUt_272-2720656_user-profile-dummy-hd-png-download.png'),
        };
    }

    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'file');
    }

    public function fileUrls(): array
    {
        $fileUrls = [];

        if ($this->files != null) {
            foreach ($this->files as $file) {
                $normalised = ltrim($file->path, '/');
                $fileUrls[] = $this->filePath($normalised);
            }
        }

        return $fileUrls;
    }
}
