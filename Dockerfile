FROM php:8.2-cli

# Install mysqli dan pdo_mysql
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Set working directory
WORKDIR /app

# Copy semua file project
COPY . .

# Jalankan PHP built-in server
CMD php -S 0.0.0.0:$PORT
