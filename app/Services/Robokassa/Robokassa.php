<?php

namespace App\Services\Robokassa;


class Robokassa
{
    public function generateRedirectUrl($id, $cost, $Shp_item)
    {
        $login = 'login';
        $password1 = 'password';

        $InvID = $id;
        $OutSum = $cost;
        $ShpItem = $Shp_item;

        $signature = md5("$login:$OutSum:$InvID:$password1:Shp_item=$ShpItem");

        $query = http_build_query([
            'InvId' => $InvID,
            'OutSum' => $OutSum,
            'Shp_item' => $ShpItem,
            'SignatureValue' => $signature,
        ]);

        $url = 'https://merchant.roboxchange.com/Index.aspx?' . $query;

        return $url;
    }
}