#!/bin/bash

# --- Verificación de conectividad SSH ---
echo "Verificando conexión SSH a los hosts..."
ansible all -i ansible/inventory -m ping

if [ $? -ne 0 ]; then
  echo "Error: No se pudo conectar a todos los hosts por SSH."
  exit 1
fi

echo "Conexión SSH verificada correctamente."

# --- Ejecución del playbook principal ---
echo "Ejecutando el playbook de despliegue..."
ansible-playbook -i ansible/inventory ansible/playbook-main.yml

if [ $? -ne 0 ]; then
  echo "Error: Fallo durante la ejecución del playbook."
  exit 1
fi

echo "Playbook ejecutado correctamente."

