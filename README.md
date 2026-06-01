# Enterprise DDD Application

High-performance, strictly typed PHP application built with Domain-Driven Design (DDD), CQRS, and Hexagonal
Architecture.

## 🚀 Tech Stack

* **Runtime:** [RoadRunner](https://roadrunner.dev/) (Stateless execution)
* **Language:** PHP 8.4
* **Framework:** Symfony 8.x
* **Database:** PostgreSQL 18.x
* **Cache/Queue:** Redis 8.x
* **Task Runner:** [Go Task](https://taskfile.dev/)

---

## 🏗 Architecture Principles

This project strictly adheres to **Domain-Driven Design** and **Hexagonal Architecture (Ports & Adapters)**.

1. **Domain Layer (`src/{Context}/Domain`)**: The absolute core. No framework dependencies (`symfony/*`, `doctrine/*`
   are forbidden). Pure PHP 8.4. Entities, Value Objects, and Domain Exceptions.
2. **Application Layer (`src/{Context}/Application`)**: Orchestrates use cases. Implements CQRS (Command/Query
   Responsibility Segregation). Uses DTOs.
3. **Infrastructure Layer (`src/{Context}/Infrastructure`)**: Database adapters (Doctrine ORM), External APIs, Redis
   caching.
4. **Presentation/Delivery (`src/Controller`)**: Thin Single-Action Controllers (`__invoke`).

> ⚠️ **Note on State:** The application runs on RoadRunner. Services MUST be stateless. Memory leaks are unacceptable.
> Do not use global state or static properties for request-specific data.

---

## 🛠 Getting Started

### Prerequisites

* Docker & Docker Compose
* [Task](https://taskfile.dev/installation/) installed globally.

### Installation

1. Clone the repository and initialize the project:

```bash
task install

```

This command will build the Docker image, start containers, install Composer dependencies, and run database migrations.

The application is now running at `http://localhost:8080`.

---

## 💻 Useful Commands (Taskfile)

We use Taskfile instead of Makefile. Run `task` to see all available commands.

### Docker & Environment

| Command                 | Description                        |
|-------------------------|------------------------------------|
| `task up` / `task down` | Start/Stop containers              |
| `task logs`             | View container logs                |
| `task shell`            | Open bash inside the PHP container |

### RoadRunner

| Command           | Description                                               |
|-------------------|-----------------------------------------------------------|
| `task rr:reset`   | Reload RoadRunner workers gracefully (Apply code changes) |
| `task rr:workers` | Show worker stats and memory usage                        |

### Quality Assurance

Code quality is strictly enforced via Level 9 PHPStan and PHP-CS-Fixer.

* `task lint:cs` - Check code style
* `task lint:cs:fix` - Auto-fix code style
* `task lint:stan` - Run PHPStan static analysis
* `task lint` - Run all checks

### Testing

* `task test` - Run all test suites
* `task test:unit` - Run only Domain/Application unit tests
* `task test:integration` - Run infrastructure tests against the test database

---

## 🔒 Code Standards

* **Strict Types:** `declare(strict_types=1);` is mandatory.
* **Immutability:** Prefer `readonly class` for DTOs and Value Objects.
* **No mixed:** The `mixed` type is strictly forbidden in Domain and Application layers.
* **Asymmetric Visibility:** Use PHP 8.4 `public private(set)` for Entity state mutations instead of generic setters.

