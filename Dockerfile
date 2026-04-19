# Utilise l'image PHP officielle avec Apache
FROM php:8.2-apache

# Copie les fichiers du projet dans le répertoire web d'Apache
COPY . /var/www/html/

# Donne les permissions appropriées
RUN chown -R www-data:www-data /var/www/html/

# Expose le port 80
EXPOSE 80

# Démarre Apache en foreground
CMD ["apache2-foreground"]