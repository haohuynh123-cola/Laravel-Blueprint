# syntax=docker/dockerfile:1.7
# Image for the laravel-blueprint CLI itself — NOT the Dockerfile generated for
# user projects (that one lives at src/Templates/docker/Dockerfile.stub).
#
# The image bundles PHP 8.3 + the runtime tools blueprint shells out to
# (composer, git, node, npm) so users with no PHP installed can still scaffold
# Laravel projects via:
#
#   docker run --rm -v "$PWD:/work" -w /work \
#     ghcr.io/haohuynh123-cola/laravel-blueprint new my-app
#
# Build arg PHAR_VERSION must match a tag with a published blueprint.phar
# attached to the GitHub Release.

ARG PHP_VERSION=8.3
FROM php:${PHP_VERSION}-cli-alpine

ARG PHAR_VERSION
ARG PHAR_URL=https://github.com/haohuynh123-cola/Laravel-Blueprint/releases/download/${PHAR_VERSION}/blueprint.phar

# Tools blueprint shells out to:
# - git: blueprint git init / commit
# - composer: composer create-project / require
# - node + npm: starter kit asset builds (Inertia, Breeze)
# - bash: nicer interactive prompts than ash
RUN apk add --no-cache \
        bash \
        git \
        nodejs \
        npm \
    && curl -fsSL https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && rm -f /tmp/installer.sig

# Pull the matching phar from the GitHub Release.
RUN curl -fsSL -o /usr/local/bin/blueprint "${PHAR_URL}" \
    && chmod +x /usr/local/bin/blueprint \
    && blueprint --version

WORKDIR /work

ENTRYPOINT ["blueprint"]
CMD ["new"]
