<?php

/*
 * Author: Lasantha Indrajith<hellolasantha@gmail.com>
 *
 */

require_once __DIR__.'/../../../vendor/autoload.php';
require_once __DIR__.'/../../../src/app.php';

use Symfony\Component\Validator\Constraints as Assert;

$app->match('/Reports', function () use ($app) {
    return $app['twig']->render('Reports/list.html.twig');
})->bind('Reports');

$app->match('/Reports/CustomerByDeliveryLocation', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {

    $vars = $request->query->all();
    $city = $vars["city"];
    $where ='';

    $table_columns = array(
        'Address',
        'Customers'
    );

    if(!empty($city)){
        $where = " WHERE Customer_Delivery_address LIKE '%{$city}%' ";
    }

    $find_sql="SELECT Customer_Delivery_address as 'Address', count(Customer_Name) as 'Customers'
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

$app->match('/Reports/SalesByMonth/', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {

    $vars = $request->query->all();
    $from = $vars["datetime_from"];
    $to = $vars["datetime_to"];
    $months = $vars["month"];
    $where ='';

    $table_columns = array(
        'Year',
        'Month',
        'Amount',
    );

    if(!empty($from) && !empty($to) ){

        $where = " WHERE YEAR(Payment_Date) BETWEEN '$from' AND '$to'  ";
    }

    if(!empty($months)){

        $where .= " AND month(Payment_Date) IN (".implode(',',$months).") ";
    }

    $find_sql="SELECT
                YEAR(Payment_Date) as 'Year',
                MONTHNAME(Payment_Date) AS 'Month',
                POP.amount AS 'Amount'
                FROM Purchase_Order_Payment POP
                $where
                GROUP BY Year,Month
                ORDER BY YEAR, Month(Payment_Date)";

    $rows_sql = $app['db']->fetchAll($find_sql, array());


    $yearly_sql="SELECT
                    Year,
                    SUM(CASE WHEN 'Month'=1 THEN 'Amount' ELSE 0 END) AS 'January',
                    SUM(CASE WHEN 'Month'=2 THEN 'Amount' ELSE 0 END) AS 'February',
                    SUM(CASE WHEN 'Month'=3 THEN 'Amount' ELSE 0 END) AS 'March',
                    SUM(CASE WHEN 'Month'=4 THEN 'Amount' ELSE 0 END) AS 'April',
                    SUM(CASE WHEN 'Month'=5 THEN 'Amount' ELSE 0 END) AS 'May',
                    SUM(CASE WHEN 'Month'=6 THEN 'Amount' ELSE 0 END) AS 'June',
                    SUM(CASE WHEN 'Month'=7 THEN 'Amount' ELSE 0 END) AS 'July',
                    SUM(CASE WHEN 'Month'=8 THEN 'Amount' ELSE 0 END) AS 'August',
                    SUM(CASE WHEN 'Month'=9 THEN 'Amount' ELSE 0 END) AS 'September',
                    SUM(CASE WHEN 'Month'=10 THEN 'Amount' ELSE 0 END) AS 'October',
                    SUM(CASE WHEN 'Month'=11 THEN 'Amount' ELSE 0 END) AS 'November',
                    SUM(CASE WHEN 'Month'=12 THEN 'Amount' ELSE 0 END) AS 'December'
                    FROM (SELECT
                            YEAR(Payment_Date) as 'Year',
                            MONTH(Payment_Date) AS 'Month',
                            POP.amount AS 'Amount'
                            FROM Purchase_Order_Payment POP

                            GROUP BY Year,Month ) P
                    GROUP BY 'Year'";

    $yearly_rows_sql = $app['db']->fetchAll($yearly_sql, array());



    $reportData  = preg_replace('/"([^"]+)"\s*:\s*/', '$1:',  json_encode($yearly_rows_sql,JSON_NUMERIC_CHECK));
    return $app['twig']->render('Reports/sales_by_month.html.twig',array(
        'report' => $rows_sql,
        'columns' =>$table_columns,
        'from' => $from,
        'to'=> $to,
        'months'=> $months,
        'reportData'=> $reportData,
    ));

})->bind('Reports_SalesByMonth');

$app->match('/Reports/YearlySalesByMonth/', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {

    $vars = $request->query->all();
    $from = $vars["datetime_from"];
    $to = $vars["datetime_to"];
    $months = $vars["month"];
    $where ='';

    $table_columns = array(
        'Year',
        'January',
        'February',
        'March',
        'April',
        'May',
        'June',
        'July',
        'August',
        'September',
        'October',
        'November',
        'December',
        'Total'
    );

    if(!empty($from) && !empty($to) ){

        $where = " WHERE YEAR(Payment_Date) BETWEEN '$from' AND '$to'  ";
    }

    if(!empty($months)){

        $where .= " AND month(Payment_Date) IN (".implode(',',$months).") ";
    }

    $yearly_sql="SELECT
                            Year,
                            SUM(CASE WHEN `Month`=1 THEN `Amount` ELSE 0 END) AS `January`,
                            SUM(CASE WHEN `Month`=2 THEN `Amount` ELSE 0 END) AS `February`,
                            SUM(CASE WHEN `Month`=3 THEN `Amount` ELSE 0 END) AS `March`,
                            SUM(CASE WHEN `Month`=4 THEN `Amount` ELSE 0 END) AS `April`,
                            SUM(CASE WHEN `Month`=5 THEN `Amount` ELSE 0 END) AS `May`,
                            SUM(CASE WHEN `Month`=6 THEN `Amount` ELSE 0 END) AS `June`,
                            SUM(CASE WHEN `Month`=7 THEN `Amount` ELSE 0 END) AS `July`,
                            SUM(CASE WHEN `Month`=8 THEN `Amount` ELSE 0 END) AS `August`,
                            SUM(CASE WHEN `Month`=9 THEN `Amount` ELSE 0 END) AS `September`,
                            SUM(CASE WHEN `Month`=10 THEN `Amount` ELSE 0 END) AS `October`,
                            SUM(CASE WHEN `Month`=11 THEN `Amount` ELSE 0 END) AS `November`,
                            SUM(CASE WHEN `Month`=12 THEN `Amount` ELSE 0 END) AS `December`,
                            SUM(`Amount`) AS `Total`
                            FROM (SELECT
                            YEAR(Payment_Date) as `Year`,
                            MONTH(Payment_Date) AS `Month`,
                            POP.amount AS `Amount`
                            FROM Purchase_Order_Payment POP
                            $where
                            GROUP BY Year,Month ) P
                            GROUP BY `Year`
";

    $yearly_rows_sql = $app['db']->fetchAll($yearly_sql, array());


    return $app['twig']->render('Reports/yearly_sales_by_month.html.twig',array(
        'report' => $yearly_rows_sql,
        'columns' =>$table_columns,
        'from' => $from,
        'to'=> $to,
        'months'=> $months,
    ));

})->bind('Reports_YearlySalesByMonth');

$app->match('/Reports/DailySalesReport', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {

    $vars = $request->query->all();
    $from = $vars["datetime_from"];
    $to = $vars["datetime_to"];
    $where ='';

    $table_columns = array(
        'Payment Date',
        'Order ID',
        'Contact',
        'Customer',
        'Amount',
        'Payment Type'
    );

    if(!empty($from) && !empty($to) ){

        $where = " AND Payment_Date BETWEEN '$from' AND '$to' AND  Amount >0 ";
    }

    $find_sql="SELECT
                POP.Payment_Date,
                POP.Order_ID,
                C.Customer_Contact,
                C.Customer_Name,
                POP.Amount,
                POP.Payment_Type

                FROM

                Purchase_Order_Payment POP,
                Customer C
                WHERE POP.Customer_Contact = C.Customer_Contact
                $where
                ORDER BY POP.Order_ID
                LIMIT 50";

    $rows_sql = $app['db']->fetchAll($find_sql, array());

    $totalSale = 0;
    foreach($rows_sql as $k=>$v){
        $totalSale+= $v['Amount'];
    }
    $totalSale = number_format($totalSale, 2, '.', ',');

    return $app['twig']->render('Reports/daily_sales_report.html.twig',array(
        'report' => $rows_sql,
        'columns' =>$table_columns,
        'from' => $from,
        'to' => $to,
        'totalSale'=>$totalSale,

    ));

})->bind('Reports_DailySalesReport');

$app->match('/Reports/Customers', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {

    $vars = $request->query->all();
    $contact = $vars["contact"];
    $name = $vars["name"];
    $where ='';

    $table_columns = array(
        'Customer Contact', 'Customer Name', 'Customer Delivery address', 'Customer email'
    );

    $where = '';

    $where = " WHERE Customer_Contact LIKE '$contact%' AND Customer_Name LIKE '$name%'";

    $find_sql="SELECT * FROM  Customer $where";

    $rows_sql = $app['db']->fetchAll($find_sql, array());

    $totalSale = 0;
    foreach($rows_sql as $k=>$v){
        $totalSale+= $v['Amount'];
    }
    $totalSale = number_format($totalSale, 2, '.', ',');

    return $app['twig']->render('Reports/customers.html.twig',array(
        'report' => $rows_sql,
        'columns' =>$table_columns,
        'contact' => $contact,
        'name' => $name,

    ));

})->bind('Reports_Customers');

$app->match('/Reports/CustomersFeedback', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {

    $vars = $request->query->all();
    $from = $vars["datetime_from"];
    $to = $vars["datetime_to"];
    $where ='';

    $table_columns = array(
        'Feedback', 'Orders'
    );

    if(!empty($from) && !empty($to) ){

        $where = " AND Payment_Date BETWEEN '$from' AND '$to' AND  Amount >0 ";
    }

    $find_sql="SELECT
                feedbck AS 'Feedback',
                count(F.feedbck) AS 'Orders'
                FROM
                Customer_Feedback F,
                Purchase_Order_Payment POP
                WHERE F.Order_ID = POP.Order_ID
                $where
                GROUP BY feedbck ";

    $rows_sql = $app['db']->fetchAll($find_sql, array());


    return $app['twig']->render('Reports/feedback.html.twig',array(
        'report' => $rows_sql,
        'columns' =>$table_columns,
        'from' => $from,
        'to' => $to,

    ));

})->bind('Reports_CustomersFeedback');

$app->match('/Reports/Void', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {

    $table_columns = array(
        'Reason', 'Orders'
    );

    $find_sql="SELECT
                reason_for_void,
                count(Order_ID) as `orders`
                FROM  Void
                GROUP BY  reason_for_void";

    $rows_sql = $app['db']->fetchAll($find_sql, array());

    return $app['twig']->render('Reports/voids.html.twig',array(
        'report' => $rows_sql,
        'columns' =>$table_columns

    ));

})->bind('Reports_Void');


$app->match('/Reports/MenuItems', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {

    $vars = $request->query->all();
    $item = $vars["name"];
    $where ='';

    $table_columns = array(
        'ItemIt ID', 'Description', 'Category', 'Selling Price', 'Raw Item', 'Raw Category', 'Quantity'
    );

    if(!empty($item)   ){

        $where = " WHERE Item_Description LIKE '%$item%'";
    }

    $find_sql="SELECT
                MII.ItemIt_ID,
                MII.Item_Description,
                MII.Item_Category,
                MII.Unit_Selling_Price,
                RMI.Description AS 'Raw Item',
                RMI.Product_category AS 'Raw Category',
                RMFMI.Quantity
                FROM
                Menu_Item_Inventory MII
                LEFT JOIN
                Raw_Materials_For_Menu_Item RMFMI
                ON MII.ItemIt_ID =  RMFMI.ItemIt_ID
                LEFT JOIN
                Raw_Material_Inventory RMI
                ON RMFMI.Raw_Material_ID =  RMI.Raw_Material_ID
                $where";

    $rows_sql = $app['db']->fetchAll($find_sql, array());


    return $app['twig']->render('Reports/menu_items.html.twig',array(
        'report' => $rows_sql,
        'columns' =>$table_columns,
        'name' => $item,
    ));

})->bind('Reports_MenuItems');

$app->match('/Reports/Rawtems', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {

    $vars = $request->query->all();
    $item = $vars["name"];
    $where ='';

    $table_columns = array(
        'ID', 'Description', 'ROL', 'Category', 'Buying Price', 'Issued Qunatity', 'Type'
    );

    if(!empty($item)   ){

        $where = " WHERE Description LIKE '%$item%'";
    }

    $find_sql="SELECT * FROM Raw_Material_Inventory $where";

    $rows_sql = $app['db']->fetchAll($find_sql, array());


    return $app['twig']->render('Reports/raw_items.html.twig',array(
        'report' => $rows_sql,
        'columns' =>$table_columns,
        'name' => $item,
    ));

})->bind('Reports_RawItems');

$app->match('/Reports/BestSellingProduct', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {

    $vars = $request->query->all();
    $best_products = $vars["best_products"];
    $from = $vars["datetime_from"];
    $to = $vars["datetime_to"];
    $where ='';
    $limit ='';

    $table_columns = array(
        'Item', 'Description', 'Orders', 'Quantity'
    );

    if(!empty($from) && !empty($to) ){

        $where = " WHERE POP.Payment_Date BETWEEN '$from' AND '$to' ";
    }

    if(!empty($best_products)){
        $limit = " LIMIT $best_products ";
    }

    $find_sql="SELECT
                    MII.ItemIt_ID AS `Item`,
                    MII.Item_Description AS `Description`,
                    COUNT(POI.Order_ID) AS `Orders`,
                    SUM(POI.Quantity) AS `Quantity`
                FROM Purchase_Order_Item POI
                LEFT JOIN Menu_Item_Inventory MII
                ON POI.ItemIt_ID = MII.ItemIt_ID
                LEFT JOIN Purchase_Order_Payment POP
                ON POI.Order_ID = POP.Order_ID
                $where
                GROUP BY POI.ItemIt_ID
                ORDER BY Quantity DESC
                $limit";

    $rows_sql = $app['db']->fetchAll($find_sql, array());


    return $app['twig']->render('Reports/best_selling_products.html.twig',array(
        'report' => $rows_sql,
        'columns' =>$table_columns,
        'best_products' => $best_products,
        'from' => $from,
        'to' => $to,
    ));

})->bind('Reports_BestSellingProduct');

$app->match('/Reports/FastMovingRawItems', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {

    $vars = $request->query->all();
    $best_products = $vars["best_products"];
    $from = $vars["datetime_from"];
    $to = $vars["datetime_to"];
    $where ='';

    $table_columns = array(
        'Raw Material ID', 'Description', 'Total Quantity', 'Type'
    );

    if(!empty($from) && !empty($to) ){

        $where = " AND POP.Payment_Date BETWEEN '$from' AND '$to' ";
    }

    $find_sql="SELECT
                        A.Raw_Material_ID,
                        RMI.Description,
                        ROUND(
                            SUM(
                                CASE
                                WHEN RMI.Raw_Material_Type = 'LITER' THEN Total_Quantity/1000
                                WHEN RMI.Raw_Material_Type = 'KG' THEN Total_Quantity/1000
                                ELSE Total_Quantity

                                END
                            ),2
                        ) AS Total_Quantity,
                        RMI.Raw_Material_Type
                    FROM
                        (
                        SELECT
                            POI.Order_ID,
                            POI.ItemIt_ID,
                            POI.Quantity AS POI_Quantity,
                            RMFMI.ItemIt_ID AS RMFMI_ItemIt_ID,
                            RMFMI.Raw_Material_ID,
                            RMFMI.Quantity AS RMFMI_Quantity,
                            POI.Quantity * RMFMI.Quantity AS `Total_Quantity`
                        FROM
                            Purchase_Order_Item POI,
                            Raw_Materials_For_Menu_Item RMFMI,
                            Purchase_Order_Payment POP

                        WHERE POI.ItemIt_ID = RMFMI.ItemIt_ID
                        AND POP.Order_ID = POI.Order_ID
                        $where
                        ORDER BY RMFMI.Raw_Material_ID
                        ) A
                        LEFT JOIN Raw_Material_Inventory RMI
                        ON A.Raw_Material_ID = RMI.Raw_Material_ID
                    GROUP BY Raw_Material_ID
                    ORDER BY Total_Quantity DESC";

    $rows_sql = $app['db']->fetchAll($find_sql, array());


    return $app['twig']->render('Reports/fast_moving_raw_items.html.twig',array(
        'report' => $rows_sql,
        'columns' =>$table_columns,
        'best_products' => $best_products,
        'from' => $from,
        'to' => $to,
    ));

})->bind('Reports_FastMovingRawItems');