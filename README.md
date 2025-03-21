## ASAN-TECH
 Repositorio creado para el desarrollo y la implementación 
 del TFG de Grado Superior de Administración de Sistemas en Red.
 Título:       ASAN-TECH 
 Autores:      Asier García y Andrés Sierra
 
## CONCEPTO GENERALES DEL PROYECTO
La idea de proyecto es montar una empresa que proporciona servicios y soluciones sencillas 
en nube a pequeñas empresas que quieren sumarse a las nuevas tecnologías. 
Se ofrecen servicios de correo privado, Drive, suite Office, facturación y CRM.
Todo el software será OpenSource.

Para la arquitectura de los recursos necesarios vamos a contar con servidores alquilados a clouding,
en los cuales vamos a implementar nuestras tecnologías. 

La idea es tener una máquina que haga de firewall , por donde irá primero el cliente al introducir nuestra pagina web, de ahí queremos redirigir el tráfico a un servidor web con LAMP y PhpMyadmin, donde tendremos alojago nuestro sitio web y nuestra aplicación web para que nuestros clientes puedan contratar servicios o gestionar lo contratado. Luego, queremos tener otro servidor de aplicaciones en donde desplegaremos las aplicaciones con docker gestionado con kubernetes, terraform y ansible. 

Queremos que según el cliente rellene un formulario para contratarnos un servicio, se le cree o levante un contenedor 
con los datos del usuario pasado por variables y el software que quiere contratar. Todo automatizado a través de script y variables.

Además, para no guardar contraseñas vamos a usar un hash para verificar las credenciales de usuarios. 
Protegiéndonos ante fuga de contraseñas.
## Estructura del Proyecto
-----------------------------------------------------
/proyecto
│
├── deploy.sh
├── deploy-ansible.sh
├── deploy-remoto.sh
├── ansible/
│   ├── inventory
│   ├── playbook-main.yml          # Playbook principal
│   ├── 01-configuraciones-comunes.yml
│   ├── 02-instalar-kubernetes.yml
│   ├── 03-configurar-master.yml
│   ├── 04-configurar-workers.yml
│   ├── 05-certificados.yml
│   ├── 06-desplegar-web.yml
│   ├── 07-despliegue-dinamico-servicios.yml
│   └── roles/
│       ├── certificados/
│       ├── common/
│       └── despliegue-dinamico-servicios/
│       ├── despliegue-web/
│       ├── kubernetes/
│       ├── kubernetes-master/
│       └── kubernetes-worker/
├────── group_vars
│       ├── all.yml/
│ 
├── web/
│   ├── index.html
│   ├── style.css
│   ├── abrir_Servicio.php
│   ├── conexion.php
│   ├── cpanel.php
│   ├── login.php
│   ├── regjistro.php
│   └── cerrar_sesion.php
│
└── database/
|    └── init_asantech.sql
├────────────────────────
-----------------------------------------------------
1. Tareas de Configuración Previa. (Más abajo)
    ----------------------
2. Ejecutar el playbook de despliegue desde /mnt/carp_com/ASAN_TECH/zz_Pruebas-Ubuntu
    1. Ejecutar el deploy-remoto en cada equipo remoto que vayamos a gestionar con ansible.
        * Tendremos que cambiar la ip y el hostname dependiendo el equipo.
        chmod +x deploy-remoto.sh
        ./deploy-remoto.sh
    2. Ejecutar el deploy-ansible.
        chmod +x deploy-ansible.sh
        ./deploy-ansible.sh
    3. Ejecutar deploy.sh para generar todo el proyecto con ansible.
        chmod +x deploy.sh
        ./deploy.sh
    ----------------------
3. Una vez desplegado se debería ver la Página Web y los Servicios que se pueden desplegar.
    ----------------------
4. Integración con la Página Web
    Una vez que todo esté configurado, puedes usar el playbook playbook-deploy.yml 
    para desplegar servicios desde la página web.

-------------------------------------------------------------------------
## 1. Configuración Previa ---
*** Se han creado scripts para la configuración previa de los hosts remotos y de ansible,
a continuación se detallan los pasos a seguir en caso de duda.

*** En un paso posterior crearemos el usuario asan, a partir de 
entonces usaremos ese usuario para todo.
    ------------------------------------------------------------------
1. Maquinas virtuales
    - Ansible: 1
    - Master: 1
    - Workers: 1/2
    - Cliente para control
    ------------------------------------------------------------------
2. Configuración de red Fija
- Interfaz NAT:
    red: 10.0.2.0/24
    ip:  10.0.2.15
    Gw:  10.0.2.2
    DNS: 8.8.8.8
- UbServDesk-Servidor1 -> hostname: ansible
    user: Servidor1
    pass: C@ntrasena
    ip: 192.168.1.11
- UbServ-Servidor2 -> hostname: master1
    user: Servidor2
    pass: C@ntrasena
    ip: 192.168.1.12
- UbServ-Servidor3 -> hostname: worker1
    user: Servidor3
    pass: C@ntrasena
    ip: 192.168.1.13
- UbServ-Servidor4 -> hostname: worker2
    user: Servidor4
    pass: C@ntrasena
    ip: 192.168.1.14
- UbDeskt-Cliente1 ->  hostname: cliente1; para controlar por ssh los servers.
    user: Cliente1
    pass: C@ntrasena
    ip: 192.168.1.21
    ------------------------------------------------------------------
3. Configurar DNS
    -- Configurar hostname en cada nodo.
        sudo hostnamectl set-hostname ansible
        sudo hostnamectl set-hostname master1
        sudo hostnamectl set-hostname worker1
        sudo hostnamectl set-hostname worker2
    -- Configurar /etc/hosts en cada nodo, ejemplo del nodo Ansible.
        192.168.1.11 ansible
        192.168.1.12 master1
        192.168.1.13 worker1
        192.168.1.14 worker2
    ------------------------------------------------------------------
4. Crear usuario "asan" en todos los host con permisos sudo
    sudo adduser asan
    sudo usermod -aG sudo asan
    ----------------------
-- Hacer que el usuario escale privilegios. Ejecutar "visudo" y añadir al final.
    asan    ALL=(ALL)       NOPASSWD: ALL
    ------------------------------------------------------------------
5.  Nodo Ansible
    1. Actualizar librerias y dependencias
        sudo apt update && sudo apt upgrade -y
    --------------------------------
    2. Instalar Ansible
        - sudo apt install -y software-properties-common gnupg2 curl
        - curl -fsSL https://apt.releases.hashicorp.com/gpg | sudo gpg --dearmor -o /usr/share/keyrings/hashicorp-archive-keyring.gpg
        - echo "deb [signed-by=/usr/share/keyrings/hashicorp-archive-keyring.gpg] https://apt.releases.hashicorp.com $(lsb_release -cs) main" | sudo tee /etc/apt/sources.list.d/hashicorp.list
        - sudo apt install ansible
        --------------------------------
    3. Instalar y generar las claves SSH
    - Instalar ssh
        apt install openssh-server -y
    - Generar la clave
    * Hacerlo todo con el usuario asan.
        asan@ansible: sudo ssh-keygen -t rsa -b 4096
    - Pulsar "/home/asan/.ssh/id_rsa" para guardar las claves
    - Deja en blanco la contraseña
    - verificar que se han creado:
            ls ~/.ssh/
    - Pasar la clave a los hosts.
        ssh-copy-id -i ~/.ssh/id_rsa.pub asan@master1
        ssh-copy-id -i ~/.ssh/id_rsa.pub asan@worker1
    - Permisos de los archivos
        sudochmod 700 /home/asan/.ssh
        sudo chmod 600 /home/asan/.ssh/id_rsa
        sudo chmod 644 /home/asan/.ssh/id_rsa.pub
        sudo chmod 644 /home/asan/.ssh/authorized_keys
        sudo chown -R asan:asan /home/asan/.ssh
    - Verificar Conexión y Revisar que se han pasado las claves:
        ssh asan@192.168.1.12
        cat ~/.ssh/authorized_keys
    --------------------------------
    4. Crear la base de datos y la web con kubernetes
    -- Configmap
        kubectl apply -f kubernetes/web-configmap.yaml
        kubectl get configmap web-files -o yaml
        kubectl apply -f kubernetes/mysql-secret.yaml
        kubectl apply -f kubernetes/mysql-deployment.yaml
        kubectl apply -f kubernetes/mysql-service.yaml
        kubectl apply -f kubernetes/web-deployment.yaml
        kubectl apply -f kubernetes/web-service.yaml
------------------------------------------------------------------------
----------- Flujo General   ----------
1. Usuario accede a la web asantech.com:
    - Se puede registrar
    - Se puede logear si 

    - En la web, elige un servicio (por ejemplo, Nextcloud o FacturaScript) y hace clic en "Contratar".

2. La aplicación web procesa la solicitud:
    - El backend de la aplicación web (PHP) recibe la elección del usuario.
    - El backend ejecuta un playbook de Ansible para desplegar el servicio en Kubernetes.

3. Ansible despliega el servicio:
    - Ansible usa las variables dinámicas para desplegar el servicio elegido.
    - El servicio se expone a través de un nuevo Ingress (por ejemplo, nextcloud.asan-tech.com o facturascript.asan-tech.com).

4. Usuario accede al servicio:
    - Una vez desplegado, el usuario puede acceder al servicio a través del dominio correspondiente.


**&copy; 2025 [Asier García & Andrés Sierra]**
