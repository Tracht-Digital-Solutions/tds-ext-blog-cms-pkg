import { useEffect, useState } from "react";
import { Alert, ConfirmDialog, Spinner, toast } from "@tracht-digital-solutions/tds-shared/components";
import { usePairing } from "@tracht-digital-solutions/tds-shared/admin";
import { apiFetch, sendJson } from "@tracht-digital-solutions/tds-shared/api";
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
    let res: Response;
    try {
      res = await sendJson("/blogs", "POST", { blog_key: blogKey, name: name.trim() });
    } catch {
      // apiFetch rejects when the request never reaches the API; uncaught, the
      // button stayed on its spinner with the form locked.
      setCreating(false);
      toast.danger("Anlegen fehlgeschlagen (Netzwerkfehler).");
      return;
    }
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
          <Alert>
            {formError}
          </Alert>
        ) : null}
        <button className="btn btn-primary" type="submit" disabled={creating}>
          {creating ? <Spinner size="sm" /> : "Blog hinzufügen"}
        </button>
      </form>

      {blogsQuery.error && blogs.length > 0 ? (
        <Alert>
          Die Blog-Liste konnte nicht aktualisiert werden ({blogsQuery.error.message}).
          Die angezeigten Daten können veraltet sein.
        </Alert>
      ) : null}

      {blogsQuery.loading ? (
        <p>
          <Spinner />
        </p>
      ) : blogsQuery.error && blogs.length === 0 ? (
        <Alert>
          Blogs konnten nicht geladen werden ({blogsQuery.error.message}).
        </Alert>
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
  const pairing = usePairing({ base: `/blogs/${blog.blog_key}`, profile: "blog", subject: "Der Blog" });
  const connection = pairing.connection as Connection | null;
  const [origin, setOrigin] = useState("");
  const [website, setWebsite] = useState("");
  const [confirmDisconnect, setConfirmDisconnect] = useState(false);
  const [rebuilding, setRebuilding] = useState(false);
  const candidateKeys = pairing.candidates ?? websites.map((item) => item.site_key);

  useEffect(() => {
    if (connection?.origin) setOrigin(connection.origin);
  }, [connection?.origin]);

  useEffect(() => {
    if (websites.length === 1) setWebsite(websites[0]!.site_key);
  }, [websites]);

  const connect = () => void pairing.connect(origin, website.trim() === "" ? {} : { website: website.trim() });

  const [disconnecting, setDisconnecting] = useState(false);
  const disconnect = async () => {
    setDisconnecting(true);
    await pairing.disconnect();
    setDisconnecting(false);
    setConfirmDisconnect(false);
  };

  const rebuildCache = async () => {
    setRebuilding(true);
    await pairing.rebuildCache();
    setRebuilding(false);
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

      {pairing.loading ? <p><Spinner size="sm" /> Verbindungsstatus wird geladen …</p> : connection ? (
        <Alert tone="success">Verbunden mit {connection.origin ?? origin}</Alert>
      ) : <Alert tone="neutral">Noch nicht mit der API verbunden.</Alert>}
      <Alert>{pairing.status}</Alert>
      {pairing.installUrl ? <p><a className="btn btn-ghost" href={pairing.installUrl}>Einrichtungslink öffnen</a></p> : null}

      <div className="tds-toolbar">
        <button className="btn btn-primary" type="button" onClick={connect} disabled={pairing.connecting || origin.trim() === "" || (candidateKeys.length > 1 && website === "")}>
          {pairing.connecting ? <Spinner size="sm" /> : connection ? "Neu verbinden" : "Mit API verbinden"}
        </button>
        <button className="btn btn-accent" type="button" onClick={() => void rebuildCache()} disabled={rebuilding} aria-busy={rebuilding}>
          {rebuilding ? <Spinner size="sm" /> : null}
          Seiten-Cache neu bauen
        </button>
        {connection ? <button className="btn btn-ghost" type="button" onClick={() => setConfirmDisconnect(true)}>Verbindung trennen</button> : null}
      </div>
      <ConfirmDialog
        open={confirmDisconnect}
        title="Verbindung trennen?"
        message={`Der Blog „${blog.name}" verliert seinen API-Zugang, bis er neu verbunden wird.`}
        confirmLabel="Trennen"
        busy={disconnecting}
        onConfirm={() => void disconnect()}
        onCancel={() => setConfirmDisconnect(false)}
      />
    </section>
  );
}
