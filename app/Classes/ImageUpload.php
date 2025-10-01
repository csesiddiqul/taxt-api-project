<?php

namespace App\Classes;

use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageUpload
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function fileUpload(
        $file = null,
        $data = null,
        string $folder = "other",
        int $width = 1500,
        ?int $height = null,
        string $property = 'image',
        ?string $fileName = null,
        ?string $extension = null,
    ): ?string {
        // যদি file ইনপুট না থাকে তবে request থেকে বের করার চেষ্টা করব
        if (!$file && request()->hasFile($property)) {
            $file = request()->file($property);
        }

        // এখনো file না থাকলে null রিটার্ন করে দেব
        if (!$file) {
            return null;
        }

        // যদি string হয় (Base64 বা blob), convert করব
        if (is_string($file)) {
            $file = $this->convertAndSaveBase64($file, $fileName);
        }

        // এখনো যদি valid UploadedFile না হয়, তাহলে exception
        if (!$file instanceof \Illuminate\Http\UploadedFile) {
            throw new \InvalidArgumentException('Invalid file type for upload.');
        }

        // ফোল্ডার তৈরি এবং পাথ ঠিক করা
        $url = "storage/{$folder}/";
        $path = public_path($url);

        if (!\Illuminate\Support\Facades\File::exists($path)) {
            \Illuminate\Support\Facades\File::makeDirectory($path, 0755, true, true);
        }

        // এক্সটেনশন সেট
        if (!$extension) {
            $extension = $file->getClientOriginalExtension() ?: 'png';
        }
        $extension = '.' . ltrim($extension, '.');

        // ফাইল নাম জেনারেট
        $fileName = $fileName ?: ($data ? $data->id : $property . '_' . time() . '_' . rand());
        $fileFullName = $fileName . $extension;

        // পুরাতন ফাইল থাকলে ডিলিট করব
        if ($data && isset($data->$property) && $data->$property && file_exists(public_path($data->$property))) {
            @unlink(public_path($data->$property));
        } elseif (file_exists(public_path($url . $fileFullName))) {
            @unlink(public_path($url . $fileFullName));
        } else {
            $fileWithExt = $this->fileExistsInPublic($url, $fileName);
            if ($fileWithExt) {
                @unlink(public_path($url . $fileWithExt));
            }
        }

        // ফাইল সেভ
        $this->saveFile($file, $width, $height, $path, $fileFullName);

        // ডাটাবেজে সেভ করলে
        if ($data && isset($data->$property)) {
            $data->$property = $url . $fileFullName;
            $data->save();
        }

        return $url . $fileFullName;
    }


    public function fileExistsInPublic($url, $fileName)
    {
        $filePath = public_path($url . $fileName . '.*');
        $files = glob($filePath);

        if (!empty($files)) {
            return basename($files[0]);
        }

        return false;
    }

    public function newFileUpload(
        $file,
        $data = null,
        $folder = "other",
        $width = 900,
        $hight = null,
        $property = 'image',
        $fileName = null,
        $extension = null,
    ) {
        if (request()->file($property) && !$file) {
            $file = request()->file($property);
        } elseif (!request()->file($property) && !$file) {
            return null;
            throw new \InvalidArgumentException('No file');
        }

        if (is_string($file)) {
            // Base64 or Blob data
            $file = $this->convertAndSaveBase64($file, $fileName);
        }

        if (!is_a($file, UploadedFile::class)) {
            throw new \InvalidArgumentException('ফাইল আপলোডের জন্য এই ডেটা টাইপ সমর্থন করে না।');
        }

        $url = "storage/{$folder}/";
        $path = public_path($url);

        if (!$extension && Str::startsWith($file->getMimeType(), 'image/')) {
            $extension = $extension ?? ".jpg";
        } else {
            $extension = $extension ?? "." . $file->getClientOriginalExtension();
        }

        $fileName = $fileName ?? "$property" . time() . rand() . $extension;

        if (!File::exists($path)) {
            // If it doesn't exist, create it
            File::makeDirectory($path, 0755, true, true);
        }

        if ($data && $data->$property && file_exists(public_path($data->$property))) {
            @unlink($data->$property);
            // Storage::delete($data->$property);
        }

        $this->saveFile($file, $width, $hight, $path, $fileName);

        if ($data) {
            $data->$property =  $url . $fileName;
            $data->save();
        }

        return $url . $fileName;
    }

   protected function saveFile($file, $width, $height, $path, $fileName)
{
    // Check if the uploaded file is an image
    if ($file->isValid() && Str::startsWith($file->getMimeType(), 'image/')) {
        try {
            $image = \Intervention\Image\Facades\Image::make($file);

            // Resize with aspect ratio maintained
            if ($width && !$height) {
                $image->resize($width, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            } elseif ($width && $height) {
                $image->resize($width, $height, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            $image->save($path . $fileName, 90); // 90% quality
        } catch (\Throwable $th) {
            // fallback if intervention fails
            File::put($path . $fileName, file_get_contents($file));
        }
    } else {
        File::put($path . $fileName, file_get_contents($file));
    }
}


    protected function convertAndSaveBase64($base64String, $filename)
    {
        // Remove the data URI scheme and save as a file
        $fileData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64String));
        $tempFilePath = tempnam(sys_get_temp_dir(), 'base64');
        file_put_contents($tempFilePath, $fileData);

        // Create an UploadedFile instance
        $file = new UploadedFile($tempFilePath, $filename);

        return $file;
    }

    public function uploadFile($file, $property = null, $folder = null)
    {
        $url = "storage/uploads/{$property}/{$folder}/";
        $path = public_path($url);
        $extension = "." . $file->getClientOriginalExtension();
        $filesize = $file->getSize();
        $fileName = "$property" . time() . rand() . $extension;
        $file->move($path, $fileName);
        $data = [
            "name" => "$property" . time() . rand(),
            "file_name" => "$property" . time() . rand() . $extension,
            "mime_type" => $file->getClientOriginalExtension(),
            "size" => $this->formatFileSize($filesize),
            "conversions_disk" => "",
            "thumbnail" => "",
            "file_type" => $file->getClientOriginalExtension(),
        ];

        return $data;
    }

    private function formatFileSize($size)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $i = 0;
        while ($size > 1024) {
            $size /= 1024;
            $i++;
        }

        return round($size, 2) . ' ' . $units[$i];
    }

    public function deleteFile(string $filePath, string $folder): bool
    {
        $fullPath = $folder . '/' . $filePath;
        if (Storage::exists(path: $fullPath)) {
            return Storage::delete($fullPath);
        }
        return false;
    }

    public function deletePdfFile(string $filePath): bool|string
    {
        $filePath = public_path($filePath);
        if (file_exists($filePath)) {
            return  @unlink($filePath);
        }
        return false;
    }
}
