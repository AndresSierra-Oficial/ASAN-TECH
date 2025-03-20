#!/bin/bash
# Archivo pensado para la configuracion inicial del servidor
# con Ansible.
# ----  Configuración básica ----
# Hostname
echo "ansible" > /etc/hostname
#sudo hostnamectl set-hostname ansible

# DNS /etc/hosts
echo "192.168.1.11 ansible" >> /etc/hosts
echo "192.168.1.12 master1" >> /etc/hosts
echo "192.168.1.13 worker1" >> /etc/hosts
#echo "192.168.1.14 worker2" >> /etc/hosts

# Crear usuario asan
useradd -m -s /bin/bash asan
echo "asan:asan" | chpasswd
usermod -aG sudo asan

# Escalar privilegios sin contraseña
echo "asan ALL=(ALL) NOPASSWD: ALL" > /etc/sudoers.d/asan

# ----  Configuración de Red ----
# Configuración de red
echo "Configurando la red..."
cat <<EOF > /etc/netplan/00-installer-config.yaml
network:
    ethernets:
        enp0s3:
            addresses:
            - 10.0.2.15/24
            nameservers:
                addresses:
                - 8.8.8.8
                search: 
                - 8.8.8.8
            routes:
            -   to: default
                via: 10.0.2.2
        enp0s8:
            addresses:
            - 192.168.1.11/24
            nameservers:
                addresses:
                - 8.8.8.8
                search: 
                - 8.8.8.8
    version: 2
EOF

sudo netplan try
sudo netplan apply

# ----  Instalación de Paquetes ----
# Actualizar el sistema
sudo apt update && sudo apt upgrade -y

#Instalar Ansible
sudo apt install -y software-properties-common gnupg2 curl openssh-server
curl -fsSL https://apt.releases.hashicorp.com/gpg | sudo gpg --dearmor -o /usr/share/keyrings/hashicorp-archive-keyring.gpg
echo "deb [signed-by=/usr/share/keyrings/hashicorp-archive-keyring.gpg] https://apt.releases.hashicorp.com $(lsb_release -cs) main" | sudo tee /etc/apt/sources.list.d/hashicorp.list
sudo apt install ansible -y

# --- Configuración de permisos ---
echo "Configurando permisos de los archivos..."

# Playbooks de Ansible
sudo chmod 644 ansible/*.yml

# Scripts de Shell
sudo chmod 755 *.sh

# Archivos YAML de Kubernetes
sudo chmod 644 kubernetes/*.yaml

# Archivos de la página web
sudo chmod 644 web/*

# Archivos de configuración
sudo chmod 644 ansible/inventory

# Archivos de base de datos
sudo chmod 644 database/init.sql

# Directorios
sudo chmod 755 ansible/ kubernetes/ web/ database/

echo "Permisos configurados correctamente."

# Generar claves SSH (si no existen)
if [ ! -f /home/asan/.ssh/id_rsa ]; then
  echo "Generando claves SSH..."
  ssh-keygen -t rsa -b 4096 -N "" -f /home/asan/.ssh/id_rsa
else
  echo "Las claves SSH ya existen."
fi

# Permisos de los archivos
sudo chmod 700 /home/asan/.ssh
sudo chmod 600 /home/asan/.ssh/id_rsa
sudo chmod 644 /home/asan/.ssh/id_rsa.pub
sudo chmod 644 /home/asan/.ssh/authorized_keys
sudo chown -R asan:asan /home/asan/.ssh

# Copiar la clave pública a los hosts
echo "Copiando la clave pública a los hosts..."
ssh-copy-id -i /home/asan/.ssh/id_rsa.pub asan@master1  # Master1
ssh-copy-id -i /home/asan/.ssh/id_rsa.pub asan@worker1  # Worker1
#ssh-copy-id -i /home/asan/.ssh/id_rsa.pub asan@192.168.1.14  # Worker2

# Instalar colecciones de Ansible para kubernetes
ansible-galaxy collection install kubernetes.core