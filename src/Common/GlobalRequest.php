<?php

namespace App\Common;

use App\Common\GlobalConfigManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GlobalRequest{

    public function __construct(protected ValidatorInterface $validator, public GlobalManager $globalConfigManager){
        $this->populate();

        if ($this->autoValidateRequest()) {
            $this->validate();
        }
    }

    public function validate(){
        $errors   =  $this->validator->validate($this);
        $response = ['result' => null, 'error' => []];
        /** @var \Symfony\Component\Validator\ConstraintViolation  */
        foreach ($errors as $message) {
            $response['error'][] = [
                'property' => $message->getPropertyPath(),
                'value' => $message->getInvalidValue(),
                'message' => $message->getMessage(),
            ];
        }
        if (count($response['error']) > 0) {
            $response = new JsonResponse($response, 201);
            $response->send();

            exit;
        }
    }

    public static function getRequest(){
        return Request::createFromGlobals()->toArray();
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

    protected function autoValidateRequest(): bool{
        return true;
    }
}