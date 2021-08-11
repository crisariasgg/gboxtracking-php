<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">
<div class="container-fluid">

<p>
<br>



<?php
//$tracking='8515-752693-1z';
//$tracking='4203319892612927005143010007020317';
//$tracking='1z2y842v0301429552';
//$tracking='1ZW709680337131270';
//$tracking='420331989374869903503391326026';
//$tracking='8515-752693-1z';
//$tracking='61290983461420188456';
    $tracking=$_POST["tracking"];
    if($tracking!=null){
    $datenext=date('Y-m-d', strtotime($date .' +1 day'));
    try{

        $contextOptions = array(
            'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ));

        $sslContext = stream_context_create($contextOptions);

        $params =  array(
            'trace' => 1,
            'exceptions' => true,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'stream_context' => $sslContext
        );

        //setting SOAP client
        $client = new SoapClient("http://181.115.48.20:3691/CSSoapService?wsdl",$params);
        $client->__setLocation('http://181.115.48.20:3691/Invoke?Handler=CSSoapService');
        //Users credential
        $user = "walteraraujo";
        $pass = "Junio21@";
        //Parameters for getting access key
        $params = array(
            "user" => $user,
            "pass" => $pass
        );
        // Calling start session for access key
        $response=$client->__soapCall('StartSession', $params);
        if($response['return']=='no_error'){
            //printing access_key from response
            $access_key=$response['access_key'];
            //echo "Access key: ".$access_key.'<br>';
        }
        else{
            echo "No Access key returned (Walter)". '<br>';
            echo "Error: " . $response['return'] . "<br>";
        }
        // starting tracking transaction block
        if($response['return']=='no_error'){
            //parameters for rename transaction
            $params1 = array(
                "access_key" => $access_key,
                "type" => 'WH',
                "start_date" => '2018-10-30',
                "end_date" => $datenext,
                "flags" =>0x08000000,
                "record_quantity"=>1,
                "backwards_order"=>1,
                //"function" =>'FindTracking',
                "jsFunction" =>'FindTracking',
                //"cookie" =>null,
                //"more_results" =>0,
                "params" =>'<Parameters><Parameter>'.$tracking.'</Parameter></Parameters>'            
            );
            //calling GetFirstTransbyDateJS API
            $more_results=0;
            $result=$client->__soapCall('GetFirstTransbyDateJS', $params1);
            //printing tracking
            //echo "<p>Tracking: ".$tracking."</p><p>";
            //echo "Resultado de GetFirstTransbyDateJS:<p>";
            $more_results=$result['more_results']."<p>";
            //echo "more_results: ".$more_results;
            
            //Send cookie info
            echo "<center><b>Tracking</b>: ".$tracking."</center><p>";

            


            while ($more_results > 0 && $result['cookie'] != null) {
                $cookie=$result['cookie'];    
                $params3 = array(
                "cookie"=>$cookie
                );
        
                //Star try GetNextTransbyDate
                try {
                    $trackinginfo=$client->__soapCall('GetNextTransbyDate', $params3);
                    $trackdetails=$trackinginfo['trans_list_xml'];
                    $xml = simplexml_load_string($trackdetails);
            $status=$xml->WarehouseReceipt->Status[0];	
            
            switch ($status) {
            case "Pending":
                echo "<div class='text-center'><img src='img/pb01.png' class='img-fluid' alt='Esperando que llegue el paquete a la bodega de Miami'>";
                echo "<p><h4>Esperando que llegue el paquete a la bodega de Miami</h4></div>";
                break;
            case "OnHand":
                echo "<div class='text-center'><img src='img/pb02.png' class='img-fluid' alt='Estamos procesando su paquete'>";
                echo "<p><h4>Recibido y procesando su paquete</h4></div>";
                break;
            case "InProcess":
                echo "<div class='text-center'><img src='img/pb03.png' class='img-fluid' alt='Preparando paquete para enviar a su país'>";
                echo "<p><h4>Preparando paquete para enviar a su país</h4></div>";
                break;
            case "InTransit":
                echo "<div class='text-center'><img src='img/pb04.png' class='img-fluid' alt='En transito para país de destino'>";
                echo "<p><h4>En transito para país de destino</h4></div>";
                break;
            case "AtDestination":
                echo "<div class='text-center'><img src='img/pb05.png' class='img-fluid' alt='Paquete listo para ser reclamado en nuestra oficina' >";
                echo "<p><h4>Paquete listo para ser reclamado en nuestra oficina</h4></div>";
                break;
            case "Delivered":
                echo "<div class='text-center'><img src='img/pb06.png' class='img-fluid' alt='Paquete entregado'>";
                echo "<p><h4>Paquete entregado</h4></div>";
                break;
            }
            $peso=(float)$xml->WarehouseReceipt->ChargeableWeight[0];
            // echo "status".$status;
            echo $xml;
            // echo $$xml->WarehouseReceipt;
            echo "<div class='text-center'><b>Peso del paquete</b>: ".number_format($peso, 2, '.', ',').' libras</b></p><p>';
                    
                    
                    
                    
                    } catch (Exception $e) {
                        echo "<div class='text-center'><img src='img/b0.png' class='img-fluid' alt='Paquete entregado'>";
                        echo "<p><h4>No existen registros sobre ese número de tracking</h4><p>";
                    }//end try GetNextTransbyDate
                break;
            }
            
            echo "<b><a class='btn btn-primary btn-lg' href='tracking.php'>Hacer otra consulta</a></div></b></p><p>";
        }
        //end session parameters
        $params2 = array (
            "access_key" => $access_key
        );
        $endsession=$client->__soapCall('EndSession', $params2);
    }
    catch(SoapFault $exception){
        print_r($exception->getmessage());
        print_r($exception->getTrace());
    }

}//End IF inicialo 
?>


</div>