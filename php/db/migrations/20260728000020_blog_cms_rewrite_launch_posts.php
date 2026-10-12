<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Tds\Ext\BlogCms\Support\PostRewriter;

/**
 * Blog-CMS — rewrite the six launch articles of `BlogCmsSeedPosts` (DE + EN).
 *
 * Editorial pass 2026-10: a short answer first, questions as headings, an FAQ
 * section (rendered as FAQPage), sources for facts and links to the matching
 * tools on tools.tracht-digital.de. `Support\PostRewriter` reads the old text
 * from the seed class and replaces only rows nobody has edited since.
 */
final class BlogCmsRewriteLaunchPosts extends AbstractMigration
{
    public const SEED = '20260728000007_blog_cms_seed_posts.php';

    public const POSTS = [
        // ---------------------------------------------------------------- 01
        [
            'slug' => 'digitalisierung-faengt-klein-an',
            'lang' => 'de',
            'title' => 'Digitalisierung im kleinen Betrieb: mit einem Ablauf anfangen',
            'excerpt' => 'Kein Großprojekt, keine neue Software für alles: Digitalisierung beginnt mit dem einen Ablauf, der jede Woche Zeit frisst. So finden Sie ihn.',
            'meta' => 'Digitalisierung im kleinen Betrieb: Warum Sie mit einem einzigen Ablauf starten, wie drei Fragen ihn finden und was ein erster Schritt kostet.',
            'body' => <<<'MD'
**Kurz gesagt:** Digitalisierung im kleinen Betrieb beginnt nicht mit neuer Software für alles. Sie beginnt mit dem einen Ablauf, der jede Woche Zeit kostet. Drei Fragen helfen, ihn zu finden. Ein guter erster Schritt dauert oft nur wenige Tage.

## Warum ist das Großprojekt der falsche Anfang?

Neue Software für den ganzen Betrieb bedeutet Schulungen, Umstellung und monatelange Unruhe. Das schreckt zu Recht ab. Und es löst selten das Problem, das wirklich drückt.

Der bessere Anfang ist unscheinbar. Es ist die Aufgabe, über die sich niemand mehr beschwert, weil sich alle daran gewöhnt haben.

## Welche drei Fragen finden den richtigen Anfang?

1. **Welche Zahl schreiben Sie mehr als einmal auf?** Ein Preis steht in der Kasse, in der Preisliste und auf der Website. Jede zweite Eingabe ist eine Stelle, an der Daten auseinanderlaufen.
2. **Was fragen Sie regelmäßig bei anderen nach?** Dann hängt eine Information an einer Person statt an einem Ort. Das geht gut, bis die Person im Urlaub ist.
3. **Welche Aufgabe schieben Sie immer wieder auf?** Aufschieben ist ein sicheres Zeichen. Die Aufgabe ist nicht unwichtig, sondern umständlich.

Eine Antwort reicht. Sie ist Ihr Anfang, nicht die Software, die am meisten kann.

## Warum ist klein anfangen der schnellere Weg?

- **Es lässt sich zurückdrehen.** Passt die Lösung nicht, sind vier Wochen verloren, nicht ein Jahr.
- **Es beweist etwas.** Danach wissen Sie konkret, ob digitale Abläufe in Ihrem Betrieb Zeit sparen.
- **Es zieht den Rest mit.** Wer Artikeldaten einmal sauber an einem Ort hat, hat Shop, Preisliste und Inventur schon halb gelöst.

## Wie sieht ein erster Schritt konkret aus?

Oft reicht ein fertiges Werkzeug statt eines neuen Systems. Drei Beispiele, die Sie heute ausprobieren können:

- **Zettel nicht mehr abtippen:** Die [Texterkennung (OCR)](https://tools.tracht-digital.de/tools/texterkennung) liest Text aus Fotos und Scans heraus. Lieferscheine und Notizen landen so direkt im Rechner.
- **Arbeitszeiten erfassen:** Der [Stundenzettel](https://tools.tracht-digital.de/tools/stundenzettel) zieht Pausen ab, rechnet die Summen und liefert ein PDF zum Unterschreiben.
- **Belege bündeln:** Mit [Bilder zu PDF](https://tools.tracht-digital.de/tools/bilder-zu-pdf) werden abfotografierte Belege zu einem sauberen Dokument.

Alle drei laufen im Browser. Ihre Dateien werden nicht hochgeladen.

Reicht ein fertiges Werkzeug nicht, folgt eine kleine eigene Lösung: eine Vorlage, eine Erfassungsmaske oder eine Auswertung aus drei Quellen. Das dauert meist wenige Tage.

## Wann lohnt sich Digitalisierung nicht?

- Bei Abläufen, die nur zweimal im Jahr vorkommen.
- Bei Aufgaben, die nur Sie betreffen und die Sie schnell erledigen.
- Wenn eine Standardlösung 80 Prozent trifft. Das ist oft besser als eine eigene Lösung, die gepflegt werden muss.

Die Frage ist nie, ob etwas digital geht. Fast alles geht. Die Frage ist, ob sich der Aufwand in überschaubarer Zeit zurückzahlt.

## Häufige Fragen

### Was kostet ein erster Digitalisierungsschritt?

Weniger als die meisten erwarten, weil er nicht den ganzen Betrieb umfasst. Eine Vorlage oder ein kleines Erfassungswerkzeug liegt oft bei wenigen Tagen Arbeit.

### Wo steckt der eigentliche Aufwand?

Selten in der Technik. Er steckt darin, den Ablauf vorher genau zu verstehen: Wer macht was, in welcher Reihenfolge und warum?

### Brauche ich dafür eine eigene App?

Meist nicht. Eine Anwendung im Browser, die auch auf dem Handy gut läuft, reicht für die meisten Betriebe.

### Wann reicht eine Excel-Tabelle?

Solange eine Person sie pflegt und nichts mit anderen Systemen abgeglichen werden muss. Mehr dazu im Beitrag [Excel oder eigenes Werkzeug?](/excel-oder-eigenes-werkzeug)

Stand: Oktober 2026. Achten Sie eine Woche lang darauf, wo Sie dieselbe Information doppelt eintragen. Schreiben Sie mir dann in zwei Sätzen, was Ihnen aufgefallen ist. Ich sage Ihnen ehrlich, ob sich eine Umsetzung lohnt, und wenn nicht, auch das.
MD,
        ],
        [
            'slug' => 'digitalisierung-faengt-klein-an',
            'lang' => 'en',
            'title' => 'Digitalization for small businesses: start with one workflow',
            'excerpt' => 'No big project, no new software for everything: digitalization starts with the one routine that eats time every week. Here is how to find it.',
            'meta' => 'Digitalization for small businesses: why you start with a single workflow, how three questions find it and what a first step really costs.',
            'body' => <<<'MD'
**In short:** Digitalization in a small business does not start with new software for everything. It starts with the one workflow that costs time every week. Three questions help you find it. A good first step often takes only a few days.

## Why is a big project the wrong start?

New software for the whole company means training, migration and months of disruption. That is off-putting for good reason. And it rarely solves the problem that actually hurts.

The better start is unremarkable. It is the task nobody complains about any more, because everyone has got used to it.

## Which three questions find the right start?

1. **Which number do you write down more than once?** A price lives in the till, in the price list and on the website. Every second entry is a place where data drifts apart.
2. **What do you regularly have to ask others?** Then a piece of information is tied to a person rather than a place. That works until the person is on holiday.
3. **Which task do you keep putting off?** Procrastination is a reliable sign. The task is not unimportant, it is awkward.

One answer is enough. It is your starting point, not the software that can do the most.

## Why is starting small the faster route?

- **It can be undone.** If the solution does not fit, you lose four weeks, not a year.
- **It proves something.** Afterwards you know concretely whether digital workflows save time in your business.
- **It pulls the rest along.** Once your product data sits cleanly in one place, the shop, the price list and the stocktake are half solved.

## What does a first step look like?

Often a ready-made tool is enough instead of a new system. Three examples you can try today:

- **Stop retyping notes:** [Text Recognition (OCR)](https://tools.tracht-digital.de/en/tools/texterkennung) reads text from photos and scans. Delivery notes and handwritten lists go straight into the computer.
- **Record working hours:** the [Timesheet](https://tools.tracht-digital.de/en/tools/stundenzettel) deducts breaks, adds up the totals and produces a PDF to sign.
- **Bundle receipts:** [Images to PDF](https://tools.tracht-digital.de/en/tools/bilder-zu-pdf) turns photographed receipts into one clean document.

All three run in the browser. Your files are not uploaded.

If a ready-made tool is not enough, the next step is a small solution of your own: a template, a capture form or a report that pulls three sources together. That usually takes a few days.

## When is digitalization not worth it?

- For workflows that happen twice a year.
- For tasks that only affect you and that you are quick at.
- When an off-the-shelf product covers 80 per cent. That is often better than a custom solution that needs maintaining.

The question is never whether something can be done digitally. Almost everything can. The question is whether the effort pays for itself within a sensible period.

## Frequently asked questions

### What does a first digitalization step cost?

Less than most people expect, because it is not the whole business. A template or a small capture tool is often a few days of work.

### Where does the real effort sit?

Rarely in the technology. It sits in understanding the workflow first: who does what, in what order and why?

### Do I need an app of my own?

Usually not. A browser application that works well on a phone is enough for most businesses.

### When is a spreadsheet enough?

As long as one person maintains it and nothing has to be reconciled with other systems. More in [Spreadsheet or a tool of your own?](/en/excel-oder-eigenes-werkzeug)

Updated: October 2026. For one week, notice where you enter the same information twice. Then tell me in two sentences what you found. I will tell you honestly whether building something is worth it, and if it is not, that too.
MD,
        ],
        // ---------------------------------------------------------------- 02
        [
            'slug' => 'website-fuenf-dinge-die-fehlen',
            'lang' => 'de',
            'title' => 'Website ohne Anfragen? Fünf Dinge, die fast immer fehlen',
            'excerpt' => 'Die meisten Websites scheitern nicht am Design, sondern an fünf Lücken. So prüfen Sie Ihre Seite in einer Stunde, direkt auf dem Handy.',
            'meta' => 'Warum Besucher abspringen, bevor sie anfragen: fünf Lücken auf Websites kleiner Betriebe, ein Test in einer Stunde und Werkzeuge zum Beheben.',
            'body' => <<<'MD'
**Kurz gesagt:** Die meisten Websites ohne Anfragen sehen ordentlich aus. Ihnen fehlen fünf Dinge: ein klarer Satz zum Angebot, ein Kontaktweg fürs Handy, ein Preisrahmen, Zeichen von Echtheit und ein kleiner nächster Schritt. Prüfen Sie Ihre Seite auf dem Handy, denn dort landen die meisten Besucher.

## 1. Steht in einem Satz, was Sie anbieten?

Lesen Sie nur, was ohne Scrollen sichtbar ist. Steht dort, was Sie tun und für wen?

„Ihr Partner für maßgeschneiderte Lösungen“ sagt nichts. „Elektroinstallation für Altbauten in Schwarzenbek“ sagt alles. Wer nach drei Sekunden nicht weiß, ob er richtig ist, geht zurück zur Trefferliste.

Prüfen Sie auch die Lesbarkeit. Hellgrauer Text auf Weiß ist in der Sonne kaum zu lesen. Der [Kontrast-Checker](https://tools.tracht-digital.de/tools/kontrast-checker) zeigt, ob Ihre Farben das Kontrastverhältnis von 4,5:1 erreichen, das die [WCAG](https://www.w3.org/TR/WCAG22/) für normalen Text empfehlen.

## 2. Erreicht man Sie mit einem Fingertipp?

Eine E-Mail-Adresse im Impressum genügt rechtlich, aber nicht praktisch.

- Die Telefonnummer ist ein Link, der beim Antippen wählt.
- Sie steht oben auf der Seite, nicht nur im Footer.
- Ein kurzes Formular gibt es für alle, die abends schreiben statt anrufen.

## 3. Gibt es einen Preis oder wenigstens einen Rahmen?

„Das hängt vom Aufwand ab“ stimmt fast immer. Gar keine Angabe ist trotzdem die schlechteste Lösung. Wer nichts findet, schätzt selbst und liegt oft daneben. Zu hoch geschätzt heißt: keine Anfrage. Zu niedrig geschätzt heißt: ein enttäuschendes Gespräch.

Ein Ab-Preis, eine typische Spanne oder ein Beispiel mit Umfang und Preis hilft beiden Seiten.

## 4. Sieht man, dass es Sie wirklich gibt?

Eine Seite ohne Ort, Gesicht und Erreichbarkeitszeiten wirkt austauschbar, auch wenn dahinter dreißig Jahre Betrieb stehen. Das braucht wenig:

- **Ihren Ort im Text**, nicht nur im Impressum.
- **Ein echtes Foto** von Ihnen oder Ihrer Werkstatt statt eines Stockbilds. Damit es schnell lädt, verkleinern Sie es vorher mit [Bild komprimieren](https://tools.tracht-digital.de/tools/bild-komprimieren).
- **Vollständige Pflichtangaben.** Der [Impressum-Generator](https://tools.tracht-digital.de/tools/impressum-generator) erstellt ein Muster nach § 5 DDG, der [Datenschutzerklärung-Generator](https://tools.tracht-digital.de/tools/datenschutzerklaerung-generator) eines nach DSGVO. Beide liefern Muster, keine Rechtsberatung.

## 5. Ist der nächste Schritt klein genug?

Zwischen „interessant“ und „ich melde mich“ liegt eine Hürde. Machen Sie sie klein:

- „Beschreiben Sie kurz, worum es geht“ statt „Fordern Sie ein Angebot an“.
- Drei Formularfelder statt acht. Jedes Pflichtfeld kostet Anfragen.
- Offline gilt dasselbe: Ein QR-Code auf Flyer oder Visitenkarte führt direkt zur Kontaktseite. Den Code erstellt der [QR-Code-Generator](https://tools.tracht-digital.de/tools/qr-code-generator), die passende Karte der [Visitenkarten-Designer](https://tools.tracht-digital.de/tools/visitenkarten-designer).

## Woher weiß ich, welche Werbung Anfragen bringt?

Mit markierten Links. Der [UTM-Link-Generator](https://tools.tracht-digital.de/tools/utm-link-generator) hängt Ihren Links Kennungen wie `utm_source` an. Ihre Statistik zeigt dann, ob ein Besuch vom Flyer, vom Newsletter oder von Instagram kam.

## Was braucht eine gute Website nicht?

- Zwanzig halbfertige Seiten. Fünf gute reichen.
- Einen Blog, den niemand pflegt.
- Ein Chatfenster, das niemand beantwortet. Das ist schlimmer als keins.

## Häufige Fragen

### Wie teste ich meine Website am schnellsten?

Geben Sie einer fremden Person Ihr Handy. Bitten Sie sie herauszufinden, was Sie anbieten und wie man Sie erreicht. Was sie zuerst fragt, fehlt auf der Seite.

### Muss ich Preise auf die Website schreiben?

Nicht in jedem Fall. Ein Preisrahmen bringt aber fast immer passendere Anfragen.

### Braucht meine Website ein Impressum?

Fast jede geschäftliche Website braucht eines, geregelt in [§ 5 DDG](https://www.gesetze-im-internet.de/ddg/__5.html). Im Zweifel lassen Sie Ihr Muster rechtlich prüfen.

### Wie groß dürfen Fotos auf der Website sein?

So klein wie möglich bei guter Schärfe. Für ein großes Foto reichen meist 200 bis 400 KB.

Stand: Oktober 2026. Sie wissen jetzt, wo es hakt, aber nicht, wie Sie es lösen? Schreiben Sie mir in zwei Sätzen, was Ihnen aufgefallen ist.
MD,
        ],
        [
            'slug' => 'website-fuenf-dinge-die-fehlen',
            'lang' => 'en',
            'title' => 'No enquiries from your website? Five things that are missing',
            'excerpt' => "Most websites don't fail on design but on five gaps. Here is how to check your own site in an hour, straight on your phone.",
            'meta' => 'Why visitors leave before getting in touch: five gaps on small-business websites, a one-hour test and the tools to close them.',
            'body' => <<<'MD'
**In short:** Most websites that bring no enquiries look perfectly tidy. They lack five things: one clear sentence about the offer, a way to get in touch from a phone, a price range, signs that you are real and a small next step. Check your site on a phone, because that is where most visitors arrive.

## 1. Does one sentence say what you offer?

Read only what is visible without scrolling. Does it say what you do and who for?

"Your partner for tailored solutions" says nothing. "Electrical work for period properties in Schwarzenbek" says everything. Anyone who does not know within three seconds whether they are in the right place goes back to the search results.

Check legibility too. Light grey text on white is barely readable in sunlight. The [Contrast Checker](https://tools.tracht-digital.de/en/tools/kontrast-checker) shows whether your colours reach the 4.5:1 contrast ratio that [WCAG](https://www.w3.org/TR/WCAG22/) recommends for normal text.

## 2. Can people reach you with one tap?

An email address in the imprint is legally fine and practically not enough.

- The phone number is a link that dials when tapped.
- It sits at the top of the page, not only in the footer.
- A short form exists for people who write in the evening rather than call.

## 3. Is there a price, or at least a range?

"It depends on the job" is almost always true. Saying nothing is still the worst option. Visitors who find no figure guess one and often guess wrong. Too high means no enquiry. Too low means a disappointing conversation.

A starting price, a typical range or one worked example with scope and cost helps both sides.

## 4. Can people see that you really exist?

A page with no place, no face and no hours reads as interchangeable, even with thirty years of business behind it. It takes very little:

- **Your town in the text**, not only in the imprint.
- **A real photo** of you or your workshop instead of a stock image. To keep it fast, shrink it first with [Compress Image](https://tools.tracht-digital.de/en/tools/bild-komprimieren).
- **Complete legal pages.** For German sites the [Imprint Generator](https://tools.tracht-digital.de/en/tools/impressum-generator) builds a template under § 5 DDG, the [Privacy Policy Generator](https://tools.tracht-digital.de/en/tools/datenschutzerklaerung-generator) one under the GDPR. Both provide templates, not legal advice.

## 5. Is the next step small enough?

Between "interesting" and "I'll get in touch" there is a hurdle. Make it small:

- "Briefly describe what it's about" instead of "Request a quote".
- Three form fields instead of eight. Every required field costs enquiries.
- The same applies offline: a QR code on a flyer or business card leads straight to your contact page. The [QR Code Generator](https://tools.tracht-digital.de/en/tools/qr-code-generator) creates the code, the [Business Card Designer](https://tools.tracht-digital.de/en/tools/visitenkarten-designer) the card to go with it.

## How do I know which advertising brings enquiries?

With tagged links. The [UTM Link Builder](https://tools.tracht-digital.de/en/tools/utm-link-generator) adds markers such as `utm_source` to your links. Your analytics then show whether a visit came from the flyer, the newsletter or Instagram.

## What does a good website not need?

- Twenty half-finished pages. Five good ones are enough.
- A blog nobody maintains.
- A chat window nobody answers. That is worse than none.

## Frequently asked questions

### What is the quickest way to test my website?

Hand your phone to someone who does not know your business. Ask them to find out what you offer and how to reach you. Whatever they ask first is missing from the site.

### Do I have to put prices on my website?

Not in every case. A price range almost always brings better-matched enquiries, though.

### Does my website need an imprint?

In Germany almost every business website needs one, under [§ 5 DDG](https://www.gesetze-im-internet.de/ddg/__5.html). If in doubt, have your template checked by a lawyer.

### How large may photos on a website be?

As small as possible while staying sharp. For a large photo 200 to 400 KB is usually enough.

Updated: October 2026. You know where it sticks but not how to fix it? Tell me in two sentences what you noticed.
MD,
        ],
        // ---------------------------------------------------------------- 03
        [
            'slug' => 'lohnt-sich-ein-webshop',
            'lang' => 'de',
            'title' => 'Lohnt sich ein Webshop für mein Ladengeschäft? Vier Fragen',
            'excerpt' => 'Ein Webshop ist nie fertig: Bestände, Preise und Bestellungen wollen gepflegt werden. Vier Fragen zeigen, ob sich das für Ihren Laden rechnet.',
            'meta' => 'Lohnt sich ein Webshop für Ihren Laden? Vier Fragen zu Versand, Pflege, Zielgruppe und Bestellungen, dazu Pflichten wie BFSG und ein Zwischenschritt.',
            'body' => <<<'MD'
**Kurz gesagt:** Ein Webshop lohnt sich, wenn Ihr Sortiment gut versendbar ist, die Pflege in den Alltag passt, klar ist, wer kaufen soll, und jemand die Bestellungen zügig bearbeitet. Fehlt eines davon, ist Abholung im Laden oder ein Online-Katalog ohne Kasse oft der bessere Start.

## Was unterscheidet einen Shop von einer Website?

Laufende Arbeit. Eine Website ist irgendwann fertig, ein Shop nie. Bestände und Preise ändern sich, Bestellungen wollen gepackt, Rückfragen beantwortet und Retouren abgewickelt werden. Das ist der eigentliche Kostenpunkt, nicht die Einrichtung.

## 1. Lässt sich Ihr Sortiment gut versenden?

Leicht, haltbar und robust ist die freundliche Seite. Schwer, zerbrechlich, gekühlt oder frisch macht den Versand schnell teurer, als die Marge hergibt.

Dann ist **Abholung** oft das bessere Modell: online aussuchen und bezahlen, im Laden mitnehmen. Das ist einfacher zu betreiben und passt gut zu einem lokalen Geschäft.

## 2. Wie oft ändern sich Preise und Bestände?

Zwanzig stabile Artikel machen kaum Arbeit. Zweihundert Artikel mit wechselnden Preisen sind eine tägliche Aufgabe.

Entscheidend ist, **wo** Sie pflegen. Geht es nur am Rechner im Büro, passiert es abends oder gar nicht. Geht es vom Handy aus, direkt an der Ware, passiert es nebenbei. Mehr dazu im Beitrag [Produktpflege per Handy](/produktpflege-per-handy).

## 3. Wer soll dort kaufen?

- **Ihre Stammkunden**, nur bequemer: realistisch und schnell erreichbar. Sie bestellen vor, sehen die Verfügbarkeit und holen ab.
- **Neue Kunden aus ganz Deutschland:** ein anderes Vorhaben. Sie konkurrieren mit großen Versendern und brauchen ein besonderes Sortiment oder einen Preis, den Sie halten können.

Ob Newsletter oder Instagram Käufer bringen, sehen Sie mit markierten Links aus dem [UTM-Link-Generator](https://tools.tracht-digital.de/tools/utm-link-generator).

## 4. Wer bearbeitet die Bestellungen?

Die unspektakulärste und wichtigste Frage. Eine Bestellung, die drei Tage liegt, kostet den Kunden und manchmal eine Bewertung. Klären Sie vorher:

- Wer sieht die Bestellung?
- Woran merkt diese Person, dass eine da ist?
- Wann am Tag wird gepackt?

Gibt es darauf keine Antwort, ist der Shop noch nicht bereit, egal wie gut er gebaut ist.

## Welche Pflichten bringt ein Onlineshop mit?

Mehr als eine Website. Drei Beispiele:

- **Datenschutz:** Zahlung, Versand und Kundenkonto gehören in die Datenschutzerklärung. Ein Muster erstellt der [Datenschutzerklärung-Generator](https://tools.tracht-digital.de/tools/datenschutzerklaerung-generator).
- **Barrierefreiheit:** Seit dem 28. Juni 2025 gilt das [Barrierefreiheitsstärkungsgesetz](https://www.gesetze-im-internet.de/bfsg/) (BFSG) für viele Shops, die an Verbraucher verkaufen. Die Erklärung dazu erstellt der [Barrierefreiheitserklärung-Generator](https://tools.tracht-digital.de/tools/barrierefreiheitserklaerung-generator). Ob Ihre Farben gut lesbar sind, prüft der [Kontrast-Checker](https://tools.tracht-digital.de/tools/kontrast-checker).
- **Widerrufsbelehrung und AGB:** Hier lohnt sich eine rechtliche Beratung.

Die Werkzeuge liefern Muster, keine Rechtsberatung.

## Gibt es einen sinnvollen Zwischenschritt?

Ja: ein Online-Katalog, der Sortiment, Preise und Verfügbarkeit **zeigt**, ohne zu verkaufen. Reservierungen laufen per Formular oder Telefon.

Das bringt einen großen Teil des Nutzens, denn Leute finden Ihre Produkte online. Zahlungsabwicklung, Versand und tägliche Bestellbearbeitung entfallen. Ein echter Shop setzt später darauf auf, ohne dass Arbeit doppelt anfällt.

## Häufige Fragen

### Was kostet ein Webshop im laufenden Betrieb?

Vor allem Zeit: Pflege, Bestellbearbeitung und Versand. Planen Sie diese Stunden ein, bevor Sie über das System entscheiden.

### Abholung oder Versand?

Bei schwerer, empfindlicher oder frischer Ware meist Abholung. Sie ist einfacher zu betreiben und passt zum lokalen Laden.

### Gilt das BFSG auch für meinen kleinen Shop?

Nicht unbedingt. Kleinstunternehmen mit weniger als zehn Beschäftigten und höchstens zwei Millionen Euro Jahresumsatz sind bei Dienstleistungen ausgenommen. Im Zweifel lassen Sie das prüfen.

### Wann ist ein Baukasten-Shop zu klein?

Wenn Preisrunden nicht mehr in die Arbeitszeit passen und Importe Handarbeit bleiben. Mehr dazu im Beitrag [Wenn der Baukasten-Shop nicht mehr mitwächst](/vom-baukasten-shop-zum-eigenen-shop).

Stand: Oktober 2026. Schreiben Sie mir in zwei Sätzen, was Sie verkaufen und wie es heute läuft. Ich sage Ihnen, welcher der drei Wege zu Ihnen passt: Shop, Abholung oder Katalog.
MD,
        ],
        [
            'slug' => 'lohnt-sich-ein-webshop',
            'lang' => 'en',
            'title' => 'Is an online shop worth it for my local store? Four questions',
            'excerpt' => 'An online shop is never finished: stock, prices and orders need constant care. Four questions show whether that adds up for your store.',
            'meta' => 'Is an online shop worth it for your store? Four questions on shipping, upkeep, customers and orders, plus legal duties and a sensible halfway step.',
            'body' => <<<'MD'
**In short:** An online shop pays off when your range ships well, upkeep fits your working day, it is clear who should buy and someone processes orders promptly. If one of these is missing, click and collect or an online catalogue without checkout is often the better start.

## What makes a shop different from a website?

Ongoing work. A website is finished at some point, a shop never is. Stock and prices change, orders need packing, questions need answers and returns need handling. That is the real cost, not the setup.

## 1. Does your range ship well?

Light, durable and robust is the friendly end. Heavy, fragile, chilled or fresh makes shipping more expensive than the margin allows, fast.

Then **collection** is often the better model: choose and pay online, pick up in the store. It is simpler to run and suits a local business well.

## 2. How often do prices and stock change?

Twenty stable products barely need care. Two hundred products with moving prices are a daily job.

What decides is **where** you do it. If upkeep only works at the office computer, it happens in the evening or not at all. If it works from a phone next to the goods, it happens in passing. More in [Product upkeep from a phone](/en/produktpflege-per-handy).

## 3. Who is meant to buy there?

- **Your regulars**, just more conveniently: realistic and quick to reach. They order ahead, check availability and collect.
- **New customers nationwide:** a different undertaking. You compete with large online sellers and need a special range or a price you can hold.

Whether your newsletter or Instagram brings buyers shows up with links tagged by the [UTM Link Builder](https://tools.tracht-digital.de/en/tools/utm-link-generator).

## 4. Who processes the orders?

The least glamorous and most important question. An order left for three days costs you the customer and sometimes a review. Settle beforehand:

- Who sees the order?
- How does that person know one has arrived?
- What time of day does packing happen?

Without answers the shop is not ready, however well it is built.

## Which legal duties come with an online shop?

More than with a website. Three examples from Germany:

- **Data protection:** payment, shipping and customer accounts belong in the privacy policy. The [Privacy Policy Generator](https://tools.tracht-digital.de/en/tools/datenschutzerklaerung-generator) builds a template.
- **Accessibility:** since 28 June 2025 Germany's [Accessibility Strengthening Act](https://www.gesetze-im-internet.de/bfsg/) (BFSG) applies to many shops selling to consumers. The [Accessibility Statement Generator](https://tools.tracht-digital.de/en/tools/barrierefreiheitserklaerung-generator) drafts the statement. The [Contrast Checker](https://tools.tracht-digital.de/en/tools/kontrast-checker) shows whether your colours are legible.
- **Cancellation policy and terms:** here legal advice pays off.

The tools provide templates, not legal advice.

## Is there a sensible halfway step?

Yes: an online catalogue that **shows** your range, prices and availability without selling. Reservations go through a form or the phone.

That delivers much of the benefit, because people find your products online. Payment handling, shipping and daily order processing fall away. A real shop can later build on it without doing the work twice.

## Frequently asked questions

### What does an online shop cost to run?

Mostly time: upkeep, order processing and shipping. Plan for those hours before choosing a platform.

### Collection or shipping?

For heavy, fragile or fresh goods usually collection. It is simpler to run and suits a local store.

### Does the BFSG apply to my small shop?

Not necessarily. Micro-enterprises with fewer than ten employees and an annual turnover of at most two million euros are exempt for services. If in doubt, have it checked.

### When is a hosted shop builder too small?

When price updates no longer fit into working hours and imports remain manual work. More in [When the hosted shop builder stops keeping up](/en/vom-baukasten-shop-zum-eigenen-shop).

Updated: October 2026. Tell me in two sentences what you sell and how it works today. I will tell you which of the three routes suits you: shop, collection or catalogue.
MD,
        ],
        // ---------------------------------------------------------------- 04
        [
            'slug' => 'excel-oder-eigenes-werkzeug',
            'lang' => 'de',
            'title' => 'Excel oder eigenes Werkzeug? Drei Kipppunkte für den Wechsel',
            'excerpt' => 'Eine Tabelle trägt erstaunlich weit. An drei Punkten kippt sie zuverlässig. Woran Sie diese erkennen und wie der Umstieg ohne Abtippen gelingt.',
            'meta' => 'Wann eine Excel-Tabelle reicht und wann ein eigenes Werkzeug günstiger wird: drei Kipppunkte, eine Faustregel und der Umstieg in vier Schritten.',
            'body' => <<<'MD'
**Kurz gesagt:** Bleiben Sie bei Excel, solange eine Person die Tabelle pflegt, nichts mit anderen Systemen abgeglichen wird und Fehler schnell auffallen. Trifft das in zwei Punkten nicht mehr zu, wird ein eigenes Werkzeug übers Jahr gerechnet meist günstiger.

## Warum ist Excel oft die richtige Antwort?

Eine Tabelle ist sofort da, kostet nichts extra und jeder kann sie bedienen. Für erstaunlich viele Aufgaben reicht sie völlig. Ich habe Kunden schon davon abgeraten, eine gut funktionierende Tabelle zu ersetzen.

## Kipppunkt 1: Arbeiten mehrere Personen daran?

Dann beginnt die bekannte Kette: `Preise_final.xlsx`, `Preise_final_2.xlsx`, `Preise_final_neu_Mai.xlsx`.

Gemeinsame Ablagen helfen nur halb. Das eigentliche Problem ist nicht das gleichzeitige Bearbeiten. Es ist, dass niemand mehr sicher weiß, **welche Datei gilt**.

## Kipppunkt 2: Gibt es Regeln, die eingehalten werden müssen?

Eine Tabelle nimmt alles an: ein Datum in der Mengenspalte, Text im Preisfeld, eine halbe Zeile. Der Fehler fällt Wochen später in einer Auswertung auf.

Pflichtfelder, keine negativen Mengen, jede Nummer nur einmal: Unter Zeitdruck hält das kein Mensch zuverlässig ein. Das ist keine Kritik, sondern Alltag. Ein Werkzeug prüft die Regeln bei jeder Eingabe.

## Kipppunkt 3: Liegen dieselben Daten an zwei Orten?

Stehen Preise oder Kundendaten auch in Kasse, Shop oder Rechnungsprogramm, laufen die Stände auseinander. Nicht vielleicht, sondern sicher.

Der Gewinn eines Werkzeugs ist dann nicht die schönere Oberfläche, sondern **eine einzige Quelle**, aus der sich alle anderen Stellen bedienen.

## Was kann eine Tabelle besser?

Schnell etwas ausprobieren, eine Spalte ergänzen, eine Formel bauen. Diese Freiheit geht zum Teil verloren, denn ein Werkzeug kann das, wofür es gebaut ist.

Der beste Zuschnitt ist deshalb meist: Die **Erfassung** übernimmt das Werkzeug, die **freie Auswertung** bleibt ein Export nach Excel.

## Wie läuft ein Umstieg ab?

1. **Die Tabelle ansehen.** Sie ist die beste Anforderungsliste, die es gibt. Leer gebliebene Spalten braucht niemand.
2. **Die Daten übernehmen**, auch die alten. Nichts wird abgetippt.
3. **Erst erfassen, dann auswerten.** Ein Werkzeug, das nur erfasst, ist nach wenigen Tagen nutzbar.
4. **Parallel vergleichen.** Die alte Tabelle läuft einige Wochen mit. Das ist die beste Kontrolle.

## Welche fertigen Werkzeuge ersetzen typische Tabellen?

Manche Tabelle ist nur ein Umweg zu einem Ausdruck. Dafür gibt es fertige Lösungen im Browser:

- **Arbeitszeiten:** Der [Stundenzettel](https://tools.tracht-digital.de/tools/stundenzettel) zieht Pausen ab und rechnet Tages- und Monatssummen.
- **Adresslisten:** [Etiketten drucken](https://tools.tracht-digital.de/tools/etiketten-drucken) macht aus einer Liste einen druckfertigen Etikettenbogen als PDF.
- **Datenexporte:** Liefert ein System JSON statt CSV, rückt der [JSON-Formatter](https://tools.tracht-digital.de/tools/json-formatter) die Datei lesbar ein und zeigt Fehler mit Position.

## Häufige Fragen

### Ab wie vielen Zeilen wird Excel zum Problem?

Die Zeilenzahl ist selten der Grund. Kritisch werden mehrere Bearbeiter, fehlende Regeln und doppelte Datenhaltung.

### Was kostet ein eigenes Werkzeug?

Ein Werkzeug, das nur erfasst, ist oft in wenigen Tagen nutzbar. Auswertungen kommen danach Schritt für Schritt dazu.

### Kann ich meine Daten aus Excel übernehmen?

Ja, inklusive der historischen Daten. Abgetippt wird nichts.

### Kann ich danach noch in Excel auswerten?

Ja. Ein Export nach Excel bleibt für freie Auswertungen erhalten.

Stand: Oktober 2026. Beschreiben Sie mir Ihre Tabelle in zwei Sätzen: wie viele Zeilen, wie viele Leute, was daran hängt. Meist kann ich daraus schon sagen, ob sich ein Wechsel lohnt.
MD,
        ],
        [
            'slug' => 'excel-oder-eigenes-werkzeug',
            'lang' => 'en',
            'title' => 'Spreadsheet or a tool of your own? Three tipping points',
            'excerpt' => 'A spreadsheet carries you surprisingly far. At three points it reliably tips over. How to spot them and how to switch without retyping anything.',
            'meta' => 'When a spreadsheet is enough and when a purpose-built tool gets cheaper: three tipping points, a rule of thumb and the switch in four steps.',
            'body' => <<<'MD'
**In short:** Stay with your spreadsheet as long as one person maintains it, nothing has to be reconciled with other systems and errors surface quickly. Once two of those no longer hold, a purpose-built tool usually works out cheaper across the year.

## Why is a spreadsheet often the right answer?

It is available at once, costs nothing extra and everyone can use it. For a surprising number of jobs it is entirely enough. I have talked clients out of replacing a spreadsheet that worked well.

## Tipping point 1: do several people work on it?

Then the familiar chain begins: `prices_final.xlsx`, `prices_final_2.xlsx`, `prices_final_new_may.xlsx`.

Shared drives only help halfway. The real problem is not simultaneous editing. It is that nobody knows for sure **which file is the right one**.

## Tipping point 2: are there rules someone has to follow?

A spreadsheet accepts everything: a date in the quantity column, text in the price field, half a row. The error surfaces weeks later in a report.

Required fields, no negative quantities, every number only once: under time pressure no one holds to that reliably. That is not a criticism, it is everyday life. A tool checks the rules on every entry.

## Tipping point 3: does the same data live in two places?

When prices or customer details also sit in the till, the shop or the invoicing software, the copies drift apart. Not possibly, certainly.

The gain from a tool is then not a nicer interface but **one single source** that every other place draws from.

## What does a spreadsheet do better?

Trying something out quickly, adding a column, building a formula. Part of that freedom goes, because a tool does what it was built for.

So the best split is usually: **data capture** moves into the tool, **free-form analysis** stays an export to a spreadsheet.

## How does a switch work?

1. **Look at the spreadsheet.** It is the best requirements list there is. Columns left empty are not needed.
2. **Bring the data across**, history included. Nothing gets retyped.
3. **Capture first, report second.** A tool that only captures is usable within days.
4. **Compare side by side.** The old spreadsheet runs alongside for a few weeks. That is the best check.

## Which ready-made tools replace typical spreadsheets?

Some spreadsheets are just a detour to a printout. Ready-made browser tools cover those:

- **Working hours:** the [Timesheet](https://tools.tracht-digital.de/en/tools/stundenzettel) deducts breaks and adds up daily and monthly totals.
- **Address lists:** [Print Labels](https://tools.tracht-digital.de/en/tools/etiketten-drucken) turns a list into a print-ready label sheet as a PDF.
- **Data exports:** when a system delivers JSON instead of CSV, the [JSON Formatter](https://tools.tracht-digital.de/en/tools/json-formatter) indents it readably and shows errors with their position.

## Frequently asked questions

### At how many rows does a spreadsheet become a problem?

Row count is rarely the reason. Several editors, missing rules and duplicated data are what tip it.

### What does a tool of my own cost?

A tool that only captures data is often usable within a few days. Reports follow step by step.

### Can I bring my spreadsheet data across?

Yes, including the historical data. Nothing is retyped.

### Can I still analyse in a spreadsheet afterwards?

Yes. An export to a spreadsheet stays available for free-form analysis.

Updated: October 2026. Describe your spreadsheet to me in two sentences: how many rows, how many people, what depends on it. That is usually enough for me to say whether switching is worth it.
MD,
        ],
        // ---------------------------------------------------------------- 05
        [
            'slug' => 'konzept-vor-umsetzung',
            'lang' => 'de',
            'title' => 'Warum ein Konzept billiger ist als ein zweiter Versuch',
            'excerpt' => 'Planung fühlt sich wie eine Ausgabe an, bevor etwas passiert. Tatsächlich ist sie die Phase, in der Änderungen fast nichts kosten.',
            'meta' => 'Ein digitales Konzept klärt vor der Umsetzung, was gebraucht wird und was es kostet. Warum sich das rechnet und was in ein gutes Konzept gehört.',
            'body' => <<<'MD'
**Kurz gesagt:** Ein Konzept lohnt sich, weil Änderungen mit jedem Schritt teurer werden. Ein Satz im Konzept ist in Minuten geändert, eine Funktion im laufenden Betrieb kostet Tage. Ein gutes Konzept ist kurz, verständlich und sagt auch, was nicht gebaut wird.

## Was kostet eine Änderung zu welchem Zeitpunkt?

| Zeitpunkt | Aufwand für dieselbe Änderung |
|---|---|
| Im Konzept | Minuten |
| In der Skizze | eine halbe Stunde |
| In der gebauten Bildschirmmaske | ein halber Tag |
| Im laufenden Betrieb | Tage: Umbau, Datenübernahme, Einweisung |

Derselbe Gedanke wird teurer, nur weil er später kommt. Und Sie werden Ihre Meinung ändern. Das ist der Normalfall, kein Versagen.

## Was geht schief, wenn man das Konzept überspringt?

Selten die Technik. Meist eines von zwei Dingen:

- **Eine Kleinigkeit ist doch groß.** „Das brauchen wir auch für Firmenkunden“ klingt nach einer Ergänzung. Es kann eine zweite Preislogik, eine zweite Rechnungsform und andere Steuersätze bedeuten.
- **Das falsche Problem wird gelöst.** Wer mit „Wir brauchen eine App“ beginnt, legt die Antwort fest, bevor die Frage geklärt ist.

## Was steht in einem brauchbaren Konzept?

Zehn verständliche Seiten schlagen vierzig, die niemand liest. Fünf Teile zählen:

1. **Der Ablauf heute:** wer was macht, in welcher Reihenfolge und wo es hakt. Ehrlich, nicht idealisiert.
2. **Der Ablauf danach:** dieselbe Beschreibung, wie es sein soll. Der Unterschied ist der eigentliche Auftrag.
3. **Was nicht dazugehört:** der wertvollste Abschnitt. Er verhindert die stille Ausweitung, an der Vorhaben teuer werden.
4. **Der Umfang in Stufen:** was zuerst kommt, was danach und was vielleicht nie.
5. **Aufwand und Zeitrahmen mit Obergrenze.** Eine Schätzung ohne Obergrenze ist keine.

## Wie wird das Aussehen früh greifbar?

Über Farben, Schriften und Knöpfe spricht man besser am Bildschirm als im Text. Mit dem [Designsystem-Studio](https://tools.tracht-digital.de/tools/designsystem-studio) gestalten Sie eine Komponente visuell und leiten Hell- und Dunkelmodus ab. Das Studio prüft die Kontraste gleich mit. Der Export als CSS, SCSS oder Tailwind ist eine gute Vorlage für die Umsetzung.

Entwürfe markieren Sie deutlich als solche. Das [PDF-Wasserzeichen](https://tools.tracht-digital.de/tools/pdf-wasserzeichen) setzt „Entwurf“ auf jede Seite. So landet keine Zwischenfassung versehentlich in der Druckerei.

## Woran erkenne ich ein schlechtes Konzept?

- **Sie verstehen es nicht.** Was Sie nicht prüfen können, können Sie auch nicht abnehmen.
- **Es beschreibt nur die Technik.** Steht nicht darin, was sich in Ihrem Alltag ändert, wurde über die Lösung geredet und nicht über die Arbeit.

## Häufige Fragen

### Wie lang muss ein Konzept sein?

So kurz wie möglich. Zehn verständliche Seiten reichen für die meisten Vorhaben kleiner Betriebe.

### Was, wenn das Konzept „nicht umsetzen“ ergibt?

Dann hat es sein Geld verdient. Es hat Ihnen eine Umsetzung erspart, die sich nicht rechnet, oder gezeigt, dass eine Standardlösung genügt.

### Wer schreibt das Konzept?

Am besten beide Seiten gemeinsam. Sie kennen den Ablauf, der Entwickler kennt die Technik. Fehlt eine Seite, fehlt die Hälfte.

### Woran erkenne ich einen guten Dienstleister?

Er rät Ihnen auch einmal ab. Wer nie von etwas abrät, verkauft Ihnen etwas.

Stand: Oktober 2026. Sie haben eine Idee, aber noch keinen Plan? Beschreiben Sie sie mir in zwei Sätzen. Sortieren können wir sie gemeinsam.
MD,
        ],
        [
            'slug' => 'konzept-vor-umsetzung',
            'lang' => 'en',
            'title' => 'Why a concept is cheaper than a second attempt',
            'excerpt' => 'Planning feels like spending before anything happens. In fact it is the stage where changes cost almost nothing.',
            'meta' => 'A digital concept settles what is needed and what it costs before anything is built. Why that pays off and what belongs in a good concept.',
            'body' => <<<'MD'
**In short:** A concept pays off because changes get more expensive with every step. A sentence in a concept changes in minutes, a feature already in use costs days. A good concept is short, readable and also says what will not be built.

## What does a change cost at each stage?

| Stage | Effort for the same change |
|---|---|
| In the concept | minutes |
| In a sketch | half an hour |
| In a built screen | half a day |
| In daily use | days: rebuild, data migration, retraining |

The same thought costs more purely because it arrives later. And you will change your mind. That is the normal case, not a failure.

## What goes wrong when you skip it?

Rarely the technology. Usually one of two things:

- **A small thing turns out big.** "We need this for business customers too" sounds like an addition. It can mean a second pricing logic, a second invoice format and different tax rates.
- **The wrong problem gets solved.** Starting with "we need an app" fixes the answer before the question is settled.

## What belongs in a concept worth having?

Ten readable pages beat forty nobody reads. Five parts matter:

1. **The workflow today:** who does what, in what order and where it sticks. Honest, not idealised.
2. **The workflow afterwards:** the same description, as it should be. The difference is the actual brief.
3. **What is out of scope:** the most valuable section. It prevents the quiet expansion that makes projects expensive.
4. **Scope in stages:** what comes first, what after and what possibly never.
5. **Effort and timeframe with a ceiling.** An estimate without a ceiling is not one.

## How does the look become tangible early?

Colours, fonts and buttons are easier to discuss on screen than in text. With the [Design System Studio](https://tools.tracht-digital.de/en/tools/designsystem-studio) you shape a component visually and derive light and dark themes. The studio checks contrast as you go. Its export as CSS, SCSS or Tailwind is a good template for the build.

Mark drafts clearly as drafts. [PDF Watermark](https://tools.tracht-digital.de/en/tools/pdf-wasserzeichen) stamps "Draft" on every page, so no interim version ends up at the printer by mistake.

## How do I spot a bad concept?

- **You do not understand it.** A concept you cannot check is one you cannot sign off.
- **It only describes the technology.** If it does not say what changes in your working day, the conversation was about the solution and not about the work.

## Frequently asked questions

### How long should a concept be?

As short as possible. Ten readable pages are enough for most projects of a small business.

### What if the concept says "don't build it"?

Then it has earned its fee. It has saved you a build that would not pay off, or shown that an off-the-shelf product will do.

### Who writes the concept?

Ideally both sides together. You know the workflow, the developer knows the technology. If one side is missing, half is missing.

### How do I recognise a good contractor?

They sometimes advise against things. Anyone who never advises against anything is selling you something.

Updated: October 2026. You have an idea but no plan yet? Describe it to me in two sentences. We can sort it out together.
MD,
        ],
        // ---------------------------------------------------------------- 06
        [
            'slug' => 'produktpflege-per-handy',
            'lang' => 'de',
            'title' => 'Produktpflege per Handy: Bestand direkt am Regal erfassen',
            'excerpt' => 'Wer Ware annimmt, steht selten am Rechner. Was sich ändert, wenn Artikel und Bestände direkt am Regal per Handy gepflegt werden.',
            'meta' => 'Bestandspflege scheitert selten am Wollen, sondern am Ort. Was die Erfassung per Handy am Regal bringt, worauf es ankommt und womit Sie anfangen.',
            'body' => <<<'MD'
**Kurz gesagt:** Bestandspflege bleibt liegen, weil die Ware im Lager ankommt und der Rechner im Büro steht. Wird direkt am Regal per Handy erfasst, entfallen Zettel, doppelte Eingabe und veraltete Bestände. Dafür braucht es keine App aus dem App Store, sondern eine gut gebaute Anwendung im Browser.

## Warum bleibt Bestandspflege liegen?

Weil sie am falschen Ort stattfindet. Die Ware kommt an der Rampe, im Lager oder im Verkaufsraum an. Der Rechner steht im Büro. Also landet der Wareneingang auf einem Zettel, im besten Fall am selben Tag im System, meist abends, manchmal nie.

Das ist kein Disziplinproblem. Es ist ein Wegeproblem.

## Was kostet der Umweg über den Zettel?

- **Veraltete Bestände.** Zwischen Wareneingang und Eintrag ist jede Auskunft geraten. Im Onlineshop heißt das: verkauft, was nicht da ist.
- **Doppelte Arbeit.** Einmal auf den Zettel, einmal in den Rechner. Der zweite Durchgang ohne Ware vor Augen ist der fehleranfällige.
- **Stapel.** Was nicht sofort erledigt wird, wird zur Abendaufgabe. Und Abendaufgaben werden schlecht erledigt.

## Was ändert sich, wenn am Regal erfasst wird?

Die Erfassung dauert nicht kürzer, aber sie passiert dort, wo Sie ohnehin stehen. Aus zwei Vorgängen wird einer, und die zweite Fehlerquelle fällt weg.

Dazu kommt ein Vorteil, den man sofort merkt: Sie können nachsehen, während der Kunde vor Ihnen steht. Statt „Ich rufe Sie zurück“ gibt es gleich eine Antwort.

## Worauf kommt es bei mobiler Erfassung an?

- **Große Bedienflächen.** Wer ein Paket in der Hand hält, tippt nicht präzise.
- **Wenige Felder pro Vorgang.** Was nicht jetzt nötig ist, wird später ergänzt.
- **Es funktioniert auch bei schlechtem Empfang.** Gehen Eingaben verloren, nutzt es niemand ein zweites Mal.
- **Suchen statt scrollen.** Bei zweihundert Artikeln hilft nur eine Suche nach Name und Nummer, gern per Barcode über die Handykamera.

## Was hilft bei Etiketten und Produktfotos?

Gute Erfassung braucht gute Beschriftung. Mit [Etiketten drucken](https://tools.tracht-digital.de/tools/etiketten-drucken) erzeugen Sie Regal- und Lagerplatzetiketten als PDF für gängige Etikettenbögen. Ein QR-Code aus dem [QR-Code-Generator](https://tools.tracht-digital.de/tools/qr-code-generator) auf dem Etikett führt direkt zum Artikel.

Produktfotos vom Handy sind oft mehrere Megabyte groß. [Bild komprimieren](https://tools.tracht-digital.de/tools/bild-komprimieren) verkleinert ganze Stapel auf eine Zielgröße, bevor sie in Shop oder Katalog landen.

## Womit sollte ich anfangen?

Mit **einem** Vorgang, nicht mit der ganzen Warenwirtschaft. Meist ist das der Wareneingang oder die Bestandskorrektur. Beide sind häufig, kurz und klar abgegrenzt.

Passiert dieser Vorgang nach zwei Wochen wirklich am Regal, haben Sie Ihre Antwort. Wenn nicht, liegt es an der Bedienung und nicht an der Idee. Das lässt sich reparieren.

## Häufige Fragen

### Brauche ich eine App aus dem App Store?

Nein. Eine Web-App, die sich auf den Startbildschirm legen lässt, reicht. Sie läuft auf Android und iPhone, ohne Installation und Freigabeprozess.

### Brauche ich einen Barcode-Scanner?

Meist nicht. Die Kamera des Handys liest Barcodes zuverlässig.

### Was passiert ohne Netz im Lager?

Eine gute Lösung speichert Eingaben zwischen und überträgt sie, sobald wieder Empfang da ist.

### Lohnt sich das auch bei wenigen Artikeln?

Bei zwanzig stabilen Artikeln kaum. Ab einigen hundert Artikeln oder bei häufigen Lieferungen merken Sie den Unterschied jeden Tag.

Stand: Oktober 2026. Schreiben Sie mir in zwei Sätzen, wie die Erfassung bei Ihnen heute läuft. Meist sieht man daran schon, welcher Vorgang der richtige Anfang ist.
MD,
        ],
        [
            'slug' => 'produktpflege-per-handy',
            'lang' => 'en',
            'title' => 'Product upkeep from a phone: record stock at the shelf',
            'excerpt' => 'People taking in stock are rarely at a computer. What changes when items and stock levels are maintained from a phone, right at the shelf.',
            'meta' => 'Stock upkeep rarely fails on willingness but on location. What recording stock by phone at the shelf brings, what makes it work and where to start.',
            'body' => <<<'MD'
**In short:** Stock upkeep gets left undone because goods arrive in the storeroom while the computer sits in the office. Recording stock by phone at the shelf removes the paper slip, the double entry and outdated figures. You do not need an app from an app store for it, just a well-built browser application.

## Why does stock upkeep get left undone?

Because it happens in the wrong place. Goods arrive at the loading bay, in the storeroom or on the shop floor. The computer is in the office. So goods-in goes onto a slip of paper, into the system the same day at best, usually in the evening, sometimes never.

That is not a discipline problem. It is a distance problem.

## What does the detour via paper cost?

- **Outdated stock figures.** Between arrival and entry every answer is a guess. In an online shop that means selling what is not there.
- **Double work.** Once on the slip, once into the computer. The second pass, without the goods in front of you, is the error-prone one.
- **Piles.** Anything not done at once becomes an evening task. And evening tasks get done badly.

## What changes when you record at the shelf?

Recording does not get faster, but it happens where you are already standing. Two steps become one, and the second source of error disappears.

There is a benefit people notice at once: you can look things up while the customer is in front of you. Instead of "I'll call you back" there is an answer straight away.

## What makes mobile recording work?

- **Large touch targets.** Anyone holding a box does not tap precisely.
- **Few fields per step.** Whatever is not needed now gets added later.
- **It works with poor reception.** If entries get lost, nobody uses it a second time.
- **Search, don't scroll.** With two hundred items only search by name and number helps, ideally with barcode scanning through the phone camera.

## What helps with labels and product photos?

Good recording needs good labelling. [Print Labels](https://tools.tracht-digital.de/en/tools/etiketten-drucken) creates shelf and bin labels as a PDF for common label sheets. A QR code from the [QR Code Generator](https://tools.tracht-digital.de/en/tools/qr-code-generator) on the label leads straight to the item.

Product photos from a phone are often several megabytes. [Compress Image](https://tools.tracht-digital.de/en/tools/bild-komprimieren) shrinks whole batches to a target size before they go into your shop or catalogue.

## Where should I start?

With **one** operation, not the whole inventory system. Usually that is goods-in or a stock correction. Both are frequent, short and clearly bounded.

If after two weeks that operation really happens at the shelf, you have your answer. If not, the problem is the handling, not the idea. That can be fixed.

## Frequently asked questions

### Do I need an app from an app store?

No. A web app that can be added to the home screen is enough. It runs on Android and iPhone, without installation or approval processes.

### Do I need a barcode scanner?

Usually not. The phone camera reads barcodes reliably.

### What happens without signal in the storeroom?

A good solution stores entries locally and sends them as soon as there is reception again.

### Is it worth it with only a few items?

With twenty stable items, hardly. From a few hundred items or with frequent deliveries you notice the difference every day.

Updated: October 2026. Tell me in two sentences how recording works for you today. That usually shows which operation is the right place to start.
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
