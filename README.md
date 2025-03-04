# Support de cours API Spotify: Symfony 7, API Plateform et Easy Admin

## Prérequis

### BIEN LIRE TOUTE LA DOCUMENTATION

Ce projet ce démarre avec Docker

- [Docker](https://docs.docker.com/engine/install/) : Installation de Docker.

## Récupération du projet

Clonez le dépôt Git pour récupérer le projet :

```bash
git clone https://github.com/lidem-admin-github/24-25-prepa-dev-webservice
```

## Configuration du .env

Dupliquez .env.test et renommez le en .env, mettre à jour les différentes infos

```bash
###> symfony/framework-bundle ###
APP_ENV=dev
APP_SECRET=43c1ce41b211a4530da2db720eb5a9f5
###< symfony/framework-bundle ###

###> doctrine/doctrine-bundle ###
DATABASE_URL="mysql://admin:admin@mariadb_spotify:3306/spotify"
###< doctrine/doctrine-bundle ###

###> symfony/messenger ###
MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0
###< symfony/messenger ###

###> symfony/mailer ###
# MAILER_DSN=null://null
###< symfony/mailer ###

###> nelmio/cors-bundle ###
CORS_ALLOW_ORIGIN='*'
###< nelmio/cors-bundle ###
```

## 🚀 Démarrage de Docker

Pour démarrer les conteneurs Docker, exécutez :

```bash
docker-compose up
```

## ⚙️ Configuration du fichier d'alias

1. Ouvrez le fichier de configuration de votre terminal :

```bash
nano ~/.bashrc
```

1. Ajoutez le script suivant pour charger les alias dynamiquement :

```bash
load_aliases() {
  if [ -f "$(pwd)/aliases.sh" ]; then
      . "$(pwd)/aliases.sh"
  fi
}

# Appeler la fonction chaque fois que le répertoire est changé
cd() {
  builtin cd "$@" && load_aliases
}

# Charger les alias au démarrage du shell si le fichier existe dans le répertoire actuel
load_aliases
```

1. Rechargez votre fichier \`.bashrc\` :

```bash
source ~/.bashrc
```

1. Configurez le fichier \`.bash_profile\` (ou \`.profile\`) :

```bash
nano ~/.bash_profile
```

1. Ajoutez cette ligne si elle n'existe pas :

```bash
if [ -f ~/.bashrc ]; then
    source ~/.bashrc
fi
```

1. Rechargez le fichier \`.bash_profile\` :

```bash
source ~/.bash_profile
```

1. Dans le fichier \`aliases.sh\`, redéfinissez les alias comme souhaité.

## 🛠 Technologies utilisées

- ![PHP](https://img.shields.io/badge/PHP-8.x-787CB5?logo=php) PHP 8.x
- ![Symfony](https://img.shields.io/badge/Symfony-7-black?logo=symfony) Symfony 7
- ![API Platform](https://img.shields.io/badge/api-plateform?logo=api-plateform) API Platform
- ![EasyAdmin](https://img.shields.io/badge/easy-admin-bundle?logo=easy-admin-bundle) EasyAdmin
- ![MySQL](https://img.shields.io/badge/MySQL-5.7-4479A1?logo=mysql) MySQL
- ![Composer](https://img.shields.io/badge/Composer-2.x-885630?logo=composer) Composer pour la gestion des dépendances
- ![Node.js](https://img.shields.io/badge/Node.js-20.x-339933?logo=node.js) Node pour la gestion des librairies

## Installation du projet Symfony

Installation des dependances

```bash
ccomposer install
```

Import de la base de données

```bash
db-import
```

## ENJOY :)
