<?php
require_once './vendor/autoload.php';

use korol666\SFHKAPI\Services\OrderService;
use korol666\SFHKAPI\Services\RouteService;

// 請填寫順豐合作資料
$config = [
    'server'     => "http://218.17.248.244:11080/" ,
    'server_ssl' => "https://218.17.248.244:11080/" ,
    'ssl'        => false ,
    'uri'        => 'bsp-oisp/sfexpressService' ,
    'checkword'  => '123456789' , //private code
    'accesscode' => '123456789' , //access code
];


$orderid = time(); //Your Order number
$orderData = [

    'custid'                  => '123456789' , //月結卡號
    'orderid'                 => $orderid ,

    //收件方信息
    'd_company'               => '馮小妹' ,
    'd_contact'               => 'KK' ,
    'd_tel'                   => '85222701234' ,
    'd_country'               => '香港' ,
    'd_deliverycode'          => '852' ,
    'd_address'               => '香港南區赤柱道190號' ,

    //寄件方信息
    'j_contact'               => '譚小明',
    'j_country'               => '香港' ,
    'j_shippercode'           => '852' ,
    'j_mobile'                => '85233701234' ,
    'j_province'              => '香港' ,
    'j_city'                  => '香港' ,
    'j_county'                => '香港' ,
    'j_address'               => '香港西貢區匡湖居西貢公路156號' ,
    'remark'                  => '書本，手錶',

    //物品價值
    'declared_value'          => 10.00 ,
    'declared_value_currency' => 'USD' ,
    'express_type'            => '1' , // 快件產品類別
    'pay_method'              => '1' , // 付款方式
    'parcel_quantity'         => '1' , // 包裹數
    'cargo_length'            => '10' , // 貨物總長
    'cargo_width'             => '10' , // 貨物總寬
    'cargo_height'            => '10' , // 貨物總高
];

// 貨物信息,可以多個,name為必填。
$Cargo = [
    [ 'name' => '書本' , 'count' => '1' , 'unit' => '本' , 'weight' => '' , 'amount' => '' , 'currency' => '' , 'source_area' => '' ] ,
    [ 'name' => '手錶' , 'count' => '1' , 'unit' => '塊' , 'weight' => '' , 'amount' => '' , 'currency' => '' , 'source_area' => '' ] ,
];

// 創建訂單
$service  = new OrderService($config);
$res = $service->Order($orderData , $Cargo);
if($res['head'] != "OK"){
    print_r($res);die;
}

//打印Label
$label = $service->getLabel($orderData,$res);
header('Content-Type: application/pdf; charset=utf-8');
header('Content-disposition: inline; filename="'  . 'SF-label.pdf"', true);
echo $label;

// 路由追蹤
$tracking  = new RouteService($config);
$trackingData = $tracking->Routes("SF1234567890");
//print_r($trackingData);


