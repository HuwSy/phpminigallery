# PHP Mini Gallery

With this PHP script you'll get a full featured gallery in an instant. It does not take much more than copying two php files, a folder placeholder file and your images to your web server, then optionally updating a couple of config variables in index.php

This is a limited evolution/extension of the retired project by http://GitHub.com/shred to be a bit more mobile friendly and to support videos and subfolders. More may be done around reducing page loads for client side calls at a later date but this has reached my requirements for now. 

## Features

Some features of this gallery script are:

* Easy installation
* Index page generation of all pictures, videos and subfolders in modified descending order
* Automatic thumbnail generation of images and gif thumbnail generation of videos
* Individual captions for each picture from txt file or exif data
* Layout is easily changed, even without PHP skills
* Runs on almost any host who supports PHP, some picture formats and video thumbnails require imagemagick or ffmpeg
* No database required
* Source code available at [GitHub](https://github.com/HuwSy/phpminigallery)

## Prerequisites

This script is quite easy-going. PHP 4.1 is already sufficient, which is provided by almost all web hosts. Register globals may be enabled or disabled. The script will run on php8.1.

For thumbnail generation, either GD2, ImageMagick or GD is required for images and FFMPEG for videos. GD is not recommended though, and should only be used as a last resort, because the quality of the thumbnail images will be really poor. If you use ImageMagick or FFMPEG, make sure PHP SafeMode is turned off.

This script is entirely file-based, and does not involve any database programming.

## Usage

PHP Mini Gallery is very simple to use. You won't need to have any PHP skills.

First, you have to create a directory for the gallery and two folders for images and thumbnails. Make sure that the script is allowed to read the images and create files in the thumbnails directory.

Now you upload all your pictures into this directory. The default image formats allowed are JPEG, PNG and GIF and video formats MP4, MOV, AVI and MPG. The file names need to have a correct suffix. The PHP Mini Gallery shows the pictures in date modified descending order.

If you want to add a caption to a picture (e.g. a description of the picture's content), you can place the text in another file, which is named like the picture this caption will belong to, but has an additional `.txt` suffix attached. Example: the caption file for the picture `04.jpg` would be named `04.jpg.txt`.

Finally you have to place the files from the PHP Mini Gallery archive into the gallery directory. And that's all!

If you invoke the page with your browser, you'll first see an index print of all pictures' thumbnails. At the first invocation, the thumbnails will be created automatically, and will be stored in files with an appropriate file name. his first invocation could take a while until all thumbnails are created, and may even time out. For subsequent calls, the thumbnail files will be used though, so you will receive the index page much faster. PHP Mini Gallery will detect if you have modified a picture, and will automatically re-create its thumbnail image.

If you click on a thumbnail, you will get the full size picture. Starting from there, you can see the next picture by swiping the current picture, or you can go forward and back using the navigation links.

## Configuration

At the beginning of the script `index.php`, you will find a few configuration parameters. PHP Mini Gallery already has reasonable default values, so you usually won't need to change them unless you really want to change them.

```php
$CONFIG['thumb.width']    = 100;      // Thumbnail width (pixels)
$CONFIG['thumb.height']   = 100;      // Thumbnail height (pixels)
```

This is the maximum dimension of thumbnail pictures. While scaling, PHP Mini Gallery will take care to keep the aspect ratio of the picture.

```php
$CONFIG['thumb.scale']    = 'gd2';    // Set to 'gd2', 'im' or 'gd'
$CONFIG['tool.imagick']   = '/usr/X11R6/bin/convert';  // Path to convert
```

Here you will set the scaling tool.

* `gd2` selects the GD2 library, which is installed in most of the recent PHP setups. It will result a good quality without any installation hassles, and thus is the recommended setting.
* `im` uses ImageMagick for scaling. You have to set the path to the `convert` tool in `tool.imagick`! Use this if GD2 is not available.
* `gd` uses the legacy GD library, which should be available in almost all PHP installations. The quality is quite poor, though, so you should only take this one if you have no other choice.

Note: You must set the absolute path to ImageMagick's `convert` tool. If you are in the unlucky situation to be confronted with a Windows server, also remember to double the backslashes (e.g. '`C:\\path\\to\\convert.exe`').

```php
$CONFIG['template']       = 'template.php';   // Template file
```

Path to the template file, which is used for HTML generation. If you want to use a common template file for several galleries, you can also set the path to this file here. The gallery will then only require the `index.php` file in each directory.

## Example debian setup
```
echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/sury-php.list
wget -qO - https://packages.sury.org/php/apt.gpg | sudo gpg --no-default-keyring --keyring gnupg-ring:/etc/apt/trusted.gpg.d/debian-php-8.gpg --import
chmod 644 /etc/apt/trusted.gpg.d/debian-php-8.gpg
apt install nginx-full mediainfo ffmpeg exiftool php8.1 php8.1-fpm php8.1-gd php8.1-mbstring php8.1-cli php8.1-dev php8.1-curl php8.1-xmlrpc php8.1-xml php8.1-zip
```

/etc/nginx/conf.d/php
```
    location ~ \.php$ {
          fastcgi_split_path_info ^(.+\.php)(/.+)$;
          fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
          fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
          fastcgi_index index.php;
          include fastcgi_params;
    }
```

/etc/nginx/conf.d/cache
```
    location ~* \.(jpg|jpeg|gif|css|png|js|woff|ttf|svg|eot)$ {
        expires 30d;
    }
```

nano /etc/nginx/sites-enabled/gallery
```
    server {
        listen 8090;
        root /var/www/gallery;
        index index.php;
        allow 172.16.0.0/8;
        deny all;
        location / {
               include /etc/nginx/conf.d/php;
               include /etc/nginx/conf.d/cache;
        }
    }
```

```
sed -i "s/user www-data;/user www-data www-data;/g" /etc/nginx/nginx.conf
sed -i "s/# server_tokens off;/server_tokens off;/g" /etc/nginx/nginx.conf
sed -i "s/access_log \/var\/log\/nginx\/access\.log;/access_log off;/g" /etc/nginx/nginx.conf
sed -i "s/error\.log;/error\.log crit;/g" /etc/nginx/nginx.conf

sed -i '/^;\?listen.owner/ c\listen.owner = www-data' /etc/php/8.1/fpm/pool.d/www.conf
sed -i '/^;\?listen.group/ c\listen.group = www-data' /etc/php/8.1/fpm/pool.d/www.conf
sed -i '/^;\?listen.mode/ c\listen.mode = 0660' /etc/php/8.1/fpm/pool.d/www.conf

mkdir -p /var/www/gallery
wget https://github.com/HuwSy/phpminigallery/archive/refs/heads/master.zip
unzip master.zip 
mv master/* /var/www/gallery
mkdir /var/www/gallery/thumbs
chown -R www-data:www-data /var/www/gallery
```

## Simple Layout Adaptions

The PHP Mini Gallery is designed that all layout changes need to be made in the `template.php` file only. There are some special "tags" which can be used for this purpose, so you can adapt the gallery to your needs, without any PHP knowledge.

These tags are available:

* `<pmg:if page="index">...</pmg:if>` - The content of this container will only be sent to the browser if the index print page is to be shown.
* `<pmg:if page="picture">...</pmg:if>` - The content of this container will only be sent to the browser if a complete picture is to be shown. You cannot nest both <pmg:if> tags, but you can use them as often as you want.
* `<pmg:first>...</pmg:first>` - A link to the gallery's first picture. You can use it like an &lt;a> tag. If there is no appropriate picture, the tag and its content will be omitted. Example: `<pmg:first>[ First ]</pmg:first>`
* `<pmg:last>...</pmg:last>` - A link to the gallery's last image.
* `<pmg:toc>...</pmg:toc>` - A link to the index print page.
* `<pmg:prev>...</pmg:prev>` - A link to the previous picture.
* `<pmg:next>...</pmg:next>` - A link to the next picture.
* `<pmg:image/>` - This tag replaces the entire image tag and a link to the next picture. The CSS class `picimg` can be used to change the rendering of this picture. For index print view, this tag is empty.
* `<pmg:caption/>` - If there is a caption for this picture, its content will be inserted here. Otherwise the tag is empty.
* `<pmg:index/>` - This tag replaces the entire table containing the thumbnails of all pictures. The CSS class `tabindex` can be used to change the rendering of the table. The CSS class thumbimg changes the rendering of the single thumbnail images.
* `<pmg:count/>` - Will be replaced by the total count of the pictures.
* `<pmg:current/>` - Will be replaced by the number of the current picture. This tag is empty for index print views.

The tag parser isn't very smart, and can be fooled quite easily. You should take care to use the tags as explained here, otherwise they might not be recognized and replaced properly. You may want to use the packaged `template.php` as an example.

## Complex Layout Adaptions

The `template.php` is included as PHP script, and thus can also contain PHP language parts. In especially complex cases, you can even implement the entire HTML creation here, instead of using the special tags. You will find all necessary information in the global `$CONTEXT` array.

The `$CONTEXT` array contains these keys:

* `'page'` - For complete picture view, you'll find 'picture' here, and 'index' for the index print view.
* `'files'` - An array containing the file names of all pictures, in their output order. Thumbnails and scripts are not included here.
* `'count'` - The number of pictures
* `'current'` - For complete picture view, you'll find the number of the current picture, counted starting with 1. So the file name of the current picture you'll find in 'files' at `$CONTEXT['current']-1`.
* `'first'` - File name of the first picture.
* `'last'` - File name of the last picture.
* `'prev'` - For complete picture view: file name of the previous picture. This entry is empty if the current picture is the first picture.
* `'next'` -  For complete picture view: file name of the next picture. This entry is empty if the current picture is the last picture.
* `'pictag'` - For complete picture view: the entire image tag for the picture, and a link to the next picture.
* `'caption'` - For complete picture view: if the current picture has a caption file, you'll find its content in here.
* `'indextag'` - The entire thumbnail table with links to the appropriate pictures.

The special tags will be replaced after executing the `template.php`, so you can randomly mix PHP script parts and special tags. The access to the `$CONTEXT` array is read only, though!

## License

The PHP Mini Gallery is distributed under GPL ([Gnu Public License](http://www.gnu.org/licenses/gpl.html)). It is free of charge, even for commercial purposes.

This software is open source. You man modify the source codes, as long as you also publish your changes under GPL. There is one exception: you do not need to publish a modified `template.php` file if it has only been customized to meet your web site's design.

Keep in mind that you are only allowed to put pictures on the PHP Mini Gallery with the consent of the respective copyright owner.
