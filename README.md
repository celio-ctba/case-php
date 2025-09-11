
# case-php ![](https://github.githubassets.com/images/icons/emoji/unicode/1f418.png?v8)
## PHP REST API com MySQL + Docker + Terraform + (GCP) + Pipeline com Github Actions


# Para usuários:
### 1. Clonar o repositório
    git clone https://github.com/celio-ctba/case-php.git
    cd case-php
### 2. Subir os containers com Docker Compose
Certifique-se de que você tem o Docker e o Docker Compose instalados. Depois, execute:
```
docker-compose up -d
```
Isso irá iniciar os serviços da aplicação PHP, banco de dados MySQL, Prometheus e Grafana
### 3. Testar a API
Você pode testar os endpoints com `curl`:
➕ Cadastrar usuário

    curl -X POST http://localhost:8000/users \
      -H "Content-Type: application/json" \
      -d '{"name": "Maria", "email": "maria@email.com", "password": "123456"}'

Listar usuários

    curl http://localhost:8000/users

### 4. Acessar os serviços de monitoramento
**Prometheus**: http://localhost:9090
**Grafana**: http://localhost:3000





# Para a empresa solicitante, com todos os detalhes 

Este projeto demonstra como construir, implantar e automatizar o deploy de uma API REST em PHP com MySQL, utilizando Docker, PHPUnit para testes e Terraform para provisionar a infraestrutura no Google Cloud Platform (GCP).

### 📦 Tecnologias Utilizadas

-   PHP + Docker
    
-   Docker Compose
    
-   GitHub Actions
    
-   Terraform
    
-   Google Cloud Platform (GCP)
    
-   Artifact Registry
    
-   SSH Deploy
    
-   PHPUnit

Estrutura de Diretórios:
```
php-api-deploy-gcp/
├── .github/
│   └── workflows/
│       └── ci-cd-pipeline.yml         # Pipeline completo do GitHub Actions
├── terraform/
│   ├── main.tf                        # Infraestrutura da VM e rede no GCP
│   └── variables.tf                   # Variáveis reutilizáveis
├── docker-compose.yml                # Arquivo de orquestração dos containers
├── README.md                         # Documentação principal do projeto
└── src/                              # Código-fonte da aplicação PHP
```

-   **Terraform/**: Contém toda a infraestrutura como código, incluindo:
    
    -   `main.tf`: Criação da VM, rede e configuração SSH.
        
    -   `variables.tf`: Variáveis parametrizadas para facilitar ajustes.
        
    -   `Dockerfile`: Define a imagem da aplicação PHP.
        
    -   `docker-compose.yml`: Gerencia os serviços da aplicação e banco de dados.
        
-   **.github/workflows/**: Pipeline automatizado que executa testes, build e deploy.
    
-   **src/**: Código-fonte PHP com estrutura MVC simple.
-   **tests/**:  Testes unitários com PHPUnit
- `.github/workflows/`: Pipeline CI/CD
- `terraform/`: Infraestrutura como código
- `docker-compose.yml`: Orquestração dos containers
- `src/`: Código da aplicação PHP
  
---

## ⚙️ Pipeline CI/CD

O pipeline realiza três etapas:

1. **Testes**: Executa PHPUnit via Docker Compose.
2. **Build**: Constrói e envia a imagem Docker para o Artifact Registry.
3. **Deploy**: Aplica a infraestrutura com Terraform e atualiza a aplicação via SSH na VM.
   
   Conecte-se a VM via SSH e execute:
     ```bash
    docker-compose pull
    docker-compose up -d

## 🛠️ Pré-requisitos

- Conta no GCP com APIs ativadas
- Repositório no Artifact Registry
- Chave SSH configurada na VM
- Conta de serviço com permissões adequadas
- Segredos configurados no GitHub

### ✅ No GCP

-   Ativar APIs:
    
    -   `Artifact Registry API`
        
    -   `Cloud Resource Manager API`
        
-   Criar repositório no Artifact Registry:
    
    -   Nome: `php-api-repo`
        
    -   Formato: `Docker`
        
    -   Região: `us-central1`
        
-   Criar chave SSH:
```
ssh-keygen -t rsa -b 4096 -C "github-actions"
```

-   Adicionar no main.tf :
```hcl
resource "google_compute_instance" "web_server" {
  metadata = {
    ssh-keys = "github-actions:${file("~/.ssh/gcp_ssh_key.pub")}"
  }
}
```

-   Criar conta de serviço `github-actions-deployer` com papéis:
    
    -   `Artifact Registry Writer`
        
    -   `Compute Admin`
        
    -   `Service Account User`
        
-   Gerar chave JSON da conta de serviço

### ✅ No GitHub

Configurar os seguintes segredos em `Settings > Secrets and variables > Actions`:
|Nome|Valor|
|--|--|
| `GCP_PROJECT_ID` | ID do projeto GCP |
| `GCP_SA_KEY` | Conteúdo do arquivo JSON da conta de serviço |
|`GCP_SSH_PRIVATE_KEY` |Chave SSH privada (`gcp_ssh_key`) |
|`GCP_SSH_HOST` |IP da VM no GCP |
|`GCP_SSH_USER` |Usuário SSH (ex: `user_test`) |

###  Atualizar o `docker-compose.yml`
Substitua a imagem local pela do Artifact Registry:

```yaml
version: '3.8'

services:
  app:
    image: us-central1-docker.pkg.dev/${GCP_PROJECT_ID}/php-api-repo/php-api:latest
    container_name: php-api-app
    restart: unless-stopped
    depends_on:
      - db
    ports:
      - "80:80"
    environment:
      - DB_HOST=db
      - DB_DATABASE=app_db
      - DB_USER=user
      - DB_PASSWORD=password

  db:
    image: mysql:8.0
    container_name: mysql-db
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: app_db
      MYSQL_USER: user
      MYSQL_PASSWORD: password
      MYSQL_ROOT_PASSWORD: rootpassword
    volumes:
      - db_data:/var/lib/mysql

volumes:
  db_data:
```
Substitua `${GCP_PROJECT_ID}` pelo ID real do projeto ou use variáveis de ambiente.

---
## Pipeline do GitHub Actions  
Crie o diretório `.github/workflows/` e o arquivo `ci-cd-pipeline.yml`:
```yaml
name: Deploy PHP App to GCP

on:
  push:
    branches:
      - main

env:
  GCP_PROJECT_ID: ${{ secrets.GCP_PROJECT_ID }}
  GCP_REGION: us-central1
  GAR_LOCATION: us-central1
  IMAGE_NAME: php-api
  REPO_NAME: php-api-repo

jobs:
  test:
    name: Run Unit Tests
    runs-on: ubuntu-latest
    steps:
      - name: Checkout code
        uses: actions/checkout@v3

      - name: Start containers
        run: docker-compose up -d

      - name: Run Composer Install
        run: docker-compose exec app composer install

      - name: Run PHPUnit tests
        run: docker-compose exec app vendor/bin/phpunit

  build:
    name: Build and Push Docker Image
    runs-on: ubuntu-latest
    needs: test
    permissions:
      contents: 'read'
      id-token: 'write'

    steps:
      - name: Checkout code
        uses: actions/checkout@v3

      - name: Authenticate to Google Cloud
        uses: google-github-actions/auth@v1
        with:
          credentials_json: '${{ secrets.GCP_SA_KEY }}'

      - name: Configure Docker for Artifact Registry
        run: gcloud auth configure-docker ${{ env.GAR_LOCATION }}-docker.pkg.dev

      - name: Build Docker image
        run: docker build -t ${{ env.IMAGE_NAME }} .

      - name: Tag Docker image
        run: docker tag ${{ env.IMAGE_NAME }} ${{ env.GAR_LOCATION }}-docker.pkg.dev/${{ env.GCP_PROJECT_ID }}/${{ env.REPO_NAME }}/${{ env.IMAGE_NAME }}:latest

      - name: Push Docker image to Artifact Registry
        run: docker push ${{ env.GAR_LOCATION }}-docker.pkg.dev/${{ env.GCP_PROJECT_ID }}/${{ env.REPO_NAME }}/${{ env.IMAGE_NAME }}:latest

  deploy:
    name: Deploy to GCP
    runs-on: ubuntu-latest
    needs: build

    steps:
      - name: Checkout code
        uses: actions/checkout@v3

      - name: Authenticate to Google Cloud
        uses: google-github-actions/auth@v1
        with:
          credentials_json: '${{ secrets.GCP_SA_KEY }}'

      - name: Set up Terraform
        uses: hashicorp/setup-terraform@v2

      - name: Terraform Init
        run: terraform init
        working-directory: ./terraform

      - name: Terraform Apply
        run: terraform apply -auto-approve
        working-directory: ./terraform

      - name: SSH and Deploy
        uses: appleboy/ssh-action@master
        with:
          host: ${{ secrets.GCP_SSH_HOST }}
          username: ${{ secrets.GCP_SSH_USER }}
          key: ${{ secrets.GCP_SSH_PRIVATE_KEY }}
          script: |
            cd /app
            sudo docker-compose pull
            sudo docker-compose up -d
```


### ![](https://github.githubassets.com/images/icons/emoji/unicode/1f680.png?v8) Deploy

Após configurar tudo, basta fazer um `git push` para a branch `main` e o GitHub Actions cuidará do resto.

---

### ![](https://github.githubassets.com/images/icons/emoji/unicode/1f9ea.png?v8) Testando a Aplicação 

*Cadastrar usuário:* 

     curl -X POST http://localhost:8000/users -H "Content-Type: application/json" -d '{"name": "Maria", "email": "maria@email.com", "password": "123456"}'


*Listar usuários:*
 

    curl http://localhost:8000/users
---
# ![](https://github.githubassets.com/images/icons/emoji/unicode/1f928.png?v8) Observabilidade 
Acesse os serviços:  
* Prometheus: [acesse](http://localhost:9090) (http://localhost:9090) (Vá até "Status" > "Targets" para ver se ele está coletando da sua API).  
* Grafana: [acesse](http://localhost:3000) (http://localhost:3000) (Login inicial: admin / admin). O dashboard já estará lá.

---
# Kubernetes
Para simplificar, vou colocar todos os recursos em um único arquivo chamado deployment.yaml. Na prática, você poderia dividi-los em arquivos separados  
(ex: mysql.yaml, php-app.yaml).  
  
Lembre-se: Estes arquivos assumem que você já tem uma imagem Docker da sua aplicação PHP enviada para um registro de contêiner (como o Google Artifact  
Registry que configuramos no pipeline).  

Como Usar  
  
1. Substitua os placeholders:  
* No Deployment da aplicação PHP, altere a linha image: para apontar para a sua imagem no Google Artifact Registry.  
* (Opcional) Altere as senhas no Secret.  
2. Conecte-se ao seu Cluster: Use a CLI para se conectar ao seu cluster Kubernetes (ex: gcloud container clusters get-credentials seu-cluster --zone  
sua-zona).  
3. Aplique os arquivos: Execute o comando no seu terminal, no mesmo diretório onde você salvou o arquivo deployment.yaml:  

```kubectl apply -f deployment.yaml```
  
Após alguns instantes, o Kubernetes irá provisionar todos esses recursos. Para descobrir o IP público da sua aplicação, você pode rodar:  
  

    kubectl get service php-app-service

  
  
Ele mostrará um EXTERNAL-IP assim que o Load Balancer for criado pelo seu provedor de nuvem.  

`deployment.yaml`

```yaml
# --- Secret do MySQL ---
# Armazena as senhas de forma segura, em vez de deixá-las visíveis no YAML.
apiVersion: v1
kind: Secret
metadata:
  name: mysql-secret
stringData:
  # ATENÇÃO: Altere estas senhas para valores seguros em produção.
  MYSQL_ROOT_PASSWORD: "rootpassword"
  MYSQL_PASSWORD: "password"

---

# --- Armazenamento Persistente para o MySQL ---
# Solicita um disco persistente do provedor de nuvem para que os dados do banco
# não sejam perdidos se o pod do MySQL reiniciar.
apiVersion: v1
kind: PersistentVolumeClaim
metadata:
  name: mysql-pvc
spec:
  accessModes:
    - ReadWriteOnce # O volume pode ser montado como leitura/escrita por um único nó.
  resources:
    requests:
      storage: 1Gi # Solicita um disco de 1 Gigabyte.

---

# --- Serviço Interno do MySQL ---
# Cria um "nome de host" interno e fixo (mysql-service) para que a aplicação PHP
# possa encontrar o banco de dados dentro do cluster.
apiVersion: v1
kind: Service
metadata:
  name: mysql-service
spec:
  ports:
    - port: 3306
  selector:
    app: mysql
  clusterIP: None # Necessário para StatefulSets.

---

# --- StatefulSet do MySQL ---
# Gerencia o pod do banco de dados, garantindo que ele tenha uma identidade
# de rede e armazenamento estáveis.
apiVersion: apps/v1
kind: StatefulSet
metadata:
  name: mysql-statefulset
spec:
  selector:
    matchLabels:
      app: mysql
  serviceName: "mysql-service"
  replicas: 1
  template:
    metadata:
      labels:
        app: mysql
    spec:
      containers:
        - name: mysql
          image: mysql:8.0
          ports:
            - containerPort: 3306
          env:
            # Usa os valores do Secret criado acima.
            - name: MYSQL_ROOT_PASSWORD
              valueFrom:
                secretKeyRef:
                  name: mysql-secret
                  key: MYSQL_ROOT_PASSWORD
            - name: MYSQL_DATABASE
              value: "app_db"
            - name: MYSQL_USER
              value: "user"
            - name: MYSQL_PASSWORD
              valueFrom:
                secretKeyRef:
                  name: mysql-secret
                  key: MYSQL_PASSWORD
          volumeMounts:
            # Monta o disco persistente no diretório de dados do MySQL.
            - name: mysql-persistent-storage
              mountPath: /var/lib/mysql
      volumes:
        - name: mysql-persistent-storage
          persistentVolumeClaim:
            claimName: mysql-pvc

---

# --- Deployment da Aplicação PHP ---
# Gerencia os pods da sua aplicação PHP.
apiVersion: apps/v1
kind: Deployment
metadata:
  name: php-app-deployment
spec:
  replicas: 2 # Inicia com 2 cópias da sua aplicação para redundância.
  selector:
    matchLabels:
      app: php
  template:
    metadata:
      labels:
        app: php
    spec:
      containers:
        - name: php-app
          # ATENÇÃO: Substitua pela URL da sua imagem no Artifact Registry.
          image: us-central1-docker.pkg.dev/SEU-PROJETO-GCP/php-api-repo/php-api:latest
          ports:
            - containerPort: 80
          env:
            # Configura a aplicação para se conectar ao serviço do MySQL.
            - name: DB_HOST
              value: "mysql-service"
            - name: DB_DATABASE
              value: "app_db"
            - name: DB_USER
              value: "user"
            - name: DB_PASSWORD
              valueFrom:
                secretKeyRef:
                  name: mysql-secret
                  key: MYSQL_PASSWORD

---

# --- Serviço Externo da Aplicação PHP ---
# Expõe sua aplicação para a internet, criando um Load Balancer na nuvem.
apiVersion: v1
kind: Service
metadata:
  name: php-app-service
spec:
  type: LoadBalancer # Cria um IP público e um Load Balancer.
  ports:
    - port: 80
      targetPort: 80
  selector:
    app: php
```
