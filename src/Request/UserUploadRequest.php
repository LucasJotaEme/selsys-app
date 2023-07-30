<?php

namespace App\Request;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Password;
use App\Common\GlobalRequest;

class UserUploadRequest extends GlobalRequest{

    // #[NotBlank(), Email()]
    // protected $email;

    // #[NotBlank(), Length(
    //     min: 5, minMessage: 'Password is too short',
    //     max: 15, maxMessage: 'Password is too long'
    //     )]
    // protected $password;
}