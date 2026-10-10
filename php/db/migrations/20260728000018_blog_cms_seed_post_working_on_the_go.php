<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Tds\Ext\BlogCms\Support\PostSeeder;

/**
 * Blog-CMS — seed the "working on the go" guide (DE + EN).
 *
 * Product lines `{{produkt:<slug>}}` use the shop slug of the row's language;
 * see `20260728000012_blog_cms_seed_post_office_wifi.php`.
 */
final class BlogCmsSeedPostWorkingOnTheGo extends AbstractMigration
{
    public const POSTS = [
        [
            'slug' => 'mobiles-arbeiten-ausruestung-fuer-unterwegs',
            'lang' => 'de',
            'category' => 'Ausstattung',
            'title' => 'Mobil arbeiten ohne Kompromisse: Die Ausrüstung für Zug, Hotel und Kundentermin',
            'excerpt' => 'Im Zug, im Hotel oder beim Kunden: Mit sieben kleinen Geräten wird jeder Tisch zum Arbeitsplatz, mit sicherem Netz, zweitem Bildschirm und vollem Akku.',
            'meta' => 'Mobiles Arbeiten richtig ausgestattet: Reiserouter, mobiler Monitor, Laptopständer, Ladegerät, Powerbank und Blickschutz. Packliste für Selbstständige.',
            'tags' => 'mobiles-arbeiten, reisen, laptop, it-sicherheit',
            'published' => '2026-10-01 09:00:00',
            'body' => <<<'MD'
**Kurz gesagt:** Für produktives Arbeiten unterwegs brauchen Sie vier Dinge: ein sicheres Netz, Strom, eine gute Haltung und Schutz vor neugierigen Blicken. Ein Reiserouter, ein 100-Watt-Ladegerät, eine Powerbank, ein Laptopständer mit Maus und ein Blickschutzfilter decken das ab. Ein mobiler Monitor ist die Kür.

Die Produkte in diesem Beitrag sind Empfehlungen aus der Praxis. Die Links sind Partnerlinks: Kaufen Sie darüber, erhalte ich eine kleine Provision, der Preis ändert sich für Sie nicht.

## Wie arbeite ich sicher im Hotel- oder Café-WLAN?

Öffentliche WLANs sind bequem, aber Sie wissen nie, wer mithört. Ein Reiserouter verbindet sich einmal mit dem fremden Netz und baut darüber ein eigenes, verschlüsseltes WLAN für Ihre Geräte auf. Mit eingebautem VPN läuft der Verkehr direkt verschlüsselt ins Büro:

{{produkt:gl-inet-beryl-ax-reiserouter}}

## Wie halte ich alle Geräte mit einem Ladegerät am Laufen?

Ein einziges USB-C-Ladegerät mit 100 Watt lädt Laptop, Handy und Kopfhörer gleichzeitig. Drei Netzteile bleiben zu Hause:

{{produkt:anker-prime-100w-ladegeraet}}

Wo keine Steckdose ist, im Zug oder auf der Messe, hilft eine Powerbank, die auch Laptops lädt:

{{produkt:anker-737-powerbank}}

## Wie vermeide ich Nackenschmerzen auf Reisen?

Der Laptop auf dem Hoteltisch zwingt den Kopf nach unten. Ein Ständer bringt den Bildschirm auf Augenhöhe, dazu eine kompakte Maus und das Arbeiten fühlt sich an wie im Büro:

{{produkt:rain-design-mstand}}

{{produkt:logitech-mx-anywhere-3s}}

## Lohnt sich ein mobiler Monitor?

Für alle, die zu Hause mit zwei Bildschirmen arbeiten: ja. Ein 15-Zoll-Monitor wiegt weniger als ein Kilo und wird über ein einziges USB-C-Kabel mit Bild und Strom versorgt:

{{produkt:asus-zenscreen-mb16acv}}

## Wie schütze ich Kundendaten vor fremden Blicken?

Im Zug liest der Sitznachbar mit, oft ohne es zu wollen. Ein Blickschutzfilter macht den Bildschirm von der Seite schwarz, von vorne bleibt er klar:

{{produkt:3m-blickschutzfilter-14-zoll}}

Für Präsentationen oder Dateien beim Kunden: ein verschlüsselter USB-Stick mit PIN statt eines Werbegeschenks:

{{produkt:kingston-ironkey-vault-privacy-50c-32gb}}

## Packliste für unterwegs

1. Reiserouter mit VPN
2. 100-Watt-Ladegerät und ein langes USB-C-Kabel
3. Powerbank mit Laptop-Unterstützung
4. Laptopständer und kompakte Maus
5. Blickschutzfilter
6. Optional: mobiler Monitor
7. Headset für Telefonate

## Häufige Fragen

### Darf ich eine Powerbank ins Flugzeug mitnehmen?

Ja, im Handgepäck. Bis 100 Wattstunden ist das bei den meisten Fluggesellschaften ohne Anmeldung erlaubt. Prüfen Sie vorher die Regeln Ihrer Airline.

### Reicht nicht der Hotspot vom Handy?

Für kurze Zeit ja. Für einen ganzen Arbeitstag leert er den Handy-Akku und das Datenvolumen. Der Reiserouter nutzt das vorhandene WLAN und sichert es ab.

### Ist ein VPN Pflicht?

Nicht gesetzlich, aber für den Zugriff auf Firmendaten aus fremden Netzen dringend empfohlen.

### Wie schwer ist die komplette Ausrüstung?

Ohne Monitor etwa ein Kilo, mit Monitor knapp zwei. Das passt in jeden Laptop-Rucksack.

Stand: Oktober 2026. Sie wollen Ihrem Team sicheres mobiles Arbeiten ermöglichen? Ich richte VPN, Reiserouter und Geräte so ein, dass sie unterwegs einfach funktionieren.
MD,
        ],
        [
            'slug' => 'mobiles-arbeiten-ausruestung-fuer-unterwegs',
            'lang' => 'en',
            'category' => 'Equipment',
            'title' => 'Working on the go without compromise: gear for the train, hotel and client visits',
            'excerpt' => 'On the train, in a hotel or at a client: seven small devices turn any table into a workplace, with a secure network, a second screen and a full battery.',
            'meta' => 'Kit for mobile work: travel router, portable monitor, laptop stand, charger, power bank and privacy filter. A packing list for freelancers and small teams.',
            'tags' => 'mobile-work, travel, laptop, it-security',
            'published' => '2026-10-01 09:00:00',
            'body' => <<<'MD'
**In short:** To work productively on the go you need four things: a secure network, power, a good posture and protection from prying eyes. A travel router, a 100-watt charger, a power bank, a laptop stand with a mouse and a privacy filter cover that. A portable monitor is the extra.

The products in this article are recommendations from practice. The links are affiliate links: if you buy through them, I earn a small commission and your price does not change.

## How do I work safely on hotel or café Wi-Fi?

Public Wi-Fi is convenient, but you never know who is listening. A travel router connects once to the foreign network and builds its own encrypted Wi-Fi for your devices on top of it. With a built-in VPN, traffic runs encrypted straight to the office:

{{produkt:gl-inet-beryl-ax-travel-router}}

## How do I keep every device going with one charger?

A single 100-watt USB-C charger charges laptop, phone and earphones at the same time. Three power adapters stay at home:

{{produkt:anker-prime-100w-charger}}

Where there is no socket, on the train or at a trade fair, a power bank that also charges laptops helps:

{{produkt:anker-737-power-bank}}

## How do I avoid neck pain when travelling?

A laptop on a hotel desk forces your head down. A stand lifts the screen to eye level, add a compact mouse and working feels like the office:

{{produkt:rain-design-mstand-laptop-stand}}

{{produkt:logitech-mx-anywhere-3s-mouse}}

## Is a portable monitor worth it?

For anyone who works with two screens at home: yes. A 15-inch monitor weighs less than a kilo and gets picture and power over a single USB-C cable:

{{produkt:asus-zenscreen-mb16acv-portable-monitor}}

## How do I protect client data from prying eyes?

On the train the person next to you reads along, often without meaning to. A privacy filter turns the screen black from the side while it stays clear from the front:

{{produkt:3m-privacy-filter-14-inch}}

For presentations or files at a client: an encrypted USB stick with a PIN instead of a promotional freebie:

{{produkt:kingston-ironkey-vault-privacy-50c-32gb-usb-c}}

## Packing list for the road

1. Travel router with VPN
2. 100-watt charger and a long USB-C cable
3. Power bank that supports laptops
4. Laptop stand and compact mouse
5. Privacy filter
6. Optional: portable monitor
7. Headset for calls

## Frequently asked questions

### May I take a power bank on a plane?

Yes, in hand luggage. Up to 100 watt-hours most airlines allow it without notice. Check your airline's rules beforehand.

### Isn't the phone hotspot enough?

For a short while, yes. For a whole working day it drains the phone battery and the data allowance. The travel router uses the existing Wi-Fi and secures it.

### Is a VPN mandatory?

Not by law, but strongly recommended for accessing company data from foreign networks.

### How heavy is the complete kit?

About one kilo without the monitor, just under two with it. That fits into any laptop backpack.

Updated: October 2026. Want your team to work safely on the go? I set up VPN, travel routers and devices so they simply work on the road.
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
