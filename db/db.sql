CREATE DATABASE isiquizy_db;
CREATE USER 'admin_wp'@'198.211.105.197' IDENTIFIED BY '2025.Software.Developer';
GRANT ALL PRIVILEGES ON isiquizy_db.* TO 'admin_wp'@'198.211.105.197';
FLUSH PRIVILEGES;
EXIT;

sudo mysql -u root -p isiquizy_db - /var/www/html/bd/sistema_bd.sql
sudo mysql -u root -p isiquizy_db

sudo mysql -u root -p sistema_bd < /var/www/html/wordpress-foro/db/local.sql

UPDATE wp_options SET option_value = '198.211.105.197/' WHERE option_name = 'siteurl';
UPDATE wp_options SET option_value = '198.211.105.197/' WHERE option_name = 'home';
UPDATE wp_posts SET post_content = REPLACE(post_content, 'http://198.211.105.197/wordpress-foro/', '198.211.105.197/');
UPDATE wp_posts SET guid = REPLACE(guid, 'http://198.211.105.197/wordpress-foro/', '198.211.105.197/');
UPDATE wp_postmeta SET meta_value = REPLACE(meta_value, 'http://198.211.105.197/wordpress-foro/', '198.211.105.197/');
EXIT;