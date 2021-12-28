<?php namespace korol666\SFHKAPI\Core;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use korol666\SFHKAPI\Services\OrderService;
use korol666\SFHKAPI\Services\RouteService;

class AbstractBSP {
    protected $config = [
        'server'     => "http://bspoisp.sit.sf-express.com:11080/" ,
        'server_ssl' => "https://bspoisp.sit.sf-express.com:11443/" ,
        'ssl'        => false ,
        'uri'        => 'bsp-oisp/sfexpressService' ,
        'checkword'  => 'j8DzkIFgmlomPt0aLuwU' ,
        'accesscode' => 'BSPdevelop' ,
    ];

    private $SERVICE = [
        OrderService::class => 'OrderService' ,
        RouteService::class => 'RouteService' ,
    ];

    protected $ret = [
        'head'    => "ERR" ,
        'message' => '系統錯誤' ,
        'code'    => -1 ,
    ];

    public function __construct( $params ) {
        $this->config = array_merge($this->config , $params);
    }

    /**
     * post data to server
     * @param array $query
     * @param array $header
     * @return string
     */
    public function ApiPost( $query = [] , $header = [] ) {
        try {
            if ( $this->config[ 'ssl' ] ) {
                $client = new Client([ 'base_uri' => $this->config[ 'server_ssl' ] ]);
            } else {
                $client = new Client([ 'base_uri' => $this->config[ 'server' ] ]);
            }

            $header[ 'charset' ] = 'UTF-8';
            $header[ 'Content-Type' ] = 'application/x-www-form-urlencoded';

            // 數據需要以form_params提交，不然傳過去時會附加多余的數據，導致簽名驗證失敗。
            $response = $client->post(
                $this->config[ 'uri' ] ,
                [
                    'form_params' => $query ,
                    'headers'     => $header ,
                    'verify'      => false ,
                ]
            );
            $body = $response->getBody();

            return $body->getContents();

        } catch (RequestException $e) {
            if ( $e->hasResponse() ) {
                return $e->getResponse()->getBody()->getContents();
            } else {
                return $e->getMessage();
            }
        }
    }

    /**
     * get request service name.
     * @param null $class
     * @return mixed
     */
    public function getServiceName( $class = null ) {
        if ( empty($class) ) {
            return $this->SERVICE[ get_called_class() ];
        }

        return $this->SERVICE[ $class ];
    }

    /**
     * build full xml.
     * @param $bodyData
     * @return string
     */
    public function buildXml( $bodyData ) {
        $xml = '<Request service="' . $this->getServiceName(get_called_class()) . '" lang="zh-CN">' .
            '<Head>' . $this->config[ 'accesscode' ] . '</Head>' .
            '<Body>' . $bodyData . '</Body>' .
            '</Request>';

        return $xml;
    }

    public function getResponse( $data ) {
        $ret = $this->ret;
        $xml = @simplexml_load_string($data , 'SimpleXMLElement' , LIBXML_NOCDATA | LIBXML_NOBLANKS);
        if ( $xml ) {
            $ret = [];
            $ret[ 'head' ] = (string) $xml->Head;
            if ( $xml->Head == 'OK' ) {
                $ret = array_merge($ret , $this->getData($xml));
            }
            if ( $xml->Head == 'ERR' ) {
                $ret = array_merge($ret , $this->getErrorMessage($xml));
            }
        }

        return $ret;
    }

    public function getErrorMessage( $xml ) {
        $ret = [];
        $ret[ 'message' ] = (string) $xml->ERROR;
        if ( isset($xml->ERROR[ 0 ]) ) {
            foreach ($xml->ERROR[ 0 ]->attributes() as $key => $val) {
                $ret[ $key ] = (string) $val;
            }
        }

        return $ret;
    }

    public function getData( $xml ) {
        $ret = [];
        if ( isset($xml->Body->OrderResponse) ) {
            foreach ($xml->Body->OrderResponse as $v) {
                foreach ($v->attributes() as $key => $val) {
                    $ret[ $key ] = (string) $val;
                }
                foreach ($v->rls_info->attributes() as $key => $val) {
                    $ret[ $key ] = (string) $val;
                }
                foreach ($v->rls_info->rls_detail->attributes() as $key => $val) {
                    $ret[ $key ] = (string) $val;
                }
            }
        }

        return $ret;
    }

    public function arrarval( $data ) {
        if ( is_object($data) && get_class($data) === 'SimpleXMLElement' ) {
            $data = (array) $data;
        }

        if ( is_array($data) ) {
            foreach ($data as $index => $value) {
                $data[ $index ] = self::arrarval($value);
            }
        }

        return $data;
    }

}