<?php

/*
 * Author: Lasantha Indrajith<hellolasantha@gmail.com>
 *
 */

require_once __DIR__.'/../../../vendor/autoload.php';
require_once __DIR__.'/../../../src/app.php';

use Symfony\Component\Validator\Constraints as Assert;

$app->match('/Reports/CustomerByDeliveryLocation', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {

    $vars = $request->query->all();
    $city = $vars["city"];
    $where ='';

    $table_columns = array(
        'Customers',
        'Address',
    );

    if(!empty($city)){
        $where = " WHERE Customer_Delivery_address LIKE '%{$city}%' ";
    }

    $find_sql="SELECT count(Customer_Name) as 'Customers', Customer_Delivery_address as 'Address'
                FROM Customer
                $where
                GROUP BY Customer_Delivery_address
                ORDER BY Customers DESC";

    $rows_sql = $app['db']->fetchAll($find_sql, array());

    return $app['twig']->render('Reports/customer_by_delivery_location.html.twig',array(
        'report' => $rows_sql,
        'columns' =>$table_columns
    ));

})->bind('Reports_CustomerByDeliveryLocation');

$app->match('/Reports', function () use ($app) {
    return $app['twig']->render('Reports/list.html.twig');
})->bind('Reports');






