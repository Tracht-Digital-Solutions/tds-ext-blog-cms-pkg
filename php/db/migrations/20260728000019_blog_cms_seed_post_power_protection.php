<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Tds\Ext\BlogCms\Support\PostSeeder;

/**
 * Blog-CMS — seed the "power cuts and surges" guide (DE + EN).
 *
 * Product lines `{{produkt:<slug>}}` use the shop slug of the row's language;
 * see `20260728000012_blog_cms_seed_post_office_wifi.php`.
 */
final class BlogCmsSeedPostPowerProtection extends AbstractMigration
{
    public const POSTS = [
        [
            'slug' => 'stromausfall-und-ueberspannung-im-buero',
            'lang' => 'de',
            'category' => 'IT-Sicherheit',
            'title' => 'Stromausfall und Überspannung im Büro: So schützen Sie Technik und Daten',
            'excerpt' => 'Ein Gewitter, ein kurzer Stromausfall, und der Server startet nicht mehr. Mit USV und Überspannungsschutz an den richtigen Stellen passiert das nicht.',
            'meta' => 'Büro-Technik vor Stromausfall und Überspannung schützen: Wann eine USV nötig ist, welche Geräte daran gehören und was eine gute Steckdosenleiste leistet.',
            'tags' => 'usv, ueberspannungsschutz, stromausfall, it-sicherheit',
            'published' => '2026-10-08 09:00:00',
            'body' => <<<'MD'
**Kurz gesagt:** Router, NAS und Telefonanlage gehören an eine USV, damit kurze Stromausfälle keine Daten zerstören und das Netz weiterläuft. Alle übrigen Geräte am Arbeitsplatz schützt eine Steckdosenleiste mit Überspannungsschutz. Beides zusammen kostet weniger als ein einziges zerstörtes Netzteil samt Ausfalltag.

Die Produkte in diesem Beitrag sind Empfehlungen aus der Praxis. Die Links sind Partnerlinks: Kaufen Sie darüber, erhalte ich eine kleine Provision, der Preis ändert sich für Sie nicht.

## Was passiert bei einem Stromausfall mit meinen Daten?

Desktop-PCs verlieren ungespeicherte Arbeit. Gefährlicher ist es für Geräte, die ständig schreiben: Ein NAS mitten in einer Sicherung kann sein Dateisystem beschädigen. Router und Telefonanlage brauchen nach dem Neustart oft mehrere Minuten, in denen niemand erreichbar ist.

## Was ist eine USV und welche Geräte gehören daran?

Eine unterbrechungsfreie Stromversorgung (USV) ist ein Akku zwischen Steckdose und Gerät. Fällt der Strom aus, übernimmt sie ohne Unterbrechung. Bei längeren Ausfällen sagt sie dem NAS per USB-Kabel Bescheid, damit es sich sauber herunterfährt.

An die USV gehören:

- NAS oder Server
- Router und Switch
- Telefonanlage

Für ein kleines Büro mit Router und NAS reicht diese Klasse:

{{produkt:apc-back-ups-bx950mi-gr}}

Mit Display und reiner Sinuskurve, für empfindlichere Netzteile und etwas mehr Last:

{{produkt:apc-back-ups-pro-br900g-gr}}

## Wie schütze ich den Arbeitsplatz vor Überspannung?

Blitzeinschläge in der Nähe und Schaltvorgänge im Stromnetz erzeugen kurze Spannungsspitzen, die Netzteile zerstören können. Eine Steckdosenleiste mit Überspannungsschutz fängt sie ab. Wichtig ist ein Modell mit Anzeige, ob der Schutz noch aktiv ist:

{{produkt:brennenstuhl-premium-line-ueberspannungsschutz}}

## Was ist mit dem Netzwerkkabel?

Überspannung kommt nicht nur über die Steckdose, sondern auch über Telefon-, Antennen- und lange Netzwerkleitungen. Ein vollständiger Schutz ist Sache des Elektrikers (Fein- und Grobschutz im Verteilerkasten). Fragen Sie bei der nächsten Prüfung der Elektrik danach.

## Und wenn trotzdem etwas kaputtgeht?

Dann zählt die Datensicherung. Wie das gelingt, steht im Beitrag [Datensicherung mit der 3-2-1-Regel](/datensicherung-3-2-1-regel-kleine-betriebe). Eine externe Kopie auf einem robusten Laufwerk überlebt auch einen Defekt im Büro:

{{produkt:samsung-t7-shield-2tb}}

## Checkliste

1. NAS, Router, Switch und Telefonanlage an die USV.
2. USV per USB mit dem NAS verbinden und das automatische Herunterfahren einrichten.
3. Arbeitsplätze über Steckdosenleisten mit Überspannungsschutz versorgen.
4. USV-Akku alle drei bis fünf Jahre tauschen.
5. Einmal im Jahr den Stecker ziehen und testen, ob alles wie geplant reagiert.

## Häufige Fragen

### Wie lange überbrückt eine kleine USV?

Mit Router und NAS meist 15 bis 30 Minuten. Das reicht für kurze Ausfälle und ein sauberes Herunterfahren.

### Gehört der Laserdrucker an die USV?

Nein. Laserdrucker ziehen beim Aufheizen sehr viel Strom und überlasten kleine USV-Geräte.

### Schützt eine USV auch vor Überspannung?

Ja, die meisten USV-Geräte haben einen eingebauten Überspannungsschutz für die angeschlossenen Geräte.

### Brauche ich das auch im Homeoffice?

Für den Router lohnt es sich, wenn Sie telefonisch erreichbar sein müssen. Eine Steckdosenleiste mit Überspannungsschutz gehört an jeden Arbeitsplatz.

Stand: Oktober 2026. Ich prüfe, welche Geräte in Ihrem Büro geschützt werden sollten, und richte USV und automatisches Herunterfahren für Sie ein.
MD,
        ],
        [
            'slug' => 'stromausfall-und-ueberspannung-im-buero',
            'lang' => 'en',
            'category' => 'IT security',
            'title' => 'Power cuts and surges in the office: how to protect equipment and data',
            'excerpt' => 'A thunderstorm, a brief power cut, and the server no longer boots. With a UPS and surge protection in the right places that does not happen.',
            'meta' => 'Protect office equipment from power cuts and surges: when a UPS is needed, which devices belong on it and what a good surge-protected power strip does.',
            'tags' => 'ups, surge-protection, power-cut, it-security',
            'published' => '2026-10-08 09:00:00',
            'body' => <<<'MD'
**In short:** Router, NAS and phone system belong on a UPS so that short power cuts do not destroy data and the network keeps running. Every other device at the desk is protected by a power strip with surge protection. Both together cost less than a single destroyed power supply plus a day of downtime.

The products in this article are recommendations from practice. The links are affiliate links: if you buy through them, I earn a small commission and your price does not change.

## What happens to my data in a power cut?

Desktop PCs lose unsaved work. It is more dangerous for devices that write all the time: a NAS in the middle of a backup can damage its file system. Router and phone system often need several minutes to restart, during which nobody can be reached.

## What is a UPS and which devices belong on it?

An uninterruptible power supply (UPS) is a battery between the socket and the device. If the power fails, it takes over without interruption. During longer outages it tells the NAS over a USB cable, so the NAS shuts down cleanly.

On the UPS belong:

- NAS or server
- Router and switch
- Phone system

For a small office with router and NAS this class is enough:

{{produkt:apc-back-ups-bx950mi-gr-ups}}

With a display and a pure sine wave, for more sensitive power supplies and a little more load:

{{produkt:apc-back-ups-pro-br900g-gr-ups}}

## How do I protect the desk from surges?

Nearby lightning strikes and switching in the grid cause short voltage spikes that can destroy power supplies. A power strip with surge protection absorbs them. Choose a model that shows whether the protection is still active:

{{produkt:brennenstuhl-premium-line-surge-protector}}

## What about the network cable?

Surges come not only through the socket but also over phone, antenna and long network lines. Complete protection is a job for an electrician (coarse and fine protection in the distribution board). Ask about it at the next inspection of your wiring.

## And if something breaks anyway?

Then your backup counts. How to set it up is covered in [Backups with the 3-2-1 rule](/en/datensicherung-3-2-1-regel-kleine-betriebe). An external copy on a rugged drive survives even a defect in the office:

{{produkt:samsung-t7-shield-2tb-ssd}}

## Checklist

1. NAS, router, switch and phone system on the UPS.
2. Connect the UPS to the NAS over USB and set up automatic shutdown.
3. Supply desks through power strips with surge protection.
4. Replace the UPS battery every three to five years.
5. Once a year, pull the plug and test that everything reacts as planned.

## Frequently asked questions

### How long does a small UPS last?

With a router and NAS usually 15 to 30 minutes. That covers short outages and a clean shutdown.

### Does the laser printer belong on the UPS?

No. Laser printers draw a lot of power while heating up and overload small UPS units.

### Does a UPS also protect against surges?

Yes, most UPS units have built-in surge protection for the connected devices.

### Do I need this in a home office too?

For the router it is worth it if you must be reachable by phone. A surge-protected power strip belongs at every desk.

Updated: October 2026. I check which devices in your office should be protected and set up the UPS and automatic shutdown for you.
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
