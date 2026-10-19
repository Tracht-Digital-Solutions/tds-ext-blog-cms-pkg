import { SummaryMetric } from "@tracht-digital-solutions/tds-shared/components";

/** "Blog-Beiträge" widget body — the total post count, from `/blog/summary`. */
export default function PostsCount() {
  return <SummaryMetric<{ posts?: number }> endpoint="/blog/summary" value={(d) => Number(d.posts ?? 0)} />;
}
