<?php

namespace App\Controllers;

class CommentUploadController extends BaseController
{
    public function upload()
    {
        $file = $this->request->getFile('upload');

        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return $this->response->setStatusCode(400)->setJSON([
                'error' => ['message' => 'File tidak valid.'],
            ]);
        }

        if (! in_array($file->getClientMimeType(), ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true)) {
            return $this->response->setStatusCode(400)->setJSON([
                'error' => ['message' => 'Cuma gambar yang boleh diupload.'],
            ]);
        }

        $newName    = $file->getRandomName();
        $uploadPath = FCPATH . 'uploads/comments';

        if (! is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $file->move($uploadPath, $newName);

        return $this->response->setJSON([
            'url' => base_url('uploads/comments/' . $newName),
        ]);
    }
}
