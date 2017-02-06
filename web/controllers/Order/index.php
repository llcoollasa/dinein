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

$app->match('/Order/place', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {

    $vars = $request->request->all();
    $items = $request->request->get('item_id');
    $date = date("Y-m-d");
    $basket = array();
    $orderId =0;
    $total = 0;

    foreach($items as $itemK=>$item){

        $itemAttributes = explode('_',$item);

        foreach($itemAttributes as $itemAttributeK=>$itemAttribute){
            $itemValues = explode('=',$itemAttribute);
            $basket[$itemK][$itemValues[0]]=  $itemValues[1];
        }
    }

    //Customer
    //`Customer_Contact`, `Customer_Name`, `Customer_Delivery_address`, `Customer_email`

    $sql_customer = "INSERT INTO Customer
                      VALUES ('".$vars['Customer_Contact']."',
                      '".$vars['Customer_Name']."',
                      '".$vars['Customer_Delivery_address']."',
                      '".$vars['Customer_email']."'
                      )";

    $app['db']->executeUpdate($sql_customer);
    //Purchase_Order
    //`Order_ID`, `Order_Date`, `Order_Delivery_date`, `order_type`

    $sql_purchase_order = "INSERT INTO Purchase_Order(Order_Date,Order_Delivery_date,order_type)
                              VALUES ('".$date."',
                              '".$date."',
                              'TAKEAWAY'
                              )";

    $app['db']->executeUpdate($sql_purchase_order);
    $orderId = $app['db']->lastInsertId();


    //Purchase_Order_Item
    //`Order_ID`, `ItemIt_ID`, `Quantity`

    foreach ($basket as $itemK=>$item) {

        $sql_purchase_items = "INSERT INTO Purchase_Order_Item(Order_ID,ItemIt_ID,Quantity)
                              VALUES ('".$orderId."',
                              '".$itemK."',
                              '".$item['itemQty']."'
                              )";
        $total += $item['itemSubTotal'];
        $app['db']->executeUpdate($sql_purchase_items);

    }



    //Purchase_Order_Payment
    //`Payment_Id`, `Order_ID`, `Amount`, `Payment_Date`, `Customer_Contact`, `Payment_Type`

    $sql_purchase_order = "INSERT INTO Purchase_Order_Payment(Order_ID,Amount,Payment_Date,Customer_Contact,Payment_Type)
                              VALUES ('".$orderId."',
                              '".$total."',
                              '".$date."',
                              '".$vars['Customer_Contact']."',
                              '".$vars['Payment_Type']."'

                              )";

    $app['db']->executeUpdate($sql_purchase_order);

   return $app['twig']->render('Order/place.html.twig', array(
        'data'=> $vars,
        'orderId' =>$orderId,
        'total' =>$total,
        'basket' =>$basket
    ));

})->bind('Order_place');