<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Tds\Ext\BlogCms\Support\PostSeeder;

/**
 * Blog-CMS — seed the "better video calls" guide (DE + EN).
 *
 * Product lines `{{produkt:<slug>}}` use the shop slug of the row's language;
 * see `20260728000012_blog_cms_seed_post_office_wifi.php`.
 */
final class BlogCmsSeedPostVideoCalls extends AbstractMigration
{
    public const POSTS = [
        [
            'slug' => 'besser-klingen-und-aussehen-im-videocall',
            'lang' => 'de',
            'category' => 'Ausstattung',
            'title' => 'Besser klingen und aussehen im Videocall: Ton, Licht und Kamera in drei Schritten',
            'excerpt' => 'Im Videocall entscheidet der erste Eindruck. Mit Mikrofon, Licht und Kamera in der richtigen Reihenfolge wirken Sie in Kundengesprächen sofort professioneller.',
            'meta' => 'Professionell im Videocall: Warum Ton wichtiger ist als Bild, welches Headset, Mikrofon, Licht und welche Webcam sich lohnen. Praxis-Tipps für Selbstständige.',
            'tags' => 'videocall, webcam, headset, mikrofon',
            'published' => '2026-09-17 09:00:00',
            'body' => <<<'MD'
**Kurz gesagt:** Im Videocall zählt zuerst der Ton, dann das Licht, erst dann die Kamera. Ein Headset oder ein Mikrofon nah am Mund, ein Licht von vorne und eine Webcam auf Augenhöhe reichen für einen professionellen Auftritt. Teure 4K-Kameras bringen ohne gutes Licht wenig.

Die Produkte in diesem Beitrag sind Empfehlungen aus der Praxis. Die Links sind Partnerlinks: Kaufen Sie darüber, erhalte ich eine kleine Provision, der Preis ändert sich für Sie nicht.

## Warum ist Ton wichtiger als Bild?

Ein unscharfes Bild verzeiht das Gegenüber. Hallender, abgehackter Ton dagegen strengt an, und das Gespräch wirkt unprofessionell. Das eingebaute Laptop-Mikrofon sitzt weit weg vom Mund und nimmt jeden Tastenanschlag auf.

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

Wer oft präsentiert, Aufnahmen macht oder ein schärferes Bild bei schwachem Licht will:

{{produkt:logitech-brio-4k-webcam}}

## Was kostet nichts und hilft trotzdem?

- **Kamera auf Augenhöhe:** Laptop auf einen Bücherstapel oder Ständer stellen.
- **Ruhiger Hintergrund** statt Weichzeichner, der Haare und Hände verwischt.
- **Kabel statt WLAN** für den Rechner, wenn das Bild oft ruckelt.
- **Benachrichtigungen aus**, damit keine Pop-ups in der Bildschirmfreigabe erscheinen.

## Häufige Fragen

### Reichen Bluetooth-Kopfhörer für Videocalls?

Zum Zuhören ja. Beim Sprechen schalten viele Bluetooth-Kopfhörer in einen Modus mit deutlich schlechterem Ton. Ein Headset mit eigenem USB-Empfänger umgeht das.

### Brauche ich einen Greenscreen?

Nein. Ein aufgeräumter Hintergrund wirkt natürlicher als ein virtueller.

### Funktioniert das mit Teams, Zoom und Google Meet?

Ja. Alle genannten Geräte arbeiten ohne Treiber mit den gängigen Videokonferenz-Programmen.

### Was bringt der größte Sprung für wenig Geld?

Ein Headset. Es kostet weniger als eine gute Webcam und verbessert den Eindruck deutlich stärker.

Stand: Oktober 2026. Sie wollen Ihren Besprechungsraum oder Ihr Team für Videocalls ausstatten? Ich stelle Ihnen die passende Technik zusammen.
MD,
        ],
        [
            'slug' => 'besser-klingen-und-aussehen-im-videocall',
            'lang' => 'en',
            'category' => 'Equipment',
            'title' => 'Sound and look better on video calls: audio, light and camera in three steps',
            'excerpt' => 'On a video call the first impression decides. With microphone, light and camera in the right order you come across as more professional in client calls at once.',
            'meta' => 'Look professional on video calls: why sound matters more than picture, and which headset, microphone, light and webcam are worth it. Tips for freelancers.',
            'tags' => 'video-call, webcam, headset, microphone',
            'published' => '2026-09-17 09:00:00',
            'body' => <<<'MD'
**In short:** On a video call sound comes first, then light, and only then the camera. A headset or a microphone close to your mouth, light from the front and a webcam at eye level are enough for a professional appearance. Expensive 4K cameras achieve little without good light.

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

## What costs nothing and still helps?

- **Camera at eye level:** put the laptop on a stack of books or a stand.
- **A calm background** instead of blur that smears hair and hands.
- **Cable instead of Wi-Fi** for the computer if the picture often stutters.
- **Notifications off**, so no pop-ups appear while sharing your screen.

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
        PostSeeder::insert($this->getAdapter()->getConnection(), self::POSTS);
    }

    public function down(): void
    {
        PostSeeder::remove($this->getAdapter()->getConnection(), self::POSTS);
    }
}
