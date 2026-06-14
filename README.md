# PODS - Portability and Data Standard

## Descrição

Este repositório contém a implementação da prova de conceito desenvolvida no artigo que propõe o **PODS (Portability and Data Standard)**, um modelo conceitual para portabilidade de dados pessoais entre plataformas de e-commerce em conformidade com os princípios da Lei Geral de Proteção de Dados (LGPD).

A prova de conceito demonstra a exportação e importação de dados pessoais entre as plataformas **OpenCart** e **WooCommerce**, utilizando um formato intermediário padronizado baseado em JSON.

## Estrutura do Projeto

```text

├── oc-download.php
├── oc-upload.php
├── oc-data-generation.php
├── wp-download.php
├── wp-upload.php
└── wp-data-generation.php
```

Os scripts devem ser copiados manualmente para o diretório raiz (*root*) de cada instalação.

* Scripts OpenCart → diretório raiz do OpenCart
* Scripts WooCommerce → diretório raiz do WordPress/WooCommerce

---

## Pré-requisitos

### Software

* PHP 8.0 ou superior
* MySQL 5.7 ou superior
* XAMPP, WAMP ou ambiente equivalente
* Composer
* OpenCart
* WordPress com WooCommerce instalado

### Dependências PHP

Para geração de dados fictícios:

```bash
composer require fakerphp/faker
```

---

## Configuração do Ambiente

### OpenCart

Os scripts utilizam as informações de conexão definidas no arquivo:

```php
config.php
```

Certifique-se de que o arquivo esteja corretamente configurado com:

```php
DB_HOSTNAME
DB_USERNAME
DB_PASSWORD
DB_DATABASE
DB_PREFIX
```

### WooCommerce

Os scripts utilizam as configurações presentes no arquivo:

```php
wp-config.php
```

Verifique se as credenciais de acesso ao banco estão corretas.

---

# Fluxo de Exportação

A exportação corresponde à etapa de obtenção dos dados do usuário e conversão para o formato PODS.

### OpenCart

Executar:

```bash
php oc-download.php
```

O script solicitará o e-mail do usuário:

```text
Enter customer email:
```

Após informar um e-mail válido, será gerado o arquivo:

```text
user_data.json
```

Exemplo:

```json
{
  "D": {},
  "A": [],
  "P": []
}
```

### WooCommerce

Executar:

```bash
php wp-download.php
```

O processo é equivalente ao utilizado no OpenCart.

---

# Fluxo de Importação

A importação corresponde à leitura do arquivo PODS e inserção dos dados na plataforma de destino.

## OpenCart

Copie o arquivo:

```text
user_data.json
```

para o diretório raiz do OpenCart.

Executar:

```bash
php oc-upload.php
```

O script realizará:

1. Leitura do arquivo JSON;
2. Validação da estrutura PODS;
3. Transformação para o modelo OpenCart;
4. Inserção dos registros no banco de dados.

---

## WooCommerce

Copie o arquivo:

```text
user_data.json
```

para o diretório raiz do WordPress.

Executar:

```bash
php wp-upload.php
```

O script realizará:

1. Leitura do arquivo JSON;
2. Validação da estrutura PODS;
3. Transformação para o modelo WooCommerce;
4. Inserção dos registros no banco de dados.

---

# Cenários de Teste

## OpenCart → WooCommerce

1. Executar:

```bash
php oc-download.php
```

2. Informar o e-mail do usuário.

3. Copiar o arquivo gerado:

```text
user_data.json
```

para o diretório WordPress.

4. Executar:

```bash
php wp-upload.php
```

5. Verificar a criação do usuário no WooCommerce.

---

## WooCommerce → OpenCart

1. Executar:

```bash
php wp-download.php
```

2. Informar o e-mail do usuário.

3. Copiar o arquivo:

```text
user_data.json
```

para o diretório OpenCart.

4. Executar:

```bash
php oc-upload.php
```

5. Verificar a criação do usuário no OpenCart.

---

# Geração de Dados de Teste

Para criar usuários fictícios compatíveis com o modelo PODS:

### OpenCart

```bash
php oc-data-generation.php
```

### WooCommerce

```bash
php wp-data-generation.php
```

Será gerado um arquivo:

```text
user_data.json
```

contendo dados sintéticos para testes.

---

# Modelo PODS

O formato intermediário utilizado possui três grupos principais:

### D - Dados Pessoais

```json
"D": {
  "u": "username",
  "n": "Nome Completo",
  "e": "email@email.com",
  "f": "Nome",
  "l": "Sobrenome"
}
```

### A - Endereços

```json
"A": [
  {
    "ti": "billing"
  },
  {
    "ti": "shipping"
  }
]
```

### P - Telefones

```json
"P": [
  {
    "pj": "+5522999999999"
  }
]
```

---

# Limitações

Esta implementação corresponde a uma prova de conceito acadêmica e possui algumas simplificações:

* Nem todos os campos das plataformas são exportados;
* Apenas dados básicos de usuários são considerados;
* Não há sincronização automática entre plataformas;
* Algumas informações são preenchidas com valores padrão durante a importação.

---

# Artigo Relacionado

Este repositório acompanha o artigo que propõe o modelo PODS para interoperabilidade e portabilidade de dados pessoais em plataformas de e-commerce, visando contribuir para a implementação prática do direito à portabilidade previsto pela LGPD.
