# case-php
PHP REST API com MySQL + Docker + Terraform (GCP)

Este projeto demonstra como construir e implantar uma API REST em PHP com MySQL, utilizando Docker, PHPUnit para testes e Terraform para provisionar a infraestrutura no Google Cloud Platform (GCP).

Estrutura do Projeto
src/: Código-fonte PHP com estrutura MVC simples
tests/: Testes unitários com PHPUnit
Dockerfile + docker-compose.yml: Ambiente de desenvolvimento
terraform/: Arquivos de provisionamento de infraestrutura na GCP

Construa os containers:
docker-compose up -d --build

Teste os endpoints com curl:
Cadastrar usuário:
curl -X POST http://localhost:8000/users \
-H "Content-Type: application/json" \
-d '{"name": "Maria", "email": "maria@email.com", "password": "123456"}'

Listar usuários:
curl http://localhost:8000/users

Testes com PHPUnit
Execute os testes dentro do container:
docker-compose exec app vendor/bin/phpunit

Provisionamento no GCP com Terraform
Instale o Terraform e a CLI do GCP na sua máquina.
Autentique-se no GCP:
gcloud auth application-default login


Crie o arquivo terraform.tfvars no mesmo diretório do projeto com o ID do seu projeto GCP:
gcp_project_id = "seu-id-de-projeto-aqui"

Atualize o script install_webserver.sh
GIT_REPO_URL="https://github.com/seu-usuario/seu-repo-php.git"

Execute os comandos do Terraform no terminal:
# Inicializa o Terraform
terraform init

# (Opcional) Visualiza o plano de execução
terraform plan

# Aplica a infraestrutura
terraform apply

Após o terraform apply, o IP público da VM será exibido no terminal. Acesse sua API no navegador ou via curl:
http://<IP-PUBLICO>
