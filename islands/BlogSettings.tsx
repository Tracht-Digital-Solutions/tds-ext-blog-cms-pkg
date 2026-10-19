import { useEffect, useState } from "react";
import { Alert, Spinner } from "@tracht-digital-solutions/tds-shared/components";
import { SecretField, useRuntimeSettings } from "@tracht-digital-solutions/tds-shared/admin";

/**
 * Blog-CMS settings section — DeepL key + auto-translate flag, persisted in the
 * core's runtime settings store (`/admin/settings/blog-cms`, admin-only)
 * through tds-shared `useRuntimeSettings`. Secrets come back masked
 * (configured + last4) and a blank secret on save keeps the existing value, so
 * the raw key never round-trips to the UI. The extension backend reads these
 * DB-first with an env fallback.
 */
export default function BlogSettings() {
  const settings = useRuntimeSettings("blog-cms");
  const [autoTranslate, setAutoTranslate] = useState(true);
  const [deeplInput, setDeeplInput] = useState("");

  useEffect(() => {
    setAutoTranslate(settings.get("auto_translate")?.value !== "0");
  }, [settings.settings]);

  const save = async () => {
    const saved = await settings.save([
      { key: "deepl_api_key", secret: true, value: deeplInput.trim() },
      { key: "auto_translate", value: autoTranslate ? "1" : "0" },
    ]);
    // The typed key stays in the field after a failure, for another attempt.
    if (saved) setDeeplInput("");
  };

  if (!settings.loaded) return <p><Spinner /></p>;

  return (
    <div className="blog-settings space-y-4">
      <SecretField label="DeepL API-Key" setting={settings.get("deepl_api_key")} value={deeplInput} onChange={setDeeplInput} placeholder="Neuen Schlüssel setzen (leer = behalten)" />

      <label className="flex items-center gap-2 text-sm">
        <input type="checkbox" checked={autoTranslate} onChange={(e) => setAutoTranslate(e.target.checked)} />
        <span>Automatische Übersetzung (DeepL) aktiv</span>
      </label>

      <p className="marginalia">
        Die Verbindung zum öffentlichen Blog einschließlich Cache-Zugang wird direkt beim
        jeweiligen Blog eingerichtet. Zugangsdaten werden hier nicht angezeigt.
      </p>

      {/* The load failure stays in-flow (persistent state); the save outcome
          is a toast. Failures only, hence the danger hue. */}
      <Alert>{settings.error}</Alert>
      <button className="btn btn-primary" type="button" onClick={() => void save()} disabled={settings.busy}>Speichern</button>
    </div>
  );
}
