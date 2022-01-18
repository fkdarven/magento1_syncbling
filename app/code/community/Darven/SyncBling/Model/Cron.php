<?php
/**
 * @category Darven
 * @package syncBling
 * @author Letícia Karolina Moreira <leticia@vivaldi.net>
 * @copyright 2021 Letícia K Moreira
 */
class Darven_SyncBling_Model_Cron
{
    public const not_sent = "not_sent";
    public const sent = "sent";
    public const url = "https://bling.com.br/Api/v2/";
    private $apikey;
    private $days;
    private $method = 'pedidos';
    private $returnType;

    public function setData(){
        $this->apikey = Mage::getStoreConfig("syncbling/general/apikey");
        $this->days = Mage::getStoreConfig("syncbling/general/days_per_exec")*-1;
        $this->returnType = "json";
    }
    public function updateOrders(){
        $this->setData();
        $today = date("d/m/Y");
        //Loop para pegar os pedidos entre data_inicial e o dia de hoje
        for($i = $this->days; $i < 0; $i++){
            $start_date = date("d/m/Y", strtotime($i ." days"));
            $url = self::url . $this->method  ."/" . $this->returnType . "?filters=dataEmissao[".$start_date ." TO". $today ."]". "&apikey=" . $this->apikey;

            $url = str_replace(" ", '%20', $url);
            $curl_handler = curl_init();
            curl_setopt($curl_handler, CURLOPT_URL, $url);
            curl_setopt($curl_handler, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($curl_handler);
            curl_close($curl_handler);
            if (curl_errno($curl_handler)) {
                Mage::log(curl_error($curl_handler),null,'syncBling_error.log',true);
            }

            else
            {
                $result = json_decode($result, true);
                $result = $result["retorno"]["pedidos"];
                foreach($result as $_order){
                    foreach ($_order as $orderDetails){
                        if(array_key_exists("volumes", $orderDetails["transporte"])){
                            $orderId = $orderDetails["numeroPedidoLoja"];
                            $blingOrderId = $orderDetails["numero"];
                            $order = Mage::getModel('sales/order')->load($orderId , 'increment_id');
                            if($order->getStatus() === "producao_pr"){
                                $order->setStatus("complete_shipped");
                                $order->save();
                                $message = "Mudança de status para Enviado no pedido: Bling - ". $blingOrderId . " | Magento - " . $orderId .", data do pedido: " . $orderDetails["data"] . " - status alterado dia:" . $today;
                                Mage::log($message, null,'syncBling.log',true);

                            }
                        }

                    }

                }

            }
        }
    }
}