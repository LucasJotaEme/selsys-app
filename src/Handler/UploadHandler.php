<?php

namespace App\Handler;

use App\Common\GlobalManager;
use App\Entity\Upload;

class UploadHandler extends GlobalManager
{

    const ENTITY_NAME = "Upload";
    const FILE_PARAM  = "file";
    const ID_PARAM    = "uploadId";

    public function set($params, $type, $edit = false)
    {
        $id    = isset($params[self::ID_PARAM])   ? $params[self::ID_PARAM]   : null;
        $file  = isset($params[self::FILE_PARAM]) ? $params[self::FILE_PARAM] : null;
        $route = $this->getParameter("upload_route");

        if (null !== $id) {
            $upload = $this->ifExistsGetById($id, self::ENTITY_NAME);
            $hash = $upload->getHash();
        }else{
            $upload = new Upload();
            $upload->setCreatedAt(new \DateTimeImmutable());
            $hash  = bin2hex(openssl_random_pseudo_bytes(16));
        }
        $file->move($route . "$type/$hash\/", $file->getClientOriginalName());
        $upload->setType($type);
        $upload->setHash($hash);
        $upload->setFileName($file->getClientOriginalName());

        return $this->beforeSave($upload);
    }

    public function beforeSave(Upload $upload)
    {
        $this->repository(self::ENTITY_NAME)->save($upload, true);

        return $upload;
    }

    // public function remove($params)
    // {
    //     $id = isset($params[self::ID_PARAM]) ? $params[self::ID_PARAM] : 0;

    //     $this->repository(self::ENTITY_NAME)->remove($this->ifExistsGetUserById($id), true);

    //     return "Deleted user";
    // }

    // public function get($params)
    // {
    //     $id = isset($params[self::ID_PARAM]) ? $params[self::ID_PARAM] : 0;

    //     return $this->ifExistsGetUserById($id);
    // }
}
