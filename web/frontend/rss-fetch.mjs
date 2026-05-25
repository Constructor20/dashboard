import fs from 'fs'
import https from 'https'
import http from 'http'

const [, , feedsPath, outputPath] = process.argv
const feeds = JSON.parse(fs.readFileSync(feedsPath, 'utf-8'))
const results = []

function fetch(url) {
  return new Promise((resolve, reject) => {
    const mod = url.startsWith('https') ? https : http
    const req = mod.get(url, { timeout: 10000 }, (res) => {
      let data = ''
      res.on('data', c => data += c)
      res.on('end', () => resolve(data))
    })
    req.on('error', reject)
    req.on('timeout', () => { req.destroy(); reject(new Error('timeout')) })
  })
}

function stripCDATA(s) {
  return (s || '').replace(/<!\[CDATA\[|\]\]>/g, '').trim()
}

function stripHTML(s) {
  return (s || '')
    .replace(/<[^>]+>/g, '')
    .replace(/&amp;/g, '&')
    .replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>')
    .replace(/&quot;/g, '"')
    .replace(/&#0?39;/g, "'")
    .replace(/&nbsp;/g, ' ')
    .trim()
}

function extract(xml, source) {
  const items = []
  for (const m of xml.match(/<item>[\s\S]*?<\/item>/gi) || []) {
    const title = stripCDATA((m.match(/<title[^>]*>([\s\S]*?)<\/title>/i) || [])[1])
    const link = ((m.match(/<link[^>]*>([\s\S]*?)<\/link>/i) || [])[1] || '').trim()
    const date = ((m.match(/<pubDate[^>]*>([\s\S]*?)<\/pubDate>/i) || [])[1] || '').trim()
    const desc = stripHTML(stripCDATA((m.match(/<description[^>]*>([\s\S]*?)<\/description>/i) || [])[1]))
    if (title) items.push({ title, link, pubDate: date, source, description: desc.slice(0, 300) })
  }
  for (const m of xml.match(/<entry[\s\S]*?<\/entry>/gi) || []) {
    const title = stripCDATA((m.match(/<title[^>]*>([\s\S]*?)<\/title>/i) || [])[1])
    const le = m.match(/<link[^>]*href="([^"]+)"/i)
    const link = le ? le[1].trim() : ''
    const date = ((m.match(/<updated[^>]*>([\s\S]*?)<\/updated>/i) || [])[1] || '').trim()
    const desc = stripHTML(stripCDATA(
      (m.match(/<content[^>]*>([\s\S]*?)<\/content>/i) || [])[1]
      || (m.match(/<summary[^>]*>([\s\S]*?)<\/summary>/i) || [])[1]
    ))
    if (title) items.push({ title, link, pubDate: date, source, description: desc.slice(0, 300) })
  }
  return items
}

;(async () => {
  for (const feed of feeds) {
    try {
      const xml = await fetch(feed.url)
      results.push(...extract(xml, feed.title))
    } catch (e) {}
  }
  results.sort((a, b) => new Date(b.pubDate || 0) - new Date(a.pubDate || 0))
  fs.writeFileSync(outputPath, JSON.stringify({ updated: new Date().toISOString(), items: results.slice(0, 50) }, null, 2))
  console.log('OK: ' + Math.min(results.length, 50) + ' items')
})().catch(e => { console.error(e.message); process.exit(1) })
