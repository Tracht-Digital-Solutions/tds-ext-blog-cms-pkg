import { useEffect, useState } from "react";
import { Skeleton } from "@tracht-digital-solutions/tds-shared/components";
import { apiFetch } from "@tracht-digital-solutions/tds-shared/api";

/** "Blog-Beiträge" widget body — the total post count, from /blog/summary. */
export default function PostsCount() {
  const [posts, setPosts] = useState<number | null>(null);
  useEffect(() => {
    let alive = true;
    apiFetch("/blog/summary")
      .then((r) => (r.ok ? r.json() : { posts: 0 }))
      .then((d) => alive && setPosts(Number(d.posts ?? 0)))
      .catch(() => alive && setPosts(0));
    return () => {
      alive = false;
    };
  }, []);
  return <p className="tds-widget__metric" aria-busy={posts === null}>
      {posts === null ? <Skeleton width="3ch" height="1.75rem" /> : posts}
    </p>;
}
