<?php

namespace App\Controller;

use App\Handler\UserHandler;
use App\Common\GlobalRequest;
use App\Request\UserCreateRequest;
use App\Request\UserEditRequest;
use App\Request\UserIdRequest;
use App\Common\GlobalManager;
use App\Handler\UploadHandler;
use App\Request\UserUploadRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/user")]
class UserController extends GlobalManager
{

    #[Route('/login')]
    public function login(UserHandler $handler): JsonResponse
    {
        $user  = $this->getUser();
        $token = $this->generateToken();

        $user->setApiToken($token);
        $this->repository($handler::ENTITY_NAME)->save($user, true);
        return $this->customResponse(array("user" => $user));
    }

    #[Route('/create', methods: ["POST"])]
    public function userCreate(UserHandler $handler, UserPasswordHasherInterface $passwordHasher, UserCreateRequest $automatizedValidator): JsonResponse
    {
        try {
            $automatizedValidator->validate();
            $request  = GlobalRequest::getRequest();
            $user     = $handler->set($request, $passwordHasher);
            $response = $handler->beforeSave($user);
        } catch (\Exception $e) {
            return $this->customResponse(null, $e->getMessage());
        }
        return $this->customResponse($response);
    }

    #[Route('/edit', methods: ["POST"])]
    public function userEdit(UserHandler $handler, UserPasswordHasherInterface $passwordHasher, UserEditRequest $automatizedValidator): JsonResponse
    {
        try {
            $automatizedValidator->validate();
            $request  = GlobalRequest::getRequest();
            $user     = $handler->set($request, $passwordHasher, true);
            $response = $handler->beforeSave($user);
        } catch (\Exception $e) {
            return $this->customResponse(null, $e->getMessage());
        }
        return $this->customResponse($response);
    }

    #[Route('/upload', methods: ["POST"])]
    public function userUpload(UserHandler $userHandler, UserPasswordHasherInterface $passwordHasher, UserUploadRequest $automatizedValidator): JsonResponse
    {
        try {
            $request  = GlobalRequest::getFieldsRequest(array(
                UploadHandler::FILE_PARAM => GlobalRequest::FILE_REQUEST,
                UserHandler::ID_PARAM   => GlobalRequest::FIELD_REQUEST
            ));
            $user     = $userHandler->set($request, null, true);
            $response = $userHandler->beforeSave($user);
        } catch (\Exception $e) {
            return $this->customResponse(null, $e->getMessage());
        }
        return $this->customResponse($response);
    }

    #[Route("/remove", methods: ["POST"])]
    public function remove(UserHandler $handler, UserIdRequest $automatizedValidator): JsonResponse
    {
        try {
            $request = GlobalRequest::getRequest();
            $result  = $handler->remove($request);
        } catch (\Exception $e) {
            return $this->customResponse(null, $e->getMessage());
        }
        return $this->customResponse($result);
    }

    #[Route("/get", methods: ["POST"])]
    public function get(UserHandler $handler, UserIdRequest $automatizedValidator): JsonResponse
    {
        try {
            $request = GlobalRequest::getRequest();
            $result  = $handler->get($request);
        } catch (\Exception $e) {
            return $this->customResponse(null, $e->getMessage());
        }
        return $this->customResponse($result);
    }
}
