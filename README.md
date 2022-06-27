# Advolux Metadata

Mit diesem Kommandozeilenprogramm lassen sich XML-Dateien zu PDF-Dateien generieren, die zur automatisierten Zuordnung von Scans zu Akten erforderlich ist.

Weitere Informationen dazu finden sich unter: https://confluence.haufe.io/display/AD/Import+von+Dokumenten+aus+Scanverzeichnis+mit+Verarbeitung+von+Metadaten

## Systemvoraussetzungen

* PHP 8.0 oder höher
* PHP-Erweiterungen: `php-xml` und `php-xmlwriter`
* composer
* poppler-utils

## Installation

Im Folgenden eine Installation auf einem Debian/Ubuntu-System als Beispiel.

Repository klonen und Abhängigkeiten installieren:

```
sudo apt-get install php8.1-fpm php8.1-xml php8.1-xmlwriter poppler-utils
curl -sS https://getcomposer.org/installer -o composer-setup.php
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer

git clone https://github.com/rocramer/advolux-metadata.git
cd advolux-metadata
composer install
```
