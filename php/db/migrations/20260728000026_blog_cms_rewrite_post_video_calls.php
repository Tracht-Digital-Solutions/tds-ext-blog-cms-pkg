<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Tds\Ext\BlogCms\Support\PostRewriter;

/**
 * Blog-CMS — rewrite the "video calls" guide (DE + EN).
 *
 * Same editorial pass as `BlogCmsRewriteLaunchPosts`. Every `{{produkt:…}}`
 * line of the seed stays, with the shop slug of the row's language.
 */
final class BlogCmsRewritePostVideoCalls extends AbstractMigration
{
    public const SEED = '20260728000016_blog_cms_seed_post_video_calls.php';

    public const POSTS = [
        [
            'slug' => 'besser-klingen-und-aussehen-im-videocall',
            'lang' => 'de',
            'title' => 'Besser klingen und aussehen im Videocall: Ton, Licht, Kamera',
            'excerpt' => 'Im Videocall entscheidet der erste Eindruck. Mit Mikrofon, Licht und Kamera in der richtigen Reihenfolge wirken Sie in Kundengesprächen sofort professioneller.',
            'meta' => 'Professionell im Videocall: warum Ton wichtiger ist als Bild, welches Headset, Licht und welche Webcam sich lohnen und wie Sie Unterlagen sicher teilen.',
            'body' => <<<'MD'
**Kurz gesagt:** Im Videocall zählt zuerst der Ton, dann das Licht und erst dann die Kamera. Ein Headset oder Mikrofon nah am Mund, Licht von vorne und eine Webcam auf Augenhöhe reichen für einen professionellen Auftritt. Teure 4K-Kameras bringen ohne gutes Licht wenig.

Die Produkte in diesem Beitrag sind Empfehlungen aus der Praxis. Die Links sind Partnerlinks: Kaufen Sie darüber, erhalte ich eine kleine Provision, der Preis ändert sich für Sie nicht.

## Warum ist Ton wichtiger als Bild?

Ein unscharfes Bild verzeiht das Gegenüber. Hallender, abgehackter Ton strengt dagegen an, und das Gespräch wirkt unprofessionell. Das eingebaute Laptop-Mikrofon sitzt weit weg vom Mund und nimmt jeden Tastenanschlag auf.

## Schritt 1: Welches Mikrofon ist das richtige?

**Headset**, wenn Sie oft telefonieren oder in einer lauten Umgebung arbeiten. Das Mikrofon sitzt nah am Mund, Störgeräusche bleiben draußen.

{{produkt:jabra-evolve2-30-se}}

Für viele Stunden am Tag und Großraumbüros, mit aktiver Geräuschunterdrückung:

{{produkt:jabra-evolve2-55}}

**Tischmikrofon**, wenn Sie kein Headset tragen möchten und viel präsentieren, etwa in Webinaren:

{{produkt:rode-nt-usb-mini}}

**Konferenzlautsprecher**, wenn mehrere Personen an einem Tisch sitzen:

{{produkt:anker-powerconf-s3}}

## Schritt 2: Wie leuchte ich mich richtig aus?

Das Licht kommt von vorne, leicht seitlich und etwas oberhalb der Augen. Ein Fenster im Rücken macht Sie zur Silhouette. Eine LED-Leuchte hinter dem Monitor löst das zuverlässig, auch an trüben Tagen:

{{produkt:elgato-key-light-air}}

## Schritt 3: Welche Webcam lohnt sich?

Für die meisten Gespräche ist Full HD genug, wenn das Licht stimmt:

{{produkt:logitech-c920-hd-pro}}

Wer oft präsentiert, Aufnahmen macht oder bei schwachem Licht ein schärferes Bild will:

{{produkt:logitech-brio-4k-webcam}}

## Wie teile ich Unterlagen, ohne zu viel zu zeigen?

Eine Bildschirmfreigabe zeigt oft mehr als gedacht: Mails, Benachrichtigungen, andere Kunden. Drei Wege sind sicherer:

- **Nur ein Fenster teilen**, nicht den ganzen Bildschirm.
- **Einzelne Seiten als Bild zeigen.** [PDF zu Bildern](https://tools.tracht-digital.de/tools/pdf-zu-bildern) rechnet ausgewählte Seiten als PNG oder JPG heraus.
- **Große Dateien vorher verkleinern.** [PDF komprimieren](https://tools.tracht-digital.de/tools/pdf-komprimieren) macht Anhänge klein genug für den Chat im Call.

Beide Werkzeuge laufen im Browser. Das Dokument verlässt Ihren Rechner nicht.

## Was kostet nichts und hilft trotzdem?

- **Kamera auf Augenhöhe:** den Laptop auf einen Bücherstapel oder Ständer stellen.
- **Ruhiger Hintergrund** statt Weichzeichner, der Haare und Hände verwischt.
- **Kabel statt WLAN** für den Rechner, wenn das Bild oft ruckelt. Mehr im Beitrag [Büro-WLAN, das überall funktioniert](/buero-wlan-das-ueberall-funktioniert).
- **Benachrichtigungen aus**, damit keine Pop-ups in der Freigabe erscheinen.

## Häufige Fragen

### Reichen Bluetooth-Kopfhörer für Videocalls?

Zum Zuhören ja. Beim Sprechen schalten viele Bluetooth-Kopfhörer in einen Modus mit deutlich schlechterem Ton. Ein Headset mit eigenem USB-Empfänger umgeht das.

### Brauche ich einen Greenscreen?

Nein. Ein aufgeräumter Hintergrund wirkt natürlicher als ein virtueller.

### Funktioniert das mit Teams, Zoom und Google Meet?

Ja. Alle genannten Geräte arbeiten ohne Treiber mit den gängigen Videokonferenz-Programmen.

### Was bringt den größten Sprung für wenig Geld?

Ein Headset. Es kostet weniger als eine gute Webcam und verbessert den Eindruck deutlich stärker.

Stand: Oktober 2026. Sie wollen Ihren Besprechungsraum oder Ihr Team für Videocalls ausstatten? Ich stelle Ihnen die passende Technik zusammen.
MD,
        ],
        [
            'slug' => 'besser-klingen-und-aussehen-im-videocall',
            'lang' => 'en',
            'title' => 'Sound and look better on video calls: audio, light, camera',
            'excerpt' => 'On a video call the first impression decides. With microphone, light and camera in the right order you come across as more professional at once.',
            'meta' => 'Look professional on video calls: why sound beats picture, which headset, light and webcam are worth it and how to share documents safely.',
            'body' => <<<'MD'
**In short:** On a video call sound comes first, then light, and only then the camera. A headset or microphone close to your mouth, light from the front and a webcam at eye level are enough for a professional appearance. Expensive 4K cameras achieve little without good light.

The products in this article are recommendations from practice. The links are affiliate links: if you buy through them, I earn a small commission and your price does not change.

## Why is sound more important than picture?

People forgive a blurry picture. Echoing, choppy sound is tiring and makes the conversation feel unprofessional. The built-in laptop microphone sits far from your mouth and picks up every keystroke.

## Step 1: Which microphone is the right one?

**A headset** if you are on calls a lot or work somewhere noisy. The microphone sits close to your mouth and background noise stays out.

{{produkt:jabra-evolve2-30-se-headset}}

For many hours a day and open-plan offices, with active noise cancellation:

{{produkt:jabra-evolve2-55-headset}}

**A desk microphone** if you would rather not wear a headset and present a lot, for example in webinars:

{{produkt:rode-nt-usb-mini-microphone}}

**A conference speakerphone** if several people sit at one table:

{{produkt:anker-powerconf-s3-speakerphone}}

## Step 2: How do I light myself properly?

Light comes from the front, slightly to the side and a little above eye level. A window behind you turns you into a silhouette. An LED light behind the monitor fixes that reliably, even on grey days:

{{produkt:elgato-key-light-air-led}}

## Step 3: Which webcam is worth it?

For most calls Full HD is enough when the light is right:

{{produkt:logitech-c920-hd-pro-webcam}}

If you present often, record videos or want a sharper picture in low light:

{{produkt:logitech-brio-4k}}

## How do I share documents without showing too much?

Screen sharing often shows more than intended: emails, notifications, other clients. Three safer ways:

- **Share one window**, not the whole screen.
- **Show single pages as images.** [PDF to Images](https://tools.tracht-digital.de/en/tools/pdf-zu-bildern) exports selected pages as PNG or JPG.
- **Shrink large files first.** [Compress PDF](https://tools.tracht-digital.de/en/tools/pdf-komprimieren) makes attachments small enough for the call chat.

Both tools run in the browser. The document never leaves your computer.

## What costs nothing and still helps?

- **Camera at eye level:** put the laptop on a stack of books or a stand.
- **A calm background** instead of blur that smears hair and hands.
- **Cable instead of Wi-Fi** for the computer if the picture often stutters. More in [Office Wi-Fi that works everywhere](/en/buero-wlan-das-ueberall-funktioniert).
- **Notifications off**, so no pop-ups appear while sharing.

## Frequently asked questions

### Are Bluetooth earphones good enough for video calls?

For listening, yes. When you speak, many Bluetooth earphones switch to a mode with clearly worse sound. A headset with its own USB receiver avoids that.

### Do I need a green screen?

No. A tidy background looks more natural than a virtual one.

### Does this work with Teams, Zoom and Google Meet?

Yes. All devices mentioned work without drivers in the common video conferencing apps.

### What gives the biggest improvement for little money?

A headset. It costs less than a good webcam and improves the impression far more.

Updated: October 2026. Want to equip your meeting room or your team for video calls? I will put together the right equipment for you.
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
