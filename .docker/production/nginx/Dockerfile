# Stage 1: Build versioned frontend assets
FROM node:22-alpine AS builder

WORKDIR /var/www

COPY package.json pnpm-lock.yaml ./

RUN corepack enable \
    && corepack prepare pnpm@9.15.9 --activate \
    && pnpm install --frozen-lockfile

COPY . /var/www

RUN pnpm build

# Stage 2: Nginx production image
FROM nginx:alpine

# Copy custom Nginx configuration
# -----------------------------------------------------------
# Replace the default Nginx configuration with our custom one
# that is optimized for serving a Laravel application.
# -----------------------------------------------------------
COPY ./.docker/production/nginx/nginx.conf /etc/nginx/nginx.conf

# Copy Laravel's public files, including the Vite build generated above.
COPY --from=builder /var/www/public /var/www/public

# Set the working directory to the public folder
WORKDIR /var/www/public

# TLS is terminated by Coolify's reverse proxy.
EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
