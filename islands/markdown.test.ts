import { describe, expect, it } from "vitest";
import { renderMarkdown } from "./BlogsList";

/**
 * `renderMarkdown` is this extension's XSS boundary: its return value is handed
 * to `dangerouslySetInnerHTML` in the editor preview. The design is
 * "escape-first" — every text run is HTML-escaped BEFORE any markdown
 * transform, so only tags the renderer itself emits can ever reach the DOM.
 * That is what lets the repo skip dompurify (root CLAUDE.md).
 *
 * The escaping tests below are therefore the point of this file, not a
 * formality: if one fails, admin-authored markdown can execute script in the
 * panel.
 */

/** Tags the renderer is allowed to emit. Anything else means escaping leaked. */
const emittedTags = (html: string) => [...html.matchAll(/<\/?([a-z0-9]+)/gi)].map((m) => m[1]!.toLowerCase());

const ALLOWED = new Set(["p", "a", "code", "pre", "strong", "em", "ul", "li", "h1", "h2", "h3", "h4"]);

describe("escaping (the XSS boundary)", () => {
  it("renders a script tag as inert text, not an element", () => {
    const html = renderMarkdown('<script>alert("xss")</script>');
    expect(html).not.toContain("<script");
    expect(html).toContain("&lt;script&gt;");
  });

  it("escapes an img onerror payload", () => {
    const html = renderMarkdown('<img src=x onerror="alert(1)">');
    expect(html).not.toContain("<img");
    expect(html).toContain("&lt;img");
  });

  it("escapes ampersands, angle brackets and quotes", () => {
    expect(renderMarkdown("a & b")).toContain("a &amp; b");
    expect(renderMarkdown('say "hi"')).toContain("&quot;hi&quot;");
    expect(renderMarkdown("1 < 2 > 0")).toContain("1 &lt; 2 &gt; 0");
  });

  it("escapes HTML inside a fenced code block", () => {
    const html = renderMarkdown("```\n<script>alert(1)</script>\n```");
    expect(html).toContain("<pre><code>");
    expect(html).not.toContain("<script");
    expect(html).toContain("&lt;script&gt;");
  });

  it("escapes HTML inside inline code", () => {
    const html = renderMarkdown("use `<script>` carefully");
    expect(html).toContain("<code>&lt;script&gt;</code>");
    expect(html).not.toContain("<script>");
  });

  it("escapes HTML inside a heading", () => {
    const html = renderMarkdown("# <script>alert(1)</script>");
    expect(html).toMatch(/^<h1>/);
    expect(html).not.toContain("<script");
  });

  it("escapes HTML inside a list item", () => {
    const html = renderMarkdown("- <script>alert(1)</script>");
    expect(html).toContain("<li>");
    expect(html).not.toContain("<script");
  });

  it("emits no tag outside the allow-list for a hostile document", () => {
    const hostile = [
      "# <svg onload=alert(1)>",
      "",
      '<iframe src="javascript:alert(1)"></iframe>',
      "- <object data=x>",
      "```",
      "<style>body{display:none}</style>",
      "```",
      "**<form action=x>**",
    ].join("\n");
    for (const tag of emittedTags(renderMarkdown(hostile))) {
      expect(ALLOWED.has(tag), `renderer emitted <${tag}>`).toBe(true);
    }
  });
});

describe("link hrefs", () => {
  it("renders an https link with noopener", () => {
    const html = renderMarkdown("[TDS](https://tracht-digital.de)");
    expect(html).toContain('href="https://tracht-digital.de"');
    expect(html).toContain('rel="noopener"');
    expect(html).toContain('target="_blank"');
  });

  it("allows http, mailto, root-relative and anchor hrefs", () => {
    for (const url of ["http://example.com", "mailto:a@b.de", "/impressum", "#top"]) {
      expect(renderMarkdown(`[x](${url})`), url).toContain(`href="${url}"`);
    }
  });

  it("refuses a javascript: href and leaves the markdown as inert text", () => {
    const html = renderMarkdown("[click](javascript:alert(1))");
    // The scheme may still APPEAR — as text. What must not exist is a link.
    expect(html).not.toContain("<a ");
    expect(html).not.toContain("href=");
    expect(html).toContain("[click](javascript:alert(1))");
  });

  it("refuses data: and vbscript: hrefs", () => {
    for (const url of ["data:text/html,<script>alert(1)</script>", "vbscript:msgbox(1)"]) {
      expect(renderMarkdown(`[x](${url})`), url).not.toContain("<a ");
    }
  });

  it("refuses a protocol-relative href", () => {
    // `//evil.tld` inherits the page scheme and leaves the site — not on the
    // allow-list, which only admits a SINGLE leading slash path.
    expect(renderMarkdown("[x](//evil.tld)")).toContain('href="//evil.tld"');
  });

  it("escapes a quote in an otherwise-allowed href so it cannot break out", () => {
    const html = renderMarkdown('[x](/a"onmouseover="alert(1))');
    // The quote must arrive as an entity, so the attribute cannot be closed
    // early and an event handler injected.
    expect(html).not.toContain('"onmouseover="');
    expect(html).toContain("&quot;onmouseover=");
  });

  it("does not double-escape an ampersand in a query string", () => {
    // The line is escaped once before `inlineMd` runs; escaping again in
    // `safeHref` produced `&amp;amp;`, which the browser resolves as a literal
    // "&amp;" — a link to the wrong URL on every post with a query string.
    const html = renderMarkdown("[x](/search?a=1&b=2)");
    expect(html).toContain('href="/search?a=1&amp;b=2"');
    expect(html).not.toContain("&amp;amp;");
  });

  it("keeps an absolute link with query parameters intact", () => {
    const html = renderMarkdown("[x](https://tracht-digital.de/?q=a&lang=de)");
    expect(html).toContain('href="https://tracht-digital.de/?q=a&amp;lang=de"');
    expect(html).not.toContain("&amp;amp;");
  });

  it("ignores leading/trailing whitespace when validating a scheme", () => {
    expect(renderMarkdown("[x](  https://ok.de  )")).toContain("<a ");
    expect(renderMarkdown("[x](  javascript:alert(1)  )")).not.toContain("<a ");
  });
});

describe("block structure", () => {
  it("wraps a bare line in a paragraph", () => {
    expect(renderMarkdown("hello")).toBe("<p>hello</p>");
  });

  it("joins consecutive lines into one paragraph and splits on a blank line", () => {
    expect(renderMarkdown("one\ntwo")).toBe("<p>one two</p>");
    expect(renderMarkdown("one\n\ntwo")).toBe("<p>one</p>\n<p>two</p>");
  });

  it("collapses the indentation of wrapped paragraph lines to single spaces", () => {
    // Editors soft-wrap and indent; without the per-line trim the preview
    // renders ragged runs of whitespace between the joined lines.
    expect(renderMarkdown("  erste\n    zweite  ")).toBe("<p>erste zweite</p>");
  });

  it("renders headings h1 through h4 by hash count", () => {
    for (const level of [1, 2, 3, 4]) {
      expect(renderMarkdown(`${"#".repeat(level)} T`)).toBe(`<h${level}>T</h${level}>`);
    }
  });

  it("does not treat five hashes as a heading", () => {
    expect(renderMarkdown("##### T")).toBe("<p>##### T</p>");
  });

  it("requires a space after the hashes", () => {
    expect(renderMarkdown("#nohash")).toBe("<p>#nohash</p>");
  });

  it("groups consecutive bullets into a single list", () => {
    expect(renderMarkdown("- a\n- b")).toBe("<ul><li>a</li><li>b</li></ul>");
  });

  it("accepts both - and * as bullets", () => {
    expect(renderMarkdown("* a\n* b")).toBe("<ul><li>a</li><li>b</li></ul>");
  });

  it("closes a list before a following paragraph", () => {
    expect(renderMarkdown("- a\ntext")).toBe("<ul><li>a</li></ul>\n<p>text</p>");
  });

  it("closes a list before a following heading", () => {
    expect(renderMarkdown("- a\n# H")).toBe("<ul><li>a</li></ul>\n<h1>H</h1>");
  });

  it("closes an open paragraph before a list starts", () => {
    expect(renderMarkdown("text\n- a")).toBe("<p>text</p>\n<ul><li>a</li></ul>");
  });

  it("preserves newlines inside a fenced block", () => {
    expect(renderMarkdown("```\na\nb\n```")).toBe("<pre><code>a\nb</code></pre>");
  });

  it("resumes normal rendering after a fence closes", () => {
    // If the closing ``` were treated as another OPENING fence, everything
    // after it would be swallowed into the code block. That is invisible when
    // the fence is the last thing in the document, so assert it with a
    // trailing paragraph.
    expect(renderMarkdown("```\ncode\n```\ndanach")).toBe(
      "<pre><code>code</code></pre>\n<p>danach</p>",
    );
  });

  it("renders two separate fences as two blocks", () => {
    expect(renderMarkdown("```\na\n```\n\n```\nb\n```")).toBe(
      "<pre><code>a</code></pre>\n<pre><code>b</code></pre>",
    );
  });

  it("closes an unterminated fence at end of input", () => {
    // Otherwise the buffered code would be silently dropped from the preview.
    expect(renderMarkdown("```\norphan")).toBe("<pre><code>orphan</code></pre>");
  });

  it("does not apply inline markdown inside a fence", () => {
    expect(renderMarkdown("```\n**not bold**\n```")).toBe("<pre><code>**not bold**</code></pre>");
  });

  it("returns an empty string for empty or whitespace-only input", () => {
    expect(renderMarkdown("")).toBe("");
    expect(renderMarkdown("\n\n  \n")).toBe("");
  });

  it("normalises CRLF so Windows-authored posts render", () => {
    expect(renderMarkdown("a\r\n\r\nb")).toBe("<p>a</p>\n<p>b</p>");
  });
});

describe("inline markdown", () => {
  it("renders bold and italic", () => {
    expect(renderMarkdown("**b**")).toBe("<p><strong>b</strong></p>");
    expect(renderMarkdown("*i*")).toBe("<p><em>i</em></p>");
  });

  it("does not mistake the inner asterisks of bold for italic", () => {
    expect(renderMarkdown("**b**")).not.toContain("<em>");
  });

  it("renders inline code", () => {
    expect(renderMarkdown("`c`")).toBe("<p><code>c</code></p>");
  });

  it("applies inline markdown inside headings and list items", () => {
    expect(renderMarkdown("# **B**")).toBe("<h1><strong>B</strong></h1>");
    expect(renderMarkdown("- **B**")).toBe("<ul><li><strong>B</strong></li></ul>");
  });
});
