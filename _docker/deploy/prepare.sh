sudo mkdir /var/www/backend
sudo mkdir /var/www/frontend

eval $(ssh-agent -s)
ssh-add /root/.ssh/project-root-git_rsa
sudo -E git clone git@github.com:project/backend.git /var/www/backend
sudo -E git clone git@github.com:project/frontend.git /var/www/backend

sudo mkdir /var/www/frontend/node-modules
sudo chmod -R 777 /var/www/backend
sudo chmod -R 777 /var/www/frontend