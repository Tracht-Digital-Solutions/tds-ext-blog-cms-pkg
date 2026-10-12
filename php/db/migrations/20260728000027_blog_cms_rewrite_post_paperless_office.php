<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Tds\Ext\BlogCms\Support\PostRewriter;

/**
 * Blog-CMS — rewrite the "paperless office" guide (DE + EN).
 *
 * Same editorial pass as `BlogCmsRewriteLaunchPosts`. Every `{{produkt:…}}`
 * line of the seed stays, with the shop slug of the row's language.
 */
final class BlogCmsRewritePostPaperlessOffice extends AbstractMigration
{
    public const SEED = '20260728000017_blog_cms_seed_post_paperless_office.php';

    public const POSTS = [
        [
            'slug' => 'papierloses-buero-in-vier-schritten',
            'lang' => 'de',
            'title' => 'Papierloses Büro in vier Schritten: So gelingt der Umstieg',
            'excerpt' => 'Ordnerwände, Suchen nach Belegen, Platzmangel: Ein papierarmes Büro spart Zeit und Fläche. So gelingt der Umstieg ohne großes Projekt.',
            'meta' => 'Papierloses Büro einfach umsetzen: scannen, ablegen, finden, vernichten. Mit Dokumentenscanner, Aufbewahrungsfristen und PDF-Werkzeugen im Browser.',
            'body' => <<<'MD'
**Kurz gesagt:** Ein papierloses Büro entsteht in vier Schritten: eingehendes Papier sofort scannen, nach einer festen Struktur ablegen, per Volltextsuche wiederfinden und das Original sicher vernichten, sobald keine Aufbewahrungspflicht dagegensteht. Das Herzstück ist ein schneller Dokumentenscanner, der beidseitig scannt und Text erkennt.

Die Produkte in diesem Beitrag sind Empfehlungen aus der Praxis. Die Links sind Partnerlinks: Kaufen Sie darüber, erhalte ich eine kleine Provision, der Preis ändert sich für Sie nicht.

## Schritt 1: Womit scanne ich am schnellsten?

Mit einem Dokumentenscanner mit Einzug, nicht mit dem Flachbett des Multifunktionsdruckers. Ein Stapel Belege ist dann in einer Minute beidseitig erfasst, mit Texterkennung (OCR) und direkt im richtigen Ordner:

{{produkt:scansnap-ix1600}}

Kein Scanner zur Hand? Dann geht es auch mit dem Handy:

- Fotografierte Belege fasst [Bilder zu PDF](https://tools.tracht-digital.de/tools/bilder-zu-pdf) zu einem sauberen Dokument zusammen.
- Den Text aus Fotos und Bildscans liest die [Texterkennung (OCR)](https://tools.tracht-digital.de/tools/texterkennung) heraus, damit die Suche ihn später findet.

## Schritt 2: Wie lege ich digitale Dokumente sinnvoll ab?

Eine einfache Struktur schlägt jedes ausgefeilte System:

- **Ordner nach Jahr und Bereich**, zum Beispiel `2026/Eingangsrechnungen`.
- **Dateiname mit Datum vorn:** `2026-10-02_Telekom_Rechnung.pdf`. So sortiert sich alles von selbst.
- **Ein Eingangsordner** für alles Neue, der jeden Freitag geleert wird.

Mehrere Scans zu einem Vorgang führen die [PDF-Werkzeuge](https://tools.tracht-digital.de/tools/pdf-werkzeuge) zu einer Datei zusammen. Sie teilen auch Sammelscans auf und drehen schiefe Seiten.

Belege für die Buchhaltung gehen am besten direkt an die Buchhaltungssoftware oder die Steuerkanzlei, statt doppelt abgelegt zu werden. Ist eine Datei zu groß für den Upload, verkleinert [PDF komprimieren](https://tools.tracht-digital.de/tools/pdf-komprimieren) sie, ohne den Text anzutasten.

## Schritt 3: Wie finde ich Dokumente wieder?

Mit Volltextsuche. Weil der Text erkannt wurde, findet die Suche in Windows, macOS oder auf dem NAS auch einen Kundennamen mitten im Dokument. Ordner und Dateinamen sind dann nur noch die Rückfallebene.

Gescannte Dokumente gehören in die Datensicherung, am besten auf ein NAS mit automatischem Backup:

{{produkt:synology-ds224-plus}}

Wie die Sicherung richtig aufgebaut wird, steht im Beitrag [Datensicherung mit der 3-2-1-Regel](/datensicherung-3-2-1-regel-kleine-betriebe).

## Schritt 4: Wie vernichte ich Papier datenschutzgerecht?

Papier mit personenbezogenen Daten gehört nicht ins Altpapier. Ein Partikelschnitt-Schredder der Sicherheitsstufe P-4 genügt für die meisten Büros:

{{produkt:fellowes-powershred-8cd}}

**Wichtig:** Manche Belege müssen trotz Scan im Original aufbewahrt werden. Klären Sie mit Ihrer Steuerberatung, welche, bevor Sie Originale vernichten.

## Was ist mit Ordnerrücken und Etiketten?

Was physisch bleibt, etwa unterschriebene Verträge, wird klar beschriftet. Ein Etikettendrucker im Netzwerk druckt Ordnerrücken, Adressen und Inventaraufkleber:

{{produkt:brother-ql-820nwbc}}

Für Etikettenbögen im normalen Drucker erzeugt [Etiketten drucken](https://tools.tracht-digital.de/tools/etiketten-drucken) ein druckfertiges PDF.

## Häufige Fragen

### Darf ich Belege nach dem Scannen wegwerfen?

Oft ja, wenn das Scannen nach einer dokumentierten Verfahrensweise erfolgt (GoBD). Es gibt aber Ausnahmen. Klären Sie das vorab mit Ihrer Steuerberatung.

### Wie lange muss ich Unterlagen aufbewahren?

Nach [§ 147 AO](https://www.gesetze-im-internet.de/ao_1977/__147.html) gelten seit 2025 meist acht Jahre für Buchungsbelege wie Rechnungen, zehn Jahre für Bücher und Jahresabschlüsse und sechs Jahre für Geschäftsbriefe.

### Brauche ich ein Dokumentenmanagement-System?

Für kleine Betriebe meist nicht. Eine klare Ordnerstruktur mit Volltextsuche reicht für Tausende Dokumente.

### Was kostet der Einstieg?

Ein guter Dokumentenscanner und ein Schredder kosten zusammen etwa 500 bis 700 Euro. Die Zeitersparnis bei der Belegsuche holt das meist im ersten Jahr wieder herein.

Stand: Oktober 2026. Sie möchten Ihr Büro papierarm aufstellen? Ich helfe bei Struktur, Scanner-Einrichtung und der Anbindung an Ihre Buchhaltung.
MD,
        ],
        [
            'slug' => 'papierloses-buero-in-vier-schritten',
            'lang' => 'en',
            'title' => 'A paperless office in four steps: scan, file, find, shred',
            'excerpt' => 'Walls of binders, hunting for receipts, no space: a low-paper office saves time and room. Here is how to switch without a big project.',
            'meta' => 'Going paperless made simple: scan, file, find, shred. With a document scanner, retention periods and PDF tools that run in your browser.',
            'body' => <<<'MD'
**In short:** A paperless office comes about in four steps: scan incoming paper right away, file it in a fixed structure, find it again with full-text search and shred the original securely once no retention duty speaks against it. The heart of it is a fast document scanner that scans both sides and recognises text.

The products in this article are recommendations from practice. The links are affiliate links: if you buy through them, I earn a small commission and your price does not change.

## Step 1: What is the fastest way to scan?

A document scanner with a feeder, not the flatbed of a multifunction printer. A stack of receipts is then captured on both sides in a minute, with text recognition (OCR), straight into the right folder:

{{produkt:scansnap-ix1600-document-scanner}}

No scanner to hand? A phone works too:

- [Images to PDF](https://tools.tracht-digital.de/en/tools/bilder-zu-pdf) combines photographed receipts into one clean document.
- [Text Recognition (OCR)](https://tools.tracht-digital.de/en/tools/texterkennung) reads the text from photos and image scans, so search can find it later.

## Step 2: How do I file digital documents sensibly?

A simple structure beats any elaborate system:

- **Folders by year and area**, for example `2026/Incoming invoices`.
- **File names starting with the date:** `2026-10-02_Telekom_Invoice.pdf`. Everything sorts itself.
- **One inbox folder** for everything new, emptied every Friday.

The [PDF Tools](https://tools.tracht-digital.de/en/tools/pdf-werkzeuge) merge several scans of one case into a single file. They also split batch scans and rotate crooked pages.

Accounting receipts are best handed straight to your accounting software or tax adviser instead of being filed twice. If a file is too large to upload, [Compress PDF](https://tools.tracht-digital.de/en/tools/pdf-komprimieren) shrinks it without touching the text.

## Step 3: How do I find documents again?

With full-text search. Because the text was recognised, search in Windows, macOS or on the NAS finds a client's name in the middle of a document. Folders and file names are then only the fallback.

Scanned documents belong in your backup, ideally on a NAS with automatic backups:

{{produkt:synology-ds224-plus-nas}}

How to set the backup up properly is covered in [Backups with the 3-2-1 rule](/en/datensicherung-3-2-1-regel-kleine-betriebe).

## Step 4: How do I destroy paper in line with data protection?

Paper with personal data does not belong in the recycling bin. A particle-cut shredder at security level P-4 is enough for most offices:

{{produkt:fellowes-powershred-8cd-shredder}}

**Important:** some documents must be kept in the original even after scanning. Check with your tax adviser which ones before destroying originals.

## What about binder spines and labels?

What stays physical, such as signed contracts, gets clear labels. A network label printer prints binder spines, addresses and inventory stickers:

{{produkt:brother-ql-820nwbc-label-printer}}

For label sheets in an ordinary printer, [Print Labels](https://tools.tracht-digital.de/en/tools/etiketten-drucken) creates a print-ready PDF.

## Frequently asked questions

### May I throw receipts away after scanning?

Often yes, in Germany if scanning follows a documented procedure (GoBD). There are exceptions, though. Clarify this with your tax adviser first.

### How long do I have to keep records?

Under Germany's [§ 147 AO](https://www.gesetze-im-internet.de/ao_1977/__147.html), since 2025 usually eight years for accounting records such as invoices, ten years for books and annual accounts and six years for business letters.

### Do I need a document management system?

Small businesses usually do not. A clear folder structure with full-text search handles thousands of documents.

### What does it cost to start?

A good document scanner and a shredder together cost about 500 to 700 euros. The time saved looking for receipts usually earns that back in the first year.

Updated: October 2026. Want to set up your office with less paper? I help with the structure, the scanner setup and the link to your accounting.
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
