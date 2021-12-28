<?php namespace korol666\SFHKAPI\Services;

use Dompdf\Dompdf;
use korol666\SFHKAPI\Core\AbstractBSP;
use korol666\SFHKAPI\Support\Auth;

/**
 * Class OrderService
 * @package korol666\SFHKAPI\BSP
 */
class OrderService extends AbstractBSP {

    public function getLabel($data,$res) {
        if($res['head'] != "OK"){
            throw new \RuntimeException('data incorrect');
        }

        $dompdf = new Dompdf();
        ob_start();
        include(__DIR__ . "/SFLabel.php");
        $content = ob_get_clean();
        $dompdf->loadHtml($content);
        $dompdf->setPaper([0,0,360,540]);
        $dompdf->render();

        return $dompdf->output();

    }

    /**
     * Place order
     * @param array $params
     * @param array $cargoes
     * @param array $addedServices
     * @return array
     */
    public function Order( $params = [] , $cargoes = [] , $addedServices = [] ) {
        $order = '<Order ';

        foreach ($params as $k => $v) {
            $order .= $k . '=' . '"' . $v . '" ';
        }

        if ( count($cargoes) > 0 || count($addedServices) > 0 ) {
            $order = trim($order) . '>';
            if ( is_array($cargoes) && count($cargoes) > 0 ) {
                $order .= $this->Cargo($cargoes);
            }
            if ( is_array($addedServices) && count($addedServices) > 0 ) {
                $order .= $this->AddedService($addedServices);
            }
            $order .= '</Order>';
        } else {
            $order .= ' />';
        }

        $xml = $this->buildXml($order);
        $verifyCode = Auth::sign($xml , $this->config[ 'checkword' ]);

        $params = [
            'xml'        => $xml ,
            'verifyCode' => $verifyCode,
        ];


        $data = $this->ApiPost($params);

        return $this->OrderResponse($data);
    }

    /**
     * Product infos
     * @param $cargoes
     * @return string
     */
    private static function Cargo( $cargoes ) {
        $data = '';
        if ( count($cargoes) > 0 ) {
            foreach ($cargoes as $item) {
                if ( count($item) > 0 ) {
                    $root = '<Cargo ';
                    foreach ($item as $k => $v) {
                        $root .= $k . '="' . $v . '" ';
                    }
                    $root .= '></Cargo>';
                    $data .= $root;
                }
            }
        }

        return $data;
    }

    /**
     * Extra Services
     * @param $AddedServices
     * @return string
     */
    private static function AddedService( $AddedServices ) {
        $data = '';
        if ( count($AddedServices) > 0 ) {
            foreach ($AddedServices as $item) {
                if ( count($item) > 0 ) {
                    $root = '<AddedService ';
                    foreach ($item as $k => $v) {
                        $root .= $k . '="' . $v . '" ';
                    }
                    $root .= '></AddedService>';
                    $data .= $root;
                }
            }
        }

        return $data;
    }

    /**
     * Response
     * @param $data
     * @return array
     */
    private function OrderResponse( $data ) {
        return $this->getResponse($data);
    }
}