# CMS-API (Server) & CMS-LARAVEL (Client)

#### Server Requirement
* Composer
* PHP >= 7
* curl
* Image Optimization Modules
    * pngquant
    * gifsicle
    * jpegoptim
* Thumbnail Generator    
    * gd
    * exif
* mysql    
* mysqldump
* Compression Modules
    * gzip
    * gunzip
    * php7.x-zip

#### CMS Installation

1. Run commands below at base directory:  

    ```
    composer install
    npm install
    ```  
2. If folder `storage` doesn't exist, create one at base directory like structure below:
* storage
    * app
    * framework
        * cache
        * sessions
        * views
    * logs
3. Create `.env` file if none exists manually or using a command below:  

    ```
    composer run post-root-package-install
    ```  
4. Setup `.env` content:
* APP_URL...
* APP_DEBUG=false
* Edit CMS_API_DOMAIN=http:...
* Edit CMS_FORM_TOKEN=...
5. Setup `config/cms.php`, `config/client-cms.php` and `config/database.php` accordingly, using key as an application name.
6. Then run command:  

    ```
    php artisan cms:install [application name]
    ```  
7. Change permissions of `public/` and `storage/` using a command below:  

    ```
    sudo chmod -R 777 public/
    sudo chmod -R 777 storage/
    ```  
8. Restart server (optional)  

    ```
    sudo service apache2 restart
    ```

#### SEO and Image Optimization
For apache2 server, activate these mods with command:
```
sudo a2enmod rewrite headers deflate setenvif filter mime expires
```

#### Image optimization
```
sudo apt-get install pngquant gifsicle jpegoptim
```
**Note:** If the server cannot installed those plugins above, the image optimizer will not effect the uploading images.

#### Thumbnail generator
* [gd](http://php.net/manual/en/image.installation.php)
* [exif](http://php.net/manual/en/exif.installation.php)

#### Database Backup Schedule
Create a new cron job for scheduling task and make sure you have cron installed.
1. `crontab -e`
2. Add cron job command below into the editor:  

    ```
    * * * * * php /path-to-your-project/artisan schedule:run >> /dev/null 2>&1
    ```
    
#### CMS Config (cms.php)
```
'test' => [
    'main_language' => [
        'code' => 'en',
        'name' => 'English',
        'locale' => 'en-GB',
        'hreflang' => 'en'
    ],
    'auto_image_optimizer_enabled' => true,
    'no_reply_email_address' => 'developers@quo-global.com',
    'form_token' => 'YofihgsHXIObx9Nvz0smAA1plgYcozN1mMUwqjOe',
    'ransom_code' => '11cef29c138fd9894c848468043718d9644f1c0c',
    'uploads_path' => 'uploads/test',
    'crops_path' => '_crops',
    'uploaded_files_path' => '_uploaded_files',
    'scheduling_backup' => true,
    
    // Postmark
    'postmark_app_email' => 'developers@quo-global.com',
    'postmark_app_token' => 'b479ca11-8dd6-445d-a69e-193fee6247c2',
    
    // SMTP
    'smtp_host' => 'sub5.mail.dreamhost.com',
    'smtp_port' => 587,
    'smtp_username' => 'no-reply@mekongkingdoms.com',
    'smtp_password' => 'RGVNbejeB6HPVq*5b-',
    
    // Google reCAPTCHA
    'google_recaptcha_site_key' => '',
    'google_recaptcha_secret' => '',
    
    'preview_post_api' => 'preview',
    'previews' => [
        // Add website api url for preview function here
        'test' => 'https://test.test/api/',
    ]
]
```
