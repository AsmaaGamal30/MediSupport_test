<?php
namespace App\Traits;
trait ApiResponse{
    public function apiResponse(string $message, $data,bool $error,int $statuscode){

        return response()->json(
            [
            'message'=>$message,
            'data'=>$data ,
            'error'=>$error
            ], $statuscode);
    }
    public function success(string $message,int $statuscode =200){
        return $this->Apiresponse($message,[],false,$statuscode);
    }
    public function error(string $message,int $statuscode=404){
        return $this->Apiresponse($message,[],true,$statuscode);
    }

    public function sendData(string $message,$data,int $statuscode=200){
        return $this->Apiresponse($message,$data,false,$statuscode);
    }

}
