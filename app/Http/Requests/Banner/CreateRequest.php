<?php

namespace App\Http\Requests\Banner;

use App\Entity\Banner\Banner;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        [$width, $height] = [0, 0];
        // возьмем из реквеста поле forman
        if ($format = $this->input('format')) {
            // если он есть эксплодим через x (разбиваем строку) и присваиваем ширине и высота
            [$width, $height] = explode('x', $format);
        }

        return [
            'name' => 'required|string',
            'limit' => 'required|integer',
            'url' => 'required|url',
            // форматы баннеров (240x400 и тд)
            'format' => ['required', 'string', Rule::in(Banner::formatsList())],
            // проверка на тип, расширение, ширину и высоту изображения
            'file' => 'required|image|mimes:jpg,jpeg,png',
        ];
    }
}
