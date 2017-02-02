<?php

/*
 * Author: Lasantha Indrajith<hellolasantha@gmail.com>
 *
 */

require_once __DIR__.'/../../../vendor/autoload.php';
require_once __DIR__.'/../../../src/app.php';

use Symfony\Component\Validator\Constraints as Assert;

$app->match('/Order', function () use ($app) {

    $initial_data = array(
        'ItemIt_ID' =>'' ,
    );

    $find_sql = "SELECT * FROM Menu_Item_Inventory ORDER BY Item_Category";
    $row_sql = $app['db']->fetchAll($find_sql);

    foreach($row_sql as $key=>$value){
        $initial_data['ItemIt_ID'][$value['Item_Category']][$value['ItemIt_ID']] =  array(
            'Item_Description'=>$value['Item_Description'],
            'Unit_Selling_Price'=>$value['Unit_Selling_Price'],
            'Item_Category'=>$value['Item_Category'],
            'ItemIt_ID'=>$value['ItemIt_ID']);
    }



    return $app['twig']->render('Order/create.html.twig', array('data'=> $initial_data));
})->bind('Order');


/*
 * Dump
 */
$app->match('/populate', function () use ($app) {


    //initial Data
    $products_2015 =array('ALA408'=> array (1368,450),
        'BEV513'=> array (1026,130),
        'BEV506'=> array (906,100),
        'BEV507'=> array (906,130),
        'DES605'=> array (846,200),
        'BEV510'=> array (826,130),
        'BEV508'=> array (824,150),
        'BRF108'=> array (824,350),
        'ALA401'=> array (800,300),
        'BEV511'=> array (726,150),
        'ALA406'=> array (724,450),
        'SID202'=> array (710,210),
        'ALA404'=> array (704,200),
        'DES601'=> array (650,250),
        'DES603'=> array (650,200),
        'BEV504'=> array (646,130),
        'ALA402'=> array (642,450),
        'BRF102'=> array (642,200),
        'SID201'=> array (642,190),
        'ALA411'=> array (626,350),
        'BRF106'=> array (626,300),
        'ALA415'=> array (528,450),
        'BRF101'=> array (512,150),
        'DES600'=> array (512,250),
        'SAL301'=> array (508,580),
        'BRF104'=> array (504,150),
        'BRF107'=> array (502,350),
        'ALA409'=> array (482,450),
        'BEV501'=> array (466,130),
        'BRF105'=> array (464,150),
        'ALA410'=> array (446,480),
        'DES602'=> array (442,250),
        'BRF109'=> array (430,300),
        'ALA407'=> array (426,450),
        'BEV509'=> array (426,100),
        'BEV512'=> array (426,80),
        'ALA412'=> array (424,450),
        'ALA414'=> array (424,250),
        'BEV502'=> array (424,150),
        'SID200'=> array (424,170),
        'BEV505'=> array (308,150),
        'ALA413'=> array (302,600),
        'ALA416'=> array (302,300),
        'BEV500'=> array (302,100),
        'BRF103'=> array (290,180),
        'ALA400'=> array (250,200),
        'BRF100'=> array (248,100),
        'ALA405'=> array (246,400),
        'DES604'=> array (246,200),
        'SAL300'=> array (242,450),
        'BEV503'=> array (66,100),
        'ALA403'=> array (64,600));
    $from_date = "2015-01-01";
    $to_date = "2015-01-31";

    $customers = array('0112144141',
        '0112154365',
        '0112154623',
        '0112171745',
        '0112251232',
        '0112262566',
        '0112457458',
        '0112565682',
        '0112585456',
        '0112655946',
        '0112665588',
        '0112784536',
        '0112956862',
        '0112998877',
        '0171263235',
        '0171745124',
        '0712456985',
        '0714582343',
        '0714585212',
        '0717458565',
        '0775184362',
        '0775222222',
        '0775333355',
        '0775445544',
        '0775515975',
        '0775555555',
        '0775656856',
        '0775666566',
        '0775736563',
        '0775741254',
        '0775745856',
        '0775778844',
        '0775846455',
        '0775852526',
        '0775884455',
        '0775919191',
        '0775984578',
        '0775985696',
        '0776458434',
        '0776895321',
        '0784112211',
        '0784343645',
        '0784741125',
        '0784788554',
        '0784789456',
        '0784845757',
        '0784854565',
        '0784956236',
        '0784985413',
        '0784985656'
    );
    $orderType = array('DINEIN','TAKEAWAY','DELIVERY');
    $orderPaymentType = array('CASH','CC','PAYPAL','BANK');

    $products_2015_total_orders = 0;

    foreach($products_2015 as $key=>$value){
        $products_2015_total_orders+=$value[0];
    }

    $monthly_orders_per_customer = $products_2015_total_orders / count($customers);
    $avg_daily_orders= floor($monthly_orders_per_customer /30);

    echo " products_2015_total_orders $products_2015_total_orders<br/>";
    echo " monthly_orders_per_customer $monthly_orders_per_customer<br/>";
    echo " avg_daily_orders $avg_daily_orders<br/>";
    echo "<br/>";

    while($products_2015_total_orders){

        foreach($customers as $customer){

            $total_amount = 0;

            //SQLs
            $int= mt_rand(strtotime($from_date),strtotime($to_date));
            $currentDate = date("Y-m-d",$int);


            echo $sql_purchase_order = "INSERT INTO  `Purchase_Order`
                                (`Order_Date`, `Order_Delivery_date`, `order_type`)
                                VALUES ('$currentDate', '$currentDate','". $orderType[array_rand($orderType)]."');
                                <br/>";

            $items = array_rand($products_2015, $avg_daily_orders);

            foreach($items as $item)
            {
                $current_qty = $products_2015[$item][0];

                $selected_qty =0;

                if($current_qty){

                    $qty = rand(1,10);

                    if($current_qty>0 && $qty<=$current_qty){
                        $selected_qty = $qty;
                    }else{
                        $selected_qty = $current_qty;
                    }

                    $products_2015[$item][0] -= $selected_qty;
                    $products_2015_total_orders -= $selected_qty;

                    $total_amount += $selected_qty * $products_2015[$item][1];
                    //echo "customer | $customer | $item | $selected_qty | ".$products_2015[$item][1] ." | $total_amount <br/>";

                    echo $sql_purchase_order_payment = "INSERT INTO  `Purchase_Order_Item`
                                (`Order_ID`, `ItemIt_ID`, `Quantity`)
                                VALUES ('ORDERID', '$item','$selected_qty');
                                <br/>";
                }

            }

            echo $sql_purchase_order_payment = "INSERT INTO  `Purchase_Order_Payment`
                                (`Order_ID`, `Amount`, `Customer_Contact`, `Payment_Type`)
                                VALUES ('ORDERID', '$total_amount','$customer','". $orderPaymentType[array_rand($orderPaymentType)]."');
                                <br/>";


        }

    }


    var_dump($products_2015);

die('exit');
    $strCurrentDate = strtotime("2015-01-01");
    for($i=0; $i <=1000; $i++){

        $date = strtotime("+1 day", $strCurrentDate);
        $strCurrentDate = $date;
        $currentDate = date("Y-m-d", $date);


        $sql_purchase_order = "INSERT INTO  `Purchase_Order`
                                (`Order_Date`, `Order_Delivery_date`, `order_type`)
                                VALUES ('$currentDate', '$currentDate','". $orderType[array_rand($orderType)]."');
                                ";

        //$row_sql = $app['db']->executeUpdate($sql_purchase_order);

        //$orderId = $app['db']->lastInsertId();



        switch(date("Y", $date)){
            case '2015':

                switch(date("m", $date)){
                    case 1:
                        break;
                    case 2:
                        break;
                    case 3:
                        break;
                    case 4:
                        break;
                    case 5:
                        break;
                    case 6:
                        break;
                    case 7:
                        break;
                    case 8:
                        break;
                    case 9:
                        break;
                    case 10:
                        break;
                    case 11:
                        break;
                    case 12:
                        break;
                    default:
                        break;
                }

                break;
            case '2016':
                break;
            case '2017':
                break;
            default:
                break;

        }





    }

    return '';

})->bind('populate');

