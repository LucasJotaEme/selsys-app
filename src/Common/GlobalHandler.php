<?php

namespace App\Common;

use Doctrine\ORM\EntityManagerInterface;

class GlobalHandler extends GlobalManager{



    public function __construct(protected EntityManagerInterface $entityManager){}

    public function ifExistsGetById($id, $entityName){
        $entity = $this->repository($entityName)->find($id);

        if (null === $entity)
            throw new \Exception("$entityName with id $id not found");

        return $entity;
    }

    public function getMessageDeleted($id, $entityName){
        return "$entityName with id $id deleted";
    }

    public function getMessageAlreadyExists($email, $entityName){
        return "$entityName with email $email already exists";
    }

}