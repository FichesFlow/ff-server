# FicheFlow

## Installer les librairies

<pre>composer i</pre>

## Démarrer le docker

<pre>docker compose build --pull --no-cache
docker compose up --wait
</pre>

## Arrêter le docker
<pre>docker compose down --remove-orphans</pre>

## Initialiser les migrations

<pre>docker exec -it id_container_php sh
php bin/console doctrine:migrations:migrate
</pre>
