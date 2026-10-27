FROM php:8.2-apache

# Install required PHP extensions
RUN docker-php-ext-install mysqli pdo_mysql && docker-php-ext-enable mysqli

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Increase PHP upload limits for assignment documents & PDFs
RUN echo "upload_max_filesize = 50M\npost_max_size = 55M\nmemory_limit = 256M\nmax_execution_time = 300" > /usr/local/etc/php/conf.d/custom-php.ini

# Configure Apache DocumentRoot and Directory Permissions
RUN sed -ri -e 's!/var/www/html!/var/www/html!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Copy project files
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html

# Create directories and grant write permissions for file uploads
RUN mkdir -p /var/www/html/resources/assignments \
    /var/www/html/resources/submissions \
    /var/www/html/resources/documents \
    /var/www/html/uploads/assignments/submissions \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/resources \
    && chmod -R 775 /var/www/html/uploads 2>/dev/null || true

# Render dynamically binds the service port using the $PORT environment variable (default 10000)
ENV PORT=80
EXPOSE 80

# On container startup, configure Apache to listen on Render's dynamic $PORT and start Apache in foreground
CMD sed -i "s/80/$PORT/g" /etc/apache2/ports.conf /etc/apache2/sites-available/*.conf && apache2-foreground
