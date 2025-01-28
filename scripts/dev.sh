#!/bin/bash

# Verifica se o Docker está rodando
if ! docker info > /dev/null 2>&1; then
    echo "Docker não está rodando. Por favor, inicie o Docker primeiro."
    exit 1
fi

# Instala dependências se node_modules não existir
if [ ! -d "node_modules" ]; then
    echo "Instalando dependências..."
    npm install
fi

# Inicia os containers
echo "Iniciando ambiente de desenvolvimento..."
docker-compose up --build 