# payment-app

Project for card payment simulation


## Setup and configuration for Linux OS local environment

- Clone the following repositories to the /var/www/html path:
  
  git@github.com:vladaj81/payment-app.git

  git@github.com:vladaj81/payment-service-provider.git

- In /etc/apache2/apache2.conf file add following virtual hosts:

      <VirtualHost *:80>
          ServerName payment-app.localhost
          ServerAlias www.payment-app.localhost
  
          DocumentRoot /var/www/html/payment-app/public
          <Directory /var/www/html/payment-app/public>
              AllowOverride All
              Require all granted
              Allow from All
              <IfModule mod_rewrite.c>
                  Options -MultiViews
                  RewriteEngine On
              </IfModule>
              FallbackResource /index.php
          </Directory>
  
          ErrorLog /var/log/apache2/project_error.log
          CustomLog /var/log/apache2/project_access.log combined
      </VirtualHost>
  
      <VirtualHost *:80>
          ServerName psp.localhost
          ServerAlias www.psp.localhost
  
          DocumentRoot /var/www/html/payment-service-provider/public
          <Directory /var/www/html/payment-service-provider/public>
              AllowOverride All
              Require all granted
              Allow from All
              <IfModule mod_rewrite.c>
                  Options -MultiViews
                  RewriteEngine On
              </IfModule>
              FallbackResource /index.php
          </Directory>
  
          ErrorLog /var/log/apache2/project_error.log
          CustomLog /var/log/apache2/project_access.log combined
      </VirtualHost>


- In /etc/hosts file add these two hosts:
  
  127.0.0.1   payment-app.localhost
  
  127.0.0.1   psp.localhost

- Restart the apache server with following command:

  sudo service apache2 restart


- Create mysql database payment_app_db
- In payment-app project, in .env.local file, setup db connection string:
  DATABASE_URL="mysql://user:password@127.0.0.1:3306/payment_app_db?serverVersion=8.0.32&charset=utf8mb4"

- Run db migrations with following command:

  php bin/console doctrine:migrations:migrate

## Application user guide

- Go to the following page: http://payment-app.localhost/payment-form
- User data is hardcoded in the form, for simplicity.
- Fill in the rest of the data and confirm the form. A request will be sent to psp app.
- After that, if there are no errors, you will be redirected to the page with payment information.
- When you confirm the payment, a message will be displayed as to whether the bill was paid successfully, or if there is an error in the callback request according to the criteria from the task.

## Notes

- The application is written for the needs of the task and can be done much more seriously.
- A large part can be refactored by moving the code from the controller to separate services.
- Also, a large amount of data is hardcoded and can be changed to be dynamic.
- I didn't get to implement sonata admin.
