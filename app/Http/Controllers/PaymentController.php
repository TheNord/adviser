<?php

namespace App\Http\Controllers;

use App\Entity\Banner\Banner;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /** Принимаем реквест с робокассы */
    public function result(Request $request)
    {
        $password2 = 'dsf234234da';

        // разбиваем полученные данные на переменные
        $out_sum = $request['OutSum'];
        $inv_id = $request['InvId'];
        $shp_item = $request['Shp_item'];
        $crc = $request['SignatureValue'];

        $crc = strtoupper($crc);

        // переводим в md5 и сверяем с сигнатурой
        $my_crc = strtoupper(md5("$out_sum:$inv_id:$password2:Shp_item=$shp_item"));

        // выводим ошибку если сигнатура не верная
        if ($my_crc !== $crc) {
            return 'bad sign';
        }

        // находим наш баннер и помечаем оплаченным
        $banner = Banner::findOrFail($inv_id);
        $banner->pay(Carbon::now());

        // возвращаем робокассе что все ок и номер заказа
        return 'OK' . $inv_id;
    }

    public function success(Request $request)
    {
        // страница успешной оплаты
    }

    public function fail(Request $request)
    {
        // страница ошибочной оплаты
    }
}