# FROM nginx:latest

FROM php:8.1-fpm  
# タイムゾーン(東京)
ENV TZ Asia/Tokyo

# PHPでMySQLを使えるようにする設定（これがないと後でデータベース接続でエラーになります）
RUN docker-php-ext-install mysqli pdo_mysql
# このコンテナに入った時のカレントディレクトリ
WORKDIR /var/www/html