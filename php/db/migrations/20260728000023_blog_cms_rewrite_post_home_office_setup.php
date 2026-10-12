<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Tds\Ext\BlogCms\Support\PostRewriter;

/**
 * Blog-CMS — rewrite the "home office setup" guide (DE + EN).
 *
 * Same editorial pass as `BlogCmsRewriteLaunchPosts`. Every `{{produkt:…}}`
 * line of the seed stays, with the shop slug of the row's language.
 */
final class BlogCmsRewritePostHomeOfficeSetup extends AbstractMigration
{
    public const SEED = '20260728000013_blog_cms_seed_post_home_office_setup.php';

    public const POSTS = [
        [
            'slug' => 'homeoffice-arbeitsplatz-einrichten',
            'lang' => 'de',
            'title' => 'Homeoffice einrichten: Was sich wirklich lohnt und was nicht',
            'excerpt' => 'Ein guter Arbeitsplatz zu Hause braucht keine Designermöbel. Fünf Dinge machen den Unterschied: Bildschirm, Eingabegeräte, Licht, Ton und Tischhöhe.',
            'meta' => 'Homeoffice einrichten mit Plan: welcher Monitor, welche Maus, welches Licht und welcher Tisch sich lohnen, plus Arbeitszeit sauber dokumentieren.',
            'body' => <<<'MD'
**Kurz gesagt:** Ein guter Homeoffice-Platz steht auf fünf Säulen: ein großer Bildschirm auf Augenhöhe, ergonomische Maus und Tastatur, blendfreies Licht, ein Headset statt Laptop-Mikrofon und ein Tisch, an dem man auch stehen kann. Investieren Sie in dieser Reihenfolge, dann wirkt jeder Euro.

Die Produkte in diesem Beitrag sind Empfehlungen aus der Praxis. Die Links sind Partnerlinks: Kaufen Sie darüber, erhalte ich eine kleine Provision, der Preis ändert sich für Sie nicht.

## Warum reicht der Laptop allein nicht?

Wer acht Stunden auf einen 14-Zoll-Bildschirm schaut, sitzt nach vorne gebeugt. Nacken und Schultern melden sich nach wenigen Wochen. Ein externer Monitor auf Augenhöhe ist deshalb die wichtigste einzelne Anschaffung.

## Welcher Monitor eignet sich fürs Homeoffice?

Für Büroarbeit sind 24 bis 27 Zoll ideal. Achten Sie auf USB-C: Ein Kabel liefert dann Bild, Strom und Netzwerk für den Laptop.

Solide und günstig für Text und Tabellen:

{{produkt:dell-p2425h}}

Für Layouts, Fotos oder viele Fenster lohnen sich 27 Zoll und eine höhere Auflösung:

{{produkt:dell-ultrasharp-u2724de}}

Ein Monitorarm schafft Platz und bringt den Bildschirm genau auf Augenhöhe:

{{produkt:ergotron-lx-monitorarm}}

## Welche Maus und Tastatur schonen die Handgelenke?

Eine flache, leise Tastatur und eine Maus, die zur Hand passt, beugen Verspannungen vor. Bei Beschwerden im Handgelenk lohnt sich ein Versuch mit einer vertikalen Maus.

{{produkt:logitech-mx-keys-s}}

{{produkt:logitech-mx-master-3s}}

{{produkt:logitech-lift-vertikale-maus}}

## Wie wird das Licht am Schreibtisch richtig?

Licht von hinten spiegelt im Bildschirm, Licht von vorne blendet. Eine Monitorleuchte beleuchtet nur den Tisch, nicht den Bildschirm. Das entlastet die Augen, besonders an dunklen Nachmittagen.

{{produkt:benq-screenbar-halo-2}}

## Wie klinge ich in Videocalls professionell?

Das Laptop-Mikrofon nimmt Raum, Tastatur und Kinder mit auf. Ein Headset mit Bügelmikrofon ist die einfachste Verbesserung:

{{produkt:jabra-evolve2-30-se}}

Für ein schärferes Bild sorgt eine Webcam oben am Monitor:

{{produkt:logitech-c920-hd-pro}}

Mehr zu Ton, Licht und Kamera steht im Beitrag [Besser klingen und aussehen im Videocall](/besser-klingen-und-aussehen-im-videocall).

## Lohnt sich ein höhenverstellbarer Schreibtisch?

Ja, wenn er genutzt wird. Schon zwei- bis dreimal am Tag eine halbe Stunde im Stehen entlastet den Rücken spürbar. Ein Gestell für die vorhandene Tischplatte ist günstiger als ein kompletter Tisch:

{{produkt:flexispot-eq5-tischgestell}}

## Wie wird aus dem Laptop ein vollständiger Arbeitsplatz?

Mit einer Dockingstation. Laptop anstecken, und Monitor, Tastatur, Maus, Netzwerk und Strom sind sofort da.

{{produkt:caldigit-ts4-thunderbolt-dock}}

Für weniger Geräte genügt ein kompakter USB-C-Hub:

{{produkt:anker-usb-c-hub-7-in-1}}

## Wie dokumentiere ich die Arbeitszeit im Homeoffice?

Auch zu Hause zählt die Arbeitszeit. Arbeitgeber müssen sie nach einem [Beschluss des Bundesarbeitsgerichts](https://www.bundesarbeitsgericht.de/entscheidung/1-abr-22-21/) vom 13. September 2022 erfassen. Für kleine Teams reicht oft ein monatlicher Nachweis. Der [Stundenzettel](https://tools.tracht-digital.de/tools/stundenzettel) zieht Pausen ab, rechnet Tages- und Monatssummen und liefert ein PDF mit Feldern für beide Unterschriften.

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

Die Oberkante liegt auf Augenhöhe oder knapp darunter, der Abstand beträgt etwa eine Armlänge.

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
            'title' => 'Setting up a home office: what is worth it and what is not',
            'excerpt' => 'A good home workplace needs no designer furniture. Five things make the difference: screen, input devices, light, sound and desk height.',
            'meta' => 'Set up a home office with a plan: which monitor, mouse, light and desk are worth it, and how to record working hours properly from home.',
            'body' => <<<'MD'
**In short:** A good home office rests on five pillars: a large screen at eye level, an ergonomic mouse and keyboard, glare-free light, a headset instead of the laptop microphone and a desk you can also stand at. Invest in that order and every euro counts.

The products in this article are recommendations from practice. The links are affiliate links: if you buy through them, I earn a small commission and your price does not change.

## Why is a laptop alone not enough?

Looking at a 14-inch screen for eight hours means sitting hunched forward. Neck and shoulders complain within weeks. An external monitor at eye level is therefore the single most important purchase.

## Which monitor suits a home office?

For office work 24 to 27 inches is ideal. Look for USB-C: one cable then carries picture, power and network for the laptop.

Solid and affordable for text and spreadsheets:

{{produkt:dell-p2425h-monitor}}

For layouts, photos or many windows, 27 inches and a higher resolution pay off:

{{produkt:dell-ultrasharp-u2724de-monitor}}

A monitor arm frees up space and puts the screen exactly at eye level:

{{produkt:ergotron-lx-monitor-arm}}

## Which mouse and keyboard are kind to your wrists?

A flat, quiet keyboard and a mouse that fits your hand help prevent strain. If your wrist already hurts, try a vertical mouse.

{{produkt:logitech-mx-keys-s-keyboard}}

{{produkt:logitech-mx-master-3s-mouse}}

{{produkt:logitech-lift-vertical-mouse}}

## How do I get the desk lighting right?

Light from behind reflects in the screen, light from the front dazzles. A monitor light illuminates only the desk, not the screen. That eases the eyes, especially on dark afternoons.

{{produkt:benq-screenbar-halo-2-monitor-lamp}}

## How do I sound professional on video calls?

The laptop microphone picks up the room, the keyboard and the children. A headset with a boom microphone is the simplest improvement:

{{produkt:jabra-evolve2-30-se-headset}}

A webcam on top of the monitor gives a sharper picture:

{{produkt:logitech-c920-hd-pro-webcam}}

More on sound, light and camera in [Sound and look better on video calls](/en/besser-klingen-und-aussehen-im-videocall).

## Is a height-adjustable desk worth it?

Yes, if you use it. Standing for half an hour two or three times a day noticeably relieves the back. A frame for your existing desktop is cheaper than a complete desk:

{{produkt:flexispot-eq5-desk-frame}}

## How does a laptop become a complete workstation?

With a docking station. Plug in the laptop and monitor, keyboard, mouse, network and power are there at once.

{{produkt:caldigit-ts4-thunderbolt-4-dock}}

For fewer devices a compact USB-C hub is enough:

{{produkt:anker-usb-c-hub-7-in-1-adapter}}

## How do I record working hours from home?

Working time counts at home too. In Germany employers must record it, following a [ruling of the Federal Labour Court](https://www.bundesarbeitsgericht.de/entscheidung/1-abr-22-21/) of 13 September 2022. For small teams a monthly record is often enough. The [Timesheet](https://tools.tracht-digital.de/en/tools/stundenzettel) deducts breaks, adds up daily and monthly totals and produces a PDF with fields for both signatures.

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

The top edge sits at eye level or slightly below, about an arm's length away.

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
        PostRewriter::apply($this->getAdapter()->getConnection(), self::SEED, self::POSTS);
    }

    public function down(): void
    {
        PostRewriter::revert($this->getAdapter()->getConnection(), self::SEED, self::POSTS);
    }
}
