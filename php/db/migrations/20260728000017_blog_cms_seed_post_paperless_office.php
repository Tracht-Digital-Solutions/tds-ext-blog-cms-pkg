<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Tds\Ext\BlogCms\Support\PostSeeder;

/**
 * Blog-CMS — seed the "paperless office" guide (DE + EN).
 *
 * Product lines `{{produkt:<slug>}}` use the shop slug of the row's language;
 * see `20260728000012_blog_cms_seed_post_office_wifi.php`.
 */
final class BlogCmsSeedPostPaperlessOffice extends AbstractMigration
{
    public const POSTS = [
        [
            'slug' => 'papierloses-buero-in-vier-schritten',
            'lang' => 'de',
            'category' => 'Digitalisierung',
            'title' => 'Papierloses Büro in vier Schritten: Scannen, ablegen, finden, vernichten',
            'excerpt' => 'Ordnerwände, Suchen nach Belegen, Platzmangel: Ein papierarmes Büro spart Zeit und Fläche. So gelingt der Umstieg ohne großes Projekt.',
            'meta' => 'Papierloses Büro einfach umsetzen: Dokumentenscanner, Ablagestruktur, Aufbewahrungsfristen und sichere Aktenvernichtung. Leitfaden für kleine Betriebe.',
            'tags' => 'papierlos, scanner, digitalisierung, ablage',
            'published' => '2026-08-20 09:00:00',
            'body' => <<<'MD'
**Kurz gesagt:** Ein papierloses Büro entsteht in vier Schritten: eingehendes Papier sofort scannen, nach einer festen Struktur ablegen, per Volltextsuche wiederfinden und das Original sicher vernichten, sobald keine Aufbewahrungspflicht mehr dagegensteht. Das Herzstück ist ein schneller Dokumentenscanner, der beidseitig scannt und Text erkennt.

Die Produkte in diesem Beitrag sind Empfehlungen aus der Praxis. Die Links sind Partnerlinks: Kaufen Sie darüber, erhalte ich eine kleine Provision, der Preis ändert sich für Sie nicht.

## Schritt 1: Womit scanne ich am schnellsten?

Mit einem Dokumentenscanner mit Einzug, nicht mit dem Flachbett des Multifunktionsdruckers. Ein Stapel Belege ist dann in einer Minute beidseitig erfasst, mit Texterkennung (OCR) und direkt im richtigen Ordner:

{{produkt:scansnap-ix1600}}

## Schritt 2: Wie lege ich digitale Dokumente sinnvoll ab?

Eine einfache Struktur schlägt jedes ausgefeilte System:

- **Ordner nach Jahr und Bereich**, zum Beispiel `2026/Eingangsrechnungen`.
- **Dateiname mit Datum vorn:** `2026-10-02_Telekom_Rechnung.pdf`. So sortiert sich alles von selbst.
- **Ein Eingangsordner** für alles Neue, der jeden Freitag geleert wird.

Für Buchhaltungsbelege lohnt sich die direkte Übergabe an die Buchhaltungssoftware oder die Steuerkanzlei, statt sie doppelt abzulegen.

## Schritt 3: Wie finde ich Dokumente wieder?

Mit Volltextsuche. Weil der Scanner den Text erkennt, findet die Suche in Windows, macOS oder auf dem NAS auch den Namen eines Kunden mitten im Dokument. Ordner und Dateinamen sind dann nur noch die Rückfallebene.

Die gescannten Dokumente gehören in die Datensicherung, am besten auf ein NAS mit automatischem Backup:

{{produkt:synology-ds224-plus}}

## Schritt 4: Wie vernichte ich Papier datenschutzgerecht?

Papier mit personenbezogenen Daten gehört nicht ins Altpapier. Ein Partikelschnitt-Schredder der Sicherheitsstufe P-4 genügt für die meisten Büros:

{{produkt:fellowes-powershred-8cd}}

**Wichtig:** Manche Belege müssen trotz Scan im Original aufbewahrt werden. Klären Sie mit Ihrer Steuerberatung, welche, bevor Sie Originale vernichten.

## Und was ist mit Etiketten und Ordnerrücken?

Was physisch bleibt, etwa Verträge mit Unterschrift oder Muster, wird klar beschriftet. Ein Etikettendrucker im Netzwerk druckt Ordnerrücken, Adressen und Inventaraufkleber:

{{produkt:brother-ql-820nwbc}}

## Häufige Fragen

### Darf ich Belege nach dem Scannen wegwerfen?

Oft ja, wenn das Scannen nach einer dokumentierten Verfahrensweise erfolgt (GoBD). Es gibt aber Ausnahmen. Klären Sie das vorab mit Ihrer Steuerberatung.

### Wie lange muss ich Unterlagen aufbewahren?

In Deutschland gelten meist acht bis zehn Jahre für Buchungsbelege, Rechnungen und Jahresabschlüsse sowie sechs Jahre für Geschäftsbriefe.

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
            'category' => 'Digitalization',
            'title' => 'A paperless office in four steps: scan, file, find, shred',
            'excerpt' => 'Walls of binders, hunting for receipts, no space: a low-paper office saves time and room. Here is how to switch without a big project.',
            'meta' => 'Going paperless made simple: document scanner, filing structure, retention periods and secure shredding. A practical guide for small businesses.',
            'tags' => 'paperless, scanner, digitalization, filing',
            'published' => '2026-08-20 09:00:00',
            'body' => <<<'MD'
**In short:** A paperless office comes about in four steps: scan incoming paper right away, file it in a fixed structure, find it again with full-text search and shred the original securely once no retention duty speaks against it. The heart of it is a fast document scanner that scans both sides and recognises text.

The products in this article are recommendations from practice. The links are affiliate links: if you buy through them, I earn a small commission and your price does not change.

## Step 1: What is the fastest way to scan?

A document scanner with a feeder, not the flatbed of a multifunction printer. A stack of receipts is then captured on both sides in a minute, with text recognition (OCR), straight into the right folder:

{{produkt:scansnap-ix1600-document-scanner}}

## Step 2: How do I file digital documents sensibly?

A simple structure beats any elaborate system:

- **Folders by year and area**, for example `2026/Incoming invoices`.
- **File names starting with the date:** `2026-10-02_Telekom_Invoice.pdf`. Everything sorts itself.
- **One inbox folder** for everything new, emptied every Friday.

For accounting receipts, handing them directly to your accounting software or tax adviser beats filing them twice.

## Step 3: How do I find documents again?

With full-text search. Because the scanner recognises the text, search in Windows, macOS or on the NAS finds a client's name in the middle of a document. Folders and file names are then only the fallback.

Scanned documents belong in your backup, ideally on a NAS with automatic backups:

{{produkt:synology-ds224-plus-nas}}

## Step 4: How do I destroy paper in line with data protection?

Paper with personal data does not belong in the recycling bin. A particle-cut shredder at security level P-4 is enough for most offices:

{{produkt:fellowes-powershred-8cd-shredder}}

**Important:** some documents must be kept in the original even after scanning. Check with your tax adviser which ones before destroying originals.

## What about labels and binder spines?

What stays physical, such as signed contracts or samples, gets clear labels. A network label printer prints binder spines, addresses and inventory stickers:

{{produkt:brother-ql-820nwbc-label-printer}}

## Frequently asked questions

### May I throw receipts away after scanning?

Often yes, in Germany if scanning follows a documented procedure (GoBD). There are exceptions, though. Clarify this with your tax adviser first.

### How long do I have to keep records?

In Germany usually eight to ten years for accounting records, invoices and annual accounts, and six years for business letters.

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
        PostSeeder::insert($this->getAdapter()->getConnection(), self::POSTS);
    }

    public function down(): void
    {
        PostSeeder::remove($this->getAdapter()->getConnection(), self::POSTS);
    }
}
