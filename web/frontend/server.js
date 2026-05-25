import https from 'node:https'
import http from 'node:http'
import fs from 'node:fs'
import path from 'node:path'
import { fileURLToPath } from 'node:url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
const distDir = path.join(__dirname, 'dist')

const certPath = '/etc/letsencrypt/live/chrisdashboard.ddnsfree.com'

const mimeTypes = {
  '.html': 'text/html; charset=utf-8',
  '.css': 'text/css; charset=utf-8',
  '.js': 'application/javascript; charset=utf-8',
  '.json': 'application/json',
  '.png': 'image/png',
  '.jpg': 'image/jpeg',
  '.jpeg': 'image/jpeg',
  '.gif': 'image/gif',
  '.svg': 'image/svg+xml',
  '.ico': 'image/x-icon',
  '.pdf': 'application/pdf',
  '.xlsx': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
  '.woff': 'font/woff',
  '.woff2': 'font/woff2',
}

function serveStatic(req, res) {
  let urlPath = req.url === '/' ? '/index.html' : req.url.split('?')[0]
  let filePath = path.join(distDir, urlPath)

  if (!filePath.startsWith(distDir)) {
    res.writeHead(403)
    return res.end('Forbidden')
  }

  try {
    const stat = fs.statSync(filePath)
    if (stat.isDirectory()) {
      filePath = path.join(filePath, 'index.html')
    }
  } catch {
    fs.readFile(path.join(filePath, 'index.html'), (err2, data2) => {
      if (err2) {
        const fallback = path.join(distDir, 'portfolio/index.html')
        fs.readFile(fallback, (err3, data3) => {
          if (err3) {
            res.writeHead(404)
            return res.end('404 Not Found')
          }
          res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' })
          res.end(data3)
        })
        return
      }
      res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' })
      res.end(data2)
    })
    return
  }

  const ext = path.extname(filePath)
  const contentType = mimeTypes[ext] || 'application/octet-stream'

  fs.readFile(filePath, (err, data) => {
    if (err) {
      res.writeHead(404)
      return res.end('404 Not Found')
    }
    res.writeHead(200, { 'Content-Type': contentType })
    res.end(data)
  })
}

const httpServer = http.createServer(serveStatic)

let httpsServer

try {
  const options = {
    key: fs.readFileSync(path.join(certPath, 'privkey.pem')),
    cert: fs.readFileSync(path.join(certPath, 'fullchain.pem')),
  }
  httpsServer = https.createServer(options, serveStatic)
  console.log('✅ HTTPS certificates loaded (Let\'s Encrypt)')
} catch (e) {
  console.error('❌ Failed to load certs:', e.message)
  console.log('⚠️  Falling back to self-signed certificate')
  httpsServer = https.createServer({
    key: fs.readFileSync(path.join(__dirname, 'server.key')),
    cert: fs.readFileSync(path.join(__dirname, 'server.cert')),
  }, serveStatic)
}

httpServer.listen(80, () => console.log('✅ HTTP Server running on port 80 (Main)'))
httpsServer.listen(443, () => console.log('✅ HTTPS Server running on port 443'))

const httpsAlt = https.createServer({
  key: fs.readFileSync(path.join(certPath, 'privkey.pem')),
  cert: fs.readFileSync(path.join(certPath, 'fullchain.pem')),
}, serveStatic)
httpsAlt.listen(8009, () => console.log('✅ HTTPS Server running on port 8009'))

const httpAlt = http.createServer((req, res) => {
  res.writeHead(301, { Location: 'http://' + req.headers.host.split(':')[0] + req.url })
  res.end()
})
httpAlt.listen(8003, () => console.log('✅ HTTP Server running on port 8003 (→ redirect to port 80)'))

const tunnelServer = http.createServer(serveStatic)
tunnelServer.listen(8080, () => console.log('✅ Tunnel HTTP Server running on port 8080 (for Cloudflare)'))
