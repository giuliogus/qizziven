Quizziven
======
![](https://giuliocervi.dev/quizziven/demo3.jpg)


Sviluppato usando [Laravel 10](https://laravel.com/) + [Nuxt 3](https://nuxt.com/)

Requisiti
------
* Docker 
* PHP 8.2
* Composer >= 2.x
* Node.js >= 18.x

Server
------
Per avviare il server:
```console
cd api
cp .env.example .env
composer install
sail up
sail artisan migrate --seed
sail artisan websocket:serve
```
url: [http://localhost:8000](http://localhost:8000)

Client
------
Per avviare il client:
```console
cd client
cp .env.example .env
npm install
npm run dev
```
url: [http://localhost:3000](http://localhost:3000)

Login
------
Per accedere all'applicativo è necesssario loggarsi con un account creato dal seeder del backend.

#### Admin
```
u: admin@example.com
p: admin
```

#### User
```
u: player1@example.com
p: player1

u: player2@example.com
p: player2

u: player3@example.com
p: player3

u: player4@example.com
p: player4
```