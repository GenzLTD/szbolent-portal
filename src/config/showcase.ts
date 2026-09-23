/**
 * 橱窗目录（Bolent 侧管理，不是作者博客的镜像）。
 *
 * 一条作品 = 一位作者 + 一条对方站原文 URL。
 * 增删、上下架、排序只改本文件；不要去改对方站点，也不要全量同步。
 */
export interface ShowcaseAuthor {
  id: string
  name: string
  /** 作者自己的站点，卡片「阅读原文」必须落在这个域上 */
  site: string
}

export interface ShowcaseWork {
  id: string
  authorId: string
  url: string
  title: string
  excerpt?: string
  /** 下架后橱窗不出现，作者站原文不受影响 */
  enabled: boolean
  /** 越大越靠前 */
  weight: number
}

export const showcaseAuthors: ShowcaseAuthor[] = [
  {
    id: 'myblog',
    name: '作者博客',
    site: 'http://myblog.szbolent.local',
  },
  {
    id: 'demo-author',
    name: '示例作者（本地）',
    site: 'http://myblog.szbolent.local',
  },
]

export const showcaseWorks: ShowcaseWork[] = [
  {
    id: 'myblog-servbay',
    authorId: 'myblog',
    url: 'http://myblog.szbolent.local/%e6%9c%ac%e6%9c%ba-servbay-%e9%80%82%e9%85%8d%e8%af%b4%e6%98%8e/',
    title: '本机 ServBay 适配说明',
    excerpt:
      '站点跑在 PHP 8.4 + MySQL 8.4 + Nginx。作者按自己的方式使用主题和插件。',
    enabled: true,
    weight: 20,
  },
  {
    id: 'myblog-welcome',
    authorId: 'myblog',
    url: 'http://myblog.szbolent.local/%e6%ac%a2%e8%bf%8e%e6%9d%a5%e5%88%b0%e4%bd%9c%e8%80%85%e4%bd%9c%e5%93%81%e5%ad%90%e7%ab%99/',
    title: '欢迎来到作者作品子站',
    excerpt: '独立博客。门户只是橱窗：点进去仍回到作者自己的站。',
    enabled: true,
    weight: 10,
  },
  {
    id: 'demo-writing',
    authorId: 'demo-author',
    url: 'http://myblog.szbolent.local/%e5%86%99%e4%bd%9c%e4%bb%8e%e8%bf%99%e9%87%8c%e5%bc%80%e5%a7%8b/',
    title: '写作从这里开始',
    excerpt: '本地用来演示「另一位作者」的卡片。上架/下架只改目录，不动对方博客。',
    enabled: true,
    weight: 5,
  },
]

export interface ShowcaseCard {
  id: string
  url: string
  title: string
  excerpt?: string
  authorId: string
  authorName: string
  authorSite: string
  weight: number
}

export function listShowcaseCards(authorId?: string | null): ShowcaseCard[] {
  const authors = new Map(showcaseAuthors.map((a) => [a.id, a]))
  return showcaseWorks
    .filter((work) => work.enabled)
    .filter((work) => !authorId || work.authorId === authorId)
    .sort((a, b) => b.weight - a.weight)
    .flatMap((work) => {
      const author = authors.get(work.authorId)
      if (!author) return []
      return [
        {
          id: work.id,
          url: work.url,
          title: work.title,
          excerpt: work.excerpt,
          authorId: author.id,
          authorName: author.name,
          authorSite: author.site,
          weight: work.weight,
        },
      ]
    })
}

export function authorsWithPublishedWorks(): ShowcaseAuthor[] {
  const published = new Set(
    showcaseWorks.filter((w) => w.enabled).map((w) => w.authorId),
  )
  return showcaseAuthors.filter((a) => published.has(a.id))
}
