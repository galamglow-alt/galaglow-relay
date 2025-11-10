# Используем PHP 8.2 с Apache
FROM php:8.2-apache

# Копируем файлы сайта в контейнер
COPY . /var/www/html/

# Включаем нужные модули PHP (если понадобится)
RUN docker-php-ext-install curl

# Устанавливаем рабочую директорию
WORKDIR /var/www/html/

# Открываем порт
EXPOSE 80

# Запускаем Apache
CMD ["apache2-foreground"]
