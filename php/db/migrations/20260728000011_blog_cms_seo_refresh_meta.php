<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Blog-CMS — bring the seeded meta descriptions inside the rendered limit.
 *
 * Eight of the twelve rows seeded by `BlogCmsSeedPosts` carry a
 * `meta_description` longer than 160 characters. That was invisible for as
 * long as nothing rendered the field; now that the blog frontend uses it as
 * `<meta name="description">`, every one of them would be cut off mid-sentence
 * in a search result. The four rows already inside the limit are left alone.
 *
 * **The guard is the old value, not the body.** `WHERE meta_description = :old`
 * means an editor who has since rewritten the description keeps it, and a
 * second run changes nothing — without copying eight article bodies into this
 * file to compare against. A row whose title or body was edited but whose
 * description is still the seeded one *should* be corrected, so those are not
 * part of the condition.
 *
 * `down()` is the same statement with the two values swapped, so it restores
 * exactly the rows this migration changed and no others.
 */
final class BlogCmsSeoRefreshMeta extends AbstractMigration
{
    /**
     * Slug, language, the seeded description and its replacement.
     *
     * Every `new` value stays between 80 and 160 characters: below 80 a
     * description stops being useful, above 160 it is truncated.
     *
     * @var list<array{slug:string, lang:string, old:string, new:string}>
     */
    private const META = [
        [
            'slug' => 'digitalisierung-faengt-klein-an',
            'lang' => 'de',
            'old' => 'Digitalisierung für kleine Unternehmen beginnt nicht mit neuer Software für den ganzen Betrieb, sondern mit einem einzigen Ablauf. Drei Fragen, die den richtigen Anfang finden.',
            'new' => 'Digitalisierung für kleine Unternehmen beginnt nicht mit Software für den ganzen Betrieb, sondern mit einem einzigen Ablauf. Drei Fragen für den Anfang.',
        ],
        [
            'slug' => 'digitalisierung-faengt-klein-an',
            'lang' => 'en',
            'old' => 'Digitalization for small businesses does not begin with new software for the whole company, but with a single workflow. Three questions that find the right place to start.',
            'new' => 'Digitalization for small businesses starts with a single workflow, not with software for the whole company. Three questions that find the right start.',
        ],
        [
            'slug' => 'website-fuenf-dinge-die-fehlen',
            'lang' => 'de',
            'old' => 'Warum Besucher abspringen, bevor sie anfragen: fünf konkrete Lücken, die auf vielen Websites kleiner Unternehmen fehlen — und wie Sie sie in einer Stunde prüfen.',
            'new' => 'Warum Besucher abspringen, bevor sie anfragen: fünf Lücken, die auf vielen Websites kleiner Unternehmen fehlen — und wie Sie sie in einer Stunde prüfen.',
        ],
        [
            'slug' => 'lohnt-sich-ein-webshop',
            'lang' => 'de',
            'old' => 'Ein Webshop bringt laufende Arbeit mit sich, die eine Website nicht hat. Vier Fragen, mit denen Sie vorher einschätzen, ob sich der Onlineverkauf für Ihren Laden rechnet.',
            'new' => 'Ein Webshop bringt laufende Arbeit mit, die eine Website nicht hat. Vier Fragen, mit denen Sie vorher einschätzen, ob sich der Onlineverkauf rechnet.',
        ],
        [
            'slug' => 'excel-oder-eigenes-werkzeug',
            'lang' => 'de',
            'old' => 'Wann eine Excel-Tabelle völlig ausreicht und wann ein eigenes Werkzeug günstiger wird: drei Kipppunkte, an denen sich der Wechsel rechnet — und wie ein Umstieg abläuft.',
            'new' => 'Wann eine Excel-Tabelle reicht und wann ein eigenes Werkzeug günstiger wird: drei Kipppunkte, an denen sich der Wechsel rechnet — und wie er abläuft.',
        ],
        [
            'slug' => 'excel-oder-eigenes-werkzeug',
            'lang' => 'en',
            'old' => 'When a spreadsheet is entirely enough and when a purpose-built tool gets cheaper: three tipping points that justify the switch — and how a migration actually runs.',
            'new' => 'When a spreadsheet is enough and when a purpose-built tool gets cheaper: three tipping points that justify the switch — and how the move runs.',
        ],
        [
            'slug' => 'konzept-vor-umsetzung',
            'lang' => 'de',
            'old' => 'Ein digitales Konzept klärt vor der Umsetzung, was gebraucht wird und was es kostet. Warum sich die Reihenfolge rechnet — und woran Sie ein brauchbares Konzept erkennen.',
            'new' => 'Ein digitales Konzept klärt vor der Umsetzung, was gebraucht wird und was es kostet. Warum sich die Reihenfolge rechnet — und woran Sie eines erkennen.',
        ],
        [
            'slug' => 'produktpflege-per-handy',
            'lang' => 'de',
            'old' => 'Bestandspflege scheitert selten am Wollen, sondern am Ort. Was sich ändert, wenn Artikel und Bestände direkt per Handy erfasst werden — und worauf es dabei ankommt.',
            'new' => 'Bestandspflege scheitert selten am Wollen, sondern am Ort. Was sich ändert, wenn Artikel und Bestände direkt per Handy erfasst werden.',
        ],
    ];

    public function up(): void
    {
        $this->apply('new', 'old');
    }

    public function down(): void
    {
        $this->apply('old', 'new');
    }

    /** Set the `$to` value on every row still carrying the `$from` one. */
    private function apply(string $to, string $from): void
    {
        $stmt = $this->getAdapter()->getConnection()->prepare(
            'UPDATE blog_post SET meta_description = :to
              WHERE slug = :s AND lang = :l AND meta_description = :from'
        );
        foreach (self::META as $row) {
            $stmt->execute([
                ':to' => $row[$to],
                ':s' => $row['slug'],
                ':l' => $row['lang'],
                ':from' => $row[$from],
            ]);
        }
    }
}
