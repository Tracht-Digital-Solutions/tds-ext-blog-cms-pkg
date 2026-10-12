<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Tds\Ext\BlogCms\Support\PostRewriter;

/**
 * Blog-CMS — rewrite the "office Wi-Fi" guide (DE + EN).
 *
 * Same editorial pass as `BlogCmsRewriteLaunchPosts`. Every `{{produkt:…}}`
 * line of the seed stays, with the shop slug of the row's language.
 */
final class BlogCmsRewritePostOfficeWifi extends AbstractMigration
{
    public const SEED = '20260728000012_blog_cms_seed_post_office_wifi.php';

    public const POSTS = [
        [
            'slug' => 'buero-wlan-das-ueberall-funktioniert',
            'lang' => 'de',
            'title' => 'Büro-WLAN, das überall funktioniert: Router, Mesh und Kabel',
            'excerpt' => 'Funklöcher im Besprechungsraum, abbrechende Videocalls am Fenster: Woran schlechtes Büro-WLAN meist liegt und welche Technik es zuverlässig behebt.',
            'meta' => 'Büro-WLAN richtig planen: wann ein Router reicht, wann Mesh, Access Points oder Powerline nötig sind, und wie Gäste per QR-Code ins eigene Netz kommen.',
            'body' => <<<'MD'
**Kurz gesagt:** Für ein kleines Büro auf einer Etage reicht meist ein guter Router an zentraler Stelle. Kommen Wände, Etagen oder viele Geräte dazu, braucht es Mesh oder Access Points an der Decke. Und was fest am Platz steht, gehört ans Kabel. Diese drei Regeln lösen die meisten WLAN-Probleme.

Die Produkte in diesem Beitrag sind Empfehlungen aus der Praxis. Die Links sind Partnerlinks: Kaufen Sie darüber, erhalte ich eine kleine Provision, der Preis ändert sich für Sie nicht.

## Woran liegt schlechtes WLAN im Büro meistens?

Selten am Internetanschluss. Meist an einem von drei Punkten:

- **Der Router steht falsch.** Im Keller, im Schrank oder hinter dem Monitor verliert das Signal schon vor der ersten Wand die Hälfte.
- **Es fehlen Funkpunkte.** Ein einzelner Router versorgt zuverlässig etwa 80 bis 100 Quadratmeter ohne dicke Wände.
- **Alles hängt am Funk.** Drucker, PCs und NAS teilen sich das WLAN mit Laptops und Handys, obwohl sie nie bewegt werden.

## Welcher Router passt zu welchem Anschluss?

In Deutschland ist die FRITZ!Box der Standard. Sie vereint Internet, WLAN und Telefonanlage und bekommt regelmäßig Sicherheits-Updates. Entscheidend ist der Anschluss.

Für DSL:

{{produkt:fritzbox-7690}}

Für Kabel-Internet, als Ersatz für das oft einfache Mietgerät des Anbieters:

{{produkt:fritzbox-6690-cable}}

## Wann brauche ich Mesh, wann Access Points?

**Mesh** verbindet mehrere Geräte zu einem einzigen WLAN, durch das Laptops und Handys automatisch wandern. Es passt, wenn kein Kabel zu den weiteren Funkpunkten führt. Zur FRITZ!Box ist der passende Repeater die einfachste Lösung:

{{produkt:fritz-repeater-3000-ax}}

Ohne FRITZ!Box oder für größere Flächen eignet sich ein Mesh-Set aus gleichwertigen Einheiten:

{{produkt:tp-link-deco-x50-mesh-set}}

**Access Points an der Decke** sind die professionelle Lösung für Praxen, Läden und Büros mit Kundenverkehr. Sie bekommen Strom und Netz über ein einziges Kabel (PoE):

{{produkt:tp-link-omada-eap650}}

Den Strom über das Netzwerkkabel liefert ein PoE-Switch:

{{produkt:netgear-gs308pp-poe-switch}}

## Was hilft, wenn kein Kabel verlegt werden kann?

Powerline überträgt das Netzwerk über die vorhandene Stromleitung. Für das Büro im Nebengebäude oder Dachgeschoss ist das oft stabiler als jede Funklösung:

{{produkt:devolo-magic-2-lan-triple-starter-kit}}

## Warum gehören feste Geräte ans Kabel?

Jedes Gerät im WLAN teilt sich die Funkzeit mit allen anderen. Ein Drucker oder PC am Kabel entlastet das WLAN und läuft selbst stabiler. Reichen die Anschlüsse am Router nicht, erweitert ein einfacher Switch das Netz:

{{produkt:tp-link-tl-sg108e-switch}}

## Wie richte ich ein sicheres Gäste-WLAN ein?

Gäste und private Handys gehören in ein eigenes Netz, getrennt von Rechnern mit Kundendaten. Die FRITZ!Box bringt dafür einen Gastzugang mit. Drei Schritte:

1. **Gastzugang einschalten** und vom Heimnetz trennen.
2. **Ein langes Zufallspasswort vergeben.** Der [Passwort-Generator](https://tools.tracht-digital.de/tools/passwort-generator) erzeugt es lokal im Browser.
3. **Einen QR-Code an den Empfang hängen.** Der [QR-Code-Generator](https://tools.tracht-digital.de/tools/qr-code-generator) erstellt einen WLAN-Code. Gäste scannen ihn und sind verbunden, ohne das Passwort abzutippen.

## Welche Lösung passt zu meinem Büro?

| Situation | Empfehlung |
|---|---|
| Ein Raum oder kleine Etage | Guter Router an zentraler Stelle |
| Mehrere Räume, kein Kabel | Router plus Mesh-Repeater |
| Große Fläche, Kundenverkehr | Access Points an der Decke mit PoE-Switch |
| Nebengebäude, Altbau | Powerline |
| Feste Geräte | Immer per Kabel, notfalls über einen Switch |

## Häufige Fragen

### Brauche ich Wi-Fi 7?

Heute selten. Wichtiger als der neueste Standard sind genug Funkpunkte an den richtigen Stellen.

### Ist ein Gäste-WLAN Pflicht?

Nicht gesetzlich, aber dringend empfohlen. Es trennt fremde Geräte von Ihren Rechnern mit Kundendaten.

### Wie sicher ist mein Router?

So sicher wie sein letztes Update. Automatische Updates einschalten und das Standardpasswort ändern sind die zwei wichtigsten Schritte. Das [BSI](https://www.bsi.bund.de/) gibt dazu verständliche Empfehlungen.

### Was tun, wenn Videocalls trotzdem abbrechen?

Den Rechner per Kabel anschließen. Weitere Tipps stehen im Beitrag [Besser klingen und aussehen im Videocall](/besser-klingen-und-aussehen-im-videocall).

### Kann ich das selbst einrichten?

Router und Mesh ja. Bei Access Points, VLANs und Gästenetzen lohnt sich Hilfe, weil Fehler hier nicht sichtbar sind, sondern nur unsicher.

Stand: Oktober 2026. Läuft Ihr Büro-WLAN trotz guter Technik nicht zuverlässig? Schreiben Sie mir kurz, wie Ihre Räume aufgeteilt sind. Ich sage Ihnen, wo der Engpass liegt.
MD,
        ],
        [
            'slug' => 'buero-wlan-das-ueberall-funktioniert',
            'lang' => 'en',
            'title' => 'Office Wi-Fi that works everywhere: router, mesh and cable',
            'excerpt' => 'Dead spots in the meeting room, video calls dropping by the window: what usually causes poor office Wi-Fi and which equipment fixes it reliably.',
            'meta' => 'Planning office Wi-Fi: when a router is enough, when mesh, access points or powerline are needed, and how guests join their own network by QR code.',
            'body' => <<<'MD'
**In short:** For a small office on one floor a good router in a central spot is usually enough. Once walls, floors or many devices come into play, you need mesh or ceiling access points. And anything that stays at a desk belongs on a cable. These three rules solve most Wi-Fi problems.

The products in this article are recommendations from practice. The links are affiliate links: if you buy through them, I earn a small commission and your price does not change.

## What usually causes poor office Wi-Fi?

Rarely the internet line. Usually one of three things:

- **The router is in the wrong place.** In the basement, a cupboard or behind the monitor the signal loses half its strength before the first wall.
- **There are too few access points.** A single router reliably covers about 80 to 100 square metres without thick walls.
- **Everything is on Wi-Fi.** Printers, PCs and the NAS share the air with laptops and phones, although they never move.

## Which router suits which connection?

In Germany the FRITZ!Box is the standard. It combines internet, Wi-Fi and a phone system and gets regular security updates. What decides is the line.

For DSL:

{{produkt:fritzbox-7690-router}}

For cable internet, replacing the provider's often basic rental device:

{{produkt:fritzbox-6690-cable-router}}

## When do I need mesh, when access points?

**Mesh** joins several devices into one Wi-Fi network that laptops and phones roam through automatically. It suits places where no cable reaches the further access points. With a FRITZ!Box the matching repeater is the simplest option:

{{produkt:fritz-repeater-3000-ax-mesh}}

Without a FRITZ!Box, or for larger spaces, a mesh set of equal units works well:

{{produkt:tp-link-deco-x50-mesh-3-pack}}

**Ceiling access points** are the professional solution for practices, shops and offices with customer traffic. They get power and network over a single cable (PoE):

{{produkt:tp-link-omada-eap650-access-point}}

A PoE switch supplies the power over the network cable:

{{produkt:netgear-gs308pp-poe-switch-8-port}}

## What helps if no cable can be laid?

Powerline carries the network over the existing electrical wiring. For an office in an annex or the attic it is often more stable than any wireless option:

{{produkt:devolo-magic-2-lan-triple-starter-kit-powerline}}

## Why do fixed devices belong on a cable?

Every device on Wi-Fi shares airtime with all the others. A printer or PC on a cable relieves the Wi-Fi and runs more stably itself. When the router runs out of ports, a simple switch extends the network:

{{produkt:tp-link-tl-sg108e-8-port-switch}}

## How do I set up a secure guest network?

Guests and private phones belong in a separate network, apart from computers holding client data. The FRITZ!Box has a built-in guest access for this. Three steps:

1. **Turn on guest access** and keep it apart from the main network.
2. **Set a long random password.** The [Password Generator](https://tools.tracht-digital.de/en/tools/passwort-generator) creates it locally in your browser.
3. **Put a QR code at reception.** The [QR Code Generator](https://tools.tracht-digital.de/en/tools/qr-code-generator) creates a Wi-Fi code. Guests scan it and are connected without typing the password.

## Which setup suits my office?

| Situation | Recommendation |
|---|---|
| One room or a small floor | A good router in a central spot |
| Several rooms, no cable | Router plus mesh repeater |
| Large space, customer traffic | Ceiling access points with a PoE switch |
| Annex, old building | Powerline |
| Fixed devices | Always on a cable, via a switch if needed |

## Frequently asked questions

### Do I need Wi-Fi 7?

Rarely today. Enough access points in the right places matter more than the newest standard.

### Is guest Wi-Fi mandatory?

Not by law, but strongly recommended. It keeps outside devices away from computers holding client data.

### How secure is my router?

As secure as its last update. Turning on automatic updates and changing the default password are the two most important steps. Germany's [BSI](https://www.bsi.bund.de/) publishes clear guidance on this.

### What if video calls still drop?

Connect the computer by cable. More tips are in [Sound and look better on video calls](/en/besser-klingen-und-aussehen-im-videocall).

### Can I set it up myself?

Router and mesh, yes. With access points, VLANs and guest networks help pays off, because mistakes here are not visible, only insecure.

Updated: October 2026. Is your office Wi-Fi unreliable despite good equipment? Write me briefly how your rooms are laid out. I will tell you where the bottleneck is.
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
