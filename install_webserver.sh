#!/bin/bash
set -e

# Atualiza os pacotes e instala as dependências
sudo apt-get update
sudo apt-get install -y docker.io docker-compose git

# Substitua pela URL do seu repositório
GIT_REPO_URL="https://github.com/seu-usuario/seu-repo-php.git"

# Clona o repositório
git clone "${GIT_REPO_URL}" /app

# Navega para o diretório da aplicação
cd /app

# Constrói as imagens e inicia os contêineres em background
sudo docker-compose up -d --build
