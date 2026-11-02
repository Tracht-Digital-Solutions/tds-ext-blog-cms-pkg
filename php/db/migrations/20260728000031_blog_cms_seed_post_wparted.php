<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Tds\Ext\BlogCms\Support\PostSeeder;

/**
 * Blog-CMS — seed the article on WParted (DE + EN).
 *
 * Facts follow WParted's own documentation (0.8.0-beta.1). "GParted for
 * Windows" appears only as a question the article answers, never as a claim.
 */
final class BlogCmsSeedPostWparted extends AbstractMigration
{
    public const POSTS = [
        [
            'slug' => 'wparted-partitionsmanager-fuer-windows-11',
            'lang' => 'de',
            'category' => 'Werkzeuge',
            'title' => 'WParted: freie GParted-Alternative für Windows 11',
            'excerpt' => 'WParted zeigt Datenträger, plant Änderungen an Partitionen und läuft direkt unter Windows 11. Was es heute kann und warum echte Datenträger noch gesperrt sind.',
            'meta' => 'WParted ist ein freier Partitionsmanager für Windows 11 mit GParted-ähnlicher Bedienung. Funktionen, Vergleich und warum echte Datenträger noch gesperrt sind.',
            'tags' => 'wparted, partitionen, windows-11, open-source',
            'published' => '2026-11-02 10:00:00',
            'body' => <<<'MD'
**Kurz gesagt:** WParted ist ein freier Open-Source-Partitionsmanager für Windows 11, der ohne Installation und ohne Linux-Live-System läuft. Es plant Änderungen zuerst und führt sie dann auf Image-Dateien und virtuellen Datenträgern aus. Das Schreiben auf echte Datenträger bleibt bis zu einem menschlichen Sicherheitsreview gesperrt.

WParted entwickle ich als privates Open-Source-Projekt. Es ist unabhängig und nicht mit GParted oder GNOME verbunden.

## Ist WParted ein GParted für Windows?

In der Bedienung ähnlich: Datenträgerkarte, Warteschlange der Operationen und ein eigener Schritt zum Anwenden folgen dem Vorbild. Im Umfang reicht es noch nicht heran, weil echte Datenträger nicht beschrieben werden. Für Arbeiten an echten Datenträgern sind heute die ausgereiften Werkzeuge die richtige Wahl.

## Was kann WParted heute?

- **Ansehen:** Modell, Anschluss, Größe, Sektorgrößen, belegter Platz und BitLocker-Status. 31 Dateisystemtypen werden erkannt.
- **Planen:** Partitionen anlegen, löschen, verschieben, kopieren, formatieren und in der Größe ändern, mit Rückgängig, Sicherheitsprüfungen und speicherbaren Plänen.
- **Anwenden:** auf Image-Dateien, VHD-Dateien fester Größe und angehängten VHD- und VHDX-Datenträgern. Jeder Schritt wird zurückgelesen und geprüft.
- **Größe ändern:** NTFS, exFAT, FAT16/32 und ext2/3/4 wachsen und schrumpfen, btrfs und XFS wachsen.
- **Automatisieren:** Das Kommandozeilenprogramm `wparted-cli` liefert JSON und kennt Probeläufe.

## Warum sind echte Datenträger gesperrt?

Ein Fehler in einem Partitionswerkzeug kann alle Daten eines Datenträgers kosten. Deshalb bleibt das Schreiben gesperrt, bis ein Mensch die Sicherheit geprüft hat. Echte Datenträger lassen sich bereits ansehen und verplanen, die Pläne lassen sich prüfen und speichern. Der Datenträger des laufenden Windows wird grundsätzlich nie beschrieben.

## Wie unterscheidet sich WParted von der Datenträgerverwaltung?

Die Datenträgerverwaltung gehört zu Windows und schreibt auf echte Datenträger. Sie plant aber nicht erst und wendet dann an, kennt kein ext2/3/4 und verschiebt keinen Partitionsanfang. WParted kann das auf Images und virtuellen Datenträgern.

## Welche Version steht zum Download bereit?

Dieser Beitrag beschreibt den Entwicklungsstand 0.8.0-beta.1. Der neueste Download ist derzeit 0.4.0-alpha und kann weniger. Die Programmdateien sind noch nicht signiert, Windows kann deshalb warnen.

## Häufige Fragen

### Was kostet WParted?

Nichts. WParted steht unter der GNU GPL v2.0 oder neuer, der Quellcode liegt auf [GitHub](https://github.com/jtracht/WParted).

### Läuft WParted unter Windows 10?

Das ist weder unterstützt noch getestet. Ziel ist nur Windows 11.

### Sendet WParted Daten?

Nein. Es gibt keine Telemetrie, keine automatische Update-Prüfung und keine Netzwerknutzung.

### Wo gibt es mehr Informationen?

Auf der [WParted-Webseite](https://wparted.tracht-digital.de/) stehen Bilder, Release-Notes und der Download.

Stand: November 2026.
MD,
        ],
        [
            'slug' => 'wparted-partitionsmanager-fuer-windows-11',
            'lang' => 'en',
            'category' => 'Tools',
            'title' => 'WParted: a free GParted alternative for Windows 11',
            'excerpt' => 'WParted shows disks, plans partition changes and runs directly on Windows 11. What it does today and why real disks are still locked.',
            'meta' => 'WParted is a free partition manager for Windows 11 with a GParted-style workflow. Features, comparison and why writing to real disks is still locked for now.',
            'tags' => 'wparted, partitions, windows-11, open-source',
            'published' => '2026-11-02 10:00:00',
            'body' => <<<'MD'
**In short:** WParted is a free, open-source partition manager for Windows 11 that runs with no installation and no Linux live system. It plans changes first and then applies them to image files and virtual disks. Writing to real disks stays locked until a human security review is done.

I develop WParted as a private open-source project. It is independent and not affiliated with GParted or GNOME.

## Is WParted a GParted for Windows?

It works in a similar way: the disk map, the operation queue and a separate apply step follow that model. It does not yet reach the same scope, because real disks are not written. For work on real disks today, the mature tools are the right choice.

## What can WParted do today?

- **Inspect:** model, bus, size, sector sizes, used space and BitLocker state. 31 file system types are detected.
- **Plan:** create, delete, move, copy, format and resize partitions, with undo, safety checks and saved plans.
- **Apply:** to image files, fixed-size VHD files and attached VHD and VHDX disks. Every step is read back and verified.
- **Resize:** NTFS, exFAT, FAT16/32 and ext2/3/4 grow and shrink, btrfs and XFS grow.
- **Automate:** the command line program `wparted-cli` returns JSON and supports dry runs.

## Why are real disks locked?

A bug in a partition tool can cost all the data on a disk. Writing therefore stays locked until a person has reviewed its safety. Real disks can already be inspected and planned for, and those plans can be checked and saved. The disk of the running Windows is never written.

## How does WParted differ from Disk Management?

Disk Management is part of Windows and writes to real disks. But it does not plan first and apply later, does not handle ext2/3/4 and does not move a partition's start. WParted does, on images and virtual disks.

## Which version can be downloaded?

This article describes the development state 0.8.0-beta.1. The newest download is currently 0.4.0-alpha and does less. The binaries are not code-signed yet, so Windows may show a warning.

## Frequently asked questions

### What does WParted cost?

Nothing. WParted is licensed under the GNU GPL v2.0 or later, and the source is on [GitHub](https://github.com/jtracht/WParted).

### Does WParted run on Windows 10?

That is neither supported nor tested. Windows 11 is the only target.

### Does WParted send data?

No. There is no telemetry, no automatic update check and no network use.

### Where can I find more?

The [WParted website](https://wparted.tracht-digital.de/en/) has pictures, release notes and the download.

As of November 2026.
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
