<?php

namespace korol666\SFHKAPI\Services;

use DOMDocument;
use korol666\SFHKAPI\Core\AbstractBSP;
use korol666\SFHKAPI\Support\Helper;
use Sabre;

class RouteService extends AbstractBSP {
    use Helper;


    /**
     * 路由查詢，香港地區使用default parameter就可以了
     * @param $tracking_number
     * @param int $tracking_type
     * @param int $method_type
     * @return array
     */
    public function Routes( $tracking_number , $tracking_type = 1 , $method_type = 1 ) {
        // Example
        /*
            <Request service='RouteService' lang='zh-CN'>
                <Head>BSPdevelop</Head>
                <Body>
                    <RouteRequest
                    tracking_type='1'
                    method_type='1'
                    tracking_number='444003077898'/>
                </Body>
            </Request>
        */

        $RouteRequest = '<RouteRequest tracking_type="' . $tracking_type . '" method_type="' . $method_type . '" tracking_number="' . $tracking_number . '" />';


        $xml = $this->buildXml($RouteRequest);

        $verifyCode = $this->sign($xml , $this->config[ 'checkword' ]);

        $params = [
            'xml'        => $xml ,
            'verifyCode' => $verifyCode ,
        ];

        $data = $this->ApiPost($params);

        return $this->RouteResponse($data);
    }

    /**
     * 獲取結果
     * @param $xml
     * @return array
     */
    protected function RouteResponse( $xml ) {
        $data = $this->LoadXml($xml);
        $service = $data[ 'attributes' ][ 'service' ];
        $head = $data[ 'Head' ];
        if ( $head == "OK" ) {
            $result = [];
            $t = [];
            $routeResponses = $data[ 'Body' ][ 'RouteResponse' ];
            if ( isset($routeResponses[ 'Route' ]) && count($routeResponses[ 'Route' ]) > 0 ) {
                $routes = $routeResponses[ 'Route' ];
                foreach ($routes as $v) {
                    $tmp = [];
                    foreach ($v[ 'attributes' ] as $k => $a) {
                        $tmp[ $k ] = $a;
                    }
                    $t[ $data[ 'Body' ][ 'RouteResponse' ][ 'attributes' ][ 'mailno' ] ][] = $tmp;
                }
            }
            $result[ 'data' ] = $t;
        } else if ( $head == "ERR" ) {
            $result[ 'code' ] = $data[ 'ERROR' ][ 'attributes' ][ 'code' ];
            $result[ 'message' ] = $data[ 'ERROR' ][ '_value' ];
        } else {
            $result = [];
        }

        return array_merge([ 'service' => $service , 'head' => $head ] , $result);
    }
}