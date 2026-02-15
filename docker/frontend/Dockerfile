FROM node:20
WORKDIR /app/schedule-editor-app

# Install dependencies first (cache layer)
COPY schedule-editor-app/package*.json ./
RUN npm install

# Copy application sources
COPY schedule-editor-app /app/schedule-editor-app

ENV NODE_ENV=development \
    PORT=3000 \
    HOST=0.0.0.0 \
    API_PREFIX=/api \
    VITE_API_BASE=/api

EXPOSE 5173 3000

CMD sh -c "npm run serve & sleep 5 && npm run dev -- --host"
