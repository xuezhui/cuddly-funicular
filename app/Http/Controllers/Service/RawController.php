<?php

namespace App\Http\Controllers\Service;

use App\Tool\UUID;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class RawController extends Controller
{
    /**
     * editormd插件文件上传
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadFile(Request $request)
    {
        if (!$request->hasFile('editormd-image-file')) {
            return response()->json([
                'status' => 0,
                'message' => '请选择文件上传'
            ]);
        }

        $file = $request->file('editormd-image-file');

        if (!$file->isValid()) {
            return response()->json([
                'status' => 0,
                'message' => $file->getErrorMessage()
            ]);
        }

        $public_dir = sprintf('/upload/%s/', date('Ymd') );
        $upload_dir = public_path() . $public_dir;
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        // 合成上传目标文件名
        $file_ext = $file->getClientOriginalExtension();
        $upload_filename = UUID::create(). '.' . $file_ext;
        $res = $file->move($upload_dir, $upload_filename);
        if (!is_object($res)) {
            return response()->json([
                'status' => 0,
                'message' => $file->getErrorMessage()
            ]);
        }
        return response()->json([
            'status' => 1,
            'message' => 'success',
            'url' => config('app.url').$public_dir.$upload_filename
        ]);
    }
}
