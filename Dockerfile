FROM php:8.2-apache

# ติดตั้ง Extension สำหรับเชื่อมต่อ MySQL
RUN docker-php-ext-install pdo pdo_mysql mysqli

# เปิดใช้งาน Apache mod_rewrite
RUN a2enmod rewrite

WORKDIR /var/www/html