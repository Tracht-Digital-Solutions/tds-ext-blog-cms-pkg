<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Blog-CMS — seed the "outgrowing a hosted shop builder" article (DE + EN).
 *
 * A second seed rather than an edit to `BlogCmsSeedPosts`: that migration has
 * long since run in production and Phinx will never execute it again, so a new
 * entry in its `POSTS` array would reach a fresh installation and nothing else.
 * Every new article therefore arrives as its own migration.
 *
 * The four rules from the first seed apply unchanged, because each one fails
 * silently — an invisible article, never an error:
 *
 *  1. Reuse the existing `blog` row (`ORDER BY name, id LIMIT 1`), and create
 *     one only when there is none. A second blog would be invisible to the
 *     public read surface.
 *  2. `draft = 0` **and** a non-null `published_at`; `publicPosts()` filters on
 *     both.
 *  3. One row per language, sharing the slug — the unique index is
 *     `(blog_id, slug, lang)`.
 *  4. `machine_translated = 0` on the English row. It is written by hand;
 *     flagged as machine-translated, `Service\TranslationSync` would replace it
 *     with DeepL output the next time the German article is saved.
 *
 * Idempotent by `(blog_id, slug, lang)`, and `down()` deletes only a row still
 * carrying the seeded title *and* body verbatim, so a rollback cannot destroy
 * an edited article.
 *
 * The article is deliberately a subject-matter piece with no customer named in
 * it. The landing page's reference block links to it, and that reference stays
 * anonymised; naming the customer here would defeat that.
 */
final class BlogCmsSeedPostShopMigration extends AbstractMigration
{
    private const BLOG_KEY = 'journal';
    private const BLOG_NAME = 'Tracht Digital Journal';

    private const AUTHOR_NAME = 'Julian Tracht';
    private const AUTHOR_BIO = 'Freier Entwickler aus Schwarzenbek bei Hamburg. Baut Websites, Webshops und individuelle Werkzeuge für Selbstständige, kleine Unternehmen und lokale Betriebe.';

    /**
     * @var list<array{
     *   slug:string, lang:string, category:string, title:string,
     *   excerpt:string, meta:string, tags:string, published:string, body:string
     * }>
     */
    private const POSTS = [
        [
            'slug' => 'vom-baukasten-shop-zum-eigenen-shop',
            'lang' => 'de',
            'category' => 'Webshop',
            'title' => 'Wenn der Baukasten-Shop nicht mehr mitwächst',
            'excerpt' => 'Ein gehosteter Shop-Baukasten trägt die ersten Jahre zuverlässig. Woran man merkt, dass er es nicht mehr tut — und was ein Umzug wirklich bedeutet.',
            'meta' => 'Wann ein Shop-Baukasten an seine Grenzen kommt, was ein Umzug auf einen eigenen Shop bedeutet und warum die Produktdaten das eigentliche Projekt sind.',
            'tags' => 'webshop, produktdaten, preispflege',
            'published' => '2026-09-01 09:00:00',
            'body' => "Ein gehosteter Shop-Baukasten ist für den Start eine vernünftige Entscheidung. Er kostet wenig, er läuft ohne eigenen Server, und er bringt Zahlungsarten, Versandregeln und eine Bestellabwicklung mit, die man sonst einzeln zusammensuchen müsste. Für die ersten Jahre reicht das in aller Regel.\n\nIrgendwann kippt das Verhältnis. Nicht an einem Tag, sondern schleichend: Der Shop läuft weiter wie immer, aber jede Änderung daran kostet mehr Zeit als früher.\n\n## Woran man merkt, dass der Baukasten eng wird\n\n**Das Sortiment wächst schneller als die Werkzeuge.** Zweihundert Artikel pflegt man von Hand. Zwanzigtausend nicht mehr. Wenn eine Preisrunde nur noch außerhalb der Geschäftszeiten zu schaffen ist, weil das Bearbeiten im Browser Artikel für Artikel läuft, ist die Grenze erreicht.\n\n**Der Import bleibt ein Formular.** Viele Baukästen können Daten einlesen, aber immer auf ihre Art: feste Spalten, feste Reihenfolge, keine Regeln. Sobald die Daten des Lieferanten anders aussehen als das Formular, entsteht Handarbeit — jedes Mal aufs Neue.\n\n**Auswertungen fehlen, wo Sie sie brauchen.** Welche Artikel haben sich seit einem Jahr nicht verkauft? Bei welchen liegt die Marge unter dem, was Sie kalkuliert haben? Wenn diese Fragen nur mit einem Export und einer Tabelle zu beantworten sind, arbeiten Sie längst neben dem Shop statt in ihm.\n\n**Kleine Wünsche werden zu großen Fragen.** Ein zusätzliches Feld an jedem Artikel, eine eigene Staffelung, ein anderer Ablauf beim Bestellabschluss: In einem Baukasten ist das entweder vorgesehen oder es geht nicht. Ein „geht nicht\" häuft sich mit den Jahren.\n\n## Ein Umzug ist kein Design-Projekt\n\nWer über einen Wechsel nachdenkt, denkt meistens zuerst an das Aussehen. Das ist der kleinere Teil. Ein Shop-System aufzusetzen, zu gestalten und mit Zahlungsarten und Versandregeln zu verbinden, ist überschaubare, gut planbare Arbeit.\n\nDer Umzug entscheidet sich an den Daten. Aus dem alten System kommt ein Export, und dieser Export ist fast nie so, wie ihn das neue System braucht: Kategorien liegen als Text in einer Spalte, Varianten stehen als eigene Artikel nebeneinander, Hersteller heißen an drei Stellen unterschiedlich, Preise enthalten mal Steuer und mal nicht, und ein Teil der Bilder fehlt.\n\nGenau dort steckt der Aufwand, und genau dort entscheidet sich, ob der neue Shop besser wird als der alte oder nur anders. Eine Datenübernahme, die die Fehler der letzten Jahre einfach mitnimmt, verschiebt das Problem in ein neues System.\n\n## Produktdaten sind das eigentliche Projekt\n\nBei großen Sortimenten ist die Pflege keine Nebentätigkeit, sondern die eigentliche Arbeit am Shop. Sie besteht aus drei Dingen, die sich immer wiederholen.\n\n**Analysieren.** Was ist überhaupt da? Welche Artikel haben keine Beschreibung, kein Bild, keine Kategorie? Wo weichen Bezeichnungen voneinander ab, sodass die Suche im Shop sie nicht zusammenbringt? Diese Auswertung einmal sauber zu bauen, lohnt sich, weil man sie jeden Monat wieder braucht.\n\n**Filtern.** Kaum eine Änderung betrifft das ganze Sortiment. Sie betrifft eine Marke, eine Serie, eine Lieferantenliste, alle Artikel unter einer bestimmten Marge. Der Unterschied zwischen einer Stunde und einer Woche liegt darin, ob man diese Teilmenge zuverlässig benennen kann.\n\n**Preise aktualisieren.** Neue Einkaufspreise, geänderte Staffeln, Aktionen mit Anfang und Ende. Als Lauf, der nachvollziehbar ist und den man zurückdrehen kann — nicht als Reihe einzelner Eingaben, bei denen niemand mehr weiß, was gestern galt.\n\nWer diese drei Schritte als wiederholbaren Ablauf hat, für den ist ein Sortiment mit fünfstelliger Artikelzahl beherrschbar. Wer sie von Hand macht, ist ab einer bestimmten Größe nur noch mit Nachpflegen beschäftigt.\n\n## Ein Sortiment, mehrere Vertriebskanäle\n\nDer eigene Shop ist selten der einzige Ort, an dem verkauft wird. Marktplätze, Preisportale und Plattformen kommen dazu, und jeder Kanal will die Daten in seinem eigenen Zuschnitt: andere Kategorien, andere Pflichtfelder, andere Bildmaße, eigene Regeln für Titel und Beschreibung.\n\nDie Versuchung ist, jeden Kanal für sich zu pflegen. Das funktioniert genau so lange, bis sich ein Preis ändert. Danach zahlt man den Unterschied — als Vertrauensverlust bei Kunden, die zwei Preise sehen, oder schlicht als Marge.\n\nTragfähig ist der andere Weg: ein gepflegter Datenbestand als Quelle, aus dem jeder Kanal seine Fassung bekommt. Was sich am Artikel ändert, ändert sich einmal.\n\n## Was man vorher entscheiden sollte\n\nDrei Fragen, die vor der Systemwahl kommen und nicht danach.\n\n**Wie viele Artikel werden es in drei Jahren, und wer pflegt sie?** Die Antwort bestimmt, wie viel Automatisierung sich lohnt — und ob sie sich überhaupt lohnt.\n\n**Welche Abläufe sind wirklich Ihre eigenen?** Alles, was Standard ist, sollte Standard bleiben. Eigenentwicklung lohnt sich an den zwei, drei Stellen, an denen Sie tatsächlich anders arbeiten als andere.\n\n**Wer kann das System später ändern?** Ein Shop, den nur eine einzige Person versteht, ist ein Risiko — unabhängig davon, ob diese Person ein Dienstleister ist oder im Haus sitzt.\n\nEin Umzug lohnt sich nicht, weil ein anderes System moderner ist. Er lohnt sich, wenn die Pflege heute mehr Zeit kostet als der Wechsel und die Pflege danach zusammen.\n\nWenn Sie überlegen, ob Ihr Shop noch zu Ihrem Sortiment passt: Schreiben Sie mir in zwei Sätzen, wie viele Artikel Sie führen und was die Pflege gerade am meisten aufhält. Ich sage Ihnen ehrlich, ob sich ein Wechsel rechnet — und wenn nicht, auch das.",
        ],
        [
            'slug' => 'vom-baukasten-shop-zum-eigenen-shop',
            'lang' => 'en',
            'category' => 'Online shop',
            'title' => 'When the hosted shop builder stops keeping up',
            'excerpt' => 'A hosted shop builder carries you reliably for the first few years. How to tell when it no longer does — and what a migration actually involves.',
            'meta' => 'When a hosted shop builder reaches its limits, what moving to your own shop actually involves, and why the product data is the real project.',
            'tags' => 'online-shop, product-data, pricing',
            'published' => '2026-09-01 09:00:00',
            'body' => "A hosted shop builder is a sensible decision at the start. It costs little, it runs without a server of your own, and it brings payment methods, shipping rules and order handling that you would otherwise have to assemble piece by piece. For the first few years that is usually enough.\n\nAt some point the balance tips. Not on a single day, but gradually: the shop keeps running exactly as before, but every change to it costs more time than it used to.\n\n## How to tell the builder is getting tight\n\n**The catalogue grows faster than the tools.** Two hundred articles can be maintained by hand. Twenty thousand cannot. When a round of price changes only fits outside business hours, because editing runs article by article in a browser, you have reached the limit.\n\n**The import stays a form.** Many builders can read data in, but always their way: fixed columns, fixed order, no rules. As soon as a supplier's data looks different from the form, manual work appears — every single time.\n\n**Reporting is missing where you need it.** Which articles have not sold in a year? On which is the margin below what you calculated? If those questions can only be answered with an export and a spreadsheet, you are already working beside the shop rather than in it.\n\n**Small wishes turn into big questions.** An extra field on every article, your own price tiers, a different step at checkout: in a builder that is either provided for or it is not. The \"not possible\" answers add up over the years.\n\n## A migration is not a design project\n\nAnyone considering a move usually thinks about the appearance first. That is the smaller part. Setting up a shop system, styling it and connecting payment methods and shipping rules is manageable, plannable work.\n\nA migration is decided by the data. The old system produces an export, and that export is almost never what the new system needs: categories sit as text in one column, variants stand next to each other as separate articles, manufacturers are spelled three different ways, prices sometimes include tax and sometimes do not, and some images are missing.\n\nThat is where the effort sits, and that is where it is decided whether the new shop is better than the old one or merely different. A data migration that simply carries the last few years of mistakes across moves the problem into a new system.\n\n## The product data is the real project\n\nWith a large catalogue, upkeep is not a side task but the actual work on the shop. It consists of three things that keep repeating.\n\n**Analysis.** What is actually there? Which articles have no description, no image, no category? Where do names differ so that the shop's own search cannot bring them together? Building that report properly once pays off, because you need it again every month.\n\n**Filtering.** Hardly any change affects the whole catalogue. It affects one brand, one series, one supplier list, everything below a certain margin. The difference between an hour and a week lies in whether that subset can be named reliably.\n\n**Price updates.** New purchase prices, changed tiers, promotions with a start and an end date. As a run that can be traced and reversed — not as a series of individual edits after which nobody knows what applied yesterday.\n\nWith those three steps as a repeatable routine, a catalogue in the tens of thousands stays manageable. Done by hand, past a certain size you are only ever catching up.\n\n## One catalogue, several sales channels\n\nYour own shop is rarely the only place you sell. Marketplaces, price portals and platforms come along, and each channel wants the data in its own shape: different categories, different mandatory fields, different image sizes, its own rules for titles and descriptions.\n\nThe temptation is to maintain each channel separately. That works exactly until a price changes. After that you pay the difference — as lost trust from customers who see two prices, or simply as margin.\n\nThe workable route is the other one: one maintained set of data as the source, from which every channel gets its own version. What changes on an article changes once.\n\n## What to decide beforehand\n\nThree questions that come before choosing a system, not after.\n\n**How many articles will there be in three years, and who maintains them?** The answer determines how much automation pays off — and whether it pays off at all.\n\n**Which routines are genuinely your own?** Anything standard should stay standard. Custom work pays off at the two or three points where you really do work differently from everyone else.\n\n**Who can change the system later?** A shop only one person understands is a risk, regardless of whether that person is a contractor or sits in the building.\n\nA migration is not worth it because another system is more modern. It is worth it when today's upkeep costs more time than the move plus the upkeep afterwards.\n\nIf you are wondering whether your shop still fits your catalogue: write me two sentences about how many articles you carry and what slows the upkeep down most. I will tell you honestly whether a move pays off — and if it does not, that too.",
        ],
    ];

    public function up(): void
    {
        $conn = $this->getAdapter()->getConnection();

        // The public read surface is scoped to `defaultBlog()` — ORDER BY name,
        // id LIMIT 1. Reuse an existing blog rather than creating a second one
        // the frontend would never read from.
        $blogId = $conn->query('SELECT id FROM blog ORDER BY name, id LIMIT 1')->fetchColumn();
        if ($blogId === false || $blogId === null) {
            $conn->prepare('INSERT INTO blog (blog_key, name) VALUES (:k, :n)')
                ->execute([':k' => self::BLOG_KEY, ':n' => self::BLOG_NAME]);
            $blogId = $conn->lastInsertId();
        }
        $blogId = (int) $blogId;

        // Without an author the post renders with no byline. `author_id` is
        // nullable, so a failure here must not take the article down with it.
        $authorStmt = $conn->prepare('SELECT id FROM blog_author WHERE name = :n LIMIT 1');
        $authorStmt->execute([':n' => self::AUTHOR_NAME]);
        $authorId = $authorStmt->fetchColumn();
        if ($authorId === false || $authorId === null) {
            $conn->prepare('INSERT INTO blog_author (name, bio) VALUES (:n, :b)')
                ->execute([':n' => self::AUTHOR_NAME, ':b' => self::AUTHOR_BIO]);
            $authorId = $conn->lastInsertId();
        }
        $authorId = $authorId !== false && $authorId !== null ? (int) $authorId : null;

        $exists = $conn->prepare(
            'SELECT id FROM blog_post WHERE blog_id = :b AND slug = :s AND lang = :l LIMIT 1'
        );
        $insert = $conn->prepare(
            'INSERT INTO blog_post
                (blog_id, slug, lang, category, title, excerpt, meta_description, tags,
                 body, cover_hint, author_id, published_at, draft, machine_translated)
             VALUES
                (:b, :s, :l, :c, :t, :e, :m, :g, :body, NULL, :a, :p, 0, 0)'
        );

        foreach (self::POSTS as $post) {
            $exists->execute([':b' => $blogId, ':s' => $post['slug'], ':l' => $post['lang']]);
            if ($exists->fetch() !== false) {
                continue;
            }
            $insert->execute([
                ':b' => $blogId,
                ':s' => $post['slug'],
                ':l' => $post['lang'],
                ':c' => $post['category'],
                ':t' => $post['title'],
                ':e' => $post['excerpt'],
                ':m' => $post['meta'],
                ':g' => $post['tags'],
                ':body' => $post['body'],
                ':a' => $authorId,
                ':p' => $post['published'],
            ]);
        }
    }

    public function down(): void
    {
        // Only rows still carrying the seeded title AND body verbatim — an
        // article somebody has since edited stays. The blog and the author row
        // are left alone: other posts reference them.
        $delete = $this->getAdapter()->getConnection()->prepare(
            'DELETE FROM blog_post WHERE slug = :s AND lang = :l AND title = :t AND body = :body'
        );
        foreach (self::POSTS as $post) {
            $delete->execute([
                ':s' => $post['slug'],
                ':l' => $post['lang'],
                ':t' => $post['title'],
                ':body' => $post['body'],
            ]);
        }
    }
}
