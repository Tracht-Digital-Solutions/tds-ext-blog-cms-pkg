import { useEffect, useState } from "react";
import { ConfirmDialog, Spinner, toast } from "@tracht-digital-solutions/tds-shared/components";
import { apiFetch } from "@tracht-digital-solutions/tds-shared/api";
// The escape-first renderer moved to tds-shared: the customer wiki renders
// handbook articles with the same function, and an XSS boundary must not
// exist twice. Its test suite moved with it.
import { renderMarkdown } from "@tracht-digital-solutions/tds-shared/markdown";

interface Blog {
  id: number;
  blog_key: string;
  name: string;
  rebuild_repo?: string | null;
  rebuild_workflow?: string | null;
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
 * Blog-CMS managed-blogs list + add-blog form + a selected blog's post list
 * (CP1) and the per-post markdown editor (CP2) — create/edit a post (slug + lang,
 * title/category/excerpt/cover + markdown body), toggle draft/publish, and delete.
 * A save-triggered static-blog rebuild lands in a later checkpoint.
 */
export default function BlogsList() {
  const [blogs, setBlogs] = useState<Blog[] | null>(null);
  const [key, setKey] = useState("");
  const [name, setName] = useState("");
  const [selected, setSelected] = useState<Blog | null>(null);

  const loadBlogs = () =>
    api("/blogs")
      .then((r) => (r.ok ? r.json() : { blogs: [] }))
      .then((d) => setBlogs(d.blogs ?? []))
      .catch(() => setBlogs([]));

  useEffect(() => {
    loadBlogs();
  }, []);

  const create = async () => {
    if (!/^[a-z0-9-]{2,64}$/.test(key) || name.trim() === "") return;
    const res = await api("/blogs", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ blog_key: key, name }),
    });
    if (res.ok) {
      setKey("");
      setName("");
      loadBlogs();
    }
  };

  if (selected) {
    return <BlogPosts blog={selected} onBack={() => setSelected(null)} />;
  }

  return (
    <div className="blog-list">
      <form
        className="tds-stack"
        onSubmit={(e) => {
          e.preventDefault();
          create();
        }}
      >
        <input className="field-boxed" value={key} onChange={(e) => setKey(e.target.value)} placeholder="blog-key (kebab)" required />
        <input className="field-boxed" value={name} onChange={(e) => setName(e.target.value)} placeholder="Name" required />
        <button className="btn btn-primary" type="submit">Blog hinzufügen</button>
      </form>

      {blogs === null ? (
        <p><Spinner /></p>
      ) : blogs.length === 0 ? (
        <p>Noch keine Blogs angelegt.</p>
      ) : (
        <ul className="tds-list">
          {blogs.map((b) => (
            <li key={b.id}>
              <button className="btn btn-ghost" type="button" onClick={() => setSelected(b)}>
                <strong>{b.name}</strong> <code>{b.blog_key}</code>
              </button>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}

function BlogPosts({ blog, onBack }: { blog: Blog; onBack: () => void }) {
  const [posts, setPosts] = useState<PostMeta[] | null>(null);
  const [editing, setEditing] = useState<PostDraft | null>(null);
  /** True when the editor targets an existing (blog, slug, lang) — locks slug/lang. */
  const [isExisting, setIsExisting] = useState(false);
  const [rebuildRepo, setRebuildRepo] = useState(blog.rebuild_repo ?? "");
  const [rebuildWorkflow, setRebuildWorkflow] = useState(blog.rebuild_workflow ?? "dev.yml");
  const [rebuildStatus, setRebuildStatus] = useState<string | null>(null);
  const [backfillStatus, setBackfillStatus] = useState<string | null>(null);
  const [authors, setAuthors] = useState<Author[]>([]);

  const loadAuthors = () =>
    api("/blog/authors")
      .then((r) => (r.ok ? r.json() : { authors: [] }))
      .then((d) => setAuthors(d.authors ?? []))
      .catch(() => setAuthors([]));

  const backfill = async () => {
    setBackfillStatus("Übersetzungen werden erzeugt …");
    const res = await api(`/blogs/${blog.blog_key}/translations/backfill`, { method: "POST" });
    if (res.ok) {
      const d = await res.json().catch(() => ({}));
      setBackfillStatus(null);
      toast.success(`Fertig: ${d.created ?? 0} erstellt, ${d.skipped ?? 0} übersprungen.`);
      loadPosts();
    } else if (res.status === 503) {
      // A missing key is a CONFIGURATION problem, not a transient outcome —
      // it stays on screen until someone sets the key.
      setBackfillStatus("Automatische Übersetzung ist nicht konfiguriert (BLOG_DEEPL_API_KEY).");
    } else {
      setBackfillStatus(null);
      toast.danger(`Übersetzungslauf fehlgeschlagen (HTTP ${res.status}).`);
    }
  };

  const saveRebuildConfig = async () => {
    const res = await api(`/blogs/${blog.blog_key}/rebuild-config`, {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ rebuild_repo: rebuildRepo.trim(), rebuild_workflow: rebuildWorkflow.trim() }),
    });
    if (res.ok) toast.success("Rebuild-Konfiguration gespeichert.");
    else toast.danger(`Rebuild-Konfiguration konnte nicht gespeichert werden (HTTP ${res.status}).`);
  };

  const rebuildNow = async () => {
    setRebuildStatus("Rebuild wird ausgelöst …");
    const res = await api(`/blogs/${blog.blog_key}/rebuild`, { method: "POST" });
    if (res.ok) {
      setRebuildStatus(null);
      toast.success("Rebuild ausgelöst.");
    } else if (res.status === 503 || res.status === 422) {
      // Both are missing CONFIGURATION (no token / no repo) — they stay on
      // screen, because they name something the operator has to go and fix.
      setRebuildStatus(
        res.status === 503
          ? "Kein Rebuild-Token konfiguriert (BLOG_REBUILD_TOKEN)."
          : "Für diesen Blog ist kein Repository hinterlegt.",
      );
    } else {
      setRebuildStatus(null);
      toast.danger(`Rebuild fehlgeschlagen (HTTP ${res.status}).`);
    }
  };

  const loadPosts = () =>
    api(`/blogs/${blog.blog_key}/posts`)
      .then((r) => (r.ok ? r.json() : { posts: [] }))
      .then((d) => setPosts(d.posts ?? []))
      .catch(() => setPosts([]));

  useEffect(() => {
    loadPosts();
    loadAuthors();
  }, []);

  const openPost = async (p: PostMeta) => {
    const res = await api(`/blogs/${blog.blog_key}/posts/${p.slug}?lang=${p.lang}`);
    const d = res.ok ? await res.json() : {};
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
        onDone={() => {
          setEditing(null);
          loadPosts();
        }}
        onCancel={() => setEditing(null)}
      />
    );
  }

  return (
    <div className="blog-posts">
      <button className="btn btn-ghost" type="button" onClick={onBack}>← Blogs</button>
      <div className="tds-row tds-row--between">
        <h2>{blog.name}</h2>
        <button className="btn btn-ghost" type="button" onClick={newPost}>Neuer Beitrag</button>
      </div>
      {posts === null ? (
        <p><Spinner /></p>
      ) : posts.length === 0 ? (
        <p>Noch keine Beiträge.</p>
      ) : (
        <ul className="tds-list">
          {posts.map((p) => (
            <li key={`${p.slug}-${p.lang}`}>
              <button className="btn btn-ghost" type="button" onClick={() => openPost(p)}>
                <strong>{p.title}</strong> <code>{p.slug}</code>
                <span className="chip chip--neutral">{p.lang}</span>
                <span className={`chip chip--${p.draft ? "warning" : "success"}`}>
                  {p.draft ? "Entwurf" : "Veröffentlicht"}
                </span>
                {p.machine_translated ? (
                  <span className="chip chip--info" title="Automatisch übersetzt">Auto-Übersetzung</span>
                ) : null}
                {p.author_name ? <span className="text-xs opacity-60"> · {p.author_name}</span> : null}
              </button>
            </li>
          ))}
        </ul>
      )}

      <AuthorManager authors={authors} onChange={loadAuthors} />

      <div className="blog-translate">
        <h3>Automatische Übersetzung</h3>
        <p className="marginalia">
          Beim Speichern eines veröffentlichten Beitrags wird die Gegensprache per DeepL
          erzeugt (Schlüssel serverseitig via <code>BLOG_DEEPL_API_KEY</code>). Vorhandene
          Beiträge lassen sich hier nachziehen.
        </p>
        {backfillStatus ? <p className="tds-alert" role="status">{backfillStatus}</p> : null}
        <button className="btn btn-primary" type="button" onClick={backfill}>Übersetzungen nachziehen</button>
      </div>

      <div className="blog-rebuild">
        <h3>Rebuild-Konfiguration</h3>
        <p className="marginalia">
          Repository (<code>owner/name</code>) und Workflow-Datei, die ein veröffentlichter
          Beitrag neu baut. Der Token wird serverseitig über <code>BLOG_REBUILD_TOKEN</code> bereitgestellt.
        </p>
        <div className="flex flex-wrap gap-2">
          <input className="field-boxed"
            value={rebuildRepo}
            onChange={(e) => setRebuildRepo(e.target.value)}
            placeholder="Tracht-Digital-Solutions/tds-blog-frontend"
          />
          <input className="field-boxed"
            value={rebuildWorkflow}
            onChange={(e) => setRebuildWorkflow(e.target.value)}
            placeholder="dev.yml"
          />
        </div>
        {rebuildStatus ? <p className="tds-alert" role="status">{rebuildStatus}</p> : null}
        <div className="flex flex-wrap gap-2">
          <button className="btn btn-primary" type="button" onClick={saveRebuildConfig}>Konfiguration speichern</button>
          <button className="btn btn-primary" type="button" onClick={rebuildNow}>Jetzt neu bauen</button>
        </div>
      </div>
    </div>
  );
}

function PostEditor({
  blogKey,
  post,
  isExisting,
  authors,
  onDone,
  onCancel,
}: {
  blogKey: string;
  post: PostDraft;
  isExisting: boolean;
  authors: Author[];
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
    if (res.ok) {
      toast.success("Beitrag gespeichert.");
      onDone();
    } else {
      toast.danger(`Speichern fehlgeschlagen (HTTP ${res.status}).`);
    }
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
function AuthorManager({ authors, onChange }: { authors: Author[]; onChange: () => void }) {
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
      {authors.length === 0 ? (
        <p className="text-xs opacity-60">Noch keine Autoren.</p>
      ) : (
        <ul className="tds-list">
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
