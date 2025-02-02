FROM tkwonn/imgdock-prod:php-latest as builder

FROM nginx:alpine

COPY ./services/prod/nginx/default.conf /etc/nginx/conf.d/default.conf

COPY --from=builder /var/www/imgdock/public /var/www/imgdock/public