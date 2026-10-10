<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Tds\Ext\BlogCms\Support\PostSeeder;

/**
 * Blog-CMS — seed the "office Wi-Fi" guide (DE + EN).
 *
 * Embeds TDShop affiliate products with `{{produkt:<slug>}}` lines, which the
 * journal renders as product cards (tds-blog-frontend `productShortcodes.ts`).
 * The slugs are the shop's, per language — DE and EN slugs differ — seeded by
 * tds-ext-shop `php/db/seed/affiliate*.php`. A product that is not released
 * yet renders nothing; the article reads on without it.
 */
final class BlogCmsSeedPostOfficeWifi extends AbstractMigration
{
    public const POSTS = [
        [
            'slug' => 'buero-wlan-das-ueberall-funktioniert',
            'lang' => 'de',
            'category' => 'Ausstattung',
            'title' => 'Büro-WLAN, das überall funktioniert: Router, Mesh und Kabel richtig kombiniert',
            'excerpt' => 'Funklöcher im Besprechungsraum, abbrechende Videocalls am Fenster: Woran schlechtes Büro-WLAN meistens liegt und welche Technik es zuverlässig behebt.',
            'meta' => 'Büro-WLAN richtig planen: wann ein Router reicht, wann Mesh, Access Points oder Powerline nötig sind, und welche Geräte sich für kleine Betriebe bewähren.',
            'tags' => 'wlan, netzwerk, router, mesh',
            'published' => '2026-09-24 09:00:00',
            'body' => <<<'MD'
**Kurz gesagt:** Für ein kleines Büro auf einer Etage reicht meist ein guter Router. Sobald Wände, Etagen oder viele Geräte dazukommen, braucht es Mesh oder Access Points an der Decke. Und was fest am Platz steht, gehört ans Kabel. Diese drei Regeln lösen die meisten WLAN-Probleme.

Die Produkte in diesem Beitrag sind Empfehlungen aus der Praxis. Die Links sind Partnerlinks: Kaufen Sie darüber, erhalte ich eine kleine Provision, der Preis ändert sich für Sie nicht.

## Woran liegt schlechtes WLAN im Büro meistens?

Selten am Internetanschluss. Meist an einem von drei Punkten:

- **Der Router steht falsch.** Im Keller, im Schrank oder hinter dem Monitor verliert das Signal schon vor der ersten Wand die Hälfte.
- **Es fehlen Funkpunkte.** Ein einzelner Router versorgt zuverlässig etwa 80 bis 100 Quadratmeter ohne dicke Wände. Alles darüber braucht weitere Geräte.
- **Alles hängt am Funk.** Drucker, Desktop-PCs und das NAS teilen sich das WLAN mit Laptops und Handys, obwohl sie nie bewegt werden.

## Welcher Router passt zu welchem Anschluss?

Der Router ist das Herz des Netzes. In Deutschland ist die FRITZ!Box der Standard, weil sie Internet, WLAN und Telefonanlage vereint und regelmäßig Sicherheits-Updates bekommt. Entscheidend ist der Anschluss: DSL oder Kabel.

Für DSL-Anschlüsse:

{{produkt:fritzbox-7690}}

Für Kabel-Internet ersetzt die eigene Box das oft einfache Mietgerät des Anbieters:

{{produkt:fritzbox-6690-cable}}

## Wann brauche ich Mesh, wann Access Points?

**Mesh** verbindet mehrere Geräte zu einem einzigen WLAN, durch das Laptops und Handys automatisch wandern. Es eignet sich, wenn kein Kabel zu den weiteren Funkpunkten führt. Bei einer FRITZ!Box ist der passende Repeater die einfachste Lösung:

{{produkt:fritz-repeater-3000-ax}}

Wer keine FRITZ!Box nutzt oder größere Flächen versorgt, ist mit einem Mesh-Set aus gleichwertigen Einheiten gut beraten:

{{produkt:tp-link-deco-x50-mesh-set}}

**Access Points an der Decke** sind die professionelle Lösung für Praxen, Läden und Büros mit Kundenverkehr. Sie hängen dort, wo sich Funk am besten verteilt, und bekommen Strom und Netz über ein einziges Kabel (PoE).

{{produkt:tp-link-omada-eap650}}

Damit Access Points über das Netzwerkkabel Strom bekommen, braucht es einen PoE-Switch:

{{produkt:netgear-gs308pp-poe-switch}}

## Was hilft, wenn kein Kabel verlegt werden kann?

Powerline überträgt das Netzwerk über die vorhandene Stromleitung. Für das Büro im Nebengebäude oder im Dachgeschoss ist das oft stabiler als jede Funklösung:

{{produkt:devolo-magic-2-lan-triple-starter-kit}}

## Warum gehören feste Geräte ans Kabel?

Jedes Gerät im WLAN teilt sich die Funkzeit mit allen anderen. Ein Drucker oder PC am Kabel entlastet das WLAN und läuft selbst stabiler. Gehen die Ports am Router aus, erweitert ein einfacher Switch das Netz:

{{produkt:tp-link-tl-sg108e-switch}}

## Welche Lösung passt zu meinem Büro?

| Situation | Empfehlung |
|---|---|
| Ein Raum oder kleine Etage | Guter Router an zentraler Stelle |
| Mehrere Räume, kein Kabel | Router plus Mesh-Repeater |
| Große Fläche, Kunden-WLAN | Access Points an der Decke mit PoE-Switch |
| Nebengebäude, Altbau | Powerline |
| Feste Geräte | Immer per Kabel, notfalls über einen Switch |

## Häufige Fragen

### Brauche ich Wi-Fi 7?

Heute selten. Wichtiger als der neueste Standard ist, dass genug Funkpunkte an den richtigen Stellen stehen.

### Ist ein Gäste-WLAN Pflicht?

Nicht gesetzlich, aber dringend empfohlen. Gäste und private Handys gehören in ein eigenes Netz, getrennt von Rechnern mit Kundendaten.

### Wie sicher ist mein Router?

So sicher wie sein letztes Update. Automatische Updates einschalten und das Standardpasswort ändern sind die zwei wichtigsten Schritte. Das [BSI](https://www.bsi.bund.de/) gibt dazu verständliche Empfehlungen.

### Kann ich das selbst einrichten?

Router und Mesh ja. Bei Access Points, VLANs und Gästenetzen lohnt sich Hilfe, weil Fehler hier nicht sichtbar sind, sondern nur unsicher.

Stand: Oktober 2026. Wenn Ihr Büro-WLAN trotz guter Technik nicht zuverlässig läuft, schreiben Sie mir kurz, wie Ihre Räume aufgeteilt sind. Ich sage Ihnen, wo der Engpass liegt.
MD,
        ],
        [
            'slug' => 'buero-wlan-das-ueberall-funktioniert',
            'lang' => 'en',
            'category' => 'Equipment',
            'title' => 'Office Wi-Fi that works everywhere: combining router, mesh and cable',
            'excerpt' => 'Dead spots in the meeting room, video calls dropping by the window: what usually causes poor office Wi-Fi and which equipment fixes it reliably.',
            'meta' => 'Planning office Wi-Fi: when a router is enough, when mesh, access points or powerline are needed, and which devices suit small businesses.',
            'tags' => 'wifi, network, router, mesh',
            'published' => '2026-09-24 09:00:00',
            'body' => <<<'MD'
**In short:** For a small office on one floor a good router is usually enough. As soon as walls, floors or many devices come into play, you need mesh or ceiling access points. And anything that stays at a desk belongs on a cable. These three rules solve most Wi-Fi problems.

The products in this article are recommendations from practice. The links are affiliate links: if you buy through them, I earn a small commission and your price does not change.

## What usually causes poor office Wi-Fi?

Rarely the internet line. Usually one of three things:

- **The router is in the wrong place.** In the basement, a cupboard or behind the monitor the signal loses half its strength before the first wall.
- **There are too few access points.** A single router reliably covers about 80 to 100 square metres without thick walls. Anything larger needs more devices.
- **Everything is on Wi-Fi.** Printers, desktop PCs and the NAS share the air with laptops and phones, although they never move.

## Which router suits which connection?

The router is the heart of the network. In Germany the FRITZ!Box is the standard because it combines internet, Wi-Fi and a phone system and gets regular security updates. What decides is the line: DSL or cable.

For DSL lines:

{{produkt:fritzbox-7690-router}}

On cable internet your own box replaces the provider's often basic rental device:

{{produkt:fritzbox-6690-cable-router}}

## When do I need mesh, when access points?

**Mesh** joins several devices into one Wi-Fi network that laptops and phones roam through automatically. It suits places where no cable reaches the further access points. With a FRITZ!Box the matching repeater is the simplest option:

{{produkt:fritz-repeater-3000-ax-mesh}}

Without a FRITZ!Box, or for larger spaces, a mesh set of equal units is a good choice:

{{produkt:tp-link-deco-x50-mesh-3-pack}}

**Ceiling access points** are the professional solution for practices, shops and offices with customer traffic. They hang where radio spreads best and get power and network over a single cable (PoE).

{{produkt:tp-link-omada-eap650-access-point}}

To power access points over the network cable you need a PoE switch:

{{produkt:netgear-gs308pp-poe-switch-8-port}}

## What helps if no cable can be laid?

Powerline carries the network over the existing electrical wiring. For an office in an annex or the attic it is often more stable than any wireless option:

{{produkt:devolo-magic-2-lan-triple-starter-kit-powerline}}

## Why do fixed devices belong on a cable?

Every device on Wi-Fi shares airtime with all the others. A printer or PC on a cable relieves the Wi-Fi and runs more stably itself. When the router runs out of ports, a simple switch extends the network:

{{produkt:tp-link-tl-sg108e-8-port-switch}}

## Which setup suits my office?

| Situation | Recommendation |
|---|---|
| One room or a small floor | A good router in a central spot |
| Several rooms, no cable | Router plus mesh repeater |
| Large space, guest Wi-Fi | Ceiling access points with a PoE switch |
| Annex, old building | Powerline |
| Fixed devices | Always on a cable, via a switch if needed |

## Frequently asked questions

### Do I need Wi-Fi 7?

Rarely today. More important than the newest standard is having enough access points in the right places.

### Is guest Wi-Fi mandatory?

Not by law, but strongly recommended. Guests and private phones belong in a separate network, apart from computers holding client data.

### How secure is my router?

As secure as its last update. Turning on automatic updates and changing the default password are the two most important steps. Germany's [BSI](https://www.bsi.bund.de/) publishes clear guidance on this.

### Can I set it up myself?

Router and mesh, yes. With access points, VLANs and guest networks help pays off, because mistakes here are not visible, only insecure.

Updated: October 2026. If your office Wi-Fi is unreliable despite good equipment, write me briefly how your rooms are laid out. I will tell you where the bottleneck is.
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
