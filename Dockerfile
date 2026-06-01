# =============================================================================
# Dockerfile — PRODUCTION
# =============================================================================

ARG PHP_VERSION=8.4
ARG ALPINE_VERSION=3.23
ARG ROADRUNNER_VERSION=2025.1.7
ARG COMPOSER_VERSION=2

# =============================================================================
# Stage 1: RoadRunner binary
# =============================================================================
FROM ghcr.io/roadrunner-server/roadrunner:${ROADRUNNER_VERSION} AS roadrunner

# =============================================================================
# Stage 2: Composer dependencies
# =============================================================================
FROM composer:${COMPOSER_VERSION} AS vendor

WORKDIR /app

COPY composer.json composer.lock symfony.lock* ./

RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --ignore-platform-reqs

COPY . .

RUN composer dump-autoload \
    --no-dev \
    --classmap-authoritative

# =============================================================================
# Stage 3: Production runtime
# =============================================================================
FROM php:${PHP_VERSION}-cli-alpine${ALPINE_VERSION} AS production

ARG APP_UID=1000
ARG APP_GID=1000
ARG TZ=UTC

LABEL org.opencontainers.image.title="Symfony REST API"

RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN apk add --no-cache \
        ca-certificates \
        curl \
        libpq \
        icu-libs \
        libzip \
        tzdata \
    && ln -snf /usr/share/zoneinfo/${TZ} /etc/localtime \
    && echo "${TZ}" > /etc/timezone \
    && apk del tzdata

COPY --from=mlocati/php-extension-installer:latest /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions \
        opcache \
        pdo_pgsql \
        intl \
        zip \
        bcmath \
        pcntl \
        sockets \
        redis \
    && rm /usr/local/bin/install-php-extensions

COPY docker/php.ini.prod "$PHP_INI_DIR/conf.d/99-app.ini"

COPY --from=roadrunner /usr/bin/rr /usr/local/bin/rr

RUN addgroup -g ${APP_GID} app \
    && adduser -u ${APP_UID} -G app -s /bin/sh -D app

WORKDIR /app

COPY --from=vendor --chown=app:app /app/vendor ./vendor
COPY --chown=app:app . .

RUN mkdir -p var/cache var/log && chown -R app:app var/

USER app

RUN APP_ENV=prod APP_DEBUG=0 php bin/console cache:warmup --no-debug

EXPOSE 8080
EXPOSE 2114

HEALTHCHECK \
    --interval=10s \
    --timeout=5s \
    --start-period=30s \
    --retries=3 \
    CMD curl -sf "http://localhost:2114/health?plugin=http" || exit 1

ENTRYPOINT ["/usr/local/bin/rr"]
CMD ["serve", "-c", ".rr.yaml"]
