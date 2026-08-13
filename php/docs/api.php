<?php
/**
 * API documentation for this module's routes — consumed through `ApiDocSource`
 * and rendered in the admin frontend's API reference (`GET /wiki.json`).
 *
 * `pattern` must match the Slim pattern in `register()` VERBATIM, inline regex
 * included: it is the join key for the route introspection.
 * `php/tests/BlogCmsApiDocsTest.php` fails the build if the documented set and
 * the registered set drift apart in either direction.
 */

declare(strict_types=1);

$blog = ['in' => 'path', 'name' => 'blog', 'type' => 'slug', 'required' => true, 'description' => 'Blog-Schlüssel (Kebab).'];
$slug = ['in' => 'path', 'name' => 'slug', 'type' => 'slug', 'required' => true, 'description' => 'Slug des Beitrags (Kebab). Er ist zusammen mit der Sprache der Schlüssel.'];
$langQuery = ['in' => 'query', 'name' => 'lang', 'type' => 'de|en', 'description' => 'Sprache; Vorgabe `de`.'];

return [
    [
        'method' => 'GET',
        'pattern' => '/content/blog',
        'tag' => 'Öffentlich',
        'summary' => 'Veröffentlichte Beiträge des Standard-Blogs (Build-Quelle)',
        'description' => 'Die Nachfolgerin von `tds-content-api`s offener `/content/blog`, '
            . 'unter demselben Pfad, damit Blog und Landingpage **unverändert** '
            . 'weiterlesen. Liefert nur veröffentlichte Beiträge in der camelCase-Form, '
            . 'die `tds-shared` als `BlogPost` definiert; der Text bleibt Markdown und '
            . 'wird erst im Frontend gerendert. **Ein Datenbankproblem gibt eine leere '
            . 'Seite zurück, niemals 500** — der statische Build fällt dann auf seine '
            . 'eingebauten Vorgaben zurück statt zu scheitern.',
        'auth' => 'public',
        'params' => [
            $langQuery,
            ['in' => 'query', 'name' => 'limit', 'type' => 'int', 'description' => 'Seitengröße.'],
            ['in' => 'query', 'name' => 'cursor', 'type' => 'string', 'description' => 'Cursor aus der vorigen Antwort.'],
        ],
        'responses' => [
            ['status' => 200, 'description' => '`{posts: [...], nextCursor}` — bei einem Fehler eine leere Seite.'],
        ],
    ],
    [
        'method' => 'GET',
        'pattern' => '/content/blog/popular',
        'tag' => 'Öffentlich',
        'summary' => 'Beliebte Beiträge — mangels Zähler die neuesten',
        'description' => 'Dieses Modul führt **keinen** Aufrufzähler, „beliebt" fällt daher '
            . 'bewusst auf „neueste zuerst" zurück. Die Route existiert trotzdem, weil '
            . 'das Blog-Frontend einen gefüllten Tab erwartet; ein 404 wäre dort eine '
            . 'leere Rubrik.',
        'auth' => 'public',
        'params' => [
            $langQuery,
            ['in' => 'query', 'name' => 'limit', 'type' => 'int', 'description' => 'Seitengröße.'],
        ],
        'responses' => [
            ['status' => 200, 'description' => '`{posts: [...]}`'],
        ],
    ],
    [
        'method' => 'GET',
        'pattern' => '/content/blog/{slug:[a-z0-9-]+}',
        'tag' => 'Öffentlich',
        'summary' => 'Einen veröffentlichten Beitrag lesen',
        'description' => 'Nur aus dem Standard-Blog und nur veröffentlicht — ein Entwurf '
            . 'ist über diese Route nicht erreichbar und ergibt 404.',
        'auth' => 'public',
        'params' => [$slug, $langQuery],
        'responses' => [
            ['status' => 200, 'description' => 'Der Beitrag in der `BlogPost`-Form.'],
            ['status' => 404, 'description' => 'Kein veröffentlichter Beitrag mit diesem Slug — auch bei einem Datenbankfehler.'],
        ],
    ],
    [
        'method' => 'GET',
        'pattern' => '/content/topics',
        'tag' => 'Öffentlich',
        'summary' => 'Kuratierte Themen — bewusst leer',
        'description' => 'Kuratierte Themen waren eine Funktion von `tds-content-api`, für '
            . 'die es hier keine Entsprechung gibt. Die Route antwortet in der Form, '
            . 'die die Frontends ohnehin als Rückfall behandeln, damit deren Build '
            . 'grün bleibt statt an einem 404 zu scheitern.',
        'auth' => 'public',
        'params' => [$langQuery],
        'responses' => [
            ['status' => 200, 'description' => 'Die „nichts gepflegt"-Form (leere Themen).'],
        ],
    ],
    [
        'method' => 'GET',
        'pattern' => '/content/snippets',
        'tag' => 'Öffentlich',
        'summary' => 'Eigene Textbausteine — bewusst leer',
        'description' => 'Wie `/content/topics`: übernommen, damit die öffentlichen Builds '
            . 'unverändert durchlaufen.',
        'auth' => 'public',
        'responses' => [
            ['status' => 200, 'description' => 'Leere Textbausteine.'],
        ],
    ],
    [
        'method' => 'GET',
        'pattern' => '/blog/summary',
        'tag' => 'Redaktion',
        'summary' => 'Anzahl der Beiträge',
        'description' => 'Die `dataEndpoint`-Route des Dashboard-Widgets.',
        'permission' => 'blog:read',
        'responses' => [
            ['status' => 200, 'description' => '`{posts: <Anzahl>}`'],
            ['status' => 401, 'description' => 'Keine Sitzung.'],
            ['status' => 403, 'description' => 'Kein `blog:read`.'],
        ],
    ],
    [
        'method' => 'GET',
        'pattern' => '/blogs',
        'tag' => 'Blogs',
        'summary' => 'Alle Blogs mit ihrer Rebuild-Konfiguration',
        'permission' => 'blog:read',
        'responses' => [
            ['status' => 200, 'description' => '`{blogs: [{id, blog_key, name, rebuild_repo, rebuild_workflow, is_default}]}`'],
            ['status' => 401, 'description' => 'Keine Sitzung.'],
            ['status' => 403, 'description' => 'Kein `blog:read`.'],
        ],
    ],
    [
        'method' => 'POST',
        'pattern' => '/blogs',
        'tag' => 'Blogs',
        'summary' => 'Blog anlegen',
        'permission' => 'blog:write',
        'params' => [
            ['in' => 'body', 'name' => 'blog_key', 'type' => 'slug', 'required' => true, 'description' => 'Kebab-Schlüssel, eindeutig.'],
            ['in' => 'body', 'name' => 'name', 'type' => 'string', 'required' => true, 'description' => 'Anzeigename.'],
        ],
        'responses' => [
            ['status' => 201, 'description' => '`{id}`'],
            ['status' => 401, 'description' => 'Keine Sitzung.'],
            ['status' => 403, 'description' => 'Kein `blog:write`.'],
            ['status' => 409, 'description' => '`blog_key` ist bereits vergeben.'],
            ['status' => 422, 'description' => '`blog_key` ist kein Kebab-Slug, oder `name` fehlt.'],
        ],
    ],
    [
        'method' => 'GET',
        'pattern' => '/blog/authors',
        'tag' => 'Autoren',
        'summary' => 'Autorenverzeichnis für Bylines',
        'description' => 'Blog-übergreifend: ein Autor kann in mehreren Blogs zeichnen.',
        'permission' => 'blog:read',
        'responses' => [
            ['status' => 200, 'description' => '`{authors: [{id, name, bio, avatar_url, user_id}]}`'],
            ['status' => 401, 'description' => 'Keine Sitzung.'],
            ['status' => 403, 'description' => 'Kein `blog:read`.'],
        ],
    ],
    [
        'method' => 'POST',
        'pattern' => '/blog/authors',
        'tag' => 'Autoren',
        'summary' => 'Autor anlegen oder die Momentaufnahme eines Nutzers auffrischen',
        'description' => 'Mit `user_id` ist der Autor an einen Panel-Nutzer gebunden und es '
            . 'gibt **genau eine** Momentaufnahme je Nutzer (erneutes Senden frischt '
            . 'sie auf, statt ein Duplikat anzulegen). Ohne `user_id` entsteht ein '
            . 'freier Gastautor.',
        'permission' => 'blog:write',
        'params' => [
            ['in' => 'body', 'name' => 'name', 'type' => 'string', 'required' => true, 'description' => 'Anzeigename der Byline.'],
            ['in' => 'body', 'name' => 'user_id', 'type' => 'int', 'description' => 'Panel-Nutzer, an den die Byline gebunden wird.'],
            ['in' => 'body', 'name' => 'bio', 'type' => 'string', 'description' => 'Optional, auf 500 Zeichen gekürzt.'],
            ['in' => 'body', 'name' => 'avatar_url', 'type' => 'string', 'description' => 'Optional, auf 500 Zeichen gekürzt.'],
        ],
        'responses' => [
            ['status' => 201, 'description' => '`{id}`'],
            ['status' => 401, 'description' => 'Keine Sitzung.'],
            ['status' => 403, 'description' => 'Kein `blog:write`.'],
            ['status' => 422, 'description' => '`name` fehlt.'],
        ],
    ],
    [
        'method' => 'DELETE',
        'pattern' => '/blog/authors/{id:[0-9]+}',
        'tag' => 'Autoren',
        'summary' => 'Autor entfernen',
        'permission' => 'blog:write',
        'params' => [
            ['in' => 'path', 'name' => 'id', 'type' => 'int', 'required' => true, 'description' => 'Id des Autors.'],
        ],
        'responses' => [
            ['status' => 200, 'description' => '`{ok: true}`'],
            ['status' => 401, 'description' => 'Keine Sitzung.'],
            ['status' => 403, 'description' => 'Kein `blog:write`.'],
        ],
    ],
    [
        'method' => 'GET',
        'pattern' => '/blogs/{blog:[a-z0-9-]+}/posts',
        'tag' => 'Redaktion',
        'summary' => 'Beiträge eines Blogs — Entwürfe eingeschlossen',
        'description' => 'Die redaktionelle Sicht, im Gegensatz zur öffentlichen '
            . '`/content/blog`: hier sind auch unveröffentlichte Beiträge dabei.',
        'permission' => 'blog:read',
        'params' => [$blog, $langQuery],
        'responses' => [
            ['status' => 200, 'description' => '`{posts: [...]}`'],
            ['status' => 401, 'description' => 'Keine Sitzung.'],
            ['status' => 403, 'description' => 'Kein `blog:read`.'],
            ['status' => 404, 'description' => 'Unbekannter Blog.'],
        ],
    ],
    [
        'method' => 'GET',
        'pattern' => '/blogs/{blog:[a-z0-9-]+}/posts/{slug:[a-z0-9-]+}',
        'tag' => 'Redaktion',
        'summary' => 'Einen Beitrag zum Bearbeiten lesen',
        'permission' => 'blog:read',
        'params' => [$blog, $slug, $langQuery],
        'responses' => [
            ['status' => 200, 'description' => 'Der Beitrag mit allen redaktionellen Feldern.'],
            ['status' => 401, 'description' => 'Keine Sitzung.'],
            ['status' => 403, 'description' => 'Kein `blog:read`.'],
            ['status' => 404, 'description' => 'Unbekannter Blog oder Beitrag.'],
        ],
    ],
    [
        'method' => 'PUT',
        'pattern' => '/blogs/{blog:[a-z0-9-]+}/posts/{slug:[a-z0-9-]+}',
        'tag' => 'Redaktion',
        'summary' => 'Beitrag speichern (anlegen oder überschreiben)',
        'description' => 'Ein Upsert über (Blog, Slug, Sprache). Drei Seiteneffekte: das '
            . 'Speichern **löscht das `machine_translated`-Kennzeichen** (Handarbeit '
            . 'sticht Maschine), es **legt die Gegensprache maschinell an** (DeepL, '
            . 'best effort, nur für Veröffentlichtes), und ein '
            . '**veröffentlichter** Speichervorgang **stößt den Rebuild des statischen '
            . 'Blogs an** — ein Entwurf nicht. `published_at` wird beim ersten '
            . 'Veröffentlichen automatisch gesetzt. Eine unbekannte `author_id` wird '
            . 'stillschweigend verworfen statt die Anfrage abzulehnen: eine gelöschte '
            . 'Byline soll das Speichern nicht blockieren.',
        'permission' => 'blog:write',
        'params' => [
            $blog,
            $slug,
            ['in' => 'body', 'name' => 'title', 'type' => 'string', 'required' => true, 'description' => 'Darf nicht leer sein.'],
            ['in' => 'body', 'name' => 'body', 'type' => 'markdown', 'required' => true, 'description' => 'Der Beitragstext als Markdown. Darf nicht leer sein.'],
            ['in' => 'body', 'name' => 'lang', 'type' => 'de|en', 'description' => 'Sprache dieser Fassung; Vorgabe `de`.'],
            ['in' => 'body', 'name' => 'draft', 'type' => 'bool', 'description' => 'Vorgabe **true** — ohne Angabe wird als Entwurf gespeichert.'],
            ['in' => 'body', 'name' => 'category', 'type' => 'string', 'description' => 'Vorgabe `allgemein`.'],
            ['in' => 'body', 'name' => 'excerpt', 'type' => 'string', 'description' => 'Anrisstext.'],
            ['in' => 'body', 'name' => 'meta_description', 'type' => 'string', 'description' => 'SEO-Beschreibung, auf 300 Zeichen gekürzt.'],
            ['in' => 'body', 'name' => 'tags', 'type' => 'string', 'description' => 'Kommaliste, auf 200 Zeichen gekürzt.'],
            ['in' => 'body', 'name' => 'cover_hint', 'type' => 'string', 'description' => 'Hinweis für das Titelbild.'],
            ['in' => 'body', 'name' => 'author_id', 'type' => 'int', 'description' => 'Byline. Unbekannte Ids werden verworfen.'],
            ['in' => 'body', 'name' => 'published_at', 'type' => 'datetime', 'description' => 'Nur für Veröffentlichtes; leer ⇒ jetzt.'],
        ],
        'responses' => [
            ['status' => 200, 'description' => '`{ok: true, translated: bool}` — `translated` sagt, ob die Gegensprache geschrieben wurde.'],
            ['status' => 401, 'description' => 'Keine Sitzung.'],
            ['status' => 403, 'description' => 'Kein `blog:write`.'],
            ['status' => 404, 'description' => 'Unbekannter Blog.'],
            ['status' => 422, 'description' => '`title` oder `body` fehlt.'],
        ],
    ],
    [
        'method' => 'DELETE',
        'pattern' => '/blogs/{blog:[a-z0-9-]+}/posts/{slug:[a-z0-9-]+}',
        'tag' => 'Redaktion',
        'summary' => 'Beitrag löschen',
        'permission' => 'blog:write',
        'params' => [$blog, $slug, $langQuery],
        'responses' => [
            ['status' => 200, 'description' => '`{ok: true}`'],
            ['status' => 401, 'description' => 'Keine Sitzung.'],
            ['status' => 403, 'description' => 'Kein `blog:write`.'],
            ['status' => 404, 'description' => 'Unbekannter Blog.'],
        ],
    ],
    [
        'method' => 'PUT',
        'pattern' => '/blogs/{blog:[a-z0-9-]+}/rebuild-config',
        'tag' => 'Rebuild',
        'summary' => 'Rebuild-Ziel eines Blogs setzen',
        'description' => 'Welches Repository und welcher Workflow nach einer Veröffentlichung '
            . 'gebaut werden. Leere Werte löschen die Konfiguration.',
        'permission' => 'blog:write',
        'params' => [
            $blog,
            ['in' => 'body', 'name' => 'rebuild_repo', 'type' => 'string', 'description' => 'Muss `owner/name` sein. Leer löscht.'],
            ['in' => 'body', 'name' => 'rebuild_workflow', 'type' => 'string', 'description' => 'Dateiname des Workflows.'],
        ],
        'responses' => [
            ['status' => 200, 'description' => '`{ok: true}`'],
            ['status' => 401, 'description' => 'Keine Sitzung.'],
            ['status' => 403, 'description' => 'Kein `blog:write`.'],
            ['status' => 404, 'description' => 'Unbekannter Blog.'],
            ['status' => 422, 'description' => '`rebuild_repo` ist nicht `owner/name`.'],
        ],
    ],
    [
        'method' => 'POST',
        'pattern' => '/blogs/{blog:[a-z0-9-]+}/rebuild',
        'tag' => 'Rebuild',
        'summary' => 'Rebuild eines Blogs von Hand auslösen',
        'description' => 'Zwei getrennte Gründe für ein Nein: gar kein Rebuild-Token '
            . 'hinterlegt (503, betrifft alle Blogs) oder kein Repository an diesem Blog '
            . '(422, betrifft nur diesen). **202**, weil der Build danach erst anläuft.',
        'permission' => 'blog:write',
        'params' => [$blog],
        'responses' => [
            ['status' => 202, 'description' => '`{ok: true}` — der Workflow wurde angestoßen.'],
            ['status' => 401, 'description' => 'Keine Sitzung.'],
            ['status' => 403, 'description' => 'Kein `blog:write`.'],
            ['status' => 404, 'description' => 'Unbekannter Blog.'],
            ['status' => 422, 'description' => 'Für diesen Blog ist kein Rebuild-Repository konfiguriert.'],
            ['status' => 503, 'description' => 'Kein Rebuild-Token konfiguriert.'],
        ],
    ],
    [
        'method' => 'POST',
        'pattern' => '/blogs/{blog:[a-z0-9-]+}/translations/backfill',
        'tag' => 'Redaktion',
        'summary' => 'Fehlende Übersetzungen für alle Beiträge nachziehen',
        'description' => 'Läuft über die von Hand gepflegten Beiträge und legt die '
            . 'Gegensprache maschinell an. **Maschinelle Fassungen sind Ziele, keine '
            . 'Quellen** und werden übersprungen — sonst würde eine Übersetzung eine '
            . 'Übersetzung übersetzen.',
        'permission' => 'blog:write',
        'params' => [$blog],
        'responses' => [
            ['status' => 200, 'description' => '`{created, skipped}`'],
            ['status' => 401, 'description' => 'Keine Sitzung.'],
            ['status' => 403, 'description' => 'Kein `blog:write`.'],
            ['status' => 404, 'description' => 'Unbekannter Blog.'],
            ['status' => 503, 'description' => 'Kein DeepL-Schlüssel hinterlegt oder Auto-Übersetzung abgeschaltet.'],
        ],
    ],
];
