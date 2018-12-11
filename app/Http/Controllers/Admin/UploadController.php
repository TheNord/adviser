<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    // принимаем реквест
    public function image(Request $request): string
    {
        // валидируем картинку
        $this->validate($request, [
            'file' => 'required|image|mimes:jpg,jpeg,png',
        ]);
        // получаем из ре квеста файл
        $file = $request->file('file');
        // добавляем в хранилище с возвращением пути
        return asset($file->store('/uploads/images'));
    }
}