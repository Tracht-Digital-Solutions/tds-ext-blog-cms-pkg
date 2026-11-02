<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Tds\Ext\BlogCms\Support\PostSeeder;

/**
 * Blog-CMS — seed the article on Pi Imager for Android (DE + EN).
 *
 * Facts follow the project's README and validation reports (v0.5.0).
 */
final class BlogCmsSeedPostPiImagerAndroid extends AbstractMigration
{
    public const POSTS = [
        [
            'slug' => 'pi-imager-fuer-android',
            'lang' => 'de',
            'category' => 'Werkzeuge',
            'title' => 'Pi Imager für Android: Raspberry-Pi-Images ohne PC schreiben',
            'excerpt' => 'Eine freie Android-App schreibt Betriebssysteme für den Raspberry Pi über USB-OTG auf SD-Karte oder USB-Stick. Was sie kann, wie sie prüft und was noch fehlt.',
            'meta' => 'Pi Imager für Android schreibt Raspberry-Pi-Images ohne PC und ohne Root über USB-OTG. Funktionen, Voraussetzungen, Prüfung und aktuelle Grenzen der App.',
            'tags' => 'raspberry-pi, android, open-source, usb-otg',
            'published' => '2026-11-02 09:00:00',
            'body' => <<<'MD'
**Kurz gesagt:** Pi Imager für Android ist eine freie App, die Betriebssysteme für den Raspberry Pi ohne PC und ohne Root auf eine SD-Karte im USB-Kartenleser oder auf einen USB-Stick schreibt. Sie braucht Android 8 oder neuer und USB-OTG. Das Schreiben auf physische USB-Geräte ist noch nicht auf echter Hardware bestätigt.

Die App ist ein eigenes Open-Source-Projekt von mir. Sie baut auf dem Raspberry Pi Imager auf, ist aber kein offizielles Produkt der Raspberry Pi Ltd.

## Wofür ist die App gedacht?

Für den Moment, in dem ein Pi neu aufgesetzt werden soll und kein Rechner in der Nähe ist. Das Handy lädt das Betriebssystem, prüft es und schreibt es auf den Datenträger.

## Was kann Pi Imager für Android?

- **Katalog:** Die App liest den offiziellen Betriebssystemkatalog, filtert nach Pi-Modell und durchsucht Namen und Beschreibungen.
- **Eigene Images:** IMG und ISO lassen sich direkt verwenden, auch verpackt als XZ, GZ oder ZIP.
- **Erster Start:** Benutzerkonto, Hostname, Zeitzone, Tastaturlayout, WLAN, SSH und Raspberry Pi Connect lassen sich vorab einstellen.
- **Hintergrund:** Vorbereitung und Schreiben laufen mit Benachrichtigung weiter, wenn die App verlassen wird.
- **Oberfläche:** Deutsch und Englisch, hell und dunkel, für Handy und Tablet.

## Wie schützt die App vor defekten Images?

Katalog-Images brauchen einen gültigen SHA-256-Wert, den die App vor jeder Änderung prüft. Nach dem Schreiben liest sie jeden Sektor zurück und vergleicht ihn. Vor dem Schreiben zeigt ein Löschdialog das Ziel an, und erst die Bestätigung startet den Vorgang.

## Was wird für die Nutzung gebraucht?

1. Ein Gerät mit Android 8 oder neuer und USB-OTG.
2. Ein USB-Kartenleser oder USB-Stick mit SCSI/Bulk-Only-Transport.
3. Genug freier Speicher für das entpackte Image.

Der interne SD-Steckplatz eines Handys eignet sich nicht, weil Android ihn nicht als Rohdatenträger freigibt.

## Was ist noch offen?

Geprüft wurde die App bisher auf Android-Emulatoren, auch mit einem vollständigen offiziellen Raspberry-Pi-OS-Image. Physische Kartenleser, der erste Start auf einem Pi, die Connect-Verknüpfung und TalkBack stehen noch aus. Secure Boot und Fastboot-Provisionierung sind nicht portiert, unterbrochene Downloads lassen sich nicht fortsetzen.

## Häufige Fragen

### Braucht die App Root?

Nein. Sie arbeitet über Androids USB-Host-Schnittstelle und bearbeitet das Image vorab als Datei im privaten App-Speicher.

### Sendet die App Daten an Dritte?

Sie enthält keine Telemetrie und keine Analyse-SDKs. Netzwerkzugriffe gibt es nur für den Katalog und die ausgewählten Images.

### Wo liegen Zugangsdaten?

Die Eingaben bleiben im Arbeitsspeicher der Sitzung. Im vorbereiteten Image können WLAN-Passwörter und Connect-Schlüssel je nach Verfahren im Klartext auf der Bootpartition stehen, bis sie verarbeitet oder gelöscht sind.

### Woher kommt die App?

Die signierte APK und der Quellcode stehen auf [GitHub](https://github.com/jtracht/rpi-imager-android), unter der Lizenz Apache-2.0. Grundlage ist der [Raspberry Pi Imager](https://github.com/raspberrypi/rpi-imager).

Stand: November 2026, Version 0.5.0. Raspberry Pi ist eine Marke der Raspberry Pi Ltd.
MD,
        ],
        [
            'slug' => 'pi-imager-fuer-android',
            'lang' => 'en',
            'category' => 'Tools',
            'title' => 'Pi Imager for Android: write Raspberry Pi images without a PC',
            'excerpt' => 'A free Android app writes Raspberry Pi operating systems to an SD card or USB stick over USB OTG. What it does, how it verifies and what is still missing.',
            'meta' => 'Pi Imager for Android writes Raspberry Pi images over USB OTG, with no PC and no root. Features, requirements, verification and the current limits of the app.',
            'tags' => 'raspberry-pi, android, open-source, usb-otg',
            'published' => '2026-11-02 09:00:00',
            'body' => <<<'MD'
**In short:** Pi Imager for Android is a free app that writes Raspberry Pi operating systems to an SD card in a USB reader or to a USB stick, with no PC and no root. It needs Android 8 or newer and USB OTG. Writing to physical USB devices has not yet been confirmed on real hardware.

The app is an open-source project of my own. It builds on Raspberry Pi Imager but is not an official product of Raspberry Pi Ltd.

## What is the app for?

For the moment a Pi needs a fresh system and no computer is nearby. The phone downloads the operating system, verifies it and writes it to the medium.

## What can Pi Imager for Android do?

- **Catalog:** The app reads the official operating system catalog, filters by Pi model and searches names and descriptions.
- **Own images:** IMG and ISO files work directly, also packed as XZ, GZ or ZIP.
- **First boot:** User account, hostname, time zone, keyboard layout, Wi-Fi, SSH and Raspberry Pi Connect can be set in advance.
- **Background:** Preparation and writing continue with a notification after leaving the app.
- **Interface:** German and English, light and dark, for phone and tablet.

## How does the app guard against broken images?

Catalog images need a valid SHA-256 value, which the app checks before any change. After writing, it reads every sector back and compares it. Before writing, an erase dialog shows the target, and only the confirmation starts the process.

## What is needed to use it?

1. A device with Android 8 or newer and USB OTG.
2. A USB card reader or USB stick with SCSI/Bulk-Only transport.
3. Enough free storage for the unpacked image.

A phone's internal SD slot does not work, because Android does not expose it as a raw device.

## What is still open?

So far the app has been tested on Android emulators, including with a complete official Raspberry Pi OS image. Physical card readers, the first boot on a Pi, linking with Connect and TalkBack are still outstanding. Secure Boot and fastboot provisioning are not ported, and interrupted downloads cannot be resumed.

## Frequently asked questions

### Does the app need root?

No. It works through Android's USB host interface and edits the image beforehand as a file in private app storage.

### Does the app send data to third parties?

It contains no telemetry and no analytics SDKs. Network access is only for the catalog and the selected images.

### Where do credentials go?

Entries stay in the session's memory. In the prepared image, Wi-Fi passwords and Connect keys can be in clear text on the boot partition, depending on the method, until they are processed or deleted.

### Where does the app come from?

The signed APK and the source are on [GitHub](https://github.com/jtracht/rpi-imager-android), under the Apache-2.0 licence. It is based on [Raspberry Pi Imager](https://github.com/raspberrypi/rpi-imager).

As of November 2026, version 0.5.0. Raspberry Pi is a trademark of Raspberry Pi Ltd.
MD,
        ],
    ];

    public function up(): void
    {
        PostSeeder::insert($this->getAdapter()->getConnection(), self::POSTS);
    }

    public function down(): void
    {
        PostSeeder::remove($this->getAdapter()->getConnection(), self::POSTS);
    }
}
