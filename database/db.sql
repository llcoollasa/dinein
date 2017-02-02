/* 
DRIVER 		: MYSQL 
DATABASE 	: dinein

Create a table called `dinein` if not exits
*/

USE dinein;

/*
Create tables
*/

create table Customer(
Customer_Contact varchar(10) primary key,
Customer_Name  varchar(30),
Customer_Delivery_address varchar(200),
Customer_email varchar(50)
);

create table Menu_Item_Inventory(
ItemIt_ID varchar(6) primary key,
Item_Description  varchar(100),
Item_Category varchar(20),
Unit_Selling_Price double(8,2)
);

create table Raw_Material_Inventory(
Raw_Material_ID int(10) primary key auto_increment,
Description  varchar(100),
Reorder_level double(8,2),
Product_category varchar(50),
Unit_Buying_Price double(8,2),
Issued_Qunatity double(8,2),
Raw_Material_Type varchar(50)
);

create table Raw_Materials_For_Menu_Item(
ItemIt_ID varchar(6) ,
Raw_Material_ID int(10),
Quantity double(8,2),
primary key(ItemIt_ID,Raw_Material_ID,Quantity),
CONSTRAINT fk_ItemIt_ID FOREIGN KEY (ItemIt_ID) REFERENCES Menu_Item_Inventory(ItemIt_ID) ON DELETE CASCADE ON UPDATE CASCADE
);

create table Purchase_Order(
Order_ID int(10) primary key auto_increment,
Order_Date date,
Order_Delivery_date date,
order_type varchar(50)
);


create table Purchase_Order_Item(
Order_ID int(10),
ItemIt_ID varchar(6),
Quantity double(8,2),
primary key(Order_ID,ItemIt_ID),
CONSTRAINT fk_purchase_Order_ID FOREIGN KEY (Order_ID) REFERENCES Purchase_Order(Order_ID)  ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT fk_purchase_ItemIt_ID FOREIGN KEY (ItemIt_ID) REFERENCES Menu_Item_Inventory(ItemIt_ID)  ON DELETE CASCADE ON UPDATE CASCADE
);


create table Purchase_Order_Payment(
Payment_Id int(10) primary key auto_increment,
Order_ID int(10),
Amount double(8,2),
Payment_Date varchar(50),
Customer_Contact varchar(10),
Payment_Type  varchar(50),
CONSTRAINT fk_Order_ID FOREIGN KEY (Order_ID) REFERENCES Purchase_Order(Order_ID) ON DELETE SET NULL,
CONSTRAINT fk_Customer_Contact FOREIGN KEY (Customer_Contact) REFERENCES Customer(Customer_Contact) ON DELETE SET NULL
);


create table Void(
Order_ID int(10)primary key auto_increment,
reason_for_void varchar(200),
CONSTRAINT fk_void_Order_ID FOREIGN KEY (Order_ID) REFERENCES Purchase_Order(Order_ID)  ON DELETE CASCADE ON UPDATE CASCADE

);


create table Customer_Feedback(
Order_ID int(10)primary key auto_increment,
feedbck varchar(200),
CONSTRAINT fk_feedback_Order_ID FOREIGN KEY (Order_ID) REFERENCES Purchase_Order(Order_ID)  ON DELETE CASCADE ON UPDATE CASCADE
);


/* Data Dumping */

/*Customer*/

INSERT INTO `Customer` 
(`Customer_Contact`, `Customer_Name`, `Customer_Delivery_address`, `Customer_email`) 
VALUES 
('0784788554','Smith Anna','Bambalapitiya','smithanna@gmail.com'),
('0784845757','Williams Ben','Kolonnawa','williamsben@gmail.com'),
('0784343645','Browne Arthur','Kohuwala','brownearthur@gmail.com'),
('0784789456','Tinsell Richard','Nugegoda','tinsellrichard@gmail.com'),
('0784985413','Rafferty Alison','Battaramulla','raffertyalison@gmail.com'),
('0784741125','Battle Victor','Rajagiriya','battlevictor@gmail.com'),
('0784985656','Bridge Jeremy','Colpetty','bridgejeremy@gmail.com'),
('0784956236','Rainbird Rita','Dehiwala','rainbirdrita@gmail.com'),
('0784112211','Hagopian Eva','Dehiwala','hagopianeva@gmail.com'),
('0784854565','Aoki Yumiko','Colpetty','aokiyumiko@gmail.com'),
('0775884455','Norman Henry','Battaramulla','normanhenry@gmail.com'),
('0775745856','Qureshi Muhhamad','Kolonnawa','qureshimuhhamad@gmail.com'),
('0775852526','Connor Dean','Colpetty','connordean@gmail.com'),
('0775656856','Dart Daniel','Borella','dartdaniel@yahoo.com'),
('0775445544','Peters Gary','Narahenpita','petersgary@gmail.com'),
('0775222222','Vipond Jane','Dehiwala','vipondjane@yahoo.com'),
('0775984578','Thomas Theresa','Havelock Town','thomastheresa@yahoo.com'),
('0775919191','Smith Raymond','Bambalapitiya','smithraymond@gmail.com'),
('0775666566','Ruocco Salvatore','Narahenpita','ruoccosalvatore@yahoo.com'),
('0775741254','Wolfson Dennis','Colpetty','wolfsondennis@yahoo.com'),
('0775778844','Curtis Peter','Colpetty','curtispeter@yahoo.com'),
('0775985696','Fordham Denise','Cinnamon Gardens','fordhamdenise@yahoo.com'),
('0775515975','Stocker Martin','Havelock Town','stockermartin@yahoo.com'),
('0775736563','Lawless Lynnette','Nugegoda','lawlesslynnette@company.com'),
('0775555555','Morgan Nigel','Union Place','morgannigel@company.com'),
('0775333355','Newley Violet','Bambalapitiya','newleyviolet@yahoo.com'),
('0775846455','Newberry George','Union Place','newberrygeorge@company.com'),
('0775184362','Pillar Edward','Borella','pillaredward@company.com'),
('0112784536','Major Ellen','Narahenpita','majorellen@yahoo.com'),
('0112956862','Allen Helen','Union Place','allenhelen@company.com'),
('0112251232','Morgan Susan','Colpetty','morgansusan@company.com'),
('0112457458','Patel Mamta','Town Hall','patelmamta@company.com'),
('0112665588','Bennet Craig','Havelock Town','bennetcraig@company.com'),
('0112171745','Tsang Swee Hor','Slave Island','tsangsweehor@company.com'),
('0112565682','Jones Harry','Union Place','jonesharry@company.com'),
('0112154365','Marsden Matt','Narahenpita','marsdenmatt@company.com'),
('0112998877','Hutchins Melissa','Town Hall','hutchinsmelissa@company.com'),
('0112154623','Pinkerton George','Slave Island','pinkertongeorge@company.com'),
('0112655946','Easter Clive','Nugegoda','easterclive@company.com'),
('0112144141','Morrow Keith','Borella','morrowkeith@company.com'),
('0112585456','Fielder Mary','Colpetty','fieldermary@company.com'),
('0112262566','Batchelor Allistair','Town Hall','batchelorallistair@company.com'),
('0717458565','Palmer Algernon','Nugegoda','palmeralgernon@company.com'),
('0171745124','Patel Dilip','Bambalapitiya','pateldilip@company.com'),
('0776458434','Ambler Peter','Cinnamon Gardens','amblerpeter@company.com'),
('0776895321','Walsh Juliet','Cinnamon Gardens','walshjuliet@company.com'),
('0712456985','Fairfax Hilary','Slave Island','fairfaxhilary@company.com'),
('0171263235','Bennett David','Nugegoda','bennettdavid@company.com'),
('0714585212','Windsor Betty','Havelock Town','windsorbetty@company.com'),
('0714582343','Chandler Mervin','Park Avenue','hello@fb.com');


/*Menu Item Inventory*/

INSERT INTO `Menu_Item_Inventory` 
(`ItemIt_ID`, `Item_Description`, `Item_Category`, `Unit_Selling_Price`) 
VALUES 
('BRF100', 'Hash Brown', 'BREAKFAST', '100'),
('BRF101', 'Egg Burger', 'BREAKFAST', '150'),
('BRF102', 'Sausage & Egg Burger', 'BREAKFAST', '200'),
('BRF103', 'Seeni Sambol Burger', 'BREAKFAST', '180'),
('BRF104', 'Omlette Burger', 'BREAKFAST', '150'),
('BRF105', 'Hot Cake', 'BREAKFAST', '150'),
('BRF106', 'EGG Burger Meal', 'BREAKFAST', '300'),
('BRF107', 'Sausage & Egg Burger Meal', 'BREAKFAST', '350'),
('BRF108', 'Seeni Sambol Burger Meal', 'BREAKFAST', '350'),
('BRF109', 'Omlette Burger Meal', 'BREAKFAST', '300'),

('SID200', 'French Fries Regular', 'SIDES', '170'),
('SID201', 'French Fries Large', 'SIDES', '190'),
('SID202', 'French Fries Extra Large', 'SIDES', '210'),

('SAL300', 'Vegitable Salad', 'SALAD', '450'),
('SAL301', 'Chicken Salad', 'SALAD', '580'),

('ALA400', 'Beef Burger', 'ALACARTE', '200'),
('ALA401', 'Cheese Burger', 'ALACARTE', '300'),
('ALA402', 'Double Cheese Burger', 'ALACARTE', '450'),
('ALA403', 'Tripple Cheese Burger', 'ALACARTE', '600'),
('ALA404', 'Chicken Mini', 'ALACARTE', '200'),
('ALA405', 'Fillet O Fish', 'ALACARTE', '400'),
('ALA406', 'Chicken Burger', 'ALACARTE', '450'),
('ALA407', 'Big Chicken Burger', 'ALACARTE', '450'),
('ALA408', 'Spicy Chicken Burger', 'ALACARTE', '450'),
('ALA409', 'Crispy Chicken ', 'ALACARTE', '450'),
('ALA410', 'Crispy Wrap ', 'ALACARTE', '480'),
('ALA411', 'Cheese & Veggie Burger', 'ALACARTE', '350'),
('ALA412', 'Nuggets 6Pcs', 'ALACARTE', '450'),
('ALA413', 'Nuggets 9Pcs', 'ALACARTE', '600'),
('ALA414', 'Chicken Wings 6Pcs', 'ALACARTE', '250'),
('ALA415', 'Chicken Wings 9Pcs', 'ALACARTE', '450'),
('ALA416', 'Chicken Rice', 'ALACARTE', '300'), 

('BEV500', 'Coke Regular', 'BEVERAGE', '100'), 
('BEV501', 'Coke Large', 'BEVERAGE', '130'), 
('BEV502', 'Coke Extra Large', 'BEVERAGE', '150'), 
('BEV503', 'Sprite Regular', 'BEVERAGE', '100'), 
('BEV504', 'Sprite Large', 'BEVERAGE', '130'), 
('BEV505', 'Sprite Extra Large', 'BEVERAGE', '150'), 
('BEV506', 'Fanta Regular', 'BEVERAGE', '100'), 
('BEV507', 'Fanta Large', 'BEVERAGE', '130'), 
('BEV508', 'Fanta Extra Large', 'BEVERAGE', '150'), 
('BEV509', 'Portello Regular', 'BEVERAGE', '100'), 
('BEV510', 'Portello Large', 'BEVERAGE', '130'), 
('BEV511', 'Portello Extra Large', 'BEVERAGE', '150'), 
('BEV512', 'Water Bottle Small', 'BEVERAGE', '80'), 
('BEV513', 'Water Bottle Large', 'BEVERAGE', '130'), 

('DES600', 'Chocalate Milk Shake', 'DESSERT', '250'), 
('DES601', 'Strawberry Milk Shake', 'DESSERT', '250'), 
('DES602', 'Vanilla Milk Shake', 'DESSERT', '250'), 
('DES603', 'Chocalate Ice Cream', 'DESSERT', '200'),  
('DES604', 'Strawberry Ice Cream', 'DESSERT', '200'),  
('DES605', 'Vanilla Ice Cream', 'DESSERT', '200');


/*Raw Material Inventory */
INSERT INTO `Raw_Material_Inventory` 
(`Description`, `Reorder_level`, `Product_category`, `Unit_Buying_Price`, `Issued_Qunatity`, `Raw_Material_Type`) 
VALUES 
('Egg', '500', 'DAIRY', '3', '100', 'UNIT'),
('Chicken', '500', 'MEAT', '300', '100', 'KG'),
('Beef', '500', 'MEAT', '200', '200', 'KG'),
('OIL', '1000', 'OIL', '380', '380', 'LITER'),
('Burger Bun', '5000', 'PASTRY', '20', '100', 'UNIT'),
('Sausage', '100', 'MEAT', '900', '100', 'KG'),
('Coconut', '500', 'PALM', '50', '100', 'UNIT'),
('Chillie', '100', 'SPICY', '1500', '100', 'KG'),
('Pepper Powder', '100', 'SPICY', '1600', '100', 'KG'),
('Green Chillies', '500', 'SPICY', '965', '100', 'KG'),
('Garlic', '500', 'SPICY', '310', '100', 'KG'),
('Mustard', '500', 'SPICY', '347', '100', 'KG'),
('Leeks', '500', 'VEGETABLE', '200', '100', 'KG'),
('Limes', '500', 'VEGETABLE', '470', '100', 'KG'),
('Potatoes', '500', 'VEGETABLE', '120', '100', 'KG'),
('Beans', '500', 'VEGETABLE', '320', '100', 'KG'),
('Beet Root', '500', 'VEGETABLE', '225', '100', 'KG'),
('Carrot', '500', 'VEGETABLE', '280', '100', 'KG'),
('Tomato', '500', 'VEGETABLE', '230', '100', 'KG'),
('B Onions', '500', 'VEGETABLE', '120', '100', 'KG'),
('Salad', '500', 'VEGETABLE', '120', '100', 'KG'),
('Lettuce', '500', 'VEGETABLE', '156', '100', 'KG'),
('Salt', '20', 'SALT', '35', '100', 'KG'),
('Coke', '2000', 'BEVERAGE', '35', '200', 'LITER'),
('Sprite', '2000', 'BEVERAGE', '35', '200', 'LITER'),
('Fanta', '2000', 'BEVERAGE', '35', '200', 'LITER'),
('Portello', '2000', 'BEVERAGE', '35', '200', 'LITER'),
('Chocolate', '2000', 'BEVERAGE', '35', '200', 'LITER'),
('Vanilla', '2000', 'BEVERAGE', '35', '200', 'LITER'),
('Strawberry', '2000', 'BEVERAGE', '35', '200', 'LITER'),
('Chocolate Ice Cream', '2000', 'ICE CREAM', '35', '100', 'LITER'),
('Vanilla Ice Cream', '2000', 'ICE CREAM', '35', '100', 'LITER'),
('Strawberry Ice Cream', '2000', 'ICE CREAM', '35', '100', 'LITER'),
('Water Bottle Small', '2000', 'BEVERAGE', '40', '100', 'LITER'),
('Water Bottle Large', '2000', 'BEVERAGE', '65', '100', 'LITER');

INSERT INTO `raw_material_inventory`
(`Raw_Material_ID`, `Description`, `Reorder_level`, `Product_category`, `Unit_Buying_Price`, `Issued_Qunatity`, `Raw_Material_Type`)
VALUES
(36, 'Flour', 500.00, 'Flour', 100.00, 100.00, 'KG'),
(37, 'Processed cheese', 500.00, 'DAIRY', 800.00, 100.00, 'KG'),
(38, 'Lettuce leaf', 100.00, 'VEGETABLE', 200.00, 100.00, 'KG');

INSERT INTO `Raw_Materials_For_Menu_Item`
(`ItemIt_ID`, `Raw_Material_ID`, `Quantity`)
VALUES
('BEV500', '24', '0.5'),
('BEV501', '24', '1.5'),
('BEV502', '24', '2'),
('BEV503', '25', '0.5'),
('BEV504', '25', '1.5'),
('BEV505', '25', '2'),
('BEV506', '26', '0.5'),
('BEV507', '26', '1.5'),
('BEV508', '26', '2'),
('BEV509', '27', '0.5'),
('BEV510', '27', '1.5'),
('BEV511', '27', '2'),
('BEV512', '34', '1'),
('BEV513', '35', '1'),
('BRF100', '15', '0.05'),
('BRF100', '20', '0.02'),
('BRF100', '1', '1'),
('BRF100', '4', '0.03'),
('BRF100', '23', '0.01'),
('BRF100', '9', '0.01'),
('BRF100', '36', '0.10'),
('BRF101', '23', '0.01'),
('BRF101', '9', '0.01'),
('BRF101', '1', '1'),
('BRF101', '5', '1'),
('BRF101', '12', '0.03'),
('BRF101', '19', '0.03'),
('BRF101', '37', '0.05'),
('BRF101', '4', '0.03'),
('BRF102', '5', '1'),
('BRF102', '19', '0.08'),
('BRF102', '20', '0.03'),
('BRF102', '4', '0.03'),
('BRF102', '1', '1'),
('BRF102', '38', '0.05'),
('BRF103', '5', '1'),
('BRF103', '23', '0.01'),
('BRF103', '9', '0.01'),
('BRF103', '20', '0.10');