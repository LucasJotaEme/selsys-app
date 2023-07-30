<?php

namespace App\Security;

use App\Common\GlobalManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;

class ApiTokenAuthenticator extends AbstractAuthenticator
{

    const HEADER_API_KEY  = "apiKey";
    const HEADER_USER     = "user";
    const HEADER_PASSWORD = "password";

    const REQUEST_LOGIN       = "login";
    const REQUEST_USER_CREATE = "/user/create";
    const GUESS_ARRAY = array(
        self::REQUEST_USER_CREATE
    );

    private $globalManager;

    public function __construct(GlobalManager $globalManager){
        $this->globalManager = $globalManager;
    }

    public function supports(Request $request): ?bool
    {
        return true;
    }

    public function authenticate(Request $request): Passport
    {
        $apiToken = $request->headers->get(self::HEADER_API_KEY);
        
        // Si es url login
        if(strpos($request->getRequestUri(), self::REQUEST_LOGIN)){
            $user = $request->headers->has(self::HEADER_USER);
            $psw  = $request->headers->has(self::HEADER_PASSWORD);
            if($user && $psw){
                $user = $request->headers->get(self::HEADER_USER);
                $psw  = $request->headers->get(self::HEADER_PASSWORD);
                try{
                    $passport = new Passport(
                        new UserBadge($user, function ($userIdentifier){
                        return $this->globalManager->repository("User")->findOneBy(array("email" => $userIdentifier));
                        }),
                        new PasswordCredentials($psw)
                    );
                    return $passport;
                }catch(\Exception $e){
                    throw new CustomUserMessageAuthenticationException($e->getMessage());
                }
            }
            $errorByUserOrPassword = !$user && !$psw
                ? "user and password parameters not found" 
                : (!$user 
                    ? "user parameter not found" 
                    : "password parameter not found");
            throw new CustomUserMessageAuthenticationException($errorByUserOrPassword);
        }else{
            if(null === $apiToken && null === $this->validateGuessUrl($request->getRequestUri())){
                throw new CustomUserMessageAuthenticationException('No API token provided');
            }
        }
        try{
            $userByApiToken = $this->globalManager->repository("User")->findOneBy(array("apiToken" => $apiToken));
        }catch(\Exception $e){
            throw new CustomUserMessageAuthenticationException($e->getMessage());
        }
        if(null === $userByApiToken){
            throw new CustomUserMessageAuthenticationException("User with API token $apiToken not found");
        }
        return new SelfValidatingPassport(new UserBadge($userByApiToken->getEmail()));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())
        ];
        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    //Chequea que la URL sea para user guess
    private function validateGuessUrl($requestUri){
        if(in_array($requestUri, self::GUESS_ARRAY)){
            return $this->globalManager->repository("User")->findOneBy(array("email" => "guess"))->getApiToken();
        }
        return null;
    }
}
