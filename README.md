# Crawl Api Data

### Follow below steps to setup project:

- Go to project root directory from the terminal.
- Copy & Paste `.env.example` into `.env` file.
  ```shell
  cp .env.example .env
  ```
- Run following commands:
  ```shell
  composer install
  php artisan key:generate
  ```
- Call [API](http://crawl-api-data.test/api/crawl-data?limit=2) from postman OR browser to get the response
