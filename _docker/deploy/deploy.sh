sudo unlink /etc/nginx/sites-enabled/project-proxy
sudo ln /etc/nginx/sites-available/project /etc/nginx/sites-enabled/

eval $(ssh-agent -s)
ssh-add /root/.ssh/project-root-git_rsa
cd /var/www/backend/; sudo -E git pull git@github.com:project/backend.git
cd /var/www/frontend/; sudo -E git pull git@github.com:project/frontend.git
cd /var/www/backend/; sudo -E git switch -C master origin/master
cd /var/www/frontend/; sudo -E git switch -C master origin/master

sudo cp /var/www/package.json /var/www/frontend/package.json
sudo /bin/dd if=/dev/zero of=/var/swap.1 bs=1M count=1024
sudo /sbin/mkswap /var/swap.1
sudo /sbin/swapon /var/swap.1
npm install --prefix /var/www/frontend

cd /var/www/frontend
npx --yes browserslist@latest --update-db
npm run build --prefix /var/www/frontend

export COMPOSER_ALLOW_SUPERUSER=1
composer install --working-dir=/var/www/backend

sudo cp /var/www/.env.website /var/www/backend/.env

sudo mysql -Bse "DROP DATABASE IF EXISTS project; CREATE DATABASE project;"
php /var/www/backend/artisan migrate --force
php /var/www/backend/artisan db:seed --force
php /var/www/backend/artisan db:seed --class=UserSeeder --force
php /var/www/backend/artisan db:seed --class=ReviewSeeder --force
php /var/www/backend/artisan db:seed --class=CourseSeeder --force
php /var/www/backend/artisan db:seed --class=PaymentPlanSeeder --force
php /var/www/backend/artisan db:seed --class=PaymentSeeder --force
php /var/www/backend/artisan db:seed --class=CourseUserSeeder --force
php /var/www/backend/artisan db:seed --class=TagSeeder --force
php /var/www/backend/artisan db:seed --class=TelegramConversationSeeder --force

sudo systemctl reload nginx
