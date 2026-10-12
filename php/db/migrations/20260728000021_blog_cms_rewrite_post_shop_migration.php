<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Tds\Ext\BlogCms\Support\PostRewriter;

/**
 * Blog-CMS — rewrite the "outgrowing a hosted shop builder" article (DE + EN).
 *
 * Same editorial pass as `BlogCmsRewriteLaunchPosts`. The article still names
 * no customer: the landing page's anonymised reference links to it.
 */
final class BlogCmsRewritePostShopMigration extends AbstractMigration
{
    public const SEED = '20260728000010_blog_cms_seed_post_shop_migration.php';

    public const POSTS = [
        [
            'slug' => 'vom-baukasten-shop-zum-eigenen-shop',
            'lang' => 'de',
            'title' => 'Wenn der Baukasten-Shop nicht mehr mitwächst',
            'excerpt' => 'Ein Shop-Baukasten trägt die ersten Jahre zuverlässig. Vier Warnzeichen zeigen, wann er zu eng wird, und warum beim Umzug die Produktdaten entscheiden.',
            'meta' => 'Wann ein Shop-Baukasten an Grenzen stößt, was der Umzug in einen eigenen Shop bedeutet und warum die Produktdaten das eigentliche Projekt sind.',
            'body' => <<<'MD'
**Kurz gesagt:** Ein Shop-Baukasten wird zu eng, wenn Preisrunden nicht mehr in die Arbeitszeit passen, Importe Handarbeit bleiben, Auswertungen fehlen und kleine Wünsche „geht nicht“ heißen. Ein Umzug ist dann vor allem ein Datenprojekt, kein Designprojekt. Er lohnt sich, wenn die Pflege heute mehr kostet als Umzug und Pflege danach zusammen.

## Warum ist ein Baukasten am Anfang die richtige Wahl?

Er kostet wenig, braucht keinen eigenen Server und bringt Zahlungsarten, Versandregeln und Bestellabwicklung mit. Für die ersten Jahre reicht das meist.

Irgendwann kippt das Verhältnis, und zwar schleichend. Der Shop läuft weiter wie immer, aber jede Änderung kostet mehr Zeit als früher.

## Woran merke ich, dass der Baukasten zu eng wird?

- **Das Sortiment wächst schneller als die Werkzeuge.** Zweihundert Artikel pflegt man von Hand, zwanzigtausend nicht. Passt eine Preisrunde nur noch in den Feierabend, ist die Grenze erreicht.
- **Der Import bleibt ein Formular.** Feste Spalten, feste Reihenfolge, keine Regeln. Sehen die Lieferantendaten anders aus, beginnt Handarbeit, jedes Mal aufs Neue.
- **Auswertungen fehlen.** Welche Artikel haben sich seit einem Jahr nicht verkauft? Wo liegt die Marge unter Plan? Geht das nur per Export und Tabelle, arbeiten Sie längst neben dem Shop.
- **Kleine Wünsche werden große Fragen.** Ein Zusatzfeld, eine eigene Preisstaffel, ein anderer Bestellabschluss: Im Baukasten ist das vorgesehen oder unmöglich.

## Warum ist ein Umzug kein Designprojekt?

Das Aussehen ist der kleinere, gut planbare Teil. Entscheidend sind die Daten. Der Export aus dem alten System passt fast nie:

- Kategorien stehen als Text in einer Spalte.
- Varianten liegen als einzelne Artikel nebeneinander.
- Hersteller heißen an drei Stellen unterschiedlich.
- Preise enthalten mal Steuer und mal nicht, und Bilder fehlen.

Wer diese Fehler einfach mitnimmt, verschiebt das Problem nur in ein neues System.

Liefert ein Shop oder Marktplatz die Daten als JSON, hilft der [JSON-Formatter](https://tools.tracht-digital.de/tools/json-formatter) beim ersten Blick. Er rückt die Datei lesbar ein und zeigt Syntaxfehler mit Position.

## Was gehört zur Pflege großer Sortimente?

Drei Schritte, die sich ständig wiederholen:

1. **Analysieren:** Welche Artikel haben keine Beschreibung, kein Bild, keine Kategorie? Wo weichen Bezeichnungen ab, sodass die Shopsuche sie nicht findet?
2. **Filtern:** Kaum eine Änderung betrifft alles. Sie betrifft eine Marke, eine Serie, einen Lieferanten. Wer diese Teilmenge sicher benennen kann, braucht eine Stunde statt einer Woche.
3. **Preise aktualisieren:** als nachvollziehbarer Lauf, den man zurückdrehen kann. Nicht als Reihe einzelner Eingaben, nach denen niemand weiß, was gestern galt.

Wer diese Schritte als wiederholbaren Ablauf hat, beherrscht auch fünfstellige Sortimente.

## Wie bediene ich mehrere Vertriebskanäle?

Marktplätze und Preisportale wollen eigene Kategorien, Pflichtfelder und Bildmaße. Jeden Kanal einzeln zu pflegen funktioniert genau so lange, bis sich ein Preis ändert.

Tragfähig ist ein gepflegter Datenbestand als Quelle, aus dem jeder Kanal seine Fassung bekommt. Was sich am Artikel ändert, ändert sich einmal.

Bei Bildern heißt das oft: dieselben Fotos in anderen Größen. [Bild komprimieren](https://tools.tracht-digital.de/tools/bild-komprimieren) bringt ganze Stapel auf eine Zielgröße in KB, als JPG, WebP oder AVIF.

## Was gilt für Produktbilder aus der KI?

Offenheit schafft Vertrauen. Die [KI-Verordnung der EU](https://eur-lex.europa.eu/eli/reg/2024/1689/oj) sieht ab August 2026 Transparenzpflichten für KI-erzeugte Inhalte vor. Die [KI-Kennzeichnung für Bilder](https://tools.tracht-digital.de/tools/ki-kennzeichnung-bilder) setzt einen sichtbaren Hinweis und eine maschinenlesbare Notiz ins Bild, ohne Upload.

## Was sollte ich vor der Systemwahl klären?

1. **Wie viele Artikel werden es in drei Jahren, und wer pflegt sie?** Die Antwort bestimmt, wie viel Automatisierung sich lohnt.
2. **Welche Abläufe sind wirklich Ihre eigenen?** Was Standard ist, bleibt Standard. Eigenentwicklung lohnt sich nur an den zwei, drei Stellen, an denen Sie anders arbeiten.
3. **Wer kann das System später ändern?** Ein Shop, den nur eine Person versteht, ist ein Risiko.

## Häufige Fragen

### Wie lange dauert ein Shop-Umzug?

Das hängt an den Daten, nicht am Design. Aufsetzen und Gestalten sind gut planbar. Die Bereinigung großer Sortimente braucht die meiste Zeit.

### Verliere ich beim Umzug meine Google-Platzierungen?

Nicht zwingend. Jede alte Adresse muss per Weiterleitung (301) auf die neue zeigen. Fehlen die Weiterleitungen, gehen Platzierungen verloren.

### Kann ich das alte Sortiment einfach importieren?

Technisch ja. Sinnvoll ist es erst nach einer Bereinigung, sonst ziehen die alten Fehler mit um.

### Lohnt sich ein Umzug bei wenigen hundert Artikeln?

Selten. Bei kleinen Sortimenten ist ein Baukasten meist die bessere Wahl. Ob sich ein Shop überhaupt rechnet, klärt der Beitrag [Lohnt sich ein Webshop?](/lohnt-sich-ein-webshop)

Stand: Oktober 2026. Passt Ihr Shop noch zu Ihrem Sortiment? Schreiben Sie mir in zwei Sätzen, wie viele Artikel Sie führen und was die Pflege am meisten aufhält. Ich sage Ihnen ehrlich, ob sich ein Wechsel rechnet, und wenn nicht, auch das.
MD,
        ],
        [
            'slug' => 'vom-baukasten-shop-zum-eigenen-shop',
            'lang' => 'en',
            'title' => 'When the hosted shop builder stops keeping up',
            'excerpt' => 'A hosted shop builder carries you reliably for the first years. Four warning signs show when it gets too tight, and why the product data decides a move.',
            'meta' => 'When a hosted shop builder reaches its limits, what moving to your own shop involves and why the product data is the real project.',
            'body' => <<<'MD'
**In short:** A hosted shop builder gets too tight when price updates no longer fit into working hours, imports stay manual, reports are missing and small wishes meet "not possible". A move is then mainly a data project, not a design project. It pays off when today's upkeep costs more than the move plus the upkeep afterwards.

## Why is a shop builder the right choice at the start?

It costs little, needs no server of your own and brings payment methods, shipping rules and order handling. For the first few years that is usually enough.

At some point the balance tips, gradually. The shop keeps running as before, but every change takes more time than it used to.

## How do I tell the builder is getting too tight?

- **The catalogue grows faster than the tools.** Two hundred articles can be maintained by hand, twenty thousand cannot. When a price update only fits after hours, you have reached the limit.
- **The import stays a form.** Fixed columns, fixed order, no rules. When supplier data looks different, manual work begins, every single time.
- **Reports are missing.** Which articles have not sold in a year? Where is the margin below plan? If only an export and a spreadsheet can answer that, you are already working beside the shop.
- **Small wishes become big questions.** An extra field, your own price tiers, a different checkout step: in a builder it is either provided for or impossible.

## Why is a move not a design project?

The look is the smaller, plannable part. The data decides. The export from the old system almost never fits:

- Categories sit as text in a single column.
- Variants appear as separate articles side by side.
- Manufacturers are spelled three different ways.
- Prices sometimes include tax and sometimes not, and images are missing.

Carrying these mistakes across only moves the problem into a new system.

When a shop or marketplace delivers data as JSON, the [JSON Formatter](https://tools.tracht-digital.de/en/tools/json-formatter) helps at first sight. It indents the file readably and shows syntax errors with their position.

## What does upkeep of a large catalogue involve?

Three steps that keep repeating:

1. **Analyse:** which articles have no description, no image, no category? Where do names differ so the shop search cannot bring them together?
2. **Filter:** hardly any change affects everything. It affects one brand, one series, one supplier. Naming that subset reliably takes an hour instead of a week.
3. **Update prices:** as a traceable run you can reverse. Not as a series of single edits after which nobody knows what applied yesterday.

With these steps as a repeatable routine, even catalogues in the tens of thousands stay manageable.

## How do I serve several sales channels?

Marketplaces and price portals want their own categories, required fields and image sizes. Maintaining each channel separately works exactly until a price changes.

The workable route is one maintained data set as the source, from which every channel gets its own version. What changes on an article changes once.

For images that often means the same photos in other sizes. [Compress Image](https://tools.tracht-digital.de/en/tools/bild-komprimieren) brings whole batches to a target size in KB, as JPG, WebP or AVIF.

## What about AI-generated product images?

Openness builds trust. The [EU AI Act](https://eur-lex.europa.eu/eli/reg/2024/1689/oj) brings transparency duties for AI-generated content from August 2026. [AI Image Labelling](https://tools.tracht-digital.de/en/tools/ki-kennzeichnung-bilder) adds a visible notice and a machine-readable note to the image, without any upload.

## What should I settle before choosing a system?

1. **How many articles will there be in three years, and who maintains them?** The answer decides how much automation pays off.
2. **Which routines are genuinely your own?** Anything standard stays standard. Custom work only pays off at the two or three points where you really work differently.
3. **Who can change the system later?** A shop only one person understands is a risk.

## Frequently asked questions

### How long does a shop migration take?

It depends on the data, not the design. Setting up and styling are plannable. Cleaning up a large catalogue takes most of the time.

### Will I lose my Google rankings when I move?

Not necessarily. Every old address must redirect (301) to its new one. Without the redirects, rankings get lost.

### Can I simply import the old catalogue?

Technically yes. It only makes sense after a clean-up, otherwise the old mistakes move with you.

### Is a move worth it with a few hundred articles?

Rarely. For small catalogues a builder is usually the better choice. Whether a shop adds up at all is covered in [Is an online shop worth it?](/en/lohnt-sich-ein-webshop)

Updated: October 2026. Does your shop still fit your catalogue? Write me two sentences about how many articles you carry and what slows the upkeep down most. I will tell you honestly whether a move pays off, and if it does not, that too.
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
