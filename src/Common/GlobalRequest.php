<?php

namespace App\Common;

use App\Common\GlobalConfigManager;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GlobalRequest extends GlobalManager{

    const FIELD_REQUEST = "request";
    const FILE_REQUEST  = "file";


    public function __construct(protected ValidatorInterface $validator, private GlobalManager $globalConfigManager){
        $this->populate();
    }

    public function validate(){
        $errors   =  $this->validator->validate($this);
        $response = ['result' => null, 'error' => ''];
        /** @var \Symfony\Component\Validator\ConstraintViolation  */
        foreach ($errors as $message) {
            $response['error'] .= "Property {$message->getPropertyPath()} is not valid, {$message->getMessage()} ";
        }
        if ($response['error'] != '') {
            throw new Exception($response['error']);
        }
    }

    public static function getRequest(){
        return Request::createFromGlobals()->toArray();
    }

    public static function getFieldsRequest($fields){
        $globalRequest = Request::createFromGlobals();
        $request = array();
        foreach($fields as $field => $type){
            $obtainedField = null;
            switch($type){
                case self::FIELD_REQUEST:
                    $obtainedField = $globalRequest->request->get($field);
                    break;
                case self::FILE_REQUEST:
                    $obtainedField = $globalRequest->files->get($field);
                    break;
            }
            if(null === $obtainedField){
                throw new Exception("Field $field not found");
            }
            $request[$field] = $obtainedField;
        }
        return $request;
    }

    protected function populate(){
        try{
            $requestAsArray = $this->getRequest();
            foreach($requestAsArray as $property => $value){
                if(property_exists($this, $property)){
                    $this->{$property} = $value;
                }
            }
        }catch(\Exception $e){

        }
    }
}