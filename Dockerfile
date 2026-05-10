FROM php:8.2-apache

# Install database extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# 1. Enable Apache rewrite module (Necessary for MVC routing)
RUN a2enmod rewrite

# 2. Copy your project files
COPY . /var/www/html/

# 3. Allow .htaccess to override Apache defaults
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# 4. Handle Render's dynamic Port
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# 5. Set permissions
RUN chown -R www-data:www-data /var/www/html