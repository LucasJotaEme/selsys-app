<?php

namespace App\Request;

use App\Common\GlobalRequest;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Password;

class UserEditRequest extends GlobalRequest{

    #[NotBlank(), Type("integer")]
    protected $userId;

    #[NotBlank(), Email()]
    protected $email;

    #[Type("string"), Length(
        min: 5, minMessage: 'Password is too short',
        max: 15, maxMessage: 'Password is too long'
        )]
    protected $password;
}