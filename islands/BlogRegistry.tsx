import { useEffect, useState } from "react";
import { Spinner, toast } from "@tracht-digital-solutions/tds-shared/components";
import { apiFetch } from "@tracht-digital-solutions/tds-shared/api";
import { invalidate, staleClass, useCachedJson } from "@tracht-digital-solutions/tds-shared/data";

interface Blog {
  id: number;
  blog_key: string;
  name: string;
}

interface Connection {
  origin?: string;
  profile?: string;
  status?: string;
  connected_at?: string | null;
  last_seen_at?: string | null;
}

interface WebsiteCandidate {
  site_key: string;
  name: string;
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
  const websitesQuery = useCachedJson<{ sites: WebsiteCandidate[] }>("/cms/sites");
  const blogs = blogsQuery.data?.blogs ?? [];
  const websites = websitesQuery.data?.sites ?? [];

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
            <BlogCard key={blog.id} blog={blog} websites={websites} />
          ))}
        </div>
      )}
    </div>
  );
}

/** One-click API connection and targeted page-cache refresh for one blog. */
function BlogCard({ blog, websites }: { blog: Blog; websites: WebsiteCandidate[] }) {
  const [connection, setConnection] = useState<Connection | null>(null);
  const [origin, setOrigin] = useState("");
  const [website, setWebsite] = useState("");
  const [candidateKeys, setCandidateKeys] = useState<string[]>(websites.map((item) => item.site_key));
  const [loading, setLoading] = useState(true);
  const [connecting, setConnecting] = useState(false);
  const [installUrl, setInstallUrl] = useState<string | null>(null);
  const [connectionStatus, setConnectionStatus] = useState<string | null>(null);
  const [cacheStatus, setCacheStatus] = useState<string | null>(null);

  const loadConnection = async () => {
    try {
      const res = await api(`/blogs/${blog.blog_key}/connection`);
      if (res.status === 404) {
        setConnection(null);
      } else if (res.ok) {
        const body = await res.json();
        const next = (body.connection ?? body) as Connection;
        setConnection(next);
        setOrigin(next.origin ?? "");
      } else {
        setConnectionStatus(`Verbindungsstatus konnte nicht geladen werden (HTTP ${res.status}).`);
      }
    } catch {
      setConnectionStatus("Verbindungsstatus konnte nicht geladen werden (Netzwerkfehler).");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    void loadConnection();
  }, [blog.blog_key]);

  useEffect(() => {
    const keys = websites.map((item) => item.site_key);
    setCandidateKeys(keys);
    if (keys.length === 1) setWebsite(keys[0]);
  }, [websites]);

  const connect = async () => {
    setConnecting(true);
    setConnectionStatus(null);
    setInstallUrl(null);
    try {
      const bindings = website.trim() === "" ? {} : { website: website.trim() };
      const res = await api(`/blogs/${blog.blog_key}/connection/pairing`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ origin: origin.trim(), profile: "blog", bindings }),
      });
      const body = await res.json().catch(() => ({}));
      if (!res.ok) {
        if (Array.isArray(body.candidates)) setCandidateKeys(body.candidates.map(String));
        setConnectionStatus(res.status === 422
          ? (body.error ?? "Bitte eine reine HTTPS-Adresse und bei mehreren Websites den passenden Website-Schlüssel angeben.")
          : `Verbinden fehlgeschlagen (HTTP ${res.status}).`);
        return;
      }
      setInstallUrl(body.fallback_url ?? body.install_url ?? null);
      if (body.delivered === true || body.connected === true) {
        toast.success("Blog mit der API verbunden.");
        await loadConnection();
      } else {
        setConnectionStatus("Die Website war nicht direkt erreichbar. Öffnen Sie den Einrichtungslink auf dem Blog-Server.");
      }
    } catch {
      setConnectionStatus("Verbinden fehlgeschlagen (Netzwerkfehler).");
    } finally {
      setConnecting(false);
    }
  };

  const disconnect = async () => {
    const res = await api(`/blogs/${blog.blog_key}/connection`, { method: "DELETE" });
    if (res.ok) {
      setConnection(null);
      setInstallUrl(null);
      toast.success("Verbindung getrennt.");
    } else {
      toast.danger(`Trennen fehlgeschlagen (HTTP ${res.status}).`);
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
      setCacheStatus("Die gespeicherte Website-Adresse ist ungültig.");
    } else if (res.status === 503) {
      setCacheStatus("Der Blog ist noch nicht vollständig mit der API verbunden.");
    } else if (res.status === 502) {
      setCacheStatus("Der Blog ist erreichbar, aber der Cache-Neubau ist fehlgeschlagen. Bitte erneut versuchen.");
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
          value={origin}
          onChange={(e) => setOrigin(e.target.value)}
          placeholder="https://blog.tracht-digital.de"
        />
      </label>
      <p className="marginalia">
        Der Blog übernimmt API-Schlüssel und Cache-Zugang automatisch. Nur die HTTPS-Adresse
        ohne Pfad eingeben.
      </p>

      {candidateKeys.length > 0 ? (
        <label className="block text-sm">
          Website-Inhalte verwenden {candidateKeys.length > 1 ? <span>(erforderlich)</span> : null}
          <select className="field-boxed" value={website} onChange={(e) => setWebsite(e.target.value)}>
            {candidateKeys.length > 1 ? <option value="">Website auswählen …</option> : null}
            {candidateKeys.map((key) => {
              const label = websites.find((item) => item.site_key === key)?.name;
              return <option key={key} value={key}>{label ? `${label} (${key})` : key}</option>;
            })}
          </select>
        </label>
      ) : null}

      {loading ? <p><Spinner size="sm" /> Verbindungsstatus wird geladen …</p> : connection ? (
        <p className="tds-alert tds-alert--success" role="status">Verbunden mit {connection.origin ?? origin}</p>
      ) : <p className="tds-alert" role="status">Noch nicht mit der API verbunden.</p>}
      {connectionStatus ? (
        <p className="tds-alert tds-alert--danger" role="alert">
          {connectionStatus}
        </p>
      ) : null}
      {installUrl ? <p><a className="btn btn-ghost" href={installUrl}>Einrichtungslink öffnen</a></p> : null}
      {cacheStatus ? (
        <p className="tds-alert" role="status">
          {cacheStatus}
        </p>
      ) : null}

      <div className="tds-toolbar">
        <button className="btn btn-primary" type="button" onClick={connect} disabled={connecting || origin.trim() === "" || (candidateKeys.length > 1 && website === "")}>
          {connecting ? <Spinner size="sm" /> : connection ? "Neu verbinden" : "Mit API verbinden"}
        </button>
        <button className="btn btn-accent" type="button" onClick={rebuildCache}>
          Seiten-Cache neu bauen
        </button>
        {connection ? <button className="btn btn-ghost" type="button" onClick={disconnect}>Verbindung trennen</button> : null}
      </div>
    </section>
  );
}
