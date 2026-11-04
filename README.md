# 🌍 TravelHub

**TravelHub** is a web platform designed for trip planning, hotel discovery, itinerary management, and sharing travel reviews among users. 

This project is built as an advanced portfolio application demonstrating modern PHP web architecture, async message processing, caching, and containerized deployment.

---

## 🛠 Tech Stack

* **Backend:** PHP 8.2+ / Symfony 6.4+ (WebApp / REST API)
* **Database:** PostgreSQL 15
* **ORM:** Doctrine ORM
* **Caching & Rate Limiting:** Redis
* **Message Broker:** RabbitMQ (Symfony Messenger)
* **Environment & Containerization:** Docker & Docker Compose
* **Web Server:** Nginx

---

## 🏗 Infrastructure Architecture (Docker)

The application runs in an isolated Docker environment with the following services:

| Service | Container Name | Description / Port Mapping                           |
| :--- | :--- |:-----------------------------------------------------|
| **Nginx** | `travel_nginx` | Web Server (Port `8090`)                             |
| **PHP-FPM** | `travel_php` | PHP 8.2 Application Runtime                          |
| **PostgreSQL** | `travel_postgres` | Relational Database (Port `5434` -> `5432`)          |
| **Redis** | `travel_redis` | Caching & Session Storage (Port `6379`)              |
| **RabbitMQ** | `travel_rabbitmq` | AMQP Message Broker (`5672`, Web Management `15672`) |

---

## 🚀 Quick Start (Local Development)

### 1. Prerequisites
Ensure you have the following installed locally:
* [Docker Desktop / Docker Engine](https://www.docker.com/)
* [Git](https://git-scm.com/)

### 2. Clone the Repository
```bash
git clone https://github.com/luts26/travel-hub.git
cd travel-hub
```

### 3. Spin Up Docker Containers
Start all services in detached mode:
```bash
docker compose up -d
```

### 4. Install Dependencies
Run Composer inside the PHP container:
```bash
docker compose exec php composer install
```

### 5. Run Database Migrations
Execute pending Doctrine migrations to prepare PostgreSQL:
```bash
docker compose exec php php bin/console doctrine:migrations:migrate
```

The application will be accessible at: **`http://localhost:8090`**

---

## 📌 Development Roadmap

- [x] Docker environment setup (PHP, Nginx, PostgreSQL, Redis, RabbitMQ)
- [x] Symfony framework initialization
- [x] Integration of Symfony Web Profiler & MakerBundle
- [x] Initial domain entities mapping (`Trip`, `Review`) and database migrations
- [ ] Controller implementation and REST API endpoints
- [ ] Redis caching layer integration
- [ ] Asynchronous task processing (e.g., photo upload optimization via RabbitMQ)
- [ ] Unit & Integration testing suite (PHPUnit)

---

## 📝 License

This project is open-source and created for educational and portfolio demonstration purposes.
