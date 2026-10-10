<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Tds\Ext\BlogCms\Support\PostSeeder;

/**
 * Blog-CMS — seed the "3-2-1 backups" guide (DE + EN).
 *
 * Product lines `{{produkt:<slug>}}` use the shop slug of the row's language;
 * see `20260728000012_blog_cms_seed_post_office_wifi.php`.
 */
final class BlogCmsSeedPostBackup321 extends AbstractMigration
{
    public const POSTS = [
        [
            'slug' => 'datensicherung-3-2-1-regel-kleine-betriebe',
            'lang' => 'de',
            'category' => 'IT-Sicherheit',
            'title' => 'Datensicherung mit der 3-2-1-Regel: So schützen kleine Betriebe ihre Daten',
            'excerpt' => 'Festplatte kaputt, Laptop gestohlen, Verschlüsselungstrojaner: Mit der 3-2-1-Regel überstehen Ihre Daten alle drei. So setzen Sie sie mit wenig Aufwand um.',
            'meta' => 'Die 3-2-1-Backup-Regel einfach erklärt: drei Kopien, zwei Medien, eine außer Haus. Mit NAS, externer SSD und Checkliste für kleine Betriebe und Praxen.',
            'tags' => 'backup, datensicherung, nas, it-sicherheit',
            'published' => '2026-08-27 09:00:00',
            'body' => <<<'MD'
**Kurz gesagt:** Die 3-2-1-Regel heißt: drei Kopien Ihrer Daten, auf zwei verschiedenen Medien, eine davon außer Haus. Für kleine Betriebe reicht dafür ein NAS im Büro, eine externe SSD im Wechsel und ein Cloud-Ziel. Entscheidend ist, dass die Sicherung automatisch läuft und regelmäßig getestet wird.

Die Produkte in diesem Beitrag sind Empfehlungen aus der Praxis. Die Links sind Partnerlinks: Kaufen Sie darüber, erhalte ich eine kleine Provision, der Preis ändert sich für Sie nicht.

## Was bedeutet die 3-2-1-Regel genau?

- **3 Kopien:** das Original und zwei Sicherungen.
- **2 Medien:** zum Beispiel ein NAS und eine externe Festplatte. Fällt ein Gerätetyp aus, bleibt der andere.
- **1 außer Haus:** eine Kopie liegt woanders, etwa zu Hause oder in einer Cloud in Deutschland. Brand, Wasser oder Einbruch treffen dann nicht alle Kopien.

Das [BSI](https://www.bsi.bund.de/) empfiehlt dieses Vorgehen ausdrücklich auch für kleine Unternehmen.

## Warum ist ein Cloud-Ordner allein kein Backup?

Ein synchronisierter Ordner spiegelt jeden Fehler sofort. Löscht jemand versehentlich einen Ordner oder verschlüsselt ein Trojaner die Dateien, ist auch die Cloud-Kopie betroffen. Ein echtes Backup hält ältere Versionen fest, die sich nicht nachträglich verändern lassen.

## Welches NAS eignet sich als zentrale Sicherung?

Ein NAS ist ein kleiner Server im Büro, der alle Rechner automatisch sichert. Für bis zu zehn Arbeitsplätze genügt ein Gerät mit zwei Festplatten:

{{produkt:synology-ds224-plus}}

Mehr Platz, mehr Nutzer oder virtuelle Maschinen? Dann ein Modell mit vier Schächten:

{{produkt:synology-ds923-plus}}

## Welche Festplatten gehören ins NAS?

Festplatten für den Dauerbetrieb. Normale Desktop-Platten sind dafür nicht gebaut.

{{produkt:seagate-ironwolf-4tb}}

{{produkt:wd-red-plus-4tb}}

## Wie kommt die Kopie außer Haus?

Am einfachsten mit zwei externen Laufwerken im Wechsel: eins hängt am NAS, eins liegt zu Hause. Jede Woche tauschen.

{{produkt:samsung-t7-shield-2tb}}

{{produkt:sandisk-extreme-portable-ssd-1tb}}

Für große Datenmengen, bei denen Geschwindigkeit zweitrangig ist:

{{produkt:wd-elements-portable-4tb}}

## Was schützt das NAS vor Stromausfall?

Ein plötzlicher Stromausfall kann laufende Schreibvorgänge zerstören. Eine USV überbrückt Minuten und fährt das NAS sauber herunter:

{{produkt:apc-back-ups-pro-br900g-gr}}

## Checkliste für Ihre Datensicherung

1. NAS sichert alle Rechner täglich automatisch.
2. Versionen werden mindestens 30 Tage aufbewahrt.
3. Wöchentlich eine Kopie auf ein externes Laufwerk, das danach außer Haus geht.
4. Einmal im Quartal eine Datei probehalber wiederherstellen.
5. Jemand ist verantwortlich und bekommt Fehlermeldungen per E-Mail.

## Häufige Fragen

### Wie oft sollte ich sichern?

Mindestens täglich. Wer viel mit Kundendaten arbeitet, sichert stündlich per Snapshot auf dem NAS.

### Reicht RAID als Backup?

Nein. RAID schützt vor dem Ausfall einer Festplatte, nicht vor Löschen, Trojanern oder Diebstahl.

### Was verlangt die DSGVO?

Artikel 32 DSGVO fordert, die Verfügbarkeit personenbezogener Daten nach einem Zwischenfall rasch wiederherzustellen. Ohne funktionierendes Backup ist das kaum möglich.

### Wie teuer ist eine solide Lösung?

Für ein kleines Büro liegen NAS, Festplatten, zwei externe Laufwerke und eine USV meist zwischen 800 und 1.500 Euro, einmalig.

Stand: Oktober 2026. Sie wissen nicht, ob Ihre Sicherung im Ernstfall funktioniert? Ich prüfe sie mit Ihnen und richte bei Bedarf eine 3-2-1-Lösung ein.
MD,
        ],
        [
            'slug' => 'datensicherung-3-2-1-regel-kleine-betriebe',
            'lang' => 'en',
            'category' => 'IT security',
            'title' => 'Backups with the 3-2-1 rule: how small businesses protect their data',
            'excerpt' => 'A dead disk, a stolen laptop, ransomware: with the 3-2-1 rule your data survives all three. Here is how to put it in place with little effort.',
            'meta' => 'The 3-2-1 backup rule explained: three copies, two media, one off-site. With a NAS, external SSDs and a checklist for small businesses and practices.',
            'tags' => 'backup, data-protection, nas, it-security',
            'published' => '2026-08-27 09:00:00',
            'body' => <<<'MD'
**In short:** The 3-2-1 rule means three copies of your data, on two different media, one of them off-site. For a small business a NAS in the office, an external SSD in rotation and a cloud target are enough. What matters is that backups run automatically and are tested regularly.

The products in this article are recommendations from practice. The links are affiliate links: if you buy through them, I earn a small commission and your price does not change.

## What exactly does the 3-2-1 rule mean?

- **3 copies:** the original and two backups.
- **2 media:** for example a NAS and an external drive. If one type of device fails, the other remains.
- **1 off-site:** one copy is stored elsewhere, at home or in a cloud in Germany. Fire, water or burglary then do not hit every copy.

Germany's [BSI](https://www.bsi.bund.de/) explicitly recommends this approach for small businesses too.

## Why is a cloud folder alone not a backup?

A synced folder mirrors every mistake instantly. If someone deletes a folder by accident or ransomware encrypts the files, the cloud copy is affected as well. A real backup keeps older versions that cannot be changed afterwards.

## Which NAS works as a central backup?

A NAS is a small server in the office that backs up every computer automatically. For up to ten workstations a two-bay device is enough:

{{produkt:synology-ds224-plus-nas}}

More space, more users or virtual machines? Then a four-bay model:

{{produkt:synology-ds923-plus-nas}}

## Which drives belong in a NAS?

Drives built for continuous operation. Ordinary desktop drives are not made for it.

{{produkt:seagate-ironwolf-4tb-nas-drive}}

{{produkt:wd-red-plus-4tb-nas-drive}}

## How does a copy get off-site?

Most simply with two external drives in rotation: one is attached to the NAS, the other is at home. Swap them every week.

{{produkt:samsung-t7-shield-2tb-ssd}}

{{produkt:sandisk-extreme-portable-ssd-1tb-external}}

For large amounts of data where speed matters less:

{{produkt:wd-elements-portable-4tb-hdd}}

## What protects the NAS from a power cut?

A sudden power cut can corrupt writes in progress. A UPS bridges a few minutes and shuts the NAS down cleanly:

{{produkt:apc-back-ups-pro-br900g-gr-ups}}

## Checklist for your backups

1. The NAS backs up every computer automatically every day.
2. Versions are kept for at least 30 days.
3. Once a week a copy goes to an external drive that then leaves the building.
4. Once a quarter, restore a file as a test.
5. Someone is responsible and gets error messages by email.

## Frequently asked questions

### How often should I back up?

At least daily. If you work with a lot of client data, take hourly snapshots on the NAS.

### Is RAID a backup?

No. RAID protects against one drive failing, not against deletion, ransomware or theft.

### What does the GDPR require?

Article 32 GDPR requires the ability to restore the availability of personal data quickly after an incident. Without a working backup that is hardly possible.

### How much does a solid setup cost?

For a small office, a NAS, drives, two external drives and a UPS usually come to between 800 and 1,500 euros, once.

Updated: October 2026. Not sure your backup would work in an emergency? I will check it with you and set up a 3-2-1 solution if needed.
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
