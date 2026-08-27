import { useEffect, useMemo, useState } from "react";
import { ConfirmDialog, Spinner, toast } from "@tracht-digital-solutions/tds-shared/components";
import { apiFetch } from "@tracht-digital-solutions/tds-shared/api";
import { invalidate, staleClass, useCachedJson } from "@tracht-digital-solutions/tds-shared/data";
// The escape-first renderer moved to tds-shared: the customer wiki renders
// handbook articles with the same function, and an XSS boundary must not
// exist twice. Its test suite moved with it.
import { renderMarkdown } from "@tracht-digital-solutions/tds-shared/markdown";

interface Blog {
  id: number;
  blog_key: string;
  name: string;
  /**
   * Origin of the public blog whose page cache a save rebuilds. Configured
   * under Einstellungen → Blog-CMS, not here.
   */
  cache_url?: string | null;
}

interface PostMeta {
  slug: string;
  lang: string;
  title: string;
  draft: number | boolean;
  machine_translated?: number | boolean;
  author_name?: string | null;
  published_at: string | null;
}

interface Author {
  id: number;
  user_id?: number | null;
  name: string;
  bio?: string | null;
  avatar_url?: string | null;
}

interface PanelUser {
  id: number;
  email: string;
  name?: string | null;
  isAdmin?: boolean;
  isBlogAuthor?: boolean;
}

interface PostDraft {
  slug: string;
  lang: string;
  category: string;
  title: string;
  excerpt: string;
  meta_description: string;
  tags: string;
  cover_hint: string;
  body: string;
  author_id: number;
  draft: boolean;
}

const api = apiFetch;

const EMPTY_POST: PostDraft = {
  slug: "",
  lang: "de",
  category: "allgemein",
  title: "",
  excerpt: "",
  meta_description: "",
  tags: "",
  cover_hint: "",
  body: "",
  author_id: 0,
  draft: true,
};

/**
 * Blog-CMS — the CONTENT screen: pick a blog, pick an article, edit it.
 *
 * ### What is deliberately NOT here any more
 *
 * Adding and connecting a blog moved to **Einstellungen → Blog-CMS**
 * (`BlogRegistry.tsx`). Connection controls were sitting above the article list, on
 * the screen someone opens to write. This one answers a single question: what
 * does this article say.
 *
 * ### Stale-while-revalidate
 *
 * The blog list, the article list and the author list all read through
 * `useCachedJson`, so returning to this screen paints last visit's contents
 * immediately and refreshes them behind the user. A list being refreshed wears
 * `tds-stale` — dimmed and pulsing — because data that may already be wrong
 * must not look current.
 */
export default function BlogsList() {
  const blogsQuery = useCachedJson<{ blogs: Blog[] }>("/blogs");
  const blogs = useMemo(() => blogsQuery.data?.blogs ?? [], [blogsQuery.data]);
  const [selectedKey, setSelectedKey] = useState<string | null>(null);

  // Follow the registry: a blog that disappears (or the first one to arrive)
  // must not leave the screen pointing at nothing.
  useEffect(() => {
    if (blogs.length === 0) {
      if (selectedKey !== null) setSelectedKey(null);
      return;
    }
    if (selectedKey === null || !blogs.some((b) => b.blog_key === selectedKey)) {
      setSelectedKey(blogs[0]?.blog_key ?? null);
    }
  }, [blogs, selectedKey]);

  const selected = blogs.find((b) => b.blog_key === selectedKey) ?? null;

  if (blogsQuery.loading) {
    return (
      <p>
        <Spinner />
      </p>
    );
  }

  if (blogsQuery.error && blogs.length === 0) {
    return (
      <p className="tds-alert tds-alert--danger" role="alert">
        Blogs konnten nicht geladen werden ({blogsQuery.error.message}).
      </p>
    );
  }

  if (blogs.length === 0) {
    return (
      <div className="tds-empty">
        <p>Noch kein Blog verbunden.</p>
        <p className="marginalia">
          Blogs werden unter <a className="link-underline" href="/einstellungen">Einstellungen → Blog-CMS</a>{" "}
          hinzugefügt. Dort liegt auch, wohin ein veröffentlichter Beitrag den Seiten-Cache
          schickt.
        </p>
      </div>
    );
  }

  return (
    <div
      className={staleClass(blogsQuery.stale, "blog-list tds-stack")}
      aria-busy={blogsQuery.stale}
    >
      {blogsQuery.error ? (
        <p className="tds-alert tds-alert--danger" role="alert">
          Die Blog-Liste konnte nicht aktualisiert werden ({blogsQuery.error.message}).
          Die angezeigten Daten können veraltet sein.
        </p>
      ) : null}
      {/* One blog is the overwhelmingly common case, so the picker only earns
          its space when there is a choice to make. */}
      {blogs.length > 1 ? (
        <div
          className="tds-toolbar"
          role="group"
          aria-label="Blog wählen"
        >
          {blogs.map((b) => (
            <button
              key={b.id}
              type="button"
              className={b.blog_key === selectedKey ? "chip chip--info" : "chip chip--neutral"}
              aria-pressed={b.blog_key === selectedKey}
              onClick={() => setSelectedKey(b.blog_key)}
            >
              {b.name}
            </button>
          ))}
        </div>
      ) : null}

      {selected ? <BlogPosts key={selected.blog_key} blog={selected} /> : null}
    </div>
  );
}

/** One blog's articles, plus the editor for the chosen one. */
function BlogPosts({ blog }: { blog: Blog }) {
  const postsQuery = useCachedJson<{ posts: PostMeta[] }>(`/blogs/${blog.blog_key}/posts`);
  const posts = postsQuery.data?.posts ?? [];
  const authorsQuery = useCachedJson<{ authors: Author[] }>("/blog/authors");
  const authors = useMemo(() => authorsQuery.data?.authors ?? [], [authorsQuery.data]);

  const [editing, setEditing] = useState<PostDraft | null>(null);
  /** True when the editor targets an existing (blog, slug, lang) — locks slug/lang. */
  const [isExisting, setIsExisting] = useState(false);
  const [backfillStatus, setBackfillStatus] = useState<string | null>(null);
  const [cacheStatus, setCacheStatus] = useState<string | null>(null);

  const cacheConfigured = Boolean((blog.cache_url ?? "").trim());

  const backfill = async () => {
    setBackfillStatus("Übersetzungen werden erzeugt …");
    const res = await api(`/blogs/${blog.blog_key}/translations/backfill`, { method: "POST" });
    if (res.ok) {
      const d = await res.json().catch(() => ({}));
      setBackfillStatus(null);
      toast.success(`Fertig: ${d.created ?? 0} erstellt, ${d.skipped ?? 0} übersprungen.`);
      invalidate(`/blogs/${blog.blog_key}/`);
    } else if (res.status === 503) {
      // A missing key is a CONFIGURATION problem, not a transient outcome —
      // it stays on screen until someone sets the key.
      setBackfillStatus("Automatische Übersetzung ist nicht konfiguriert (Einstellungen → Blog-CMS).");
    } else {
      setBackfillStatus(null);
      toast.danger(`Übersetzungslauf fehlgeschlagen (HTTP ${res.status}).`);
    }
  };

  /**
   * Re-render the cached pages of ONE article.
   *
   * This is the case the whole page cache exists for: correcting one paragraph
   * used to cost a rebuild of the entire corpus. Publishing does it by itself —
   * this button is the catch-up for when that did not land.
   */
  const rebuildArticle = async (slug: string) => {
    setCacheStatus(`Seiten von „${slug}“ werden neu gebaut …`);
    const res = await api(`/blogs/${blog.blog_key}/cache/rebuild`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ slug }),
    });
    if (res.ok) {
      setCacheStatus(null);
      toast.success(`Cache-Neubau für „${slug}“ wurde angefragt.`);
    } else if (res.status === 422) {
      // A persistent configuration gap, so it stays on screen: a vanishing
      // message would leave the operator pressing a button that can never work.
      setCacheStatus("Für diesen Blog ist keine Adresse hinterlegt (Einstellungen → Blog-CMS).");
    } else if (res.status === 503) {
      setCacheStatus("Der Seiten-Cache ist nicht vollständig konfiguriert (Token unter Einstellungen → Blog-CMS prüfen).");
    } else {
      setCacheStatus(null);
      toast.danger(`Cache-Neubau fehlgeschlagen (HTTP ${res.status}).`);
    }
  };

  const openPost = async (p: PostMeta) => {
    const res = await api(`/blogs/${blog.blog_key}/posts/${p.slug}?lang=${p.lang}`);
    if (!res.ok) {
      toast.danger(`Beitrag konnte nicht geladen werden (HTTP ${res.status}).`);
      return;
    }
    const d = await res.json();
    setIsExisting(true);
    setEditing({
      slug: p.slug,
      lang: p.lang,
      category: (d.category as string) ?? "allgemein",
      title: (d.title as string) ?? p.title,
      excerpt: (d.excerpt as string) ?? "",
      meta_description: (d.meta_description as string) ?? "",
      tags: (d.tags as string) ?? "",
      cover_hint: (d.cover_hint as string) ?? "",
      body: (d.body as string) ?? "",
      author_id: (d.author_id as number) ?? 0,
      draft: Boolean(d.draft ?? p.draft),
    });
  };

  const newPost = () => {
    setIsExisting(false);
    setEditing({ ...EMPTY_POST });
  };

  if (editing) {
    return (
      <PostEditor
        blogKey={blog.blog_key}
        post={editing}
        isExisting={isExisting}
        authors={authors}
        cacheConfigured={cacheConfigured}
        onDone={() => {
          setEditing(null);
          invalidate(`/blogs/${blog.blog_key}/`);
        }}
        onCancel={() => setEditing(null)}
      />
    );
  }

  return (
    <div className="blog-posts tds-stack">
      <div className="tds-row tds-row--between">
        <h2>{blog.name}</h2>
        <button className="btn btn-ghost" type="button" onClick={newPost}>
          Neuer Beitrag
        </button>
      </div>

      {postsQuery.error ? (
        <p className="tds-alert tds-alert--danger" role="alert">
          {posts.length === 0
            ? `Beiträge konnten nicht geladen werden (${postsQuery.error.message}).`
            : `Die Beiträge konnten nicht aktualisiert werden (${postsQuery.error.message}). Die angezeigten Daten können veraltet sein.`}
        </p>
      ) : null}
      {cacheStatus ? (
        <p className="tds-alert" role="status">
          {cacheStatus}
        </p>
      ) : null}

      {postsQuery.loading ? (
        <p>
          <Spinner />
        </p>
      ) : posts.length === 0 ? (
        <p className="tds-empty">Noch keine Beiträge.</p>
      ) : (
        <ul className={staleClass(postsQuery.stale, "tds-list")} aria-busy={postsQuery.stale}>
          {posts.map((p) => (
            <li key={`${p.slug}-${p.lang}`} className="tds-list__row">
              <button className="btn btn-ghost" type="button" onClick={() => openPost(p)}>
                <strong>{p.title}</strong> <code>{p.slug}</code>
                <span className="chip chip--neutral">{p.lang}</span>
                <span className={`chip chip--${p.draft ? "warning" : "success"}`}>
                  {p.draft ? "Entwurf" : "Veröffentlicht"}
                </span>
                {p.machine_translated ? (
                  <span className="chip chip--info" title="Automatisch übersetzt">
                    Auto-Übersetzung
                  </span>
                ) : null}
                {p.author_name ? <span className="text-xs opacity-60"> · {p.author_name}</span> : null}
              </button>
              {/* Per article, because that is the case this whole mechanism
                  exists for. Draft rows get no button — nothing of theirs is
                  public to rebuild. */}
              {p.draft ? null : (
                <button
                  className="btn btn-ghost"
                  type="button"
                  onClick={() => rebuildArticle(p.slug)}
                  title="Nur die Seiten dieses Beitrags neu rendern"
                >
                  Cache neu bauen
                </button>
              )}
            </li>
          ))}
        </ul>
      )}

      <AuthorManager
        authors={authors}
        loading={authorsQuery.loading}
        stale={authorsQuery.stale}
        error={authorsQuery.error}
        onChange={() => invalidate("/blog/authors")}
      />

      <div className="blog-translate">
        <h3>Automatische Übersetzung</h3>
        <p className="marginalia">
          Beim Speichern eines veröffentlichten Beitrags wird die Gegensprache per DeepL
          erzeugt (Schlüssel unter Einstellungen → Blog-CMS). Vorhandene Beiträge lassen
          sich hier nachziehen.
        </p>
        {backfillStatus ? (
          <p className="tds-alert" role="status">
            {backfillStatus}
          </p>
        ) : null}
        <button className="btn btn-primary" type="button" onClick={backfill}>
          Übersetzungen nachziehen
        </button>
      </div>
    </div>
  );
}

function PostEditor({
  blogKey,
  post,
  isExisting,
  authors,
  cacheConfigured,
  onDone,
  onCancel,
}: {
  blogKey: string;
  post: PostDraft;
  isExisting: boolean;
  authors: Author[];
  /** Whether this blog has a page-cache address — see the save message below. */
  cacheConfigured: boolean;
  onDone: () => void;
  onCancel: () => void;
}) {
  const [form, setForm] = useState<PostDraft>(post);
  const [status, setStatus] = useState<string | null>(null);
  const [busy, setBusy] = useState(false);
  const [preview, setPreview] = useState(false);
  const [confirmDelete, setConfirmDelete] = useState(false);

  const set = <K extends keyof PostDraft>(field: K, value: PostDraft[K]) =>
    setForm((f) => ({ ...f, [field]: value }));

  const save = async () => {
    if (!/^[a-z0-9-]{2,64}$/.test(form.slug)) {
      setStatus("Slug muss kebab-case sein (a-z, 0-9, -).");
      return;
    }
    if (form.title.trim() === "" || form.body.trim() === "") {
      setStatus("Titel und Inhalt sind erforderlich.");
      return;
    }
    setBusy(true);
    setStatus(null);
    const res = await api(`/blogs/${blogKey}/posts/${form.slug}`, {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        lang: form.lang,
        category: form.category.trim() || "allgemein",
        title: form.title.trim(),
        excerpt: form.excerpt.trim(),
        meta_description: form.meta_description.trim(),
        tags: form.tags.trim(),
        cover_hint: form.cover_hint.trim(),
        body: form.body,
        author_id: form.author_id,
        draft: form.draft,
      }),
    });
    setBusy(false);
    if (!res.ok) {
      // Never swallow the status: it is what tells "session expired" from
      // "service down" apart in a bug report.
      toast.danger(`Speichern fehlgeschlagen (HTTP ${res.status}).`);
      return;
    }
    const body = (await res.json().catch(() => ({}))) as { cached?: boolean };
    // The API reports whether THIS article's pages were really asked to
    // re-render. A draft never triggers one, so inferring it from `ok` would
    // promise a rebuild after every draft save.
    toast.success(
      body.cached === true
        ? "Beitrag gespeichert — der Neubau seiner Seiten wurde angefragt."
        : form.draft
          ? "Entwurf gespeichert. Entwürfe sind nicht öffentlich."
          : cacheConfigured
            ? "Beitrag gespeichert. Der Seiten-Cache konnte nicht angestoßen werden."
            : "Beitrag gespeichert. Für diesen Blog ist kein Seiten-Cache hinterlegt.",
    );
    onDone();
  };

  // This delete had NO confirmation at all — a single click on „Löschen" wiped
  // the post. It is now gated by the same <ConfirmDialog> as every other
  // destructive action.
  const remove = async () => {
    setBusy(true);
    const res = await api(`/blogs/${blogKey}/posts/${form.slug}?lang=${form.lang}`, { method: "DELETE" });
    setBusy(false);
    setConfirmDelete(false);
    if (res.ok) {
      toast.success("Beitrag gelöscht.");
      onDone();
    } else {
      toast.danger(`Löschen fehlgeschlagen (HTTP ${res.status}).`);
    }
  };

  return (
    <div className="blog-editor">
      <button className="btn btn-ghost" type="button" onClick={onCancel}>← Beiträge</button>
      <h2>{isExisting ? "Beitrag bearbeiten" : "Neuer Beitrag"}</h2>

      {/* Was `.marginalia`, which is a TYPOGRAPHY rule (13px, muted colour)
          and carries no layout at all — so these four fields stacked at every
          width AND the inputs inherited the muted text colour.
          `.tds-field-row` is the label/control pair this file already uses
          further down; the grid is two-up from `sm`. */}
      <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <label className="tds-field-row">
          Slug
          <input className="field-boxed"
            value={form.slug}
            onChange={(e) => set("slug", e.target.value)}
            placeholder="mein-beitrag"
            disabled={isExisting}
          />
        </label>
        <label className="tds-field-row">
          Sprache
          <select className="field-boxed" value={form.lang} onChange={(e) => set("lang", e.target.value)} disabled={isExisting}>
            <option value="de">de</option>
            <option value="en">en</option>
          </select>
        </label>
        <label className="tds-field-row">
          Kategorie
          <input className="field-boxed" value={form.category} onChange={(e) => set("category", e.target.value)} placeholder="allgemein" />
        </label>
        <label className="tds-field-row">
          Autor
          <select className="field-boxed" value={String(form.author_id)} onChange={(e) => set("author_id", Number(e.target.value))}>
            <option value="0">— kein Autor —</option>
            {authors.map((a) => (
              <option key={a.id} value={a.id}>{a.name}</option>
            ))}
          </select>
        </label>
      </div>

      <label className="tds-field-row">
        Titel
        <input className="field-boxed" value={form.title} onChange={(e) => set("title", e.target.value)} placeholder="Titel des Beitrags" />
      </label>

      <label className="tds-field-row">
        Auszug
        <input className="field-boxed" value={form.excerpt} onChange={(e) => set("excerpt", e.target.value)} placeholder="Kurzbeschreibung (optional)" />
      </label>

      <label className="tds-field-row">
        Cover-Hinweis
        <input className="field-boxed" value={form.cover_hint} onChange={(e) => set("cover_hint", e.target.value)} placeholder="Bild-Hinweis (optional)" />
      </label>

      <label className="tds-field-row">
        Meta-Description (SEO)
        <input className="field-boxed"
          value={form.meta_description}
          onChange={(e) => set("meta_description", e.target.value)}
          maxLength={300}
          placeholder="Suchmaschinen-Beschreibung (≤160 Zeichen ideal)"
        />
      </label>

      <label className="tds-field-row">
        Tags / Keywords
        <input className="field-boxed"
          value={form.tags}
          onChange={(e) => set("tags", e.target.value)}
          maxLength={200}
          placeholder="komma, getrennt, keywords"
        />
      </label>

      <div className="tds-field-row">
        <div className="flex items-center gap-3">
          <span>Inhalt (Markdown)</span>
          <button type="button" className="btn btn-ghost text-xs ml-auto" onClick={() => setPreview((v) => !v)}>
            {preview ? "Bearbeiten" : "Vorschau"}
          </button>
        </div>
        {preview ? (
          <div
            className="blog-editor__preview prose"
            dangerouslySetInnerHTML={{ __html: renderMarkdown(form.body) }}
          />
        ) : (
          <textarea
            className="field-boxed"
            value={form.body}
            onChange={(e) => set("body", e.target.value)}
            rows={18}
            spellCheck={false}
            placeholder="# Überschrift&#10;&#10;Text in Markdown …"
          />
        )}
      </div>

      <label className="blog-editor__publish">
        <input type="checkbox" checked={!form.draft} onChange={(e) => set("draft", !e.target.checked)} />
        Veröffentlichen (sonst Entwurf)
      </label>

      {/* Validation only now — outcomes are toasts. */}
      {status ? <p className="tds-alert tds-alert--danger" role="alert">{status}</p> : null}

      <div className="tds-toolbar">
        <button className="btn btn-primary" type="button" onClick={save} disabled={busy}>Speichern</button>
        {isExisting ? (
          <button type="button" className="btn btn-danger" onClick={() => setConfirmDelete(true)} disabled={busy}>Löschen</button>
        ) : null}
      </div>

      <ConfirmDialog
        open={confirmDelete}
        title={`Beitrag „${form.title || form.slug}“ löschen?`}
        message="Die Sprachfassung wird dauerhaft entfernt. Das lässt sich nicht rückgängig machen."
        busy={busy}
        onConfirm={() => void remove()}
        onCancel={() => setConfirmDelete(false)}
      />
    </div>
  );
}

/** Manage the byline registry: list authors, add one, remove one. */
function AuthorManager({
  authors,
  loading,
  stale,
  error,
  onChange,
}: {
  authors: Author[];
  loading: boolean;
  stale: boolean;
  error: Error | null;
  onChange: () => void;
}) {
  const [name, setName] = useState("");
  const [bio, setBio] = useState("");
  const [avatar, setAvatar] = useState("");
  const [status, setStatus] = useState<string | null>(null);
  const [panelUsers, setPanelUsers] = useState<PanelUser[]>([]);
  const [pickedUser, setPickedUser] = useState("");
  const [pendingDelete, setPendingDelete] = useState<Author | null>(null);
  const [deleting, setDeleting] = useState(false);

  // Panel users eligible to be a byline: blog authors (admins are implicit).
  useEffect(() => {
    apiFetch("/auth/admin/users")
      .then((r) => (r.ok ? r.json() : { users: [] }))
      .then((d: { users?: PanelUser[] }) =>
        setPanelUsers((d.users ?? []).filter((u) => u.isBlogAuthor || u.isAdmin)))
      .catch(() => setPanelUsers([]));
  }, []);

  // user_ids already imported as a snapshot, so we don't offer them twice.
  const linkedUserIds = new Set(authors.map((a) => a.user_id).filter((v): v is number => typeof v === "number"));
  const importable = panelUsers.filter((u) => !linkedUserIds.has(u.id));

  const post = async (payload: Record<string, unknown>, reset?: () => void) => {
    const res = await api("/blog/authors", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });
    if (res.ok) {
      reset?.();
      setStatus(null);
      onChange();
    } else {
      toast.danger(`Speichern fehlgeschlagen (HTTP ${res.status}).`);
    }
  };

  const add = () => {
    if (name.trim().length < 2) {
      setStatus("Name ist erforderlich.");
      return;
    }
    void post({ name: name.trim(), bio: bio.trim(), avatar_url: avatar.trim() }, () => {
      setName("");
      setBio("");
      setAvatar("");
    });
  };

  const importUser = () => {
    const u = panelUsers.find((x) => String(x.id) === pickedUser);
    if (!u) return;
    void post({ user_id: u.id, name: (u.name ?? u.email).trim() }, () => setPickedUser(""));
  };

  const confirmRemove = async () => {
    const a = pendingDelete;
    if (!a) return;
    setDeleting(true);
    try {
      const res = await api(`/blog/authors/${a.id}`, { method: "DELETE" });
      setPendingDelete(null);
      if (res.ok) onChange();
    } finally {
      setDeleting(false);
    }
  };

  return (
    <div className="blog-authors">
      <h3>Autoren</h3>
      {error ? (
        <p className="tds-alert tds-alert--danger" role="alert">
          {authors.length === 0
            ? `Autoren konnten nicht geladen werden (${error.message}).`
            : `Die Autoren konnten nicht aktualisiert werden (${error.message}). Die angezeigten Daten können veraltet sein.`}
        </p>
      ) : null}
      {loading ? (
        <p><Spinner /></p>
      ) : authors.length === 0 ? (
        <p className="text-xs opacity-60">Noch keine Autoren.</p>
      ) : (
        <ul className={staleClass(stale, "tds-list")} aria-busy={stale}>
          {authors.map((a) => (
            // `.tds-list__row` — the class this `<ul className="tds-list">`
            // was already asking for, and the one that brings `flex-wrap`.
            // Hand-rolled `flex` here meant four items (name, chip, a free-text
            // bio and a button) on one un-wrappable line.
            <li key={a.id} className="tds-list__row">
              <strong>{a.name}</strong>
              {a.user_id ? <span className="chip chip--cat-violet">Panel-Nutzer</span> : null}
              {a.bio ? <span className="text-xs opacity-60">{a.bio}</span> : null}
              <button type="button" className="btn btn-danger text-xs ml-auto" onClick={() => setPendingDelete(a)}>Entfernen</button>
            </li>
          ))}
        </ul>
      )}

      <ConfirmDialog
        open={pendingDelete !== null}
        title={`Autor „${pendingDelete?.name ?? ""}“ entfernen?`}
        message="Bestehende Beiträge behalten die Byline nicht."
        confirmLabel="Entfernen"
        busy={deleting}
        onConfirm={() => void confirmRemove()}
        onCancel={() => setPendingDelete(null)}
      />


      {importable.length > 0 ? (
        <div className="flex flex-wrap items-center gap-2 mt-3">
          <span className="text-sm">Aus Panel-Nutzer:</span>
          <select className="field-boxed" value={pickedUser} onChange={(e) => setPickedUser(e.target.value)}>
            <option value="">— Nutzer wählen —</option>
            {importable.map((u) => (
              <option key={u.id} value={u.id}>{u.name ?? u.email}</option>
            ))}
          </select>
          <button className="btn btn-primary" type="button" onClick={importUser} disabled={pickedUser === ""}>Als Autor übernehmen</button>
        </div>
      ) : null}

      <div className="flex flex-wrap gap-2 mt-2">
        <input className="field-boxed" value={name} onChange={(e) => setName(e.target.value)} placeholder="Name (Gast-Autor)" />
        <input className="field-boxed" value={bio} onChange={(e) => setBio(e.target.value)} placeholder="Kurzbio (optional)" />
        <input className="field-boxed" value={avatar} onChange={(e) => setAvatar(e.target.value)} placeholder="Avatar-URL (optional)" />
        <button className="btn btn-primary" type="button" onClick={add}>Autor hinzufügen</button>
      </div>
      {/* Validation only now — outcomes are toasts. */}
      {status ? <p className="tds-alert tds-alert--danger" role="alert">{status}</p> : null}
    </div>
  );
}
