<?php

namespace App\Helpers;
use Illuminate\Support\Str;
use Storage;

class FileUploader {

    private static $fileUploadPath;
    private static $diskName = "uploads";

    public static function createFileName($prefix = '', $file) {
        return strtolower(preg_replace('/[\W_]+/', '', $prefix) .'-' .Date('Ymd-His') .'-' .Str::random(32) .'.' .$file->getClientOriginalExtension());
    }

    public static function getCleanName($filename)
    {
        return preg_replace("/[^a-zA-Z0-9-_. ]/", "", $filename);
    }

    public static function multipleUpload($files, $options = [
        'file_name_prefix' => 'upload',
        'file_attachment_type' => null,
        'file_upload_path' => null,
    ]) {

        if ( !$files ) {
            throw new \Exception('The files is required!');
        }

        if ( !isset($options['file_upload_path']) ) {
            throw new \Exception('The file_upload_path option is required!');
        }

        $data = [];

        foreach ($files as $key => $file) {
            $type = $options['file_attachment_type'] ?? 'other';
            $filename = self::createFileName($options['file_name_prefix'], $file);
            $path = Storage::disk(self::getDiskName())->putFileAs($options['file_upload_path'], $file, $filename);
            
            $data[] = [
                'filename' => $filename,
                'path' => $path,
                'type' => $type,
                'content_type' => substr( $file->getMimeType(), 0, 50 ),
                'size' => $file->getSize(),
                'uploaded_by' => auth()->user()->id,
            ];
        }

        return $data;
    }

    public static function singleUpload($file, $options = [
        'file_name_prefix' => 'upload',
        'file_attachment_type' => null,
        'file_upload_path' => null,
        'old_file_path' => null,
    ]) {

        if ( !$file ) {
            throw new \Exception('The file is required!');
        }

        if ( !isset($options['file_upload_path']) ) {
            throw new \Exception('The file_upload_path option is required!');
        }

        $data = [];

        $type = $options['file_attachment_type'] ?? 'other';
        $filename = self::createFileName($options['file_name_prefix'], $file);
        $path = Storage::disk(self::getDiskName())->putFileAs($options['file_upload_path'], $file, $filename);
        
        if (isset($options['old_file_path'])) {
            Storage::disk(self::getDiskName())->delete($options['old_file_path']);
        }

        $data = [
            'filename' => $filename,
            'path' => $path,
            'origfilename' => self::getCleanName($file->getClientOriginalName()),
            'type' => $type,
            'content_type' => substr( $file->getMimeType(), 0, 50 ),
            'size' => $file->getSize(),
            'uploaded_by' => auth()->user()->id,
        ];
        
        return $data;
    }

    public static function delete($path)
    {
        return Storage::disk(self::getDiskName())->delete($path);
    }

    public static function download($path)
    {
        return Storage::disk(self::getDiskName())->download($path);
    }

    public static function setDiskName($disk)
    {
        self::$diskName = $disk;

        return new static;
    }

    public static function getDiskName()
    {
        return self::$diskName;
    }

}
