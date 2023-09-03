<?php

namespace App\Handler;

use App\Common\GlobalHandler;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserHandler extends GlobalHandler
{

    const ENTITY_NAME = "User";
    const ID_PARAM = "userId";
    const PASSWORD_PARAM = "password";

    public function __construct(protected EntityManagerInterface $entityManager, private UploadHandler $uploadHandler)
    {
        parent::__construct($entityManager);
    }

    private function updateUserUpload(User $user, $file, $byDefault = false)
    {
        $uploadId = null !== $user->getUpload() ? $user->getUpload()->getId() : null;
        $type = UploadHandler::PROFILE_PHOTO_FILE_TYPE;

        $upload = $this->uploadHandler->set(
            [
                "file" => $byDefault ? UploadHandler::DEFAULT : $file,
                UploadHandler::ID_PARAM => $uploadId,
            ],
            $type
        );
        $user->setUpload($upload);
    }

    public function set($params, UserPasswordHasherInterface $passwordHasher = null, $edit = false)
    {
        $id = $params[self::ID_PARAM] ?? 0;
        $psw = $params[self::PASSWORD_PARAM] ?? null;
        $file = $params[UploadHandler::FILE_PARAM] ?? null;
        $user = $edit ? $this->ifExistsGetById($id, self::ENTITY_NAME) : new User();

        if (isset($params["email"])) {
            $user->setEmail($params["email"]);
        }

        if ($this->validatePassword($psw) && null !== $passwordHasher) {
            $user->setPassword($passwordHasher->hashPassword($user, $psw));
        }

        $this->updateUserUpload($user, $file, !$edit);

        return $user;
    }

    public function beforeSave(User $user)
    {
        $this->validate($user);
        $this->repository(self::ENTITY_NAME)->save($user, true);
        return $user;
    }

    public function remove($params)
    {
        $id = $params[self::ID_PARAM] ?? 0;

        $this->repository(self::ENTITY_NAME)->remove($this->ifExistsGetById($id, self::ENTITY_NAME), true);

        return $this->getMessageDeleted($id, self::ENTITY_NAME);
    }

    public function get($params)
    {
        $id = $params[self::ID_PARAM] ?? 0;

        return $this->ifExistsGetById($id, self::ENTITY_NAME);
    }

    private function validate(User $user)
    {
        $repository = $this->repository(self::ENTITY_NAME);
        $userAlreadyExists = $repository->findOneBy(array("email" => $user->getEmail()));

        if (null !== $userAlreadyExists && $userAlreadyExists->getId() != $user->getId()) {
            throw new \Exception($this->getMessageAlreadyExists($user->getEmail(), self::ENTITY_NAME));
        }
    }

    private function validatePassword($psw): bool
    {
        // Se lo deja en una función ya que en un futuro pueden haber más validaciones que hacer en password.
        // Chequear si es necesario trasladar a GlobalHandler.
        return null !== $psw;
    }
}
