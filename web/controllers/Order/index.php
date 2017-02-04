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