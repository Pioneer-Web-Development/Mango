== (a) QR Code  ===
Contributors: Anton Shevchuk
Donate link: http://anton.shevchuk.name/
Tags: qr code
Requires at least: 2.5.0
Tested up to: 2.5
Stable tag: 0.1

QR Code for your blog

== Description ==


== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload `a-qr-code` to the `/wp-content/plugins/` directory
2. Set write permissions for `/wp-content/plugins/a-qr-code/cache` directory
3. Activate the plugin through the 'Plugins' menu in WordPress

Change your templates

For only link to image with post URL:
<?php if (function_exists('aQRCode')) { echo aQRCode(get_permalink()); }?>

For image with post URL:
<?php if (function_exists('aQRCode')) { echo '<img src="'.aQRCode(get_permalink()).'" alt="QR Code for '.the_title('','',false).'"/> '; }?>

== Frequently Asked Questions ==

= What is QR code =

Read http://en.wikipedia.org/wiki/QR_Code

= How I can check QR code =

You can use online service http://zxing.org/w/decode.jspx

= Where I can download application for mobile =

http://www.i-nigma.mobi/
http://reader.kaywa.com/

== Changes in QR Code library ==

http://www.swetake.com/qr/qr_cgi_e.html

remove perl directory
changes in php/qr_img.php file:

1. set path in setting area
2. rem options from $_GET
3. rem image header 

