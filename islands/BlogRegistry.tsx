import { useState } from "react";
import { Spinner, toast } from "@tracht-digital-solutions/tds-shared/components";
import { apiFetch } from "@tracht-digital-solutions/tds-shared/api";
import { invalidate, staleClass, useCachedJson } from "@tracht-digital-solutions/tds-shared/data";

interface Blog {
  id: number;
  blog_key: string;
  name: string;
  rebuild_repo?: string | null;
  rebuild_workflow?: string | null;
  cache_url?: string | null;
}

const api = apiFetch;

/**
 * The managed-blogs registry — **this is where a blog is added**, and the only
 * place its rebuild target and page-cache address are set.
 *
 * It moved off the Blog-CMS content screen for the same reason the website
 * registry did: connecting a blog is a once-per-blog act by whoever runs the
 * platform, writing its articles is a daily act by whoever writes them, and a
 * GitHub repository field had no business sitting above the article list.
 *
 * ### Two rebuild buttons, and the expensive one is the wrong guess
 *
 * *Jetzt neu bauen* dispatches a CI build: it ships code, re-runs every DeepL
 * translation and re-renders one OG card per post — minutes. *Seiten-Cache neu
 * bauen* re-renders pages from articles already stored — seconds, and it is
 * what publishing does by itself, per article.
 */
export default function BlogRegistry() {
  const blogsQuery = useCachedJson<{ blogs: Blog[] }>("/blogs");
  const blogs = blogsQuery.data?.blogs ?? [];

  const [key, setKey] = useState("");
  const [name, setName] = useState("");
  const [creating, setCreating] = useState(false);
  const [formError, setFormError] = useState<string | null>(null);

  const create = async (event: React.FormEvent) => {
    event.preventDefault();
    const blogKey = key.trim();
    if (!/^[a-z0-9-]{2,64}$/.test(blogKey)) {
      setFormError("Der Schlüssel darf nur Kleinbuchstaben, Ziffern und Bindestriche enthalten (2–64 Zeichen).");
      return;
    }
    if (name.trim() === "") {
      setFormError("Ein Name ist erforderlich.");
      return;
    }
    setFormError(null);
    setCreating(true);
    const res = await api("/blogs", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ blog_key: blogKey, name: name.trim() }),
    });
    setCreating(false);
    if (res.ok) {
      setKey("");
      setName("");
      toast.success("Blog angelegt.");
      invalidate("/blogs");
    } else {
      toast.danger(`Anlegen fehlgeschlagen (HTTP ${res.status}).`);
    }
  };

  return (
    <div className="tds-stack">
      {/* noValidate on purpose: the browser's `required` bubble stops the
          submit before our handler runs, so the operator never sees the message
          that explains the key format — the one thing that cannot be corrected
          afterwards. */}
      <form className="tds-stack tds-stack--tight" onSubmit={create} noValidate>
        <p className="marginalia">
          Der Schlüssel verbindet die Beiträge mit dem öffentlichen Blog und lässt sich
          später nicht ändern.
        </p>
        <div className="tds-row">
          <label className="block text-sm">
            Schlüssel
            <input
              className="field-boxed"
              value={key}
              onChange={(e) => setKey(e.target.value)}
              placeholder="journal"
              autoComplete="off"
            />
          </label>
          <label className="block text-sm">
            Name
            <input
              className="field-boxed"
              value={name}
              onChange={(e) => setName(e.target.value)}
              placeholder="Journal blog.tracht-digital.de"
            />
          </label>
        </div>
        {formError ? (
          <p className="tds-alert tds-alert--danger" role="alert">
            {formError}
          </p>
        ) : null}
        <button className="btn btn-primary" type="submit" disabled={creating}>
          {creating ? <Spinner size="sm" /> : "Blog hinzufügen"}
        </button>
      </form>

      {blogsQuery.error && blogs.length > 0 ? (
        <p className="tds-alert tds-alert--danger" role="alert">
          Die Blog-Liste konnte nicht aktualisiert werden ({blogsQuery.error.message}).
          Die angezeigten Daten können veraltet sein.
        </p>
      ) : null}

      {blogsQuery.loading ? (
        <p>
          <Spinner />
        </p>
      ) : blogsQuery.error && blogs.length === 0 ? (
        <p className="tds-alert tds-alert--danger" role="alert">
          Blogs konnten nicht geladen werden ({blogsQuery.error.message}).
        </p>
      ) : blogs.length === 0 ? (
        <p className="tds-empty">Noch kein Blog verbunden.</p>
      ) : (
        <div className={staleClass(blogsQuery.stale, "tds-stack")} aria-busy={blogsQuery.stale}>
          {blogs.map((blog) => (
            <BlogCard key={blog.id} blog={blog} />
          ))}
        </div>
      )}
    </div>
  );
}

/** Rebuild target and page-cache address for one blog, plus the two buttons. */
function BlogCard({ blog }: { blog: Blog }) {
  const [repo, setRepo] = useState(blog.rebuild_repo ?? "");
  const [workflow, setWorkflow] = useState(blog.rebuild_workflow ?? "dev.yml");
  const [cacheUrl, setCacheUrl] = useState(blog.cache_url ?? "");
  const [saving, setSaving] = useState(false);
  const [configStatus, setConfigStatus] = useState<string | null>(null);
  const [cacheStatus, setCacheStatus] = useState<string | null>(null);
  const [rebuildStatus, setRebuildStatus] = useState<string | null>(null);

  const saveConfig = async () => {
    setConfigStatus(null);
    setSaving(true);
    const res = await api(`/blogs/${blog.blog_key}/rebuild-config`, {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        rebuild_repo: repo.trim(),
        rebuild_workflow: workflow.trim(),
        cache_url: cacheUrl.trim(),
      }),
    });
    setSaving(false);
    if (res.ok) {
      setRepo(repo.trim());
      setWorkflow(workflow.trim());
      setCacheUrl(cacheUrl.trim().replace(/\/+$/, ""));
      toast.success("Konfiguration gespeichert.");
      invalidate("/blogs");
    } else if (res.status === 422) {
      setConfigStatus("Repository oder Cache-Adresse ist ungültig. Die Cache-Adresse muss eine reine http(s)-Origin ohne Pfad, Anmeldung, Query oder Fragment sein.");
    } else {
      toast.danger(`Speichern fehlgeschlagen (HTTP ${res.status}).`);
    }
  };

  const rebuildRepository = async () => {
    setRebuildStatus("Build wird ausgelöst …");
    const res = await api(`/blogs/${blog.blog_key}/rebuild`, { method: "POST" });
    if (res.ok) {
      setRebuildStatus(null);
      toast.success("Build ausgelöst.");
    } else if (res.status === 503 || res.status === 422) {
      // Both are missing CONFIGURATION (no token / no repo). They stay on
      // screen, because they name something the operator has to go and fix;
      // a vanishing message leaves them pressing a button that cannot work.
      setRebuildStatus(
        res.status === 503
          ? "Kein Rebuild-Token hinterlegt (weiter oben in diesem Abschnitt)."
          : "Für diesen Blog ist kein Repository hinterlegt.",
      );
    } else {
      setRebuildStatus(null);
      toast.danger(`Build fehlgeschlagen (HTTP ${res.status}).`);
    }
  };

  const rebuildCache = async () => {
    setCacheStatus("Seiten-Cache wird neu gebaut …");
    const res = await api(`/blogs/${blog.blog_key}/cache/rebuild`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({}),
    });
    if (res.ok) {
      setCacheStatus(null);
      toast.success("Cache-Neubau wurde angefragt.");
    } else if (res.status === 422) {
      setCacheStatus("Für diesen Blog ist keine Adresse hinterlegt.");
    } else if (res.status === 503) {
      setCacheStatus("Der Seiten-Cache ist nicht vollständig konfiguriert (Token weiter oben prüfen).");
    } else {
      setCacheStatus(null);
      toast.danger(`Cache-Neubau fehlgeschlagen (HTTP ${res.status}).`);
    }
  };

  return (
    <section className="tds-card tds-stack">
      <div className="flex flex-wrap items-baseline gap-2">
        <h4>{blog.name}</h4>
        <code className="text-xs opacity-70">{blog.blog_key}</code>
      </div>

      <label className="block text-sm">
        Adresse des öffentlichen Blogs
        <input
          className="field-boxed"
          type="url"
          inputMode="url"
          value={cacheUrl}
          onChange={(e) => setCacheUrl(e.target.value)}
          placeholder="https://blog.tracht-digital.de"
        />
      </label>
      <p className="marginalia">
        Wohin ein veröffentlichter Beitrag den Seiten-Cache schickt. Ohne diese Adresse
        wird gespeichert, aber der öffentliche Blog zeigt weiter die alte Fassung. Nur
        die Origin eintragen (z. B. <code>https://blog.example</code>), keinen Pfad.
      </p>

      <div className="tds-row">
        <label className="block text-sm">
          Repository
          <input
            className="field-boxed"
            value={repo}
            onChange={(e) => setRepo(e.target.value)}
            placeholder="Tracht-Digital-Solutions/tds-blog-frontend"
          />
        </label>
        <label className="block text-sm">
          Workflow
          <input
            className="field-boxed"
            value={workflow}
            onChange={(e) => setWorkflow(e.target.value)}
            placeholder="dev.yml"
          />
        </label>
      </div>
      <p className="marginalia">
        Nur für Code- und Design-Änderungen. Der Token liegt weiter oben in diesem Abschnitt.
      </p>

      {rebuildStatus ? (
        <p className="tds-alert" role="status">
          {rebuildStatus}
        </p>
      ) : null}
      {configStatus ? (
        <p className="tds-alert tds-alert--danger" role="alert">
          {configStatus}
        </p>
      ) : null}
      {cacheStatus ? (
        <p className="tds-alert" role="status">
          {cacheStatus}
        </p>
      ) : null}

      <div className="tds-toolbar">
        <button className="btn btn-primary" type="button" onClick={saveConfig} disabled={saving}>
          {saving ? <Spinner size="sm" /> : "Konfiguration speichern"}
        </button>
        <button className="btn btn-accent" type="button" onClick={rebuildCache}>
          Seiten-Cache neu bauen
        </button>
        <button className="btn btn-ghost" type="button" onClick={rebuildRepository}>
          Jetzt neu bauen (CI)
        </button>
      </div>
    </section>
  );
}
