<?php

namespace Appsolutely\AIO\Http\Controllers;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class VditorController
{
    public function upload(Request $request)
    {
        $files = $request->file('file[]') ?: $request->file('file');
        $dir   = trim($request->get('dir'), '/');
        $disk  = $this->disk();

        $succMap  = [];
        $errFiles = [];

        foreach ((array) $files as $file) {
            try {
                $newName = $this->generateNewName($file);
                $disk->putFileAs($dir, $file, $newName);
                $succMap[$file->getClientOriginalName()] = $disk->url("{$dir}/{$newName}");
            } catch (\Throwable $e) {
                $errFiles[] = $file->getClientOriginalName();
            }
        }

        return response()->json([
            'msg'  => '',
            'code' => 0,
            'data' => [
                'errFiles' => $errFiles,
                'succMap'  => $succMap,
            ],
        ]);
    }

    protected function generateNewName(UploadedFile $file): string
    {
        return uniqid(md5($file->getClientOriginalName())) . '.' . $file->getClientOriginalExtension();
    }

    /**
     * @return Filesystem|FilesystemAdapter
     */
    protected function disk()
    {
        $disk = request()->get('disk') ?: config('admin.upload.disk');

        return Storage::disk($disk);
    }
}
