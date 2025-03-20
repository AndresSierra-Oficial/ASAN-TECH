#!/bin/bash
# Archivo pensado para la configuracion inicial de los 
# servidores remotos que ansible va a gestionar.
#
# Antes de ejecutarlo habrá que cambiar el hostname y la dirección IP
# dependiendo del servidor que estemos configurando.
#
# ----  Configuración básica ----
# Hostname
echo "master1" > /etc/hostname
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
            - 192.168.1.12/24
            nameservers:
                addresses:
                - 8.8.8.8
                search: 
                - 8.8.8.8
    version: 2
EOF 

sudo netplan try
sudo netplan apply