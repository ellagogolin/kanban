# Projekt lokal hochfahren
* Dev-Server starten `symfony server:start` 
* xampp mysql starten

# Projekt aufsetzen
* php8.2 und gängige Pakete über die entsprechende Xampp Version auf Windows installieren
* mysql
* composer installieren
* im Projekt `composer install` um Softwareabhängigkeiten zu installieren
* `php bin/console doctrine:schema:drop --force` um db zu löschen
* DB erstellen `php bin/console doctrine:database:create`
* DB Schema erstellen
  * über Forcierung
    * `php bin/console doctrine:schema:update --force` um db Schema ohne Migrationen zu forcieren (jetzt am Anfang praktisch)
  * über Migration
    * `php bin/console doctrine:migrations:migrate` um Migrationen auszuführen
* `php bin/console doctrine:fixtures:load` um Dummydaten zu laden
* `php bin/console doctrine:migrations:diff` um neue Migration nach ORM Anpassung zu erzeugen
* Assistent für neue Entities `php bin/console make:entity`