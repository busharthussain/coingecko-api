# Coingecko Coin List

#### Install dependencies
Laravel utilizes Composer to manage its dependencies. Make sure you have Composer installed on your machine.

``composer install``

### Config file
Rename or copy .env.example file to .env

1. Set your database credentials in your .env file
2. Please increase the mysql ``max_allowed_packet = 128M`` 


#### Import Database before run the command

Please create a database of(coingecko-api) on your server.

Please download the database file from the public folder and import it to the database that you have created.

##### The database database file name in the public folder is: 
``coingecko-api.sql``

#### Command 
Please run the below command in the project terminal to retrieve the data from Coingecko Api response
```
php artisan coin:list
```