<?php

namespace App\Handler;

use App\Common\GlobalHandler;
use App\Entity\Upload;
use Exception;

class UploadHandler extends GlobalHandler
{

    const ENTITY_NAME = "Upload";

    const FILE_PARAM  = "file";
    const ID_PARAM    = "uploadId";

    const PROFILE_PHOTO_FILE_TYPE  = "profilePhoto";
    const DEFAULT = "default";

    public function set($params, $type, $edit = false)
    {
        $id    = $params[self::ID_PARAM] ?? null;
        $file  = $params[self::FILE_PARAM] ?? null;
        $route = $this->getRoute();
        $hash  = bin2hex(openssl_random_pseudo_bytes(16));

        if (null !== $id) {
            $upload = $this->ifExistsGetById($id, self::ENTITY_NAME);
            $hash = $upload->getHash();
        } else {
            $upload = new Upload();
            $upload->setCreatedAt(new \DateTimeImmutable());
        }

        $upload->setType($type);
        if ($file === self::DEFAULT) {
            $this->createDefaultFile($route,$type,$hash,$upload);
        } else {
            $this->cleanFolder($route . "$type/$hash");
            $file->move($route . "$type/$hash\/", $file->getClientOriginalName());
            $upload->setHash($hash);
            $upload->setFileName($file->getClientOriginalName());
        }

        return $this->beforeSave($upload);
    }

    private function createDefaultFile($route, $type, $hash, &$upload)
    {
        $sourceFile = $route . "$type/default/" . self::DEFAULT . ".jpg";
        $destinationDirectory = $route . "$type/$hash/";
        $destinationFile = $destinationDirectory . self::DEFAULT . ".jpg";

        if (!file_exists($destinationDirectory)) {
            if (mkdir($destinationDirectory, 0777, true)) {
                if (copy($sourceFile, $destinationFile)) {
                    $upload->setHash($hash);
                    $upload->setFileName(self::DEFAULT . ".jpg");
                }
            }
        } else {
            if (copy($sourceFile, $destinationFile)) {
                $upload->setHash($hash);
                $upload->setFileName(self::DEFAULT . ".jpg");
            } else {
                throw new Exception("Error copying default image");
            }
        }
    }


    private function cleanFolder($route)
    {
        $files = glob("$route/*");
        foreach ($files as $file) {
            if (is_file($file))
                unlink($file);
        }
    }

    public function beforeSave(Upload $upload)
    {
        $this->repository(self::ENTITY_NAME)->save($upload, true);

        return $upload;
    }

    public function getRoute()
    {
        return $this->getParameter("upload_route");
    }
}
