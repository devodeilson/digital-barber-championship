#!/bin/bash

# Roda os testes
echo "Rodando testes..."
npm run test

# Se os testes passarem, faz o build
if [ $? -eq 0 ]; then
    echo "Testes passaram! Iniciando build..."
    npm run build
    
    # Se o build for bem sucedido
    if [ $? -eq 0 ]; then
        echo "Build completado com sucesso!"
        echo "Você pode agora iniciar o servidor com: npm start"
    else
        echo "Erro no build!"
        exit 1
    fi
else
    echo "Testes falharam! Corrija os erros antes de fazer o build."
    exit 1
fi 