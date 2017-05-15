<?php namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use App\Models\ErrorCode;
use Illuminate\Http\Request;
use App\Tool\UUID;
use App\Models\M3Result;

class UploadController extends Controller
{
    /**
     * @param Request $request [上传文件]
     * @param array $type [以该值为上传文件夹命名，如jpg，则上传目录会出现一个jpg的dir]
     * @return string
     */
	 public function uploadFile(Request $request, $type = ['jpg'])
	 {
	 	$width = $request->input("width", '');
		$height = $request->input("height", '');
//		$m3_result = new M3Result();

         if(is_null($request->file('file')))
         {
             return M3Result::init(ErrorCode::$NO_FILE_UPLOAD);
         }

         if( $_FILES["file"]["error"] > 0 )
         {
            return M3Result::init(ErrorCode::$NO_FILE_UPLOAD,$_FILES["file"]["error"]);
        }

         $file_size = $_FILES["file"]["size"];
         if ( $file_size > 1024*1024) {
             return M3Result::init(ErrorCode::$FILE_TOO_LARGE,'请注意图片上传大小不能超过1M');
         }

         //$public_dir = sprintf('/upload/%s/%s/', $type, date('Ymd') );
         $public_dir = sprintf('/upload/%s/', date('Ymd') );
         $upload_dir = public_path() . $public_dir;
         if( !file_exists($upload_dir) ) {
             mkdir($upload_dir, 0777, true);
         }
         // 获取文件扩展名
         $arr_ext = explode('.', $_FILES["file"]['name']);
         $file_ext = count($arr_ext) > 1 && strlen( end($arr_ext) ) ? end($arr_ext) : "unknow";
         // 合成上传目标文件名
         $upload_filename = UUID::create();
         $upload_file_path = $upload_dir . $upload_filename . '.' . $file_ext;
         if (strlen($width) > 0)
         {
             $public_uri = $public_dir . $upload_filename . '.' . $file_ext;
             return M3Result::init(ErrorCode::$OK,'http://'.$_SERVER['SERVER_NAME'].$public_uri);
         } else {
             // 从临时目标移到上传目录
             if( move_uploaded_file($_FILES["file"]["tmp_name"], $upload_file_path) )
             {
                 $public_uri = $public_dir . $upload_filename . '.' . $file_ext;
                 return M3Result::init(ErrorCode::$OK,'http://'.$_SERVER['SERVER_NAME'].$public_uri);
             }
             return M3Result::init(ErrorCode::$NO_AUTH,'上传失败, 权限不足');
         }
	 }
}
