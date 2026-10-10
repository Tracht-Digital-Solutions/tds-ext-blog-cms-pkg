<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Tds\Ext\BlogCms\Support\PostSeeder;

/**
 * Blog-CMS — seed the "home office setup" guide (DE + EN).
 *
 * Product lines `{{produkt:<slug>}}` use the shop slug of the row's language;
 * see `20260728000012_blog_cms_seed_post_office_wifi.php`.
 */
final class BlogCmsSeedPostHomeOfficeSetup extends AbstractMigration
{
    public const POSTS = [
        [
            'slug' => 'homeoffice-arbeitsplatz-einrichten',
            'lang' => 'de',
            'category' => 'Ausstattung',
            'title' => 'Homeoffice-Arbeitsplatz einrichten: Was sich wirklich lohnt und was nicht',
            'excerpt' => 'Ein guter Arbeitsplatz zu Hause braucht keine Designermöbel. Fünf Dinge machen den Unterschied: Bildschirm, Eingabegeräte, Licht, Ton und Höhe.',
            'meta' => 'Homeoffice einrichten mit Plan: welcher Monitor, welche Maus und Tastatur, welches Licht und welcher Tisch sich lohnen. Mit Checkliste und Produkt-Tipps.',
            'tags' => 'homeoffice, arbeitsplatz, ergonomie, monitor',
            'published' => '2026-09-10 09:00:00',
            'body' => <<<'MD'
**Kurz gesagt:** Ein guter Homeoffice-Platz steht auf fünf Säulen: ein großer Bildschirm auf Augenhöhe, ergonomische Maus und Tastatur, blendfreies Licht, ein Headset statt Laptop-Mikrofon und ein Tisch, an dem man auch stehen kann. In dieser Reihenfolge investieren, dann wirkt jeder Euro.

Die Produkte in diesem Beitrag sind Empfehlungen aus der Praxis. Die Links sind Partnerlinks: Kaufen Sie darüber, erhalte ich eine kleine Provision, der Preis ändert sich für Sie nicht.

## Warum reicht der Laptop allein nicht?

Wer acht Stunden auf einen 14-Zoll-Bildschirm schaut, sitzt nach vorne gebeugt. Nacken und Schultern melden sich nach wenigen Wochen. Ein externer Monitor auf Augenhöhe ist deshalb die wichtigste einzelne Anschaffung.

## Welcher Monitor eignet sich fürs Homeoffice?

Für Büroarbeit sind 24 bis 27 Zoll ideal. Wichtig ist USB-C: Ein Kabel liefert dann Bild, Strom und Netzwerk für den Laptop.

Solide und günstig für Text und Tabellen:

{{produkt:dell-p2425h}}

Wer viel mit Layouts, Fotos oder mehreren Fenstern arbeitet, profitiert von 27 Zoll und höherer Auflösung:

{{produkt:dell-ultrasharp-u2724de}}

Ein Monitorarm schafft Platz auf dem Tisch und bringt den Bildschirm genau auf Augenhöhe:

{{produkt:ergotron-lx-monitorarm}}

## Welche Maus und Tastatur schonen Hände und Handgelenke?

Eine flache, leise Tastatur und eine Maus, die zur Hand passt, verhindern Verspannungen. Wer schon Beschwerden im Handgelenk hat, sollte eine vertikale Maus ausprobieren.

{{produkt:logitech-mx-keys-s}}

{{produkt:logitech-mx-master-3s}}

{{produkt:logitech-lift-vertikale-maus}}

## Wie wird das Licht am Schreibtisch richtig?

Licht von hinten spiegelt im Bildschirm, Licht von vorne blendet. Eine Monitorleuchte beleuchtet nur den Tisch und nicht den Bildschirm. Das entlastet die Augen besonders an dunklen Nachmittagen.

{{produkt:benq-screenbar-halo-2}}

## Wie klinge ich in Videocalls professionell?

Das Laptop-Mikrofon nimmt Raum, Tastatur und Kinder mit auf. Ein Headset mit Bügelmikrofon ist die einfachste Verbesserung:

{{produkt:jabra-evolve2-30-se}}

Für ein schärferes Bild sorgt eine externe Webcam oben am Monitor:

{{produkt:logitech-c920-hd-pro}}

## Lohnt sich ein höhenverstellbarer Schreibtisch?

Ja, wenn er genutzt wird. Schon zwei- bis dreimal am Tag für eine halbe Stunde aufzustehen, entlastet den Rücken spürbar. Ein Tischgestell, auf das die vorhandene Platte passt, ist günstiger als ein kompletter Tisch:

{{produkt:flexispot-eq5-tischgestell}}

## Wie wird aus dem Laptop ein vollständiger Arbeitsplatz?

Mit einer Dockingstation. Laptop anstecken, und Monitor, Tastatur, Maus, Netzwerk und Strom sind sofort da.

{{produkt:caldigit-ts4-thunderbolt-dock}}

Für weniger Geräte genügt ein kompakter USB-C-Hub:

{{produkt:anker-usb-c-hub-7-in-1}}

## Checkliste: in dieser Reihenfolge anschaffen

1. Externer Monitor auf Augenhöhe
2. Tastatur und Maus
3. Headset
4. Licht
5. Dockingstation oder Hub
6. Höhenverstellbarer Tisch

## Häufige Fragen

### Kann ich die Ausstattung von der Steuer absetzen?

Arbeitsmittel wie Monitor, Tastatur oder Headset sind in der Regel als Werbungskosten oder Betriebsausgabe absetzbar. Die Einzelheiten klärt Ihre Steuerberatung.

### Wie hoch muss der Monitor stehen?

Die Oberkante sollte auf Augenhöhe oder knapp darunter liegen, der Abstand etwa eine Armlänge betragen.

### Brauche ich zwei Monitore?

Selten. Ein 27-Zoll-Monitor mit zwei Fenstern nebeneinander ersetzt für die meisten Aufgaben zwei kleine Bildschirme.

### Funktioniert das auch mit einem Mac?

Ja. Alle genannten Monitore, Docks und Eingabegeräte arbeiten mit Windows und macOS.

Stand: Oktober 2026. Sie richten mehrere Homeoffice-Plätze für Ihr Team ein? Ich stelle Ihnen eine passende Ausstattungsliste zusammen.
MD,
        ],
        [
            'slug' => 'homeoffice-arbeitsplatz-einrichten',
            'lang' => 'en',
            'category' => 'Equipment',
            'title' => 'Setting up a home office: what is worth the money and what is not',
            'excerpt' => 'A good home workplace needs no designer furniture. Five things make the difference: screen, input devices, light, sound and desk height.',
            'meta' => 'Set up a home office with a plan: which monitor, mouse and keyboard, which light and which desk are worth it. With a checklist and product tips.',
            'tags' => 'home-office, workplace, ergonomics, monitor',
            'published' => '2026-09-10 09:00:00',
            'body' => <<<'MD'
**In short:** A good home office rests on five pillars: a large screen at eye level, an ergonomic mouse and keyboard, glare-free light, a headset instead of the laptop microphone and a desk you can also stand at. Invest in that order and every euro counts.

The products in this article are recommendations from practice. The links are affiliate links: if you buy through them, I earn a small commission and your price does not change.

## Why is a laptop alone not enough?

Looking at a 14-inch screen for eight hours means sitting hunched forward. Neck and shoulders complain within weeks. An external monitor at eye level is therefore the single most important purchase.

## Which monitor suits a home office?

For office work 24 to 27 inches is ideal. USB-C matters: one cable then carries picture, power and network for the laptop.

Solid and affordable for text and spreadsheets:

{{produkt:dell-p2425h-monitor}}

If you work a lot with layouts, photos or many windows, 27 inches and a higher resolution pay off:

{{produkt:dell-ultrasharp-u2724de-monitor}}

A monitor arm frees up the desk and puts the screen exactly at eye level:

{{produkt:ergotron-lx-monitor-arm}}

## Which mouse and keyboard are kind to hands and wrists?

A flat, quiet keyboard and a mouse that fits your hand prevent strain. If your wrist already hurts, try a vertical mouse.

{{produkt:logitech-mx-keys-s-keyboard}}

{{produkt:logitech-mx-master-3s-mouse}}

{{produkt:logitech-lift-vertical-mouse}}

## How do I get the desk lighting right?

Light from behind reflects in the screen, light from the front dazzles. A monitor light illuminates only the desk, not the screen. That eases the eyes, especially on dark afternoons.

{{produkt:benq-screenbar-halo-2-monitor-lamp}}

## How do I sound professional on video calls?

The laptop microphone picks up the room, the keyboard and the children. A headset with a boom microphone is the simplest improvement:

{{produkt:jabra-evolve2-30-se-headset}}

An external webcam on top of the monitor gives a sharper picture:

{{produkt:logitech-c920-hd-pro-webcam}}

## Is a height-adjustable desk worth it?

Yes, if you use it. Standing up two or three times a day for half an hour noticeably relieves the back. A frame that takes your existing desktop is cheaper than a complete desk:

{{produkt:flexispot-eq5-desk-frame}}

## How does a laptop become a complete workstation?

With a docking station. Plug in the laptop and monitor, keyboard, mouse, network and power are there at once.

{{produkt:caldigit-ts4-thunderbolt-4-dock}}

For fewer devices a compact USB-C hub is enough:

{{produkt:anker-usb-c-hub-7-in-1-adapter}}

## Checklist: buy in this order

1. External monitor at eye level
2. Keyboard and mouse
3. Headset
4. Light
5. Docking station or hub
6. Height-adjustable desk

## Frequently asked questions

### Can I deduct the equipment from my taxes?

Work equipment such as a monitor, keyboard or headset is usually deductible as an income-related or business expense. Your tax adviser will clarify the details.

### How high should the monitor be?

The top edge should be at eye level or slightly below, about an arm's length away.

### Do I need two monitors?

Rarely. A 27-inch monitor with two windows side by side replaces two small screens for most tasks.

### Does this work with a Mac too?

Yes. All monitors, docks and input devices mentioned work with Windows and macOS.

Updated: October 2026. Equipping several home offices for your team? I will put together a matching equipment list for you.
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
