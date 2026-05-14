import 'dotenv/config';
import express from 'express';
import compression from 'compression';
import cors from 'cors';
import path from 'path';
import { fileURLToPath } from 'url';
import { createProxyMiddleware } from 'http-proxy-middleware';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const PORT = Number(process.env.PORT || 3000);
const HOST = process.env.HOST || '0.0.0.0';
const STATIC_DIR = process.env.STATIC_DIR || 'dist';
const API_PREFIX = process.env.API_PREFIX || '/api';
const API_PROXY_TARGET = process.env.API_PROXY_TARGET || 'https://schedule-editor-api.local/api/';

const app = express();

app.disable('x-powered-by');
app.use(compression());
app.use(cors({ origin: 'http://localhost:5173', credentials: true }));

// Proxy API requests from the frontend to the backend target
app.use(
  API_PREFIX,
  createProxyMiddleware({
    target: API_PROXY_TARGET,
    changeOrigin: true,
    cookieDomainRewrite: '',
    secure: false,
    ws: true,
    // Preserve the path following the API_PREFIX
    pathRewrite: (pathStr) => pathStr,
    // Forward cookies and credentials
    onProxyReq: (proxyReq, req) => {
      console.log(req);
      if (!proxyReq.getHeader('origin') && req.headers.origin) {
        proxyReq.setHeader('origin', req.headers.origin);
      }
    },
  })
);

// Serve static assets
const staticPath = path.resolve(__dirname, STATIC_DIR);
app.use(express.static(staticPath, { index: false, maxAge: '1h', setHeaders: (res) => res.set('Cache-Control', 'public, max-age=3600') }));

// Fallback to SPA index.html
app.get('/health', (_req, res) => {
  res.json({message: 'ИДИ НАХУЙ!!!'});
});

app.listen(PORT, HOST, () => {
  // eslint-disable-next-line no-console
  console.log(`Server listening on http://${HOST}:${PORT}`);
});

export default app;


