Dinein Restaurant Software Implementation
=========================================

Pre-requisites
--------------

**PHP 5 or Greater**

**Web Server (Apache)**

**MYSQL**

Installation
------------

Download composer:

    curl -sS https://getcomposer.org/installer | php

Install vendors:

    php composer.phar install

You need point the document root of your virtual host to /path_to/admingenerator/web

This is an example of VirtualHost:

    <VirtualHost *:80>
        DocumentRoot /path_to/admingenerator/web
        DirectoryIndex index.php
        ServerName local.rms.com
        <Directory "/path_to/admingenerator/web">
            Options Indexes FollowSymLinks
            Order Allow,Deny
            Allow from all
            AllowOverride all
            <IfModule mod_php5.c>
                php_admin_flag engine on
                php_admin_flag safe_mode off
                php_admin_value open_basedir none
            </ifModule>
        </Directory>
    </VirtualHost>
    

Install Database
---------------------

import the sql file from /path_to/admingenerator/database/db.sql into mysql server using one of given method 

Method 1

    Open up mysql workbench. open the above db.sql file and execute all the sql statements

Method 2

    Create database called `dinein` inside mysql
    mysqldump -hlocalhost -u[root] -p  dinein < /path_to/admingenerator/database/db.sql
    

You need to set the url of the resources folder.
Change this line:

    $app['asset_path'] = '/resources';

For the url of your project, for example:

    $app['asset_path'] = 'http://local.rms.com/resources';


**This is it!** Now access with your favorite web browser.