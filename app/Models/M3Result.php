<?php

namespace App\Models;

class M3Result
{

  public $status;
  public $message;

  public function toJson()
  {
    return json_encode($this, JSON_UNESCAPED_UNICODE);
  }

  public static function init($errorCode = null, $result = null, $extraInfo = null)
    {
        return json_encode(array(
            'errorCode'  => is_array($errorCode) ? $errorCode[0] : $errorCode,
            'errorStr'   => is_array($errorCode) ? $errorCode[1] : $errorCode,
            'resultCount'=> (is_array($result) && array_values($result) === $result ? count($result) : 1),
            'results'    => $result,
            'extraInfo'  => $extraInfo
        ), JSON_UNESCAPED_UNICODE);
    }
}
