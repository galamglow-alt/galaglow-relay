# Используем официальный PHP-образ с Apache
FROM php:8.2-apache

# Копируем файлы проекта в контейнер
COPY . /var/www/html/

# Включаем нужные модули Apache
RUN docker-php-ext-install mysqli && a2enmod rewrite

# Открываем порт 80
EXPOSE 80

# Указываем команду запуска
CMD ["apache2-foreground"]
